<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
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
            $results = Course::where('course_year', 'LIKE', '%' . $search_term . '%')
                ->orWhere('hijri_year', 'LIKE', '%' . $search_term . '%')
                ->orWhere('semester', 'LIKE', '%' . $search_term . '%')
                ->orWhere('duration', 'LIKE', '%' . $search_term . '%')
                // ->orWhere('address', 'LIKE', '%' . $search_term . '%')
                ->orWhereHas('academicPath', function ($query) use ($search_term) {
                    $query->where('academic_paths.academic_path_name', 'like', '%' . $search_term . '%');
                })->get()->pluck('long_name', 'id');;
        } elseif ($search_term) {
            $results = Course::where('course_year', 'LIKE', '%' . $search_term . '%')
                ->orWhere('hijri_year', 'LIKE', '%' . $search_term . '%')
                ->orWhere('semester', 'LIKE', '%' . $search_term . '%')
                ->orWhere('duration', 'LIKE', '%' . $search_term . '%')
                // ->orWhere('address', 'LIKE', '%' . $search_term . '%')
                ->orWhereHas('academicPath', function ($query) use ($search_term) {
                    $query->where('academic_paths.academic_path_name', 'like', '%' . $search_term . '%');
                })
                ->paginate(10);
        } else {
            $results = Course::paginate(10);
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
        return Course::find($id);
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
