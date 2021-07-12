<?php

namespace Drupal\ezcontent_smart_article\Plugin\Ezcontent\ImageTagging;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\ezcontent_smart_article\EzcontentImageTaggingInterface;
use Drupal\ezcontent_smart_article\EzcontentImageTaggingPluginBase;
use Drupal\file\Entity\File;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'srijan_image_tagging' image tagging.
 *
 * @EzcontentImageTagging(
 *   id = "srijan_image_tagging",
 *   label = @Translation("Srijan Image Tagging"),
 *   description = @Translation("Provide image Tagging feature using srijan
 *   AI tool."),
 * )
 */
class SrijanEzcontentImageTagging extends EzcontentImageTaggingPluginBase implements EzcontentImageTaggingInterface {

  /**
   * File system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Guzzle http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Json serialization.
   *
   * @var \Drupal\Component\Serialization\Json
   */
  protected $jsonSerialization;

  /**
   * Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * SrijanEzcontentImageTagging constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param $plugin_id
   *   The plugin_id for the plugin instance.
   * @param $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   File system.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config factory.
   * @param \GuzzleHttp\Client $httpClient
   *   Guzzle http client.
   * @param \Drupal\Component\Serialization\Json $jsonSerialization
   *   Json serialization.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Logger factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileSystemInterface $fileSystem, ConfigFactoryInterface $config, Client $httpClient, Json $jsonSerialization, LoggerChannelFactoryInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileSystem = $fileSystem;
    $this->config = $config->get('ezcontent_smart_article.settings');
    $this->httpClient = $httpClient;
    $this->jsonSerialization = $jsonSerialization;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('file_system'),
      $container->get('config.factory'),
      $container->get('http_client'),
      $container->get('serialization.json'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getImageTags(File $file) {
    $tags = [];
    if ($file) {
      $imageFile = file_get_contents($this->fileSystem->realpath($file->getFileUri()));
      $url = $this->config->get('image_generate_tags_api_url');
      try {
        $response = $this->httpClient->request('POST', $url, [
          'headers' => [
            'content-type' => $file->getMimeType(),
          ],
          'body' => $imageFile,
        ]);
        if ($response->getStatusCode() == 200) {
          $body = $this->jsonSerialization->decode($response->getBody()
            ->getContents());
          $tags = $body['data']['objects'];
        }
      }
      catch (\Exception $e) {
        $this->logger->get('srijan_image_tagging')->error($e->getMessage());
      }
    }
    return $tags;
  }

}
