<?php

namespace Drupal\ezcontent_smart_article\Plugin\Ezcontent\TextToSpeech;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ezcontent_smart_article\EzcontentTextToSpeechInterface;
use Drupal\ezcontent_smart_article\EzcontentTextToSpeechPluginBase;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of 'google_text_to_speech'.
 *
 * @EzcontentTextToSpeech(
 *   id = "google_text_to_speech",
 *   label = @Translation("Google Text to Speech"),
 *   description = @Translation("Converts text-to-speech using Google Cloud Text-to-Speech API."),
 * )
 */
class GoogleTextToSpeech extends EzcontentTextToSpeechPluginBase implements EzcontentTextToSpeechInterface {

  /**
   * The base url of the Google Cloud Text to Speech API.
   */
  const API_ENDPOINT = 'https://texttospeech.googleapis.com/v1/text:synthesize?key=';

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory object.
   * @param \GuzzleHttp\Client $httpClient
   *   The guzzelhttp client object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory, Client $httpClient) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entityTypeManager, $configFactory);
    $this->httpClient = $httpClient;
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
      $container->get('config.factory'),
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function convertTextToSpeech($text) {
    $speech = '';
    if ($text) {
      $api_key = $this->configFactory->get('ezcontent_smart_article.settings')
        ->get('gcp_text_to_speech_key');
      $data = [
        'input' => [
          'text' => $text,
        ],
        'voice' => [
          'languageCode' => 'en-US',
          'ssmlGender' => 'FEMALE',
        ],
        'audioConfig' => [
          'audioEncoding' => 'MP3',
        ],
      ];
      $url = static::API_ENDPOINT . $api_key;
      $response = $this->httpClient->post($url, [
        RequestOptions::JSON => $data,
        RequestOptions::HEADERS => ['Content-Type' => 'application/json'],
      ]);

      if ($response->getStatusCode() == 200) {
        $result = Json::decode($response->getBody()->getContents());
        $speech = base64_decode($result['audioContent']);
      }
    }
    return $speech;
  }

}
