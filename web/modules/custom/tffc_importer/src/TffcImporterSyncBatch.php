<?php

namespace Drupal\tffc_importer;


/**
 * Class TffcImporterSyncBatch
 *
 * @package Drupal\tffc_importer
 */
class TffcImporterSyncBatch {

  /**
   * @param $context
   */
  public static function run($nid, &$context) {
    $TffcImporter = \Drupal::service('tffc.sync');
    $response = $TffcImporter->syncInformation($nid);
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
          $errors = $result->getErrors();
          $success = $result->getSuccess();

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
