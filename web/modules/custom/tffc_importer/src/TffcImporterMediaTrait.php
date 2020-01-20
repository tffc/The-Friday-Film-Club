<?php

namespace Drupal\tffc_importer;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;

trait TffcImporterMediaTrait {

  /**
   * The user we assigned the importing too
   *
   * @var int
   */
  protected $uid = 50;

  /**
   * Create media uses combines both createImageFromUrl & setFidOnMedia
   * To make a new media item
   *
   * @param $url
   * @param $name
   * @param $bundle
   * @param $field
   * @param string $folder
   *
   * @return bool|int|string|null
   */
  public function createMedia($url, $name, $bundle, $field, $folder = 'tffc') {
    $mid = FALSE;
    // create file id
    $fid = $this->createImageFromUrl($url, $folder);

    // if we have file id
    if ($fid) {
      // create media id
      $mid = $this->setFidOnMedia($fid, $name, $bundle, $field);
    }

    // return the media item
    return $mid;
  }

  /**
   * creates and image from url
   *
   * @param $url
   * @param string $folder
   *
   * @return bool|int|string|null
   */
  protected function createImageFromUrl($url, $folder = 'tffc') {
    $fid = FALSE;

    // create a new file name
    $filename = md5($url);
    // get the file extension
    $ext = pathinfo($url, PATHINFO_EXTENSION);
    // load the data
    $data = file_get_contents($url);

    // create new file name
    $newFile = $filename . '.' . $ext;

    // make sure the directory is ready for use
    $directory = 'public://' . $folder . '/';
    \Drupal::service('file_system')
      ->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);

    // save the file replacing any it finds
    $file = file_save_data($data, $directory . $newFile, FileSystemInterface::EXISTS_REPLACE);

    // if it found
    // set the fid
    if ($file) {
      $fid = $file->id();
    }

    // return the fid
    return $fid;
  }

  /**
   * Creates new media item and adds adds on the file
   *
   * @param $fid
   * @param $name
   * @param $bundle
   * @param $field
   *
   * @return bool|int|string|null
   */
  protected function setFidOnMedia($fid, $name, $bundle, $field) {
    $mid = FALSE;

    // create media item
    $media = Media::create([
      'bundle' => $bundle,
      'uid' => $this->uid,
      $field => [
        'target_id' => $fid,
        'alt' => $name,
      ],
    ]);

    // set the media item name
    $media->setName($name);

    // try and save
    try {
      $media->save();
    } catch (EntityStorageException $e) {
      $this->setError($e->getMessage());
    }

    // if saved
    // set the mid
    if ($media) {
      $mid = $media->id();
    }

    // return the mid
    return $mid;
  }

}
