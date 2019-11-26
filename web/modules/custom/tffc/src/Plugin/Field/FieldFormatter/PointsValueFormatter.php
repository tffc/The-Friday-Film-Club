<?php

namespace Drupal\tffc\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'field_tffc_points_value_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "field_tffc_points_value_formatter",
 *   label = @Translation("Points Value"),
 *   field_types = {
 *     "field_tffc_points"
 *   }
 * )
 */
class PointsValueFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the points value.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#markup' => $item->total
      ];
    }

    return $element;
  }

}
