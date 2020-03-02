<?php

namespace Drupal\ezcontent_smart_article;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Component\Serialization\SerializationInterface;
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
    $this->config = $configFactory->get('summary_generator.settings');
    $this->httpClient = $httpClient;
    $this->serializer = $serializer;
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
    $url = $this->config->get('smart_tags_api_url') . '/process_article';

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
   * Finds all term reference fields for a given entity type.
   *
   * @param object $fieldDefinitions
   *   The field definition.
   * @param string $bundleName
   *   The bundle name.
   *
   * @return array
   *   The term reference fields keyed by their respective bundle.
   */
  public function findTermReferenceFieldsForEntityType($fieldDefinitions, $bundleName) {
    $referenceFields = [];
    foreach ($fieldDefinitions as $fieldDefinition) {
      if ($fieldDefinition->getType() == 'ezcontent_smart_tags' && $fieldDefinition->getSetting('target_type') == 'taxonomy_term') {
        $referenceFields[$bundleName][] = $fieldDefinition->getName();
        break;
      }
    }

    return $referenceFields;
  }

}
