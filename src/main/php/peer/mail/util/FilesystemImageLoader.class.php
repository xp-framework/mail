<?php namespace peer\mail\util;

use io\File;
use io\FileUtil;
use util\MimeType;


/**
 * Loads images from the filesystem
 *
 * @purpose  ImageLoader
 */
class FilesystemImageLoader extends \lang\Object implements ImageLoader {

  /**
   * Load an image
   *
   * @param   peer.URL source
   * @return  string[2] data and contenttype
   */
  public function load($source) { 
    return array(
      FileUtil::getContents(new File($source->getURL())),
      MimeType::getByFilename($source->getURL())
    );
  }

} 
