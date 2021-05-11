<?php

namespace Drupal\ezcontent_smart_article\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\File\FileSystem;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\ezcontent_smart_article\EzcontentSpeechToTextManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes tasks for speech transcription and attaches them to it's entities.
 *
 * @QueueWorker(
 *   id = "speech_to_text_queue",
 *   title = @Translation("Convert speech to text queue"),
 *   cron = {"time" = 30}
 * )
 */
class SpeechToTextQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * The File System.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * Ezcontent Speech to Text Manager.
   *
   * @var \Drupal\ezcontent_smart_article\EzcontentSpeechToTextManager
   */
  protected $speechToTextManager;

  /**
   * The Key Value factory.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

  /**
   * SpeechToTextQueue constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The config factory object.
   * @param EntityTypeManager $entityTypeManager
   *   The entity type manager.
   * @param LoggerChannelFactory $logger
   *   The logger factory.
   * @param FileSystem $file_system
   *   The file system object.
   * @param \Drupal\ezcontent_smart_article\EzcontentSpeechToTextManager $speechToTextManager
   *   Ezcontent speech to text manager.
   * @param KeyValueFactoryInterface $key_value
   *   The key value factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $configFactory, EntityTypeManager $entityTypeManager, LoggerChannelFactory $logger, FileSystem $file_system, EzcontentSpeechToTextManager $speechToTextManager, KeyValueFactoryInterface $key_value) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger;
    $this->fileSystem = $file_system;
    $this->speechToTextManager = $speechToTextManager;
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
      $container->get('entity_type.manager'),
      $container->get('logger.factory'),
      $container->get('file_system'),
      $container->get('plugin.manager.ezcontent_speech_to_text'),
      $container->get('keyvalue')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Fetch the speech-to-text service selected in the configuration.
    $speech_to_text_service = $this->configFactory->get('ezcontent_smart_article.settings')
      ->get('speech_to_text_service');
    // Instantiate plugin for the configured service.
    $plugin = $this->speechToTextManager->createInstance($speech_to_text_service);
    try {
      // Load entity based on it's uuid stored in the $data.
      $entity = $this->entityTypeManager->getStorage($data['entity_type_id'])->loadByProperties(['uuid' => $data['entity_uuid']]);
      // Proceed only if entity exists.
      if (!empty($entity)) {
        $entity = $entity[key($entity)];
        // Check if the object is an entity and check if it has the
        // source_field with the file entity referenced in it.
        if ($entity instanceof EntityInterface && $entity->hasField($data['source_field_name']) && $entity->{$data['source_field_name']}->entity) {
          // Fetch the file path.
          $input_path = $this->fileSystem->realPath($entity->{$data['source_field_name']}->entity->getFileUri());
          // API call for converting speech to text.
          $text = $plugin->convertSpeechToText($input_path);
          // Saving the text into the entity.
          $plugin->saveTextToEntity($data['entity_type_id'], $entity->id(), $data['field_name'], $text);
          // Delete current entity's entry from key value store.
          $this->keyValue->get('speech_to_text')->delete($data['entity_uuid']);
        }
      }
    } catch (\Exception $e) {
      $this->logger->get('ezcontent_smart_article')->error($e->getMessage());
    }
  }

}
