<?php

namespace Drupal\ezcontent_smart_article\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
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
   * The Key Value factory.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

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
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value
   *   The key value factory object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $configFactory, EzcontentTextToSpeechManager $textToSpeechManager, KeyValueFactoryInterface $key_value) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->textToSpeechManager = $textToSpeechManager;
    $this->keyValue = $key_value;
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
      $container->get('plugin.manager.ezcontent_text_to_speech'),
      $container->get('keyvalue')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Fetch text-to-speech service from the configuration.
    $text_to_speech_service = $this->configFactory->get('ezcontent_smart_article.settings')
      ->get('text_to_speech_service');
    $plugin = $this->textToSpeechManager->createInstance($text_to_speech_service);
    // Perform conversion.
    $speech = $plugin->convertTextToSpeech($data['text']);
    // Save the conversion result as media into the node entity.
    $plugin->saveSpeechToEntity($data['entity_type_id'], $data['entity_id'], $data['field_name'], $speech);
    // Delete current node's entry from key value store.
    $this->keyValue->get('text_to_speech_node')->delete($data['entity_id']);
  }

}
