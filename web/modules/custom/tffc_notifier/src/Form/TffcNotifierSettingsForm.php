<?php

namespace Drupal\tffc_notifier\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a form that configures TFFC Notifier settings.
 */
class TffcNotifierSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tffc_notifier_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tffc_notifier.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $tffc_config = $this->config('tffc_notifier.settings');

    $form['general'] = [
      '#type' => 'details',
      '#title' => t('General Settings'),
      '#open' => TRUE,
    ];

    $form['general']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enabled'),
      '#description' => t('Flag to enable/disable notifier.'),
      '#default_value' => $tffc_config->get('enabled') ?? TFFC_NOTIFIER_ENABLED,
    ];

    $form['general']['roles'] = [
      '#type' => 'checkboxes',
      '#title' => t('Roles'),
      '#options' => user_role_names(),
      '#description' => t('The roles that should be sent the notifications when a question is published.'),
      '#default_value' => $tffc_config->get('roles') ?? [],
    ];

    $form['email'] = [
      '#type' => 'details',
      '#title' => t('Email Settings'),
      '#open' => TRUE,
    ];

    $form['email']['subject'] = [
      '#type' => 'textfield',
      '#title' => t('Subject'),
      '#description' => t('Email subject.'),
      '#default_value' => $tffc_config->get('subject') ?? '',
    ];

    $form['email']['message'] = [
      '#type' => 'text_format',
      '#title' => t('Message'),
      '#description' => t('The message to send when the content goes live.'),
      '#default_value' => $tffc_config->get('message')['value'] ?? '',
      '#format' => $tffc_config->get('message')['format'] ?? 'full_html',
    ];

    $form['email']['token_tree'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['user', 'node'],
      '#show_restricted' => TRUE,
      '#show_nested' => FALSE,
      '#weight' => 90,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('tffc_notifier.settings')
      ->set('enabled', $values['enabled'])
      ->set('roles', $values['roles'])
      ->set('subject', $values['subject'])
      ->set('message', $values['message'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
