<?php

namespace Drupal\ezcontent_smart_article\Plugin\Action;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Action\ActionBase;
use Drupal\file\Entity\File;
use Drupal\field\Entity\FieldConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ezcontent_smart_article\EzcontentImageCaptioningManager;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Link;

/**
 * Provides an action to update image caption or title of media image field.
 *
 * @Action(
 *   id = "media_update_image_caption_title_action",
 *   label = @Translation("Update image caption or title"),
 *   type = "media"
 * )
 */
class GenerateImageCaptionAndTitleAction extends ActionBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;
  /**
   * The plugin_id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The plugin implementation definition.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * Configuration information passed into the plugin.
   *
   * When using an interface like
   * \Drupal\Component\Plugin\ConfigurableInterface, this is where the
   * configuration should be stored.
   *
   * Plugin configuration is optional, so plugin implementations must provide
   * their own setters and getters.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Configuration Factory.
   *
   * @var \Drupal\ezcontent_smart_article\EzcontentImageCaptioningManager
   */
  protected $captioningManager;

  /**
   * Messenger object.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory object.
   * @param \Drupal\ezcontent_smart_article\EzcontentImageCaptioningManager $captioningManager
   *   The EzcontentImageCaptioningManager object.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory, EzcontentImageCaptioningManager $captioningManager, Messenger $messenger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->captioningManager = $captioningManager;
    $this->messenger = $messenger;
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
      $container->get('plugin.manager.image_captioning'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if ($entity->getEntityType()->id() == 'media') {
      foreach ($entity->getFields() as $field) {
        // Check if entity has any field of image type.
        if ($field->getFieldDefinition()->getType() == 'image') {
          $imageField = $field->getFieldDefinition()->getName();
          $cardinality = $field->getFieldDefinition()
            ->getFieldStorageDefinition()
            ->getCardinality();
          $fieldConfig = FieldConfig::loadByName($entity->getEntityType()
            ->id(), $entity->bundle(), $imageField);
          $isTitleRequired = FALSE;
          // Check if image title is required.
          if ($fieldConfig) {
            $isTitleRequired = $fieldConfig->getSettings()['title_field_required'];
          }
          $serviceType = $this->configFactory->get('ezcontent_smart_article.settings')
            ->get('image_captioning_service');
          $link = Link::createFromRoute('EzContent Smart Article', 'ezcontent_smart_article.config')
            ->toString();
          // Check if we have a image caption service.
          if ($serviceType) {
            $plugin = $this->captioningManager->createInstance($serviceType);
            // Handle single image.
            if ($cardinality == 1) {
              if (empty($entity->$imageField->alt)) {
                $file = $entity->$imageField->entity;
                // Get image caption from plugin.
                $caption = $plugin->getImageCaption($file);
                if ($caption) {
                  $entity->$imageField->alt = $caption;
                }
                else {
                  $this->messenger->addError($this->t('Image caption not updated for media @mediaName
                    , Please check setting @link.', [
                      '@link' => $link,
                      '@mediaName' => $entity->getName(),
                    ]));
                }
              }
              // Update image title if it is mandatory.
              if ($isTitleRequired && empty($entity->$imageField->title)) {
                $entity->$imageField->title = $entity->getName();
              }
              $entity->save();
            }
            // Handle more than one images.
            elseif ($cardinality > 1 || $cardinality === -1) {
              foreach ($entity->$imageField as $image) {
                if (empty($image->alt)) {
                  $file = File::load($image->target_id);
                  // Get image caption from plugin.
                  $caption = $plugin->getImageCaption($file);
                  if ($caption) {
                    $image->alt = $caption;
                  }
                  else {
                    $this->messenger->addError($this->t('Image caption not updated for media @mediaName
                    , Please check setting @link.', [
                      '@link' => $link,
                      '@mediaName' => $entity->getName(),
                    ]));
                  }
                }
                // Update image title if it is mandatory.
                if ($isTitleRequired && empty($image->title)) {
                  $image->title = $entity->getName();
                }
              }
              $entity->save();
            }
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'media') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }
    return TRUE;
  }

}
