<?php

namespace Drupal\tffc_importer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\tffc_importer\TffcImporter;

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

    $tffcImporter = new TffcImporter();
    $films = [];
//    $films = $tffcImporter->getFilm('tt0246578');
//    $tffcImporter->addToDrupal();


    $element = [
      '#markup' => "<pre>" . print_r($films, TRUE) . "</pre>",
    ];
    return $element;
  }

}
