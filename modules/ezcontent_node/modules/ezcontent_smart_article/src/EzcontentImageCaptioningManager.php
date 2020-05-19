<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides an image captioning plugin manager.
 *
 * @see \Drupal\ezcontent_smart_article\Annotation\EzcontentImageCaptioning
 * @see \Drupal\ezcontent_smart_article\EzcontentImageCaptioningInterface
 * @see plugin_api
 */
class EzcontentImageCaptioningManager extends DefaultPluginManager {

  /**
   * Constructs a ImageCaptioningManager object.
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
    parent::__construct(
      'Plugin/Ezcontent/ImageCaptioning',
      $namespaces,
      $module_handler, 'Drupal\ezcontent_smart_article\EzcontentImageCaptioningInterface',
      'Drupal\ezcontent_smart_article\Annotation\EzcontentImageCaptioning'
    );
    $this->alterInfo('image_captioning_info');
    $this->setCacheBackend($cache_backend, 'image_captioning_info_plugins');
  }

}
