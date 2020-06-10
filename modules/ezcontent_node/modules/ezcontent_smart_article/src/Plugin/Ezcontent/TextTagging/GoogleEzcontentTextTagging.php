<?php

namespace Drupal\ezcontent_smart_article\Plugin\Ezcontent\TextTagging;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\ezcontent_smart_article\EzcontentTextTaggingInterface;
use Drupal\ezcontent_smart_article\EzcontentTextTaggingPluginBase;
use GuzzleHttp\Client;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $configFactory, Client $httpClient) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->httpClient = $httpClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('config.factory'), $container->get('http_client'));
  }

  /**
   * {@inheritdoc}
   */
  public function getTags($text = '') {
    // @todo: Add google api call here and return tag array
  }

}
