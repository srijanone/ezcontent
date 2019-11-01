<?php

namespace Drupal\ezcontent_preview\Plugin\Menu;

use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Dynamic tabs
 */
class DynamicTab extends LocalTaskDefault {

  /**
  * {@inheritdoc}
  */
  public function getCacheMaxAge() {
    return 0;
  }

}
