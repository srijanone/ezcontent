<?php

namespace Drupal\ezcontent_preview\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\ezcontent_preview\PreviewInterface;

/**
 * Defines the Preview entity.
 *
 * @ConfigEntityType(
 *   id = "ezcontent_preview",
 *   label = @Translation("Ezcontent Preview"),
 *   handlers = {
 *     "list_builder" = "Drupal\ezcontent_preview\Controller\PreviewListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ezcontent_preview\Form\PreviewForm",
 *       "edit" = "Drupal\ezcontent_preview\Form\PreviewForm",
 *       "delete" = "Drupal\ezcontent_preview\Form\PreviewDeleteForm",
 *     }
 *   },
 *   config_prefix = "ezcontent_preview",
 *   admin_permission = "EZContent preview config",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "url" = "url",
 *     "newtab" = "newtab",
 *     "token_time" = "token_time",
 *     "content_entity" = "content_entity",
 *   },
 *  config_export = {
 *     "id",
 *     "url",
 *     "label",
 *     "newtab",
 *     "token_time",
 *     "content_entity",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/ezcontent_preview/{preview}",
 *     "delete-form" = "/admin/config/system/ezcontent_preview/{preview}/delete",
 *   }
 * )
 */
class Preview extends ConfigEntityBase implements PreviewInterface {

  /**
   *
   * @var string
   */
  public $url;

  /**
   *
   * @var string
   */
  public $id;

  /**
   *
   * @var string
   */
  public $label;

  /**
   *
   * @var boolean
   */
  public $newtab;

  /**
   *
   * @var string
   */
  public $token_time;

  /**
   *
   * @var string
   */
  public $content_entity;
}
