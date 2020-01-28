<?php

namespace Drupal\tffc_validation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ValidationController extends ControllerBase {

  public function success(NodeInterface $node, Request $request) {
    dpm($node);

    $this->is_type_film($node);

    return [
      '#markup' => 'success',
    ];
  }

  public function skip(NodeInterface $node, Request $request) {
    dpm($node);

    $this->is_type_film($node);

    return [
      '#markup' => 'skip',
    ];
  }

  public function issue() {

  }

  /**
   * @param \Drupal\node\NodeInterface $node
   *
   * @return bool
   */
  private function is_type_film(NodeInterface $node) {
    if ("film" !== $node->bundle()) {
      throw new NotFoundHttpException();
    }
    return TRUE;
  }

  private function redirect_back() {

  }

}
