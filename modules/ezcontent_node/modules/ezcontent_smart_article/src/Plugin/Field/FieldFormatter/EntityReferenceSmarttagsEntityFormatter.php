<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'ezcontent_smart_tags_entity' formatter.
 *
 * @FieldFormatter(
 *   id = "ezcontent_smart_tags_entity",
 *   label = @Translation("Rendered entity"),
 *   description = @Translation("Display the referenced entities rendered by entity_view(), with optional field smarttags."),
 *   field_types = {
 *     "ezcontent_smart_tags"
 *   }
 * )
 */
class EntityReferenceSmarttagsEntityFormatter extends EntityReferenceEntityFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $view_mode = $this->getSetting('view_mode');
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      $clone = clone $entity;
      $view_builder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
      $elements[$delta] = $view_builder->view($clone, $view_mode, $entity->language()->getId());
    }

    return $elements;
  }
}
