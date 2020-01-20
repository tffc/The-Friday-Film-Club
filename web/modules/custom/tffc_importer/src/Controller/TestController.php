<?php

namespace Drupal\tffc_importer\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class TestController
 *
 * @package Drupal\tffc_importer\Controller
 */
class TestController extends ControllerBase {

  /**
   * Returns a test page.
   *
   * @return array
   */
  public function testPage() {

    //    $TffcSync = \Drupal::service('tffc.sync');
    //    $syncableFilms = $TffcSync->getSyncableFilms();
    //    $TffcSync->syncInformation(114);
    //    $errors = $TffcSync->getErrors();

    $movieScreencaps = \Drupal::service('tffc.screencaps');
    $img = $movieScreencaps->search('Avengers');

    //    if (!empty($syncableFilms)) {
    //      foreach ($syncableFilms as $nid) {
    //        $TffcSync->syncInformation($nid);
    //      }
    //    }


    $element = [
      //      '#markup' => "<pre>" . print_r($syncableFilms, TRUE) . print_r($errors, TRUE) . "</pre>",
      '#markup' => $img ? "<img src='$img'>" : "No Image found",
    ];
    return $element;
  }

}
