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
//    $tffc_config = $this->config('tffc_validation.settings');
//
//    $form['general'] = [
//      '#type' => 'details',
//      '#title' => t('General Settings'),
//      '#open' => TRUE,
//    ];
//
//    $form['general']['enabled'] = [
//      '#type' => 'checkbox',
//      '#title' => t('Enabled'),
//      '#description' => t('Flag to enable/disable importer.'),
//      '#default_value' => $tffc_config->get('enabled') ?? TFFC_IMPORT_ENABLED,
//    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('tffc_validation.settings')
      ->set('enabled', $values['enabled'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
