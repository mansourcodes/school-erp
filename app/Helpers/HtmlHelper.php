<?php

namespace App\Helpers;


class HtmlHelper
{





  static public function dropdownMenuButton($list, $title = '')
  {

    $links = [];
    foreach ($list as $key => $value) {
      $links[] = '<a class="dropdown-item" target="_blank" href="' . $value['url']   . '">' . $value['label'] . '</a>';
    }


    return '<div class="btn-group">
        <div class="dropdown">
          <button class="btn btn-primary btn-sm dropdown-toggle" id="dropdownMenuButton" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="la la-print"></i> ' . $title . '
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="">

                    ' . implode('', $links) . '

          </div>
        </div>
      </div>';
  }


  static function getDaysInRange($day_number, $dateFromString, $dateToString)
  {
    $dateFrom = new \DateTime($dateFromString);
    $dateTo = new \DateTime($dateToString);
    $dates = [];

    $week_days = [
      7 => 'Sunday',
      1 => 'Monday',
      2 => 'Tuesday',
      3 => 'Wednesday',
      4 => 'Thursday',
      5 => 'Friday',
      6 => 'Saturday',
    ];

    if ($dateFrom > $dateTo) {
      return $dates;
    }

    $dateFrom->modify('next ' . $week_days[$day_number]);

    while ($dateFrom <= $dateTo) {
      $dates[] = $dateFrom->format('Y-m-d');
      $dateFrom->modify('+1 week');
    }

    return $dates;
  }
}
