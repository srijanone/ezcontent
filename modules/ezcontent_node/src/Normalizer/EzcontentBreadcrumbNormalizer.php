<?php

namespace Drupal\ezcontent_node\Normalizer;

use Drupal\serialization\Normalizer\ContentEntityNormalizer;

/**
 * Converts the Drupal entity object structures to a normalized array.
 */
class EzcontentBreadcrumbNormalizer extends ContentEntityNormalizer {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\Breadcrumb\Breadcrumb';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $breadcrumbLinks = [];
    foreach ($entity->getLinks() as $link) {
      $text = $link->getText();
      $breadcrumbLinks[] = [
        'text' => $text,
        'url' => $text == 'Home' ? $link->getUrl()->toString() : '',
      ];
    }
    return $breadcrumbLinks;
  }

}
