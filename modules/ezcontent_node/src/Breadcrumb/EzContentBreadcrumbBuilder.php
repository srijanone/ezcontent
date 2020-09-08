<?php

namespace Drupal\ezcontent_node\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

/**
 * Implements custom breadcrumb builder.
 */
class EzContentBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $entity = $route_match->getParameter('entity');
    $links = [];
    if ($entity) {
      $links[] = Link::createFromRoute(t('Home'), '<front>');
      $links[] = Link::createFromRoute($entity->bundle(), 'entity.node.canonical');
      $links[] = Link::createFromRoute($entity->getTitle(), 'entity.node.canonical', ['node' => $entity->id()]);
    }
    $breadcrumb = new Breadcrumb();
    // Cache breadcrumb by URL.
    $breadcrumb->addCacheContexts(['url.path']);
    return $breadcrumb->setLinks($links);
  }

}
