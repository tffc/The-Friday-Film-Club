<?php

namespace Drupal\tffc\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface as StorageDefinition;

/**
 * Plugin implementation of the 'field_tffc_points' field type.
 *
 * @FieldType(
 *   id = "field_tffc_points",
 *   label = @Translation("Points"),
 *   module = "tffc",
 *   description = @Translation("Calculates points value based upon it's
 *   settings."), category = @Translation("The Friday Film Club"),
 *   default_widget = "field_tffc_points_widget",
 *   default_formatter = "field_tffc_points_value_formatter"
 * )
 */
class Points extends FieldItemBase {

  /**
   * Field type properties definition.
   *
   * Inside this method we defines all the fields (properties) that our
   * custom field type will have.
   *
   */
  public static function propertyDefinitions(StorageDefinition $storage) {

    $properties = [];

    $properties['total'] = DataDefinition::create('integer')
      ->setLabel(t('Points'));

    return $properties;
  }

  /**
   * Field type schema definition.
   *
   * Inside this method we defines the database schema used to store data for
   * our field type.
   *
   */
  public static function schema(StorageDefinition $storage) {

    $columns = [];
    $columns['total'] = [
      'type' => 'int',
    ];

    return [
      'columns' => $columns,
      'indexes' => [],
    ];
  }

  /**
   * Define when the field type is empty.
   *
   * This method is important and used internally by Drupal. Take a moment
   * to define when the field fype must be considered empty.
   */
  public function isEmpty() {
    $value = $this->get('total')->getValue();
    return $value === NULL || $value === '';
  }

}
