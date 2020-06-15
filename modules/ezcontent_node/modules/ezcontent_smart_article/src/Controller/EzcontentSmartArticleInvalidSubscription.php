<?php

namespace Drupal\ezcontent_smart_article\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a route controller for ezcontent_smart_article invalid subscription.
 */
class EzcontentSmartArticleInvalidSubscription extends ControllerBase {

  /**
   * Handler for invalid smart article subscription popup.
   */
  public function showInvalidSubscriptionErrorPopup(Request $request) {
    $response = [
      '#theme' => 'smart_article_invalid_subscription_popup',
    ];
    return $response;
  }

}
