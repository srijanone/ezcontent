<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\field\FieldConfigInterface;

/**
 * Plugin implementation of the 'ezcontent_smart_tags' field type.
 *
 * @FieldType(
 *   id = "ezcontent_smart_tags",
 *   label = @Translation("Entity reference w/smart tags"),
 *   description = @Translation("Entity reference with smart tags"),
 *   category = @Translation("Reference"),
 *   default_widget = "ezcontent_smart_tags_autocomplete_tags",
 *   default_formatter = "ezcontent_smart_tags_entity",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList" * )
 *
 */
class EntityReferenceSmarttags extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);
    $long_text_fields = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('long_text_fields'))
      ->setRequired(FALSE);
    $properties['long_text_fields'] = $long_text_fields;
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    $schema['columns']['long_text_fields'] = array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => FALSE,
    );
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return array(
      'long_text_fields' => NULL,
    ) + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::fieldSettingsForm($form, $form_state);

    $entity = FieldItemBase::getEntity();
    $fields = [];
    $types = array('text_with_summary', 'text_long', 'string_long');
    foreach($entity as $key => $value) {
      $field_type = $entity->get($key)->getFieldDefinition()->getType();
      if(strpos($key, 'field_') !== false){
        if(in_array($field_type, $types)) {
          $fields[$key] = $entity->$key->getFieldDefinition()->getLabel();
        }
      } else if($key == 'body') {
        if(in_array($field_type, $types)) {
          $fields[$key] = $entity->$key->getFieldDefinition()->getLabel();
        }
      }
    }
    $elements['handler']['long_text_fields'] = [
      '#type' => 'select',
      '#title' => t('Long Text Fields'),
      '#options' => $fields,
      '#default_value' => $this->getSetting('long_text_fields'),
      '#description' => t('List of text fields(Long Text, Long Text with Summary) used in the content type.'),
      '#required' => true,
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions() {
    // In the base EntityReference class, this is used to populate the
    // list of field-types with options for each destination entity type.
    // Too much work, we'll just make people fill that out later.
    // Also, keeps the field type dropdown from getting too cluttered.
    return array();
  }

  public static function contentTypeFields($contentType) {
    $entityManager = \Drupal::service('entity.manager');
    $fields = [];

    if(!empty($contentType)) {
        $fields = array_filter(
            $entityManager->getFieldDefinitions('node', $contentType), 
                function ($field_definition) {
                   return 
                       $field_definition instanceof FieldConfigInterface;
                }
        );
    }

    return $fields;      
  }

}
