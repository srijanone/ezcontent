<?php

namespace Drupal\ezcontent_smart_article\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a SpeechToText annotation object.
 *
 * @Annotation
 */
class EzcontentSpeechToText extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the SpeechToText type.
   *
   * @var \Drupal\Core\Annotation\Translation
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A short description of the SpeechToText type.
   *
   * @var \Drupal\Core\Annotation\Translation
   * @ingroup plugin_translatable
   */
  public $description;

}
