<?php

namespace Drupal\ezcontent_node\PathProcessor;

use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Class EZContentAmpOutboundPathProcessor.
 *
 * Handles transition between AMP pages.
 *
 * @package Drupal\ezcontent_node\PathProcessor
 */
class EZContentAmpOutboundPathProcessor implements OutboundPathProcessorInterface {

  /**
   * Module Handler service.
   *
   * @var Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Constructs an EZContentAmpOutboundPathProcessor object.
   *
   * @param Drupal\Core\Extension\ModuleHandler $moduleHandler
   *   The module handler service.
   */
  public function __construct(ModuleHandler $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    // Check if AMP module exists.
    if ($this->moduleHandler->moduleExists('amp')) {
      // If current route is an AMPRoute, then all node page links including
      // the front page should have the '?amp' parameter in their url to
      // maintain transition.
      $isAmpRoute = \Drupal::service('router.amp_context')->isAmpRoute();
      if (isset($options['route']) && $options['route'] instanceof Route) {
        if (in_array($options['route']->getPath(), ['/', '/node/{node}'])
          && $isAmpRoute) {
          $options['query']['amp'] = TRUE;
        }
      }
    }
    return $path;
  }

}
