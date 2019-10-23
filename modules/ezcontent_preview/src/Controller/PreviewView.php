<?php

namespace Drupal\ezcontent_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\access_unpublished\AccessTokenManager;
use Drupal\Core\Url;

/**
 * Preview the content
 */
class PreviewView extends ControllerBase {

  public function preview(NodeInterface $node = NULL) {
    $config = \Drupal::config('ezcontent_preview.settings');
    $preview_base_url = $config->get('ezcontent_preview_url');
    $node_id = $node->id();
    $node_alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $node_id);
    $node_type = $node->getEntityType();
    $urlQuery = [];
    
    // if node is unpublished using https://www.drupal.org/project/access_unpublished module to
    // generate token and pass it to Drupal
    if(!$node->isPublished()) {
      $tokenKey = \Drupal::config('access_unpublished.settings')->get('hash_key');
      $tokenManager = \Drupal::service('access_unpublished.access_token_manager');
      $activeToken = $tokenManager->getActiveAccessToken($node);
      
      if(!$activeToken) {
        $activeToken = $this->buildToken($node);
      } 
      $tokenValue = $activeToken->get('value')->value;

      $urlQuery = [
        'query' => [ $tokenKey => $tokenValue],
      ];
    }
    
    $siteUrl = Url::fromUri($preview_base_url . $node_alias, $urlQuery);

    $output = '<iframe class="decoupled-content--preview" src="' . $siteUrl->toString() . '"></iframe>';
    
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

  public function buildToken($entity) {
    $tokenKey = \Drupal::config('ezcontent_preview.settings')->get('ezcontent_preview_token_expire_time');
    if (!$tokenKey) {
      $tokenKey = 300;
    }
    $access_token = \Drupal::entityTypeManager()->getStorage('access_token')->create(
      [
        'entity_type' => $entity->getEntityType()->id(),
        'entity_id' => $entity->id(),
        'expire' => \Drupal::time()->getRequestTime() + $tokenKey,
      ]
    );
    $access_token->save();
    return $access_token;
  }

}
