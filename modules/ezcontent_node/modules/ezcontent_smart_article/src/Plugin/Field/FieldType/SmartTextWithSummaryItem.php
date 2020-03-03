<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldType;

use Drupal\text\Plugin\Field\FieldType\TextWithSummaryItem;

/**
 * Plugin implementation of the 'text_with_summary' field type.
 *
 * @FieldType(
 *   id = "smart_text_with_summary",
 *   weight = 10,
 *   label = @Translation("Smart Text (formatted, long, with summary)"),
 *   description = @Translation("This field stores long text with a format and
 *   an optional smart summary."),
 *   category = @Translation("Text"),
 *   default_widget = "smart_text_textarea_with_summary",
 *   default_formatter = "smart_text_default"
 * )
 */
class SmartTextWithSummaryItem extends TextWithSummaryItem {

}
