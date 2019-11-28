<?php

namespace Drupal\tffc;


class Tffc {

  /**
   * Method used to calculate the current points
   *
   * @param $start_time - the start time code
   * @param int $total_points - the total score that can be won
   * @param int $end_points - the very last number that is classed as a point
   *
   * @return int
   */
  public static function calculatePoints($start_time, $total_points = 0, $end_points = 0) {
    // new output variable
    $output = $total_points;

    // get the timestamp from the time field
    $time = (int) $start_time;
    $now = time();

    // find the difference between the timestamp and now
    $diff = $now - $time;

    // output to the points value - diff
    $output = $output - $diff;


    // make sure the points cannot be after the end value
    if ($output <= $end_points) {
      $output = $end_points;
    }

    // make sure the score cannot go higher than the amount set
    if ($output >= $total_points) {
      $output = $total_points;
    }

    // return the value
    return $output;
  }

}
