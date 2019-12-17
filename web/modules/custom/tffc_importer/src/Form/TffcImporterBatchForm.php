<?php

namespace Drupal\tffc_importer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures TFFC Importer Batch.
 */
class TffcImporterBatchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tffc_importer_batch_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['number'] = [
      '#type' => 'number',
      '#title' => $this->t('Number to import'),
      '#description' => $this->t('Please enter the number of films to try and import.'),
      '#default_value' => 60,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $number = $values['number'];

    \Drupal::messenger()->addMessage($number);


    $batch = [
      'title' => t('Importing Films...'),
      'operations' => [],
      'init_message' => t('Starting Import...'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message' => t('An error occurred during processing'),
      'finished' => '\Drupal\tffc_importer\TffcImporterBatch::callback',
    ];

    if (is_numeric($number) && $number > 0) {
      for ($i = 0; $i < $number; $i++) {
        $batch['operations'][] = [
          '\Drupal\tffc_importer\TffcImporterBatch::run',
          [],
        ];
      }

      batch_set($batch);
    }
  }

}
