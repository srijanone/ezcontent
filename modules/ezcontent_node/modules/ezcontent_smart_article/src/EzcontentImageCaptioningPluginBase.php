<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Component\Plugin\PluginBase;
use Drupal\file\Entity\File;

/**
 * A base class implementation for image captioning plugin manager.
 */
abstract class EzcontentImageCaptioningPluginBase extends PluginBase implements EzcontentImageCaptioningInterface {

  /**
   * {@inheritdoc}
   */
  public function getImageCaption(File $file) {
  }

}
