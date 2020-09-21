<?php

namespace Drupal\ezcontent_smart_article\Plugin\Ezcontent\TextTagging;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\ezcontent_smart_article\EzcontentTextTaggingInterface;
use Drupal\ezcontent_smart_article\EzcontentTextTaggingPluginBase;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'google_text_tagging' text tagging.
 *
 * @EzcontentTextTagging(
 *   id = "google_text_tagging",
 *   label = @Translation("Google Text Tagging"),
 *   description = @Translation("Provide text tagging feature using google
 *   AI tool."),
 * )
 */
class GoogleEzcontentTextTagging extends EzcontentTextTaggingPluginBase implements EzcontentTextTaggingInterface {

  /**
   * The base url of the Google Cloud Natural Language API.
   */
  const API_ENDPOINT = 'https://language.googleapis.com/v1beta2/documents:analyzeEntities?key=';

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
   * The channel logger object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

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
   * @param \GuzzleHttp\Client $httpClient
   *   The guzzelhttp client object.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   The loggerChannelFactory client object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory, Client $httpClient, LoggerChannelFactory $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->httpClient = $httpClient;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('config.factory'), $container->get('http_client'), $container->get('logger.factory'));
  }

  /**
   * {@inheritdoc}
   */
  public function getTags($text = '') {
    $tags = [];
    if ($text) {
      $config = $this->configFactory->get('ezcontent_smart_article.settings');
      $secretKey = $config->get('gcm_text_tag_api_key');
      $data = [
        'encodingType' => 'UTF8',
        'document' => [
          'type' => 'HTML',
          'content' => Json::decode($text),
        ],
      ];
      $url = static::API_ENDPOINT . $secretKey;
      $response = $this->httpClient->request('POST', $url, [
        RequestOptions::JSON => $data,
        RequestOptions::HEADERS => ['Content-Type' => 'application/json'],
      ]);

      if ($response->getStatusCode() == 200) {
        $result = Json::decode($response->getBody());
        foreach ($result['entities'] as $entity) {
          $tags[] = $entity['name'];
        }
      }
      else {
        $this->logger->get('ezcontent_smart_article')->error('Call to API
       endpoint failed. Reason: %reason.', [
         '%reason' => $response->getReasonPhrase(),
       ]);
      }
    }
    return array_unique($tags);
  }

}
