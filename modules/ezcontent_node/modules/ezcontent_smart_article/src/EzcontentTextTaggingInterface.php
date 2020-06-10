<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * An interface implementation for image captioning plugin.
 */
interface EzcontentTextTaggingInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * Return the tags from input text.
   *
   * @param string $text
   *   A body field text.
   *
   * @return array
   *   Tags based on text input.
   */
  public function getTags($text = '');

}
