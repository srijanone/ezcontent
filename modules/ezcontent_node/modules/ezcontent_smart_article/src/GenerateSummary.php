<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Defines a route controller for watches autocomplete form elements.
 */
class GenerateSummary {

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
   * @param string $text
   *   The entity name.
   * @param string $type
   *   The summary type name.
   * @param int $sent_count
   *   The number of sentences.
   */
  public function generateSummary($text = '', $type = 'abstractive', $sent_count = 5) {
    $abstractive_api_endpoint = $this->config->get('abstractive_summary_api_url');
    $extractive_api_endpoint = $this->config->get('extractive_summary_api_url');
    $end_point = $type === 'abstractive' ? $abstractive_api_endpoint : $extractive_api_endpoint;
    $payload = ($type === 'extractive') ? [
      [
        'name' => 'text',
        'Content-type' => 'multipart/form-data',
        'contents' => $text,
      ],
      [
        'name' => 'sent_count',
        'Content-type' => 'multipart/form-data',
        'contents' => $sent_count,
      ],

    ] : [
      [
        'name' => 'text',
        'Content-type' => 'multipart/form-data',
        'contents' => $text,
      ],
    ];
    // Make api call to get summary based on type.
    try {
      $request = $this->httpClient->post($end_point, [
        'multipart' => $payload,
      ]);
      if ($request->getStatusCode() == 200) {
        return $request->getBody();
      }
    }
    catch (\Exception $e) {
      $this->logger->get('ezcontent_smart_article')->error('Call to API
       endpoint failed. Reason: %reason.', [
         '%reason' => $e->getMessage(),
       ]);
    }
  }

}
