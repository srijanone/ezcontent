<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Defines a route controller for watches autocomplete form elements.
 */
class GenerateSmartTags {

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * EzContent Text Tagging Manager.
   *
   * @var \Drupal\ezcontent_smart_article\EzcontentTextTaggingManager
   */
  protected $ezcontentTextTaggingManager;

  /**
   * Constructs GenerateSmartTags.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\ezcontent_smart_article\EzcontentTextTaggingManager $ezcontentTextTaggingManager
   *   EzContent Text Tagging Manager.
   */
  public function __construct(ConfigFactoryInterface $configFactory, EzcontentTextTaggingManager $ezcontentTextTaggingManager) {
    $this->config = $configFactory->get('ezcontent_smart_article.settings');
    $this->ezcontentTextTaggingManager = $ezcontentTextTaggingManager;
  }

  /**
   * Generate Tags via configured text tagging service.
   *
   * @param string $field_value
   *   The source text for generating tags.
   *
   * @return mixed
   *   Returns generated tags.
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function generateTags($field_value) {
    $serviceType = $this->config->get('text_tagging_service');
    $plugin = $this->ezcontentTextTaggingManager->createInstance($serviceType);
    return $plugin->getTags(Json::encode($field_value));
  }

}
