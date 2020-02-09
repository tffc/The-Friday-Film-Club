<?php

namespace Drupal\tffc_validation\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a form that configures TFFC Importer settings.
 */
class TffcValidationSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tffc_validation_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tffc_validation.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $tffc_config = $this->config('tffc_validation.settings');

    $form['validation'] = [
      '#type' => 'details',
      '#title' => t('Validation Settings'),
      '#open' => TRUE,
    ];

    $form['validation']['validated_count'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => t('Validated count'),
      '#description' => t('The number of successful validations for a film to be counted as validated.'),
      '#default_value' => $tffc_config->get('validated_count') ?? TFFC_VALIDATION_NUM,
    ];


    $form['validation']['issues_count'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => t('Validated count'),
      '#description' => t('The number of issues for a film to be counted as invalid.'),
      '#default_value' => $tffc_config->get('issues_count') ?? TFFC_VALIDATION_ISSUE_NUM,
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('tffc_validation.settings')
      ->set('validated_count', $values['validated_count'])
      ->set('issues_count', $values['issues_count'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
