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
    $unsyncableFilms = $TffcSync->getUnsyncableFilms();
    $allFilms = $TffcSync->getAllFilms();

    $syncCount = count($syncableFilms);
    $unsyncCount = count($unsyncableFilms);
    $filmsCount = count($allFilms);


    $text = t('<strong>@all</strong> total films found in the system.', [
      '@all' => $filmsCount,
    ]);

    $text .= "<br>" . t('There are <strong>@sync</strong> films ready for syncing.', [
        '@sync' => $syncCount,
      ]);

    if ($unsyncCount > 0) {
      $text .= "<br>" . t('There are also <strong>@unsync</strong> films that we tried to sync but to complete.', [
          '@unsync' => $unsyncCount,
        ]);
    }

    $form['info'] = [
      '#markup' => "<p>" . $text . "</p>",
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $syncCount > 0 ? $this->t('Sync all details') : $this->t('Nothing to sync'),
      '#disabled' => $syncCount > 0 ? FALSE : TRUE,
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
    $TffcSync = \Drupal::service('tffc.sync');
    $syncableFilms = $TffcSync->getSyncableFilms();
    $syncCount = count($syncableFilms);


    \Drupal::messenger()->addMessage("Trying to sync $syncCount films. ");

    if ($syncCount > 0) {

      $batch = [
        'title' => t('Syncing Films...'),
        'operations' => [],
        'init_message' => t('Starting Sync...'),
        'progress_message' => t('Processed @current out of @total.'),
        'error_message' => t('An error occurred during processing'),
        'finished' => '\Drupal\tffc_importer\TffcImporterSyncBatch::callback',
      ];

      foreach ($syncableFilms as $film) {
        $batch['operations'][] = [
          '\Drupal\tffc_importer\TffcImporterSyncBatch::run',
          [$film],
        ];
      }

      batch_set($batch);
    }
  }


}
