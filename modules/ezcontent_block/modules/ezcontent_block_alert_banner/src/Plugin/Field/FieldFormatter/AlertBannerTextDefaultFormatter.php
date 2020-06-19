<?php

namespace Drupal\ezcontent_block_alert_banner\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'alert_banner_text_default' formatter.
 *
 * @FieldFormatter(
 *   id = "alert_banner_text_default",
 *   label = @Translation("Alert Banner"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class AlertBannerTextDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $entity = $items->getEntity();
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'processed_text',
        '#text' => $item->value,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      ];
    }
    // If background color not selected a default background color
    // will be applied from field setting.
    $bg = $entity->get('field_background_color')->first()->getValue();
    $elements['#attributes']['style'] = "background-color: " . $bg['color'] . ";";
    return $elements;
  }

}
