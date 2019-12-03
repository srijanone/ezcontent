<?php

namespace Drupal\summary_generator\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\summary_generator\CompaniesList;

/**
 * Defines a route controller for watches autocomplete form elements.
 */
class CompaniesAutoCompleteController extends ControllerBase {
  
  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request) {
    $query = $request->query->get('q');
    $results = new CompaniesList();
    $get_data = $results->getData("null", 'all');
    $new_results = [];

    foreach($get_data as $company) {
      if (substr(strtolower($company), 0, strlen($query)) == strtolower($query)) {
        $new_results[] = [
          'value' => $company,
          'label' => $company,
        ];
      }
    }
    return new JsonResponse($new_results);
  }

  public function mySearch($array, $key, $search) {
    $results = array();
    foreach ($array as $rootKey => $data) {
      if (array_key_exists($key, $data)) {
        if (strncmp($search, substr($data[$key], 0, 2), strlen($search)) == 0) {
          $results[] = $rootKey;
        }
      }
    }
    return $results;
  }

}