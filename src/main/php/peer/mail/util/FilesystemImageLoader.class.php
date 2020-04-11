<?php namespace peer\mail\util;

use io\{File, FileUtil};
use util\MimeType;

/**
 * Loads images from the filesystem
 *
 * @purpose  ImageLoader
 */
class FilesystemImageLoader implements ImageLoader {

  /**
   * Load an image
   *
   * @param   peer.URL source
   * @return  string[2] data and contenttype
   */
  public function load($source) { 
    return [
      FileUtil::getContents(new File($source->getURL())),
      MimeType::getByFilename($source->getURL())
    ];
  }
} 