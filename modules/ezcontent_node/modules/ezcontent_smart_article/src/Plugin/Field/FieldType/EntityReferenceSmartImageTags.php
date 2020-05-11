<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemBase;

/**
 * Plugin implementation of the 'ezcontent_smart_image_tags' field type.
 *
 * @FieldType(
 *   id = "ezcontent_smart_image_tags",
 *   label = @Translation("Entity reference w/smart image tags"),
 *   description = @Translation("Entity reference with smart image tags"),
 *   category = @Translation("Reference"),
 *   default_widget = "smart_entity_reference_autocomplete_tags",
 *   default_formatter = "ezcontent_smart_image_tags_entity",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList"
 * )
 */
class EntityReferenceSmartImageTags extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);
    $image_fields = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('image_fields'))
      ->setRequired(FALSE);
    $properties['image_fields'] = $image_fields;
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    $schema['columns']['image_fields'] = [
      'type' => 'varchar',
      'length' => 255,
      'not null' => FALSE,
    ];
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'image_fields' => NULL,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::fieldSettingsForm($form, $form_state);

    $entity = FieldItemBase::getEntity();
    $fields = [];
    $types = ['image'];
    foreach ($entity as $key => $value) {
      $field_type = $entity->get($key)->getFieldDefinition()->getType();
      if (strpos($key, 'field_') !== FALSE) {
        if (in_array($field_type, $types)) {
          $fields[$key] = $entity->$key->getFieldDefinition()->getLabel();
        }
      }
    }
    $elements['handler']['image_fields'] = [
      '#type' => 'select',
      '#title' => $this->t('Image Field'),
      '#options' => $fields,
      '#default_value' => $this->getSetting('image_fields'),
      '#description' => $this->t('List of Image fields used in the content type.'),
      '#required' => TRUE,
    ];
    return $elements;
  }

}
