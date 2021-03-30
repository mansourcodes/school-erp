<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use Illuminate\Http\Request;

class ClassRoomsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $search_term = $request->input('q');
        $term = $request->input('term');
        if ($term) {
            $results = ClassRoom::where('class_room_name', 'LIKE', '%' . $search_term . '%')
                ->orWhere('class_room_number', 'LIKE', '%' . $search_term . '%');
        } elseif ($search_term) {
            $results = ClassRoom::where('class_room_name', 'LIKE', '%' . $search_term . '%')
                ->orWhere('class_room_number', 'LIKE', '%' . $search_term . '%')
                ->paginate(10);
        } else {
            $results = ClassRoom::paginate(10);
        }

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
        return ClassRoom::find($id);
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
