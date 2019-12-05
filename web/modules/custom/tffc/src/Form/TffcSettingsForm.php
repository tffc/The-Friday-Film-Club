<?php

namespace Drupal\tffc\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a form that configures TFFC settings.
 */
class TffcSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tffc_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tffc.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $tffc_config = $this->config('tffc.settings');

    $form['tffc'] = [
      '#type' => 'details',
      '#title' => t('Settings'),
      '#description' => t('Settings for configuring The Friday Film Club.'),
      '#open' => TRUE,
    ];

    $form['tffc']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enabled'),
      '#description' => t('Flag to enable/disable The Friday Film Club.'),
      '#default_value' => $tffc_config->get('enabled') ?? FALSE,
    ];

    $form['tffc']['guesses'] = [
      '#type' => 'number',
      '#title' => t('Guesses'),
      '#description' => t('The max number of guesses someone can make per question.'),
      '#default_value' => $tffc_config->get('guesses') ?? 3,
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('tffc.settings')
      ->set('enabled', $values['enabled'])
      ->set('guesses', $values['guesses'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
