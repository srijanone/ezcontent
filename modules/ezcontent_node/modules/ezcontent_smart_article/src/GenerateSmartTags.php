<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Defines a route controller for watches autocomplete form elements.
 */
class GenerateSmartTags {

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
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   The channel logger object.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ClientInterface $httpClient, LoggerChannelFactory $logger) {
    $this->config = $configFactory->get('smart_article.settings');
    $this->httpClient = $httpClient;
    $this->logger = $logger;
  }

  /**
   * Get data from Endpoint.
   *
   * @param string $value
   *   The field value.
   *
   * @return mixed
   *   The tags generated from the above field value.
   */
  public function getTags($value = '') {
    $url = $this->config->get('smart_tags_api_url');

    $request = $this->httpClient->post($url, [
      'json' => [
        'article' => $value,
      ],
    ]);

    if ($request->getStatusCode() == 200) {
      return json_decode($request->getBody())->Tags;
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

  /**
   * Fetch ezcontent_smart_tags taxonomy_term reference field from given.
   *
   * FieldDefinitions list.
   *
   * @param object $fieldDefinitions
   *   The list of field definition.
   *
   * @return mixed
   *   Machine name of the term reference field.
   */
  public function findTermReferenceFieldsForEntityType($fieldDefinitions) {
    foreach ($fieldDefinitions as $fieldDefinition) {
      if ($fieldDefinition->getType() == 'ezcontent_smart_tags' && $fieldDefinition->getSetting('target_type') == 'taxonomy_term') {
        return $fieldDefinition->getName();
      }
    }
  }

}
