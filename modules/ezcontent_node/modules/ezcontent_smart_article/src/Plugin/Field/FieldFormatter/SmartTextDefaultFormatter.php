<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldFormatter;

use Drupal\text\Plugin\Field\FieldFormatter\TextDefaultFormatter;

/**
 * Plugin implementation of the 'smart_text_default' formatter.
 *
 * @FieldFormatter(
 *   id = "smart_text_default",
 *   label = @Translation(" Smart Text Default"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *     "smart_text_with_summary",
 *   }
 * )
 */
class SmartTextDefaultFormatter extends TextDefaultFormatter {

}
