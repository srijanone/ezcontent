<?php

namespace Drupal\ezcontent_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\ezcontent_preview\Utils;


/**
 * Preview the content
 */
class PreviewView extends ControllerBase {

  public function preview(NodeInterface $node = NULL) {
    $urlUtils = new Utils();
    $url = $urlUtils->buildUrl($node);
    if ($url) {
      $output = '<iframe class="decoupled-content--preview" src="' . $url->toString() . '"></iframe>';
      return array(
        '#type' => 'markup',
        '#allowed_tags' => ['iframe'],
        '#markup' => $output,
        '#attached' => [
          'library' => [
            'ezcontent_preview/global',
          ],
        ],
      );
    }

    // if nothing return as URL
    // return empty array
    return array(
      '#type' => 'markup',
      '#markup' => '',
    );
  }

}
