<?php

namespace Drupal\tffc_validation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ValidationController extends ControllerBase {

  /**
   * Field that holds a list of users that marked as valid
   *
   * @var string
   */
  private $field_validators = 'field_validator';

  /**
   * Field that holds a list of users that marked as invalid
   *
   * @var string
   */
  private $field_invalidators = 'field_invalid_user';


  /**
   * Field that holds a list of users that marked as skipped
   *
   * @var string
   */
  private $field_skip = 'field_skipped_validation';

  /**
   * Field that holds the reasons why a user marked a film invalid
   *
   * @var string
   */
  private $field_reasons = 'field_invalid_reasons';

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function page(Request $request) {

    $data = $this->load_json('/films/json/validation/' . $this->current_uid());
    $id = 0;

    if (isset($data[0]) && isset($data[0]['id'])) {
      $id = $data[0]['id'];
    }

    return $this->redirect('view.validation.validation_page', [
      'nid' => $id,
    ]);
  }

  /**
   * Mark this film as passing validation
   *
   * @param \Drupal\node\NodeInterface $node
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function success(NodeInterface $node, Request $request) {
    $this->is_type_film($node);

    if ($this->has_already_validated($node)) {
      \Drupal::messenger()
        ->addStatus(t('You have already validated this film.'));
      return $this->redirect_back();
    }

    $node->{$this->field_validators}[] = ['target_id' => $this->current_uid()];
    try {
      $node->save();
    } catch (EntityStorageException $e) {
      die($e->getMessage());
    }

    \Drupal::messenger()
      ->addStatus(t('Thank you, marked film as valid.'));
    return $this->redirect_back();
  }

  /**
   * Mark this film as skipped
   *
   * @param \Drupal\node\NodeInterface $node
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function skip(NodeInterface $node, Request $request) {
    $this->is_type_film($node);

    if ($this->has_already_validated($node)) {
      \Drupal::messenger()
        ->addStatus(t('You have already validated this film.'));
      return $this->redirect_back();
    }

    $node->{$this->field_skip}[] = ['target_id' => $this->current_uid()];
    try {
      $node->save();
    } catch (EntityStorageException $e) {
      die($e->getMessage());
    }

    \Drupal::messenger()->addStatus(t('You have skipped validating the film.'));
    return $this->redirect_back();
  }


  /**
   * Mark this film as having an issue
   *
   * @param \Drupal\node\NodeInterface $node
   * @param \Drupal\taxonomy\TermInterface $term
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function issue(NodeInterface $node, TermInterface $term, Request $request) {
    $this->is_type_film($node);
    $this->is_issue_taxonomy($term);

    if ($this->has_already_validated($node)) {
      \Drupal::messenger()
        ->addStatus(t('You have already validated this film.'));
      return $this->redirect_back();
    }

    $node->{$this->field_invalidators}[] = ['target_id' => $this->current_uid()];
    $node->{$this->field_reasons}[] = ['target_id' => $term->id()];
    try {
      $node->save();
    } catch (EntityStorageException $e) {
      die($e->getMessage());
    }

    \Drupal::messenger()
      ->addStatus(t('Thank you, marked film as having some issues.'));
    return $this->redirect_back();
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

  private function is_issue_taxonomy(TermInterface $term) {
    if ("invalid_reasons" !== $term->bundle()) {
      throw new NotFoundHttpException();
    }
    return TRUE;
  }

  /**
   * @param \Drupal\node\NodeInterface $node
   *
   * @return bool
   */
  private function has_already_validated(NodeInterface $node) {
    $uid = $this->current_uid();

    $validators = $node->get($this->field_validators)->getValue();
    $ids = array_column($validators, 'target_id', 'target_id');
    if (isset($ids[$uid])) {
      return TRUE;
    }

    $invalidators = $node->get($this->field_invalidators)->getValue();
    $ids = array_column($invalidators, 'target_id', 'target_id');
    if (isset($ids[$uid])) {
      return TRUE;
    }

    $skipped = $node->get($this->field_skip)->getValue();
    $ids = array_column($skipped, 'target_id', 'target_id');
    if (isset($ids[$uid])) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Redirects back to the validation page which will then sort out what to do
   * next
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  private function redirect_back() {
    return $this->redirect('tffc_validation.page');
  }

  /**
   * @param $url
   *
   * @return mixed
   */
  private function load_json($url) {
    $host = \Drupal::request()->getSchemeAndHttpHost();
    $url = $host . $url;
    return json_decode(file_get_contents($url), TRUE);
  }

  /**
   * @return int
   */
  private function current_uid() {
    return \Drupal::currentUser()->id();
  }

}
