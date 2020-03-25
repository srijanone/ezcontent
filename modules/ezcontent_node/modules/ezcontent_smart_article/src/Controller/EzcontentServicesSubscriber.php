<?php

namespace Drupal\ezcontent_smart_article\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a route controller for ezcontent_smart_article autocomplete.
 */
class EzcontentSmartArticleServicesSubscriber extends ControllerBase {

  /**
   * Handler for autocomplete request.
   */
  public function getSubscription(Request $request) {
    $response = [
      '#theme' => 'smart_article_get_subscription_landing_page',
      '#attached' => [
        'library' => [
          'ezcontent_smart_article/ezcontent_smart_article_subscription_page_libs',
        ],
      ],
    ];
    return $response;
  }

}
