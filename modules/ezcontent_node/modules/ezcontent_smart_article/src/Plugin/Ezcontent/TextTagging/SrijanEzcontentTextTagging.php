<?php

namespace Drupal\ezcontent_smart_article\Plugin\Ezcontent\TextTagging;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\ezcontent_smart_article\EzcontentTextTaggingInterface;
use Drupal\ezcontent_smart_article\EzcontentTextTaggingPluginBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Plugin implementation of the 'srijan_text_tagging' text tagging.
 *
 * @EzcontentTextTagging(
 *   id = "srijan_text_tagging",
 *   label = @Translation("Srijan Text Tagging"),
 *   description = @Translation("Provide text tagging feature using srijan
 *   AI tool."),
 * )
 */
class SrijanEzcontentTextTagging extends EzcontentTextTaggingPluginBase implements EzcontentTextTaggingInterface {
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
   * GuzzleHttp.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * The channel logger object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

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
    $config = $this->configFactory->get('ezcontent_smart_article.settings');
    $url = $config->get('smart_tags_api_url');
    $request = $this->httpClient->post($url, [
      'json' => [
        'article' => $text,
      ],
    ]);
    if ($request->getStatusCode() == 200) {
      return json_decode($request->getBody())->Tags;
    }
    else {
      $this->logger->get('ezcontent_smart_article')->error('Call to API
       endpoint failed. Reason: %reason.', [
         '%reason' => $request->getReasonPhrase(),
       ]);
    }

    return '';
  }

}
