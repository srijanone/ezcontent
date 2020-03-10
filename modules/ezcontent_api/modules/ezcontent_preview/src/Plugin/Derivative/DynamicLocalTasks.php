<?php

namespace Drupal\ezcontent_preview\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\ezcontent_preview\Utils;
use Drupal\Core\Url;

/**
 * Defines dynamic local tasks.
 */
class DynamicLocalTasks extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $decoupledRoutes = \Drupal::entityTypeManager()->getStorage('ezcontent_preview')->loadMultiple();
    if ($decoupledRoutes) {
      foreach ($decoupledRoutes as $id => $route) {
        $target = ($route->newtab ? '_blank' : '_self');
        $this->derivatives['ezcontent_preview.' . $id] = [
          $base_plugin_definition,
          'title' => $route->label,
          'route_name' => 'ezcontent.preview.view',
          'base_route' => 'entity.node.canonical',
          'route_parameters' => ['preview_type' => $route->id],
          'options' => ['attributes' => ['target' => $target]],
          'weight' => 100,
        ];
      }
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
