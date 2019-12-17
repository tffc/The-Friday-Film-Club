<?php

namespace Drupal\tffc_importer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a form that configures TFFC Importer settings.
 */
class TffcImporterSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tffc_importer_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tffc_importer.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $tffc_config = $this->config('tffc_importer.settings');

    $form['general'] = [
      '#type' => 'details',
      '#title' => t('General Settings'),
      '#open' => TRUE,
    ];

    $form['general']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enabled'),
      '#description' => t('Flag to enable/disable importer.'),
      '#default_value' => $tffc_config->get('enabled') ?? FALSE,
    ];


    $form['omdb'] = [
      '#type' => 'details',
      '#title' => t('OMDb Settings'),
      '#open' => TRUE,
    ];

    $form['omdb']['omdb_key'] = [
      '#type' => 'textfield',
      '#title' => t('OMDB Key'),
      '#description' => t('The OMDB Key'),
      '#default_value' => $tffc_config->get('omdb_key') ?? '',
    ];

    $form['omdb']['last_id'] = [
      '#type' => 'textfield',
      '#title' => t('Last Id'),
      '#description' => t('The last IMDB ID imported.'),
      '#default_value' => $tffc_config->get('last_id') ?? TFFC_IMPORT_DEFAULT_LAST_ID,
    ];

    $form['omdb']['validate'] = [
      '#type' => 'checkbox',
      '#title' => t('Validate'),
      '#description' => t('Flag to enable validation, this will check for things such as vote/rating thresholds, already imported & is a certain type.'),
      '#default_value' => $tffc_config->get('validate') ?? FALSE,
    ];

    $form['omdb']['type_validation'] = [
      '#type' => 'select',
      '#title' => t('Type validation'),
      '#options' => [
        'any' => $this->t('All'),
        'movie' => $this->t('Movie'),
        'series' => $this->t('Series'),
        'episode' => $this->t('Episode'),
      ],
      '#description' => t('What type of content do you want to import?'),
      '#default_value' => $tffc_config->get('type_validation') ?? TFFC_IMPORT_DEFAULT_TYPE,
    ];

    $form['omdb']['votes_threshold'] = [
      '#type' => 'number',
      '#title' => t('Votes threshold'),
      '#min' => 0,
      '#description' => t('The number of votes required for a movie to be imported.'),
      '#default_value' => $tffc_config->get('votes_threshold') ?? TFFC_IMPORT_DEFAULT_VOTES,
    ];

    $form['omdb']['rating_threshold'] = [
      '#type' => 'number',
      '#title' => t('Rating threshold'),
      '#min' => 0,
      '#step' => 0.1,
      '#description' => t('The average rating required for a movie to be imported.'),
      '#default_value' => $tffc_config->get('rating_threshold') ?? TFFC_IMPORT_DEFAULT_RATING,
    ];

    $form['omdb']['release_threshold'] = [
      '#type' => 'date',
      '#title' => t('Release threshold'),
      '#date_date_format' => 'Y-m-d',
      '#description' => t('The date no films can be older than to allow importing.'),
      '#default_value' => $tffc_config->get('release_threshold') ?? TFFC_IMPORT_DEFAULT_RELEASE,
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('tffc_importer.settings')
      ->set('enabled', $values['enabled'])
      ->set('omdb_key', $values['omdb_key'])
      ->set('last_id', $values['last_id'])
      ->set('votes_threshold', $values['votes_threshold'])
      ->set('rating_threshold', $values['rating_threshold'])
      ->set('type_validation', $values['type_validation'])
      ->set('release_threshold', $values['release_threshold'])
      ->set('validate', $values['validate'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
