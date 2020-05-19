<?php

namespace Drupal\ezcontent_smart_article\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a ImageCaptioning annotation object.
 *
 * @Annotation
 */
class EzcontentImageCaptioning extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the ImageCaptioning type.
   *
   * @var \Drupal\Core\Annotation\Translation
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A short description of the ImageCaptioning type.
   *
   * @var \Drupal\Core\Annotation\Translation
   * @ingroup plugin_translatable
   */
  public $description;

}
