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


      // get mark where label is equal to max_mark label
      $gain_mark = collect($student_mark)->where('label', $max_mark->label)->first();
      if ($gain_mark === null) {
        // if no gain mark found, set it to 'n/a'
        $result[] = (object)[
          'max' => $max_mark->mark,
          'mark' => 'n/a',
          'label' => $max_mark->label
        ];
      } else {
        try {
          $result[] = (object)[
            'max' => $max_mark->mark,
            'mark' => $gain_mark->mark,
            'label' => $max_mark->label
          ];
        } catch (\Throwable $th) {
          //throw $th;
          dd($gain_mark);
        }
      }
    }

    return $result;
  }
}
