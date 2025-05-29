<?php

namespace App\Helpers;


class BriefMarkHelper
{



  static function composeMark($curriculum_mark_template, $student_mark, $compress_type, $key)
  {
    return BriefMarkHelper::{'processAs' . $compress_type}($curriculum_mark_template, $student_mark, $key);
  }


  private static function processAsAvgMerge($curriculum_mark_template, $student_mark, $key)
  {
    $result = [];

    $student_mark = collect($student_mark);
    $curriculum_mark_template = collect($curriculum_mark_template);

    $student_total = $student_mark->avg('mark');
    $curriculum_total = $curriculum_mark_template->avg('mark');


    $result = (object)[
      'label' => $key,
      'mark' => $student_total,
      'max' => $curriculum_total
    ];

    return $result;
  }

  private static function processAsSumMerge($curriculum_mark_template, $student_mark, $key)
  {
    $result = [];

    $student_mark = collect($student_mark);
    $curriculum_mark_template = collect($curriculum_mark_template);

    $student_total = $student_mark->sum('mark');
    $curriculum_total = $curriculum_mark_template->sum('mark');


    $result = (object)[
      'label' => $key,
      'mark' => $student_total,
      'max' => $curriculum_total
    ];

    return $result;
  }


  private static function processAsNone($curriculum_mark_template, $student_mark, $key)
  {
    return [];
  }


  private static function processAsSingle($curriculum_mark_template, $student_mark, $key)
  {


    $result = [];
    foreach ($curriculum_mark_template as $_key => $max_mark) {
      $result[$_key] = (object)[
        'max' => $max_mark->mark,
        'mark' => $student_mark[$_key]->mark,
        'label' => $max_mark->label
      ];
    }
    return $result;
  }
}
