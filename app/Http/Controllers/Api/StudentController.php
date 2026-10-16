<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search_term = $request->input('q');

        // A Backpack field with include_all_form_fields (the admin message form) sends the whole
        // form: when it has a classroom, only that classroom's students are offered.
        $classRoomId = optional(collect($request->input('form', []))->firstWhere('name', 'class_room_id'))['value'] ?? null;
        $query = Student::query();
        if ($classRoomId) {
            $query->whereHas('classRooms', function ($q) use ($classRoomId) {
                $q->where('class_rooms.id', $classRoomId);
            });
        }

        if ($search_term) {
            $results = $query->where(function ($q) use ($search_term) {
                $q->where('name', 'LIKE', '%' . $search_term . '%')
                    ->orWhere('cpr', 'LIKE', '%' . $search_term . '%')
                    ->orWhere('mobile', 'LIKE', '%' . $search_term . '%')
                    ->orWhere('mobile2', 'LIKE', '%' . $search_term . '%')
                    ->orWhere('address', 'LIKE', '%' . $search_term . '%');
            })->paginate(10);
        } else {
            $results = $query->paginate(10);
        }

        // Only what the select2 fields show: no address, and no per-row `courses` query.
        $results->getCollection()->each->setVisible(['id', 'long_name']);

        return $results;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Student::find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
