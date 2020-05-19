<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\file\Entity\File;

/**
 * An interface implementation for image captioning plugin.
 */
interface EzcontentImageCaptioningInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * Return the caption of an image file.
   *
   * @param \Drupal\file\Entity\File $file
   *   An image file object.
   *
   * @return string
   *   Image caption.
   */
  public function getImageCaption(File $file);

}
