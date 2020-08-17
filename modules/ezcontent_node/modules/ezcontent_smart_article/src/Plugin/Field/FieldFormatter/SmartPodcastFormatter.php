<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;

/**
 * Plugin implementation of the 'smart_podcast_entity_view' formatter.
 *
 * @FieldFormatter(
 *   id = "smart_podcast_entity_view",
 *   label = @Translation("Smart Podcast"),
 *   description = @Translation("Display the referenced entities rendered by entity_view()."),
 *   field_types = {
 *     "ezcontent_smart_podcast",
 *   }
 * )
 */
class SmartPodcastFormatter extends EntityReferenceEntityFormatter {

}
