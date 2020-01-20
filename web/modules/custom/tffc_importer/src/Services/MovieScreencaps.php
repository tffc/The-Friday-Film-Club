<?php

namespace Drupal\tffc_importer\Services;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\CurlException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;

/**
 * Class MovieScreencaps
 *
 * @package Drupal\tffc_importer
 */
class MovieScreencaps {

  /**
   * @var string
   */
  private $url = "https://movie-screencaps.com/";

  /**
   * @var
   */
  private $currentUrl;

  /**
   * @var \PHPHtmlParser\Dom
   */
  private $dom;

  /**
   * Set page
   *
   * @var int
   */
  private $page = 1;

  /**
   * Max number of pages
   *
   * @var int
   */
  private $maxPages = 1;

  /**
   * MovieScreencaps constructor.
   */
  public function __construct() {
    $this->dom = new Dom;
  }

  /**
   * @param string $title
   *
   * @return bool
   * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
   * @throws \PHPHtmlParser\Exceptions\CircularException
   * @throws \PHPHtmlParser\Exceptions\CurlException
   * @throws \PHPHtmlParser\Exceptions\StrictException
   */
  public function search(string $title) {
    $search = $this->url . '?s=' . urlencode($title);
    $this->dom->loadFromUrl($search);


    if ($this->validateSearch()) {
      $this->maxPages = $this->getMaxPages();
      $this->page = $this->getRandomPage();
      $this->currentUrl = $this->getCurrentPageUrl();

      if ($this->loadRandomPage()) {
        return $this->getRandomImage();
      }
    }

    return FALSE;
  }

  /**
   * Loads a random page
   *
   * @return bool
   */
  protected function loadRandomPage() {
    if ($this->currentUrl !== "") {
      $randomPage = $this->currentUrl . $this->page;
      try {
        $this->dom->loadFromUrl($randomPage);
      } catch (ChildNotFoundException $e) {
        return FALSE;
      } catch (CircularException $e) {
        return FALSE;
      } catch (CurlException $e) {
        return FALSE;
      } catch (StrictException $e) {
        return FALSE;
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Gets a random image
   *
   * @return mixed
   */
  protected function getRandomImage() {
    try {
      $images = $this->dom->find('img.thumb');
    } catch (ChildNotFoundException $e) {
      return FALSE;
    } catch (NotLoadedException $e) {
      return FALSE;
    }
    $count = count($images);
    $key = $this->getRandomKey($count);
    return strtok($images[$key]->getTag()->getAttribute('src')['value'], "?");
  }

  /**
   * Gets the max number of pages
   *
   * @return int
   */
  protected function getMaxPages() {
    try {
      $pagination = $this->dom->find('.wp-pagenavi a');
    } catch (ChildNotFoundException $e) {
      return -1;
    } catch (NotLoadedException $e) {
      return -1;
    }
    $secondFromLast = count($pagination) - 2;
    $secondFromLast = $secondFromLast < 1 ? 0 : $secondFromLast;

    if (isset($pagination[$secondFromLast]) && is_numeric($pagination[$secondFromLast]->innerHtml)) {
      return (int) $pagination[$secondFromLast]->innerHtml;
    }

    return 1;
  }

  /**
   * Gets a random page number
   *
   * @return int
   */
  protected function getRandomPage() {
    return rand(1, $this->getMaxPages());
  }

  private function validateSearch() {
    if ($this->isOnFilmPage() && !$this->isOnSearchPage()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Checks if on the search page
   *
   * @return bool
   */
  private function isOnSearchPage() {
    try {
      $searchPage = $this->dom->find('body.search-results');
    } catch (ChildNotFoundException $e) {
      return FALSE;
    } catch (NotLoadedException $e) {
      return FALSE;
    }
    if (isset($searchPage[0])) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Checks if on the film page
   *
   * @return bool
   */
  private function isOnFilmPage() {
    try {
      $filmPage = $this->dom->find('body.single-post');
    } catch (ChildNotFoundException $e) {
      return FALSE;
    } catch (NotLoadedException $e) {
      return FALSE;
    }
    if (isset($filmPage[0])) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Gets the current page url
   *
   * @return string
   */
  private function getCurrentPageUrl() {
    $output = '';
    try {
      $canonical = $this->dom->find('[rel=canonical]');
    } catch (ChildNotFoundException $e) {
      return '';
    } catch (NotLoadedException $e) {
      return '';
    }
    if (isset($canonical) && !empty($canonical[0])) {
      $tags = $canonical[0]->getTag();
      if (isset($tags) && !empty($tags)) {
        $href = $tags->getAttribute('href');
        if (isset($href) && !empty($href)) {
          $output = $href['value'];
        }
      }
    }

    return $output;
  }

  /**
   * Random Key
   *
   * @param int $max
   *
   * @return int
   */
  private function getRandomKey($max = 12) {
    $max = $max - 1;
    $max = $max < 1 ? 0 : $max;
    return rand(0, $max);
  }

}
