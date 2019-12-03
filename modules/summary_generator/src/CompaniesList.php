<?php

namespace Drupal\summary_generator;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a route controller for watches autocomplete form elements.
 */
class CompaniesList {
  
  /**
   * Handler for autocomplete request.
   */
  public function getData($enity = '', $type = '') {
    $results = [];
    $url = \Drupal::config('summary_generator.settings')->get('summary_generator_api_url') . '/fetchcontent';
    $serialized_entity = json_encode(
      [
        "entity" => $enity,
        "type" => $type
      ]
    );
    $client = \Drupal::httpClient();
    $request = $client->post($url, [
      'body' => $serialized_entity
    ]);
    $response = json_decode($request->getBody());

    if ($response && $response->body) {
      return $response->body;      
    }
  }

}