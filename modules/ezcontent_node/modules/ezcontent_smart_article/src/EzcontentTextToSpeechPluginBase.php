<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base class implementation for text-to-speech conversion plugin manager.
 */
abstract class EzcontentTextToSpeechPluginBase extends PluginBase implements EzcontentTextToSpeechInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * EzcontentTextToSpeechPluginBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function convertTextToSpeech($text) {
  }

  /**
   * Create and attach audible media to entity.
   *
   * @param string $entity_type_id
   *   Entity Type ID.
   * @param string $entity_id
   *   Entity ID.
   * @param string $field_name
   *   Field's Machine name in which the created media will be stored.
   * @param string $speech
   *   Audio string from text-to-speech service response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function saveSpeechToEntity($entity_type_id, $entity_id, $field_name, $speech) {
    $entity = $this->entityTypeManager->getStorage($entity_type_id)
      ->load($entity_id);
    if ($entity instanceof ContentEntityInterface) {
      $basename = 'smart_podcast_' . $entity->id();
      $speech_filename = $basename . '.mp3';
      $scheme = $this->configFactory->get('system.file')->get('default_scheme');
      $destination = $scheme . '://' . $speech_filename;
      if (file_exists($destination)) {
        $counter = 0;
        do {
          $speech_filename = $basename . '_' . $counter++ . '.mp3';
          $destination = $scheme . '://' . $speech_filename;
        } while (file_exists($destination));
      }
      $speech_file = file_save_data($speech, $destination, FileSystemInterface::EXISTS_RENAME);
      $media = $this->entityTypeManager->getStorage('media')
        ->loadByProperties(['field_media_audio_file' => ['target_id' => $speech_file->id()]]);
      $media = !empty($media) ? $media[key($media)] : [];
      if (!$media) {
        $media = Media::create([
          'bundle' => 'audio',
          'uid' => $entity->get('uid')->target_id,
          'field_media_audio_file' => [
            'target_id' => $speech_file->id(),
          ],
        ]);
        $media->setName($speech_filename)
          ->setPublished(TRUE);
      }
      else {
        $media->set('field_media_audio_file', ['target_id' => $speech_file->id()]);
      }
      $media->save();
      $entity->set($field_name, ['target_id' => $media->id()]);
      $entity->save();
    }
  }

}
