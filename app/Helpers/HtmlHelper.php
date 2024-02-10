<?php

namespace App\Helpers;


class HtmlHelper
{

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
