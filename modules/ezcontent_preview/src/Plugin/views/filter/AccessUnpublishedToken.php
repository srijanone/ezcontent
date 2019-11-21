<?php

namespace Drupal\ezcontent_preview\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\ViewExecutable;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\Views;

/**
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("published_access_unpublished_token")
 */
class AccessUnpublishedToken extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Published or Unpublish Access Token');
  }
  /**
   * Override the query so that no filtering takes place if the user doesn't
   * select any options.
   */
  public function query() {
    // build query and join tables
    $configuration = [
      'table' => "node",
      'field' => 'nid',
      'left_table' => 'node_field_data',
      'left_field' => 'nid',
      'operator' => '=',
    ];
    $join = Views::pluginManager('join')->createInstance('standard', $configuration);
    $this->query->addRelationship('node', $join, 'node_field_data');
    
    // default value for snippet
    $snippet = "node_field_data.status = 1";

    $tokenNid = $this->checkTokenAuthNid();
    if($tokenNid) {
      $snippet = "node_field_data.status = 1 OR node.nid = " . $tokenNid;
    } 
    $this->query->addWhereExpression($this->options['group'], $snippet);
  }

  public function checkTokenAuthNid() {
    $tokenKey = \Drupal::config('access_unpublished.settings')->get('hash_key');
    if (\Drupal::request()->query->has($tokenKey)) {
      $storage = \Drupal::entityTypeManager()->getStorage('access_token');
      $object = $storage->getQuery()
        ->condition('value', \Drupal::request()->get($tokenKey))
        ->execute();
      if ($object) {
        $node = $storage->load(current($object));
        $nid = $node->get('entity_id')->value;
        return $nid;
      }
    }
    return FALSE;
  }

}