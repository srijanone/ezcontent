<?php

namespace Drupal\ezcontent_smart_article\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\ezcontent_smart_article\CompaniesList;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a route controller for ezcontent_smart_article autocomplete.
 */
class CompaniesAutoCompleteController extends ControllerBase {

  /**
   * The EZCcontent smart article companies list object.
   *
   * @var \Drupal\ezcontent_smart_article\CompaniesList
   */
  protected $companiesList;

  /**
   * Constructs a CompaniesAutoCompleteController object.
   *
   * @param \Drupal\ezcontent_smart_article\CompaniesList $companiesList
   *   The EZCcontent smart article companies list object.
   */
  public function __construct(CompaniesList $companiesList) {
    $this->companiesList = $companiesList;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ezcontent_smart_article.companies_list')
    );
  }

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request) {
    $query = $request->query->get('q');
    // @todo: The response returns all data, Filter it based on query, via API
    // or else cache data and flush cache on new file upload.
    $companies = $this->companiesList->getData("null", 'all');
    $results = [];

    // @todo: Move filtering from here to API.
    foreach ($companies as $company) {
      if (strpos(strtolower($company), strtolower($query)) !== FALSE) {
        $results[] = [
          'value' => $company,
          'label' => $company,
        ];
      }
    }
    return new JsonResponse($results);
  }

}
