<?php

namespace App\Http\Controllers\Pull;

use App\Http\Controllers\Controller;
use App\Models\Account\Payment;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Old\OldClassRoom;
use App\Models\Old\OldPayment;
use App\Models\Old\OldStudent;
use App\Models\Student;
use Backpack\Settings\app\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Class 
 */
class OldToNewDbController extends Controller
{

    /**
     * Title: 
     *
     * @return view
     */
    public function pullClassRoom(Request $request)
    {



        $validator = Validator::make($request->all(), [
            'old_course_id' => 'required|integer',
            'course_id' => 'required|integer',
            'today_date' => ['required',  Rule::in([date("d")])],
            'fresh' => [
                'required',
                Rule::in(['yes', 'no']),
            ],
        ]);

        if ($validator->fails()) {
            abort(403, 'missing ?old_course_id=@&course_id=@&today_date=day_of_month&fresh=no');
        }

        // Retrieve the validated input...
        $validated = $validator->validated();

        if ($validated['fresh'] == 'yes') {
            $this->emptyTables();
        }
        $page = 0;
        $limit = 40;

        $old_course_id = $validated['old_course_id'];
        $course_id = $validated['course_id'];

        $oldClassRooms = OldClassRoom::where('semester', $old_course_id)->skip($page * $limit)->take($limit)->get();

        foreach ($oldClassRooms as $key => $oldClassRoom) {
            $this->pushClassRoom($oldClassRoom, $old_course_id, $course_id);
        }



        $this->updateSetting();

        dd('Done');
    }
    /**
     * Title: 
     *
     * @return view
     */
    public function emptyTables()
    {
        Payment::whereNotNull('id')->forceDelete();
        ClassRoom::whereNotNull('id')->forceDelete();
        Student::whereNotNull('id')->forceDelete();
    }

    /**
     * Title: 
     *
     * @return view
     */
    public function updateSetting()
    {

        //update payment_types
        $payments = Payment::all();

        $payment_types = Setting::where('key', 'payment_types')->first();
        $payment_types_array = json_decode($payment_types->value);
        $payment_types_array = Arr::pluck($payment_types_array, 'key');
        $used_payment_types = array_unique($payments->pluck('type')->toArray());
        $new_payment_types = array_unique(array_merge($payment_types_array, $used_payment_types));

        $new_pp = [];
        foreach ($new_payment_types as $key => $value) {
            $new_pp[] = [
                'key' => $value
            ];
        }
        $new_pp = json_encode($new_pp);
        $payment_types->value = $new_pp;
        $payment_types->save();
    }



    /**
     * Title: 
     *
     * @return view
     */
    public function pushClassRoom($oldClassRoom, $old_course_id, $course_id)
    {

        if (empty($oldClassRoom->students)) {
            return;
        }

        $teacher = OldStudent::find($oldClassRoom->teacher);

        //find curriculum id
        switch ($oldClassRoom->level) {
            case 'التأهيلي':
                $curriculum = Curriculum::Where('short_name', 'حفظ')->firstOrFail();
                break;

            default:
                $curriculum = Curriculum::Where('short_name', $oldClassRoom->level)->firstOrFail();
                break;
        }

        try {
            //code...
            // start & end time 
            $re = '/\d+\:\d\d/m';
            preg_match_all($re, $oldClassRoom->time, $matches, PREG_SET_ORDER, 0);

            $start_time = str_pad($matches[0][0], 5, "0", STR_PAD_LEFT);
            $end_time = str_pad($matches[1][0], 5, "0", STR_PAD_LEFT);


            if (strpos($oldClassRoom->time, 'صباحا') > -1) {
                // do nothing
            } else if (strpos($oldClassRoom->time, 'ظهرا') > -1) {
                $start_hour = substr($start_time, 0, 2);
                if ((int)$start_hour < 12) {
                    $start_hour += 12;
                    $start_time = $start_hour + substr($start_time, 2);
                }

                $end_hour = substr($end_time, 0, 2);
                if ((int)$end_hour < 12) {
                    $end_hour += 12;
                    $end_time = $end_hour . substr($end_time, 2);
                }
            } else {
                dd($oldClassRoom->time);
            }
        } catch (\Throwable $th) {
            // throw $th;
            // dd($matches, "match not find time");
            $start_time = "01:00";
            $end_time = "01:01";
        }

        // days
        if (strpos($oldClassRoom->day, 'الجمعة') > -1) {
            $day_1 = 5; // الجمعة
            $day_2 = 6; // السبت
        } else {
            $day_1 = 1; // الاثنين
            $day_2 = 3; // الاربعا
        }



        $classRoom = new ClassRoom();
        $classRoom->course_id =     $course_id;
        $classRoom->class_room_number =     $oldClassRoom->room;
        $classRoom->class_room_name =     $oldClassRoom->location;
        $classRoom->teachers =  json_decode('[{"curriculum_id":"' . $curriculum->id . '","teacher_name":"' . $teacher->name . '"}]');
        $classRoom->attend_table =  json_decode('[{"curriculum_id":"' . $curriculum->id . '","day":"' . $day_1 . '","start_time":"' . $start_time . '","end_time":"' . $end_time . '"},{"curriculum_id":"' . $curriculum->id . '","day":"' . $day_2 . '","start_time":"' . $start_time . '","end_time":"' . $end_time . '"}]');
        $classRoom->save();

        // [{"curriculum_id":"9","day":"5","start_time":"10:36","end_time":"10:37"},{"curriculum_id":"9","day":"6","start_time":"11:37","end_time":"23:37"}]

        $oldStudents = OldStudent::whereIn('id', explode(',', $oldClassRoom->students))->get();


        $students = [];
        $payments = [];
        foreach ($oldStudents as $key => $old) {

            $mobile = (empty($old->mobile)) ? $old->id : $old->mobile;

            $student = Student::where([
                ['mobile', $mobile],
                ['name', $old->name]
            ])->first();

            if (!$student) {
                //* new student
                $student = new Student();
                if (strlen($old->cpr) < 8) {
                    $student->cpr = '99999' . $old->mobile . rand(111, 999);
                } else {
                    $student->cpr = str_pad($old->cpr, 9, '0', STR_PAD_LEFT);
                }
                $student->password = Hash::make($old->cpr);


                $student->name = $old->name;
                $student->email = $old->email;
                $student->mobile = $mobile;
                $student->mobile2 = (empty($old->phone)) ? null : $old->phone;
                $student->dob = null;
                $student->address = $old->address;
                $student->live_in_state = 'UNKNOWN';
                $student->relationship_state = 'SINGLE';
                $student->family_members = 0;
                $student->family_depends = 0;
                $student->degree = null;
                $student->hawza_history = 0;
                $student->hawza_history_details = null;
                $student->health_history = 0;
                $student->health_history_details = null;
                $student->financial_state = 'UNKNOWN';
                $student->financial_details = null;
                $student->student_notes = '';
                $student->registration_at =  Carbon::now();
                $student->save();
            }
            $students[] = $student;

            //*

            $oldPayments = OldPayment::where([
                ['student', $old->id],
                ['semester', $old_course_id],
            ])->get();

            if (count($oldPayments)) {

                foreach ($oldPayments as $key_p => $old_p) {
                    $payment = new Payment();

                    $payment->course_id = $course_id;
                    $payment->student_id = $student->id;
                    $payment->amount = $old_p->fee;
                    $payment->source = 'cash';
                    $payment->type = $old_p->type;
                    $payment->save();

                    $payments[] = $payment;
                }
            }

            //*/
        }
        // $payments = new Collection($payments);

        $students = new Collection($students);
        $classRoom->students()->sync($students->pluck('id')->toArray());
        $classRoom->save();
    }
}
