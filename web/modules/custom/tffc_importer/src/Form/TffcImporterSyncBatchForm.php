<?php

namespace Drupal\tffc_importer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures TFFC Importer Get Details Batch.
 */
class TffcImporterSyncBatchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tffc_importer_get_details_batch_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $TffcSync = \Drupal::service('tffc.sync');
    $syncableFilms = $TffcSync->getSyncableFilms();
    $allFilms = $TffcSync->getAllFilms();
    $syncCount = count($syncableFilms);
    $filmsCount = count($allFilms);

    $form['info'] = [
      '#markup' => "<p>" . t('There are currently <i>@sync</i> films out of <i>@all</i> ready to sync extra information.', ['@sync' => $syncCount, '@all' => $filmsCount]) . "</p>",
    ];

    if ($syncCount > 0) {
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Sync all details'),
      ];
    }

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

    \Drupal::messenger()->addMessage("Trying to sync $number films . ");
  }

}
