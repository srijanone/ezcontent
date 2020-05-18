<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Component\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;

/**
 * A base class implementation for image tagging plugin manager.
 */
abstract class EzcontentImageTaggingPluginBase extends PluginBase implements EzcontentImageTaggingInterface {

  /**
   * Constructs a EzcontentImageTaggingPluginBase object.
   *
   * @param array $configuration
   *   A config array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getImageTags(File $file) {
  }

}
