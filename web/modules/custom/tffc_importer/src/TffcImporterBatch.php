<?php

namespace Drupal\tffc_importer;


/**
 * Class TffcImporterBatch
 *
 * @package Drupal\TffcImporterBatch
 */
class TffcImporterBatch {

  /**
   * @param $context
   */
  public static function run(&$context) {
    $TffcImporter = \Drupal::service('tffc.importer');
    $response = $TffcImporter->run();
    $context['results'][] = $response;
  }


  /**
   * @param $success
   * @param $results
   * @param $operations
   */
  public static function callback($success, $results, $operations) {

    if ($success) {

      if (!empty($results)) {
        foreach ($results as $result) {
          $errors = $result['errors'];
          $success = $result['success'];
          $info = $result['info'];

          if (count($errors) > 0) {
            foreach ($errors as $e) {
              \Drupal::messenger()
                ->addMessage($e, \Drupal::messenger()::TYPE_ERROR);
            }
          }

          if (count($success) > 0) {
            foreach ($success as $s) {
              \Drupal::messenger()
                ->addMessage($s, \Drupal::messenger()::TYPE_STATUS);
            }
          }

          if (count($info) > 0) {
            foreach ($info as $i) {
              \Drupal::messenger()
                ->addMessage($i, \Drupal::messenger()::TYPE_WARNING);
            }
          }
        }
      }
    }
    else {
      $error_operation = reset($operations);
      \Drupal::messenger()
        ->addMessage(t('An error occurred while processing @operation with arguments : @args', [
          '@operation' => $error_operation[0],
          '@args' => print_r($error_operation[0], TRUE),
        ]), \Drupal::messenger()::TYPE_ERROR);
    }
  }

}
