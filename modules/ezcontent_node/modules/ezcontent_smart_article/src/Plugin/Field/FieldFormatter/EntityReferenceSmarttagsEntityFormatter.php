<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;

/**
 * Plugin implementation of the 'ezcontent_smart_tags_entity' formatter.
 *
 * @FieldFormatter(
 *   id = "ezcontent_smart_tags_entity",
 *   label = @Translation("Smart Tags"),
 *   description = @Translation("Display the referenced entities rendered by entity_view(), with optional field smarttags."),
 *   field_types = {
 *     "ezcontent_smart_tags",
 *   }
 * )
 */
class EntityReferenceSmarttagsEntityFormatter extends EntityReferenceEntityFormatter {

}
