<?php

namespace Drupal\ezcontent_smart_article\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\ezcontent_smart_article\EzcontentTextToSpeechManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes Tasks for creating podcasts and attaching them to entities.
 *
 * @QueueWorker(
 *   id = "text_to_speech_queue",
 *   title = @Translation("Convert text to speech queue"),
 *   cron = {"time" = 30}
 * )
 */
class TextToSpeechQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Ezcontent Text to Speech Manager.
   *
   * @var \Drupal\ezcontent_smart_article\EzcontentTextToSpeechManager
   */
  protected $textToSpeechManager;

  /**
   * TextToSpeechQueue constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The config factory object.
   * @param \Drupal\ezcontent_smart_article\EzcontentTextToSpeechManager $textToSpeechManager
   *   Ezcontent text to speech manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $configFactory, EzcontentTextToSpeechManager $textToSpeechManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->textToSpeechManager = $textToSpeechManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('plugin.manager.ezcontent_text_to_speech')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $text_to_speech_service = $this->configFactory->get('smart_article.settings')
      ->get('text_to_speech_service');
    $plugin = $this->textToSpeechManager->createInstance($text_to_speech_service);
    $speech = $plugin->convertTextToSpeech($data['text']);
    $plugin->saveSpeechToEntity($data['entity_type_id'], $data['entity_id'], $data['field_name'], $speech);
  }

}
