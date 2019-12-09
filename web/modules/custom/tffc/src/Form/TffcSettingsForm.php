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


    $form['replies_good'] = [
      '#type' => 'details',
      '#title' => t('Positive responses'),
      '#description' => t('The responses that will be selected at random if the the correct answer is selected.'),
      '#open' => FALSE,
    ];

    $form['replies_good']['positive_response'] = [
      '#type' => 'textarea',
      '#title' => t('Positive responses'),
      '#description' => t('Enter a comma separated list of responses.'),
      '#default_value' => $tffc_config->get('positive_response') ?? 'Well done, Congrats, Yes!',
    ];

    $form['replies_bad'] = [
      '#type' => 'details',
      '#title' => t('Negative responses'),
      '#description' => t('The negative that will be selected at random if the correct answer is wrong.'),
      '#open' => FALSE,
    ];

    $form['replies_bad']['negative_response'] = [
      '#type' => 'textarea',
      '#title' => t('Negative responses'),
      '#description' => t('Enter a comma separated list of responses.'),
      '#default_value' => $tffc_config->get('negative_response') ?? 'Wrong, Incorrect, Nope',
    ];


    $form['replies_limit'] = [
      '#type' => 'details',
      '#title' => t('Finished responses'),
      '#description' => t('The response that will be selected at random if the run out of guesses.'),
      '#open' => FALSE,
    ];

    $form['replies_limit']['finished_response'] = [
      '#type' => 'textarea',
      '#title' => t('Finished responses'),
      '#description' => t('Enter a comma separated list of responses.'),
      '#default_value' => $tffc_config->get('finished_response') ?? 'Out of guesses, Sorry you did not get it this time, Better luck next time',
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
      ->set('positive_response', $values['positive_response'])
      ->set('negative_response', $values['negative_response'])
      ->set('finished_response', $values['finished_response'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
