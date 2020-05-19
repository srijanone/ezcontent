<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides an image tagging plugin manager.
 *
 * @see \Drupal\ezcontent_smart_article\Annotation\EzcontentImageTagging
 * @see \Drupal\ezcontent_smart_article\EzcontentImageTaggingInterface
 * @see plugin_api
 */
class EzcontentImageTaggingManager extends DefaultPluginManager {

  /**
   * Constructs a EzcontentImageTaggingManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Ezcontent/ImageTagging',
      $namespaces,
      $module_handler,
      'Drupal\ezcontent_smart_article\EzcontentImageTaggingInterface',
      'Drupal\ezcontent_smart_article\Annotation\EzcontentImageTagging'
    );
    $this->alterInfo('image_tagging_info');
    $this->setCacheBackend($cache_backend, 'image_tagging_info_plugins');
  }

}
