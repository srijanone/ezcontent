<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Component\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base class implementation for image captioning plugin manager.
 */
abstract class EzcontentTextTaggingPluginBase extends PluginBase implements EzcontentTextTaggingInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getTags($text = '') {
  }

}
