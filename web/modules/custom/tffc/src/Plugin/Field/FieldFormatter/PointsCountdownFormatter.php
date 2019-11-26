<?php

namespace Drupal\tffc\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_tffc_points_formatter_value' formatter.
 *
 * @FieldFormatter(
 *   id = "field_tffc_points_countdown_formatter",
 *   label = @Translation("Points Countdown"),
 *   field_types = {
 *     "field_tffc_points"
 *   }
 * )
 */
class PointsCountdownFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the points value counting down.');
    $summary[] = $this->t('Realtime enabled: @realtime',['@realtime' => $this->getSetting('realtime') ? 'True' : 'False']);
    $summary[] = $this->t('Calculate enabled: @calculate',['@calculate' => $this->getSetting('calculate') ? 'True' : 'False']);
    $summary[] = $this->t('Time set to: @time',['@time' => $this->getTime()]);
    $summary[] = $this->t('End value set to: @end',['@end' =>$this->getSetting('end')]);
    $summary[] = $this->t('Speed value set to: @speed (ms)',['@speed' =>$this->getSetting('speed')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'points_countdown',
        '#total_points' => $this->calculatePoints($item->total),
        '#options' => [
          'realtime' => $this->getSetting('realtime'),
          'calculate' => $this->getSetting('calculate'),
          'time' => $this->getSetting('time'),
          'end' => $this->getSetting('end'),
          'speed' => $this->getSetting('speed'),
        ],
      ];

      if ($this->getSetting('calculate')) {
        $element[$delta]['#cache'] = [
          'max-age' => 0,
        ];
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'realtime' => 0,
        'calculate' => 0,
        'time' => '',
        'end' => 0,
        'speed' => 1000,
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['realtime'] = [
      '#title' => $this->t('Realtime'),
      '#type' => 'checkbox',
      '#description' => $this->t('If enabled the points value will countdown.'),
      '#default_value' => $this->getSetting('realtime'),
    ];

    $form['calculate'] = [
      '#title' => $this->t('Calculate points'),
      '#type' => 'checkbox',
      '#description' => $this->t('Calculate the points remaining based upon the number of seconds passed from time field.'),
      '#default_value' => $this->getSetting('calculate'),
    ];

    $form['time'] = [
      '#title' => $this->t('Timestamp'),
      '#type' => 'textfield',
      '#description' => $this->t('Used with the calculate points field to calculate the number of seconds from a certain time stamp.<br>Enter a timestamp, can use tokens such as <code>[node:created:raw]</code>'),
      '#default_value' => $this->getSetting('time'),
    ];

    $form['end'] = [
      '#title' => $this->t('End value'),
      '#type' => 'number',
      '#description' => $this->t('The points value where it should end the countdown.'),
      '#default_value' => $this->getSetting('end'),
    ];

    $form['speed'] = [
      '#title' => $this->t('Speed (ms)'),
      '#type' => 'number',
      '#description' => $this->t('The speed in milliseconds, indicating how fast it should countdown.'),
      '#default_value' => $this->getSetting('speed'),
    ];

    return $form;
  }

  /**
   * Method used to calculate the current points
   *
   * @param int $points
   *
   * @return int
   */
  private function calculatePoints($points = 0) {
    // get some params
    $calculate = $this->getSetting('calculate');
    $end = $this->getSetting('end');

    // if calculate is not on stop
    if (!$calculate) {
      return $points;
    }

    // new output variable
    $output = $points;

    // get the timestamp from the time field
    $time = (int)$this->getTime();
    $now = time();

    // find the difference between the timestamp and now
    $diff = $now - $time;

    // output to the points value - diff
    $output = $output - $diff;

    // make sure the points cannot be after the end value
    if($output <= $end){
      $output = $end;
    }

    // make sure the score cannot go higher than the amount set
    if($output >= $points){
      $output = $points;
    }

    // return the value
    return $output;
  }

  /**
   * Get the time value while passing it through tokens
   *
   * @return string
   */
  protected function getTime() {
    $node = \Drupal::routeMatch()->getParameter('node');
    $token_service = \Drupal::token();
    $time = $this->getSetting('time');
    return $token_service->replace($time, ['node' => $node]);
  }

}
