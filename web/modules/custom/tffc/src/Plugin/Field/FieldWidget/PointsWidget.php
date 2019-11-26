<?php

namespace Drupal\tffc\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_tffc_points_widget' widget.
 *
 * @FieldWidget(
 *   id = "field_tffc_points_widget",
 *   label = @Translation("Points Select"),
 *   field_types = {
 *     "field_tffc_points"
 *   }
 * )
 */
class PointsWidget extends WidgetBase {

  /**
   * Define the form for the field type.
   *
   * Inside this method we can define the form used to edit the field type.
   *
   */
  public function formElement(FieldItemListInterface $items, $delta, Array $element, Array &$form, FormStateInterface $formState) {

    $element['total'] = [
      '#type' => 'number',
      '#title' => t('Points'),
      '#default_value' => $items[$delta]->total ?? NULL,
      '#empty_value' => '',
      '#placeholder' => t('Points amount'),
    ];

    return $element;
  }

}
