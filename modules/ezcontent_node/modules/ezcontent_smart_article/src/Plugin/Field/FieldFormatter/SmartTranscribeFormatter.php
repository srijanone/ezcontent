<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'ez_smart_transcribe_view' formatter.
 *
 * @FieldFormatter(
 *   id = "ez_smart_transcribe_view",
 *   label = @Translation("Smart Transcribe"),
 *   description = @Translation("Displays the value stored in the field."),
 *   field_types = {
 *     "ezcontent_smart_transcribe",
 *   }
 * )
 */
class SmartTranscribeFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = ['#markup' => $item->value];
    }
    return $element;
  }

}
