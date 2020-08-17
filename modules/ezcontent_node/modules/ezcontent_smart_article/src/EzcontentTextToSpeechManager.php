<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a text-to-speech conversion plugin manager.
 *
 * @see \Drupal\ezcontent_smart_article\Annotation\EzcontentTextToSpeech
 * @see \Drupal\ezcontent_smart_article\EzcontentTextToSpeechInterface
 * @see plugin_api
 */
class EzcontentTextToSpeechManager extends DefaultPluginManager {

  /**
   * Constructs a EzcontentTextToSpeechManager object.
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
    parent::__construct('Plugin/Ezcontent/TextToSpeech',
      $namespaces,
      $module_handler,
      'Drupal\ezcontent_smart_article\EzcontentTextToSpeechInterface',
      'Drupal\ezcontent_smart_article\Annotation\EzcontentTextToSpeech'
    );
    $this->alterInfo('ezcontent_text_to_speech_info');
    $this->setCacheBackend($cache_backend, 'ezcontent_text_to_speech_info_plugins');
  }

}
