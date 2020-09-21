<?php

namespace Drupal\ezcontent_smart_article\Plugin\Ezcontent\ImageTagging;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\ezcontent_smart_article\EzcontentImageTaggingInterface;
use Drupal\ezcontent_smart_article\EzcontentImageTaggingPluginBase;
use Drupal\file\Entity\File;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileSystem;

/**
 * Plugin implementation of the 'google_image_tagging' image tagging.
 *
 * @EzcontentImageTagging(
 *   id = "google_image_tagging",
 *   label = @Translation("Google Image Tagging"),
 *   description = @Translation("Provide image Tagging feature using google
 *   AI tool."),
 * )
 */
class GoogleEzcontentImageTagging extends EzcontentImageTaggingPluginBase implements EzcontentImageTaggingInterface {

  /**
   * The base url of the Google Cloud Vision API.
   */
  const API_ENDPOINT = 'https://vision.googleapis.com/v1/images:annotate?key=';

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
   * GuzzleHttp.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

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
   * @param \GuzzleHttp\Client $httpClient
   *   The guzzelhttp client object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory, FileSystem $fileSystem, Client $httpClient) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->fileSystem = $fileSystem;
    $this->httpClient = $httpClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('config.factory'), $container->get('file_system'), $container->get('http_client'));
  }

  /**
   * {@inheritdoc}
   */
  public function getImageTags(File $file) {
    $tags = [];
    if ($file) {
      $imageFile = $this->fileSystem->realpath($file->getFileUri());
      $config = $this->configFactory->get('ezcontent_smart_article.settings');
      $secretKey = $config->get('gcm_secret_key_image_tags');
      $maxCount = $config->get('gcm_max_count_image_tags') ? $config->get('gcm_max_count_image_tags') : 12;
      $data = [
        'requests' => [
          [
            'image' => [
              'content' => base64_encode(file_get_contents($imageFile)),
            ],
            'features' => [
              [
                'type' => 'LABEL_DETECTION',
                'maxResults' => $maxCount,
              ],
            ],
          ],
        ],
      ];
      $url = static::API_ENDPOINT . $secretKey;
      $response = $this->httpClient->request('POST', $url, [
        RequestOptions::JSON => $data,
        RequestOptions::HEADERS => ['Content-Type' => 'application/json'],
      ]);

      if ($response->getStatusCode() == 200) {
        $result = Json::decode($response->getBody());
        foreach ($result['responses'][0]['labelAnnotations'] as $label) {
          $tags[] = $label['description'];
        }
      }
    }
    return $tags;
  }

}
