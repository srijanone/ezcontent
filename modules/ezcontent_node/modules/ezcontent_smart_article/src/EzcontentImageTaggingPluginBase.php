<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Component\Plugin\PluginBase;
use Drupal\file\Entity\File;

/**
 * A base class implementation for image tagging plugin manager.
 */
abstract class EzcontentImageTaggingPluginBase extends PluginBase implements EzcontentImageTaggingInterface {

  /**
   * {@inheritdoc}
   */
  public function getImageTags(File $file) {
  }

}
