<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A base class implementation for speech-to-text conversion plugin manager.
 */
abstract class EzcontentSpeechToTextPluginBase extends PluginBase implements EzcontentSpeechToTextInterface {

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
   * This function is used to save text to field.
   *
   * @param int $entity_type_id
   *   Given entity type id.
   * @param int $entity_id
   *   Given entity id.
   * @param string $field_name
   *   Given field name.
   * @param string $text
   *   Response text returned from speech-to-text service.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function saveTextToEntity($entity_type_id, $entity_id, $field_name, $text) {
    $entity = $this->entityTypeManager->getStorage($entity_type_id)->load($entity_id);
    if ($entity instanceOf EntityInterface && $entity->hasField($field_name)) {
      $entity->set($field_name, ['convert_text_to_speech' => 0, 'value' => $text]);
      $entity->save();
    }
  }

}
