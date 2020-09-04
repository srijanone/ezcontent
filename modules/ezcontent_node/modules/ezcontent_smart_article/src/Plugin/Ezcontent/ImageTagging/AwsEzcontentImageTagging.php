<?php

namespace Drupal\ezcontent_smart_article\Plugin\Ezcontent\ImageTagging;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystem;
use Drupal\ezcontent_smart_article\EzcontentImageTaggingInterface;
use Drupal\ezcontent_smart_article\EzcontentImageTaggingPluginBase;
use Drupal\file\Entity\File;
use Aws\Rekognition\RekognitionClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'aws_image_tagging' image tagging.
 *
 * @EzcontentImageTagging(
 *   id = "aws_image_tagging",
 *   label = @Translation("Aws Image Tagging"),
 *   description = @Translation("Provide image Tagging feature using Aws
 *   AI tool."),
 * )
 */
class AwsEzcontentImageTagging extends EzcontentImageTaggingPluginBase implements EzcontentImageTaggingInterface {
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
   * FileSystem.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

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
   * @param \Drupal\Core\File\FileSystem $fileSystem
   *   The file system object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory, FileSystem $fileSystem) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->fileSystem = $fileSystem;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('config.factory'), $container->get('file_system'));
  }

  /**
   * {@inheritdoc}
   */
  public function getImageTags(File $file) {
    $tags = [];
    if ($file && class_exists('\Aws\Rekognition\RekognitionClient')) {
      $imageFile = file_get_contents($this->fileSystem->realpath($file->getFileUri()));
      $config = $this->configFactory->get('ezcontent_smart_article.settings');
      $accessKey = $config->get('aws_access_key_image_tags');
      $secretKey = $config->get('aws_secret_key_image_tags');
      $maxCount = $config->get('aws_max_count_image_tags') ? $config->get('aws_max_count_image_tags') : 12;
      $client = new RekognitionClient([
        'version' => 'latest',
        'region' => 'us-east-2',
        'credentials' => [
          'key' => $accessKey,
          'secret' => $secretKey,
        ],
      ]);
      $response = $client->detectLabels([
        'Image' => [
          'Bytes' => $imageFile,
        ],
        'MaxLabels' => (int) $maxCount,
        'MinConfidence' => 1,
      ]);
      if (!empty($response)) {
        $result = $response->toArray();
        foreach ($result['Labels'] as $label) {
          $tags[] = $label['Name'];
        }
      }
    }
    return $tags;
  }

}
