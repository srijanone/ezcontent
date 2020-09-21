<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Defines a route controller for watches autocomplete form elements.
 */
class CompaniesList {

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * An http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The JSON serialization class to use.
   *
   * @var \Drupal\Component\Serialization\SerializationInterface
   */
  protected $serializer;

  /**
   * The channel logger object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * Constructs this factory object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   An http client.
   * @param \Drupal\Component\Serialization\SerializationInterface $serializer
   *   The JSON serialization class to use.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   The channel logger object.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ClientInterface $httpClient, SerializationInterface $serializer, LoggerChannelFactory $logger) {
    $this->config = $configFactory->get('ezcontent_smart_article.settings');
    $this->httpClient = $httpClient;
    $this->serializer = $serializer;
    $this->logger = $logger;
  }

  /**
   * Get data from Endpoint.
   *
   * @param string $entity
   *   The entity name.
   * @param string $type
   *   The bundle name.
   *
   * @return mixed
   *   Summary based on company name.
   */
  public function getData($entity = '', $type = '') {
    $url = $this->config->get('summary_generator_api_url') . '/fetchcontent';
    $serialized_entity = $this->serializer->encode(
      [
        "entity" => $entity,
        "type" => $type,
      ]
    );

    $request = $this->httpClient->post($url, [
      'body' => $serialized_entity,
    ]);

    if ($request->getStatusCode() == 200) {
      return $this->serializer->decode($request->getBody())['body'];
    }
    else {
      $this->logger->get('ezcontent_smart_article')->error('Call to API
       endpoint failed. Reason: %reason.',
        [
          '%reason' => $request->getReasonPhrase(),
        ]
      );
    }

    return '';
  }

}
