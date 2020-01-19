<?php

namespace Drupal\tffc_importer\Services;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\tffc_importer\TffcImporterMediaTrait;
use Rooxie\Exception\ApiErrorException;
use Rooxie\Exception\IncorrectImdbIdException;
use Rooxie\Exception\InvalidApiKeyException;
use Rooxie\Exception\InvalidResponseException;
use Rooxie\Exception\MovieNotFoundException;
use Rooxie\OMDb as OMDb;

/**
 * Class TffcImporter
 *
 * @package Drupal\TffcImporter
 */
class TffcImporter {

  use TffcImporterMediaTrait;

  private $config;

  /**
   * The user we assigned the importing too
   *
   * @var int
   */
  protected $uid = 50;

  /**
   * OMDb Class
   *
   * @var \Rooxie\OMDb
   */
  protected $omdb;

  /**
   * The last IMDB ID
   *
   * @var string
   */
  protected $last_id;

  /**
   * Validate state
   *
   * @var int
   */
  protected $validation;

  /**
   * The votes threshold to import a film.
   *
   * @var int
   */
  protected $votes_threshold;

  /**
   * The rating threshold to import a film.
   *
   * @var float
   */
  protected $rating_threshold;

  /**
   * The type of film we want to import
   *
   * @var float
   */
  protected $type_validation;

  /**
   * The release threshold
   *
   * @var float
   */
  protected $release_threshold;

  /**
   * The API Key for OMDb
   *
   * @var string
   */
  private $api_key = FALSE;

  /**
   * @var \Rooxie\Model\Movie
   */
  private $film = NULL;

  /**
   * States if the film meets requirements to be imported
   *
   * @var bool
   */
  private $can_import = TRUE;

  /**
   * Stores any errors or successes
   *
   * @var array
   */
  private $log = [];

  /**
   * TffcImporter constructor.
   */
  public function __construct() {
    $this->config = \Drupal::config('tffc_importer.settings');
    $api_key = $this->config->get('omdb_key') ?? FALSE;

    $this->api_key = $api_key;
    $this->omdb = new OMDb($this->api_key);

    // reset the log
    $this->log = ['errors' => [], 'info' => [], 'success' => []];
  }

  public function init() {
    $last_id = $this->config->get('last_id') ?? TFFC_IMPORT_DEFAULT_LAST_ID;
    $validate = $this->config->get('validate') ?? FALSE;
    $votes_threshold = $this->config->get('votes_threshold') ?? TFFC_IMPORT_DEFAULT_VOTES;
    $rating_threshold = $this->config->get('rating_threshold') ?? TFFC_IMPORT_DEFAULT_RATING;
    $type_validation = $this->config->get('type_validation') ?? TFFC_IMPORT_DEFAULT_TYPE;
    $release_threshold = $this->config->get('release_threshold') ?? TFFC_IMPORT_DEFAULT_RELEASE;


    $this->last_id = $last_id;
    $this->validation = $validate;
    $this->votes_threshold = $votes_threshold;
    $this->rating_threshold = $rating_threshold;
    $this->type_validation = $type_validation;
    $this->release_threshold = $release_threshold;
  }

  /**
   * Import one film
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function run() {
    $this->init();
    $this->getFilm();
    $this->addToDrupal();

    return $this->log;
  }

  /**
   * Import a certain number of films
   *
   * @param int $number
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function importX(int $number = 0) {
    if ($number > 0) {
      for ($i = 0; $i <= $number; $i++) {
        $this->run();
      }
    }

    return $this->log;
  }

  /**
   * Get the next film
   *
   * @param bool $id
   *
   * @return array
   */
  public function getFilm($id = FALSE) {
    $film = FALSE;
    $nextImdbId = !$id ? $this->getNextImdbId() : $id;

    if ($nextImdbId) {

      try {
        $film = $this->omdb->getByImdbId($nextImdbId);
      } catch (ApiErrorException $e) {
        $this->setError($e->getMessage());
      } catch (IncorrectImdbIdException $e) {
        $this->setError($e->getMessage());
      } catch (InvalidApiKeyException $e) {
        $this->setError($e->getMessage());
      } catch (InvalidResponseException $e) {
        $this->setError($e->getMessage());
      } catch (MovieNotFoundException $e) {
        $this->setError($e->getMessage());
      }

      if ($film) {
        $this->setFilm($film);
        return $film->toArray();
      }
    }
    else {
      // error - next ID not found
      $this->setError(t('Could not get a valid IMDB ID'));
    }

    return [];
  }

  /**
   * Add the film into the drupal database
   *
   * @return string
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function addToDrupal(): string {
    // update the last used IMDB value
    $this->setLastImdbId($this->getNextImdbId());

    // next check that we can import this film
    $canImport = $this->canImport();
    if ($canImport !== TRUE) {
      $this->setError(t('Cannot import this item, as it did not meet the requirements. For the following reasons: @reasons', [
        '@reasons' => implode("<br>", $canImport),
      ]));
      return FALSE;
    }

    // check that we have not already imported it
    if ($this->alreadyImported($this->getNextImdbId())) {
      $this->setError(t('Cannot import this item, as we have already imported it.'));
      return FALSE;
    }

    $film = $this->film;

    if ($film) {
      $film = $film->toArray();

      $node = Node::create([
        'nid' => NULL,
        'type' => 'film',
        'title' => $film['Title'],
        'field_release_date' => $this->convertDate($film['Released']),
        'body' => [
          'value' => $film['Plot'],
          'format' => 'full_html',
        ],
        'field_imdb_id' => $film['ImdbId'],
        'field_imdb_votes' => $film['IMDbVotes'],
        'field_imdb_rating' => $film['IMDbRating'],
        'field_imdb_director' => $film['Director'],
        'field_imdb_writer' => $film['Writer'],
        'field_imdb_actors' => $film['Actors'],
        'field_imdb_language' => $this->getTaxonomyMultiple($film['Language'], 'imdb_language'),
        'field_imdb_genre' => $this->getTaxonomyMultiple($film['Genre'], 'imdb_genre'),
        'field_imdb_rated' => [
          'target_id' => $this->getTaxonomyId($film['Rated'], 'imdb_rated'),
        ],
        'field_film_poster' => [
          'target_id' => $this->createMedia($film['Poster'], $film['Title'] . ' Poster', 'film_poster', 'field_media_image_1', 'tffc/posters'),
        ],
        'uid' => $this->uid,
        'status' => TRUE,
      ]);

      try {
        $node->save();
        $nid = $node->id();
        $this->setSuccess(t('Imported item.'));
        return $nid;
      } catch (EntityStorageException $e) {
        $this->setError($e->getMessage());
      }
    }

    $this->setError(t('No valid item found.'));
    return FALSE;
  }

  /**
   * We want to check if the film meets the requirements to be imported
   *
   * @return array|bool
   */
  protected function canImport() {
    // if validation is disabled
    // don't check we can import
    if (!$this->validation) {
      return TRUE;
    }

    $reasons = [];

    $state = TRUE;

    // make sure we have a film
    if ($this->film) {
      $film = $this->film->toArray();

      // get the rating and votes
      $rating = (float) $film['IMDbRating'] ?? 0;
      $votes = (int) $film['IMDbVotes'] ?? 0;
      $type = (string) $film['Type'] ?? 'unknown';
      $release = $film['Released'] ?? 0;
      $year = $film['Year'] ?? 1970;

      // if we cannot find a release date
      // try and use the year to make up a date
      if ($release === "N/A") {
        $release = '1 Jan ' . $year;
      }

      // make sure that the rating and votes meets or exceeds the requirements
      if ($votes < $this->votes_threshold) {
        $reasons[] = t('Fail validating the voting, wanted: @wanted or greater | found: @found', [
          '@wanted' => $this->votes_threshold,
          '@found' => $votes,
        ]);
        $state = FALSE;
      }

      if ($rating < $this->rating_threshold) {
        $reasons[] = t('Fail validating the rating, wanted: @wanted or greater | found: @found', [
          '@wanted' => $this->rating_threshold,
          '@found' => $rating,
        ]);
        $state = FALSE;
      }

      // if the type validation is enabled (e.g. anything but any) and they do not match
      // change the state to false
      if ($this->type_validation !== 'any') {
        if ($this->type_validation !== $type) {
          $reasons[] = t('Fail validating the type, wanted: @wanted | found: @found', [
            '@wanted' => $this->type_validation,
            '@found' => $type,
          ]);
          $state = FALSE;
        }
      }

      // check if the release date has been met, this it to prevent
      // really really really old films on coming into the system
      if (strtotime($release) < strtotime($this->release_threshold)) {
        $reasons[] = t('Fail validating the release, wanted: @wanted or greater | found: @found', [
          '@wanted' => date('d-m-Y', strtotime($this->release_threshold)),
          '@found' => date('d-m-Y', strtotime($release)),
        ]);
        $state = FALSE;
      }

    }

    $this->setCanImport($state);
    return $this->getCanImport() === TRUE ? TRUE : $reasons;
  }

  /**
   * Checks if the IMDB id already exists
   *
   * @param $imdbId
   *
   * @return bool
   */
  protected function alreadyImported($imdbId): bool {
    // if validation is disabled
    // don't check for already imported
    if (!$this->validation) {
      return FALSE;
    }

    $nodes = [];

    try {
      $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties(['field_imdb_id' => $imdbId]);
    } catch (InvalidPluginDefinitionException $e) {
      $this->setError($e->getMessage());
    } catch (PluginNotFoundException $e) {
      $this->setError($e->getMessage());
    }

    return count($nodes) > 0;
  }

  /**
   * Gets the next IMDB ID
   *
   * @return array|mixed|string|null
   */
  private function getNextImdbId(): string {
    // holder for next ID
    $next_id = FALSE;
    // get last id
    $last_id = $this->last_id;
    // remove everything but numbers
    $last_id = preg_replace("/[^0-9,.]/", "", $last_id);
    // remove all trailing zeros
    $last_id = ltrim($last_id, '0');

    // make sure what we have is a number
    // and if true increment it
    if (is_numeric($last_id)) {
      $next_id = $last_id + 1;
    }

    // if we have a valid next id
    // lets pad the value to 7 (any spaces get zeros)
    // and add back the 'tt' value
    if ($next_id !== FALSE && is_numeric($next_id)) {
      $next_id = str_pad($next_id, 7, 0, STR_PAD_LEFT);
      return "tt" . $next_id;
    }

    // otherwise return false
    return $next_id;
  }

  /**
   * Update the last used IMDB id
   *
   * @param $id
   */
  private function setLastImdbId($id) {
    $config_factory = \Drupal::configFactory();
    $config_factory->getEditable('tffc_importer.settings')
      ->set('last_id', $id)
      ->save();
  }

  /**
   * Sets an error message
   *
   * @param $message
   */
  private function setError($message) {
    $this->log['errors'][] = $this->getNextImdbId() . " - " . $message;
  }

  /**
   * Sets a success message
   *
   * @param $message
   */
  private function setSuccess($message) {
    $this->log['success'][] = $this->getNextImdbId() . " - " . $message;
  }

  /**
   * Set information message
   *
   * @param $message
   */
  private function setInformation($message) {
    $this->log['info'][] = $this->getNextImdbId() . " - " . $message;
  }

  /**
   * Convert the date to timestamp
   *
   * @param $value
   *
   * @return false|int
   */
  private function convertDate($value) {
    return date('Y-m-d', strtotime($value));
  }

  /**
   * @param $term_name
   * @param $vid
   *
   * @return int|string|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getTaxonomyId($term_name, $vid) {
    $term_id = FALSE;

    // try to load the taxonomy by name and vocab
    $term = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['name' => $term_name, 'vid' => $vid]);
    $term = reset($term);

    // if we find the term id
    // set the id
    if ($term) {
      $term_id = $term->id();
    }
    else {
      // otherwise we can create a new id
      // and return hat instead
      try {
        $term = Term::create([
          'name' => $term_name,
          'vid' => $vid,
        ]);
        $term->save();
        $term_id = $term->id();
      } catch (EntityStorageException $e) {
        $this->setError($e->getMessage());
      }
    }

    // return the new term_id
    return $term_id;
  }

  /**
   * Tries to return an array of tids
   *
   * @param $terms
   * @param $vid
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getTaxonomyMultiple($terms, $vid) {
    $output = [];

    // make sure we have terms
    if ($terms && !empty($terms)) {
      // loop thought all the and get or create the tids
      foreach ($terms as $term) {
        $tid = $this->getTaxonomyId($term, $vid);
        if ($tid) {
          $output[] = [
            'target_id' => $tid,
          ];
        }
      }
    }

    return $output;
  }

  /**
   * @return bool
   */
  public function getCanImport(): bool {
    return $this->can_import;
  }

  /**
   * @param bool $can_import
   */
  public function setCanImport(bool $can_import) {
    $this->can_import = $can_import;
  }

  /**
   * @param \Rooxie\Model\Movie $film
   *
   * @return TffcImporter
   */
  public function setFilm(\Rooxie\Model\Movie $film): TffcImporter {
    $this->film = $film;
    return $this;
  }

}
