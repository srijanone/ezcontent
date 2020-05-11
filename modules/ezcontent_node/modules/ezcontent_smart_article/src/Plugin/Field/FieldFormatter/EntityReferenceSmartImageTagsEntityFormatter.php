<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;

/**
 * Plugin implementation of the 'ezcontent_smart_image_tags_entity' formatter.
 *
 * @FieldFormatter(
 *   id = "ezcontent_smart_image_tags_entity",
 *   label = @Translation("Smart Image Tags"),
 *   description = @Translation("Display the referenced entities rendered by entity_view(), with optional field smarttags."),
 *   field_types = {
 *     "ezcontent_smart_image_tags",
 *   }
 * )
 */
class EntityReferenceSmartImageTagsEntityFormatter extends EntityReferenceEntityFormatter {

}
