<?php

namespace Drupal\ezcontent_paragraphs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defines a route controller.
 */
class EZContentParagraphsController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function build($paragraph, $view_mode = 'default') {
    $content = $this->entityTypeManager()
      ->getViewBuilder('paragraph')
      ->view($paragraph, $view_mode);
    return new Response(render($content));
  }

}
