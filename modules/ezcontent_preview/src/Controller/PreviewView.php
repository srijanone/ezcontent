<?php

namespace Drupal\ezcontent_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\Core\Routing\TrustedRedirectResponse; 
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\ezcontent_preview\Utils;


/**
 * Preview the content
 */
class PreviewView extends ControllerBase {

  protected $entityTypeManager;

  /**
   * Constructor
   */
  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

   /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('ezcontent_preview')
    );
  }

  public function preview(NodeInterface $node = NULL, $preview_type) {

    if ($preview_type) {
      $decoupledRoutes = $this->entityTypeManager->load($preview_type);
      if($decoupledRoutes) {
        $urlUtils = new Utils();
        $url = $urlUtils->buildUrl($node, $decoupledRoutes);
        if ($url) {

          // if new tab open seperate a tab with decoupled URL
          // else just iframe
          if ($decoupledRoutes->newtab) {
            return new TrustedRedirectResponse($url->toString());
          }
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
      }
    }
    
    // if nothing return as URL
    // return empty array
    return array(
      '#type' => 'markup',
      '#markup' => '',
    );
  }

}
