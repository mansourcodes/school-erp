<?php

namespace App\Http\Controllers\Pull;

use App\Http\Controllers\Controller;
use App\Models\Account\Payment;
use App\Models\ClassRoom;
use App\Models\Course;
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

        $this->emptyTables();
        $page = 0;
        $limit = 40;

        $old_course_id = 38;
        $course_id = 1;

        $oldClassRooms = OldClassRoom::where('semester', $old_course_id)->skip($page * $limit)->take($limit)->get();

        foreach ($oldClassRooms as $key => $oldClassRoom) {
            $this->pushClassRoom($oldClassRoom, $old_course_id, $course_id);
        }



        $this->updateSetting();

        dd(1);
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

        $classRoom = new ClassRoom();
        $classRoom->course_id =     $course_id;
        $classRoom->class_room_number =     $oldClassRoom->room;
        $classRoom->class_room_name =     $oldClassRoom->location;
        $classRoom->teachers =  json_decode('[{"curriculum\u0640id":"1","teacher_name":"' . $teacher->name . '"}]');
        $classRoom->attend_table =  json_decode('[{"curriculum\u0640id":"1","day":"5","start_time":"01:00"},{"curriculum\u0640id":"1","day":"6","start_time":"01:00"}]');
        $classRoom->save();


        $oldStudents = OldStudent::whereIn('id', explode(',', $oldClassRoom->students))->get();


        $students = [];
        $payments = [];
        foreach ($oldStudents as $key => $old) {


            //*
            $student = new Student();
            if (strlen($old->cpr) < 8) {
                $student->cpr = '99999' . $old->mobile . rand(111, 999);
            } else {
                $student->cpr = str_pad($old->cpr, 9, '0', STR_PAD_LEFT);
            }


            $student->student_name = $old->name;
            $student->email = $old->email;
            $student->mobile =  (empty($old->mobile)) ? null : $old->mobile;
            $student->mobile2 = (empty($old->phone)) ? null : $old->phone;
            $student->dob = null;
            $student->address = $old->address;
            $student->live_inـstate = 'UNKNOWN';
            $student->relationshipـstate = 'SINGLE';
            $student->family_members = 0;
            $student->family_depends = 0;
            $student->degree = null;
            $student->hawzaـhistory = 0;
            $student->hawzaـhistory_details = null;
            $student->healthـhistory = 0;
            $student->healthـhistory_details = null;
            $student->financialـstate = 'UNKNOWN';
            $student->financial_details = null;
            $student->student_notes = '';
            $student->registration_at =  Carbon::now();
            $student->save();

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
