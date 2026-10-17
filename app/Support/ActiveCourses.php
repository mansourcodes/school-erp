<?php

namespace App\Support;

use App\Models\Course;

/**
 * Active courses (`courses.is_active`) grouped by term (semester + hijri year), for the term chips in the
 * admin navbar (inc/main_header).
 */
class ActiveCourses
{
    /** @return array<int, array{label: string, count: int, names: string[]}> */
    public static function terms()
    {
        $courses = Course::with('academicPath')->where('is_active', true)
            ->orderByDesc('hijri_year')->orderByDesc('start_date')->get();

        return $courses->groupBy(function ($course) {
            return trim($course->semester).'|'.trim($course->hijri_year);
        })->map(function ($group) {
            $first = $group->first();

            return [
                'label' => __('base.active_term', ['semester' => trim($first->semester), 'year' => trim($first->hijri_year)]),
                'count' => $group->count(),
                'names' => $group->pluck('long_name')->all(),
            ];
        })->values()->all();
    }
}
