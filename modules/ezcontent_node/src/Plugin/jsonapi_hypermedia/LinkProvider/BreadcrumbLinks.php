<?php

namespace Drupal\ezcontent_node\Plugin\jsonapi_hypermedia\LinkProvider;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\jsonapi_hypermedia\AccessRestrictedLink;
use Drupal\jsonapi_hypermedia\Plugin\LinkProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ezcontent_node\Breadcrumb\EzContentBreadcrumbBuilder;

/**
 * Link plugin for article resource.
 *
 * @JsonapiHypermediaLinkProvider(
 *   id = "jsonapi_hypermedia.breadcrumb",
 *   link_relation_type = "node",
 *   link_context = {
 *    "resource_object" = "node--article",
 *   }
 * )
 */
class BreadcrumbLinks extends LinkProviderBase implements ContainerFactoryPluginInterface {

  /**
   * The plugin_id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The plugin implementation definition.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * Configuration information passed into the plugin.
   *
   * When using an interface like
   * \Drupal\Component\Plugin\ConfigurableInterface, this is where the
   * configuration should be stored.
   *
   * Plugin configuration is optional, so plugin implementations must provide
   * their own setters and getters.
   *
   * @var array
   */
  protected $configuration;

  /**
   * The breadcrumb builder object.
   *
   * @var \Drupal\ezcontent_article\Breadcrumb\EzContentBreadcrumbBuilder
   */
  protected $ezconteBreadcrumbBuilder;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\ezcontent_node\Breadcrumb\EzContentBreadcrumbBuilder $ezconteBreadcrumbBuilder
   *   The breadcrumb builder object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EzContentBreadcrumbBuilder $ezconteBreadcrumbBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->ezconteBreadcrumbBuilder = $ezconteBreadcrumbBuilder;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('ezcontent_node_normalizer.ezcontent_breadcrumb'));
  }

  /**
   * {@inheritdoc}
   */
  public function getLink($resource_object) {
    $entity = \Drupal::routeMatch()->getParameter('entity');
    $link_attributes = [];
    $url = Url::fromRoute('<front>');
    if ($entity) {
      $breadCrumbLinks = $this->ezconteBreadcrumbBuilder->build(\Drupal::routeMatch());
      $link_attributes = [
        'data' => $breadCrumbLinks,
      ];
      $url = Url::fromRoute('entity.node.canonical', ['node' => $entity->id()]);
    }
    $access_result = AccessResult::allowedIf(TRUE);
    return AccessRestrictedLink::createLink($access_result, CacheableMetadata::createFromObject($resource_object), $url, $this->getLinkRelationType(), $link_attributes);
  }

  /**
   * {@inheritdoc}
   */
  public function getLinkRelationType() {
    return 'breadcrumb';
  }

}
