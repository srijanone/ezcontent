<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\file\Entity\File;

/**
 * An interface implementation for image tagging plugin.
 */
interface EzcontentImageTaggingInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * Return the tags of an image file.
   *
   * @param \Drupal\file\Entity\File $file
   *   An image file object.
   *
   * @return array
   *   An array of tags.
   */
  public function getImageTags(File $file);

}
