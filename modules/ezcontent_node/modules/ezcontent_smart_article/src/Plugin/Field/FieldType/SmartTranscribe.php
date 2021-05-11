<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'ezcontent_smart_transcribe' field type.
 *
 * @FieldType(
 *   id = "ezcontent_smart_transcribe",
 *   label = @Translation("Smart Transcribe"),
 *   description = @Translation("Generate transcribe from media."),
 *   category = @Translation("Text"),
 *   default_widget = "ez_smart_transcribe_widget",
 *   default_formatter = "ez_smart_transcribe_view"
 * )
 */
class SmartTranscribe extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'target_type' => \Drupal::moduleHandler()->moduleExists('media') ? 'media' : 'user',
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema['columns']['convert_speech_to_text'] = [
      'type' => 'int',
      'size' => 'tiny',
    ];
    $schema['columns']['value'] = [
      'type' => 'text',
      'size' => 'big',
    ];
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['convert_speech_to_text'] = DataDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Convert Speech to Text'))
      ->setRequired(FALSE);
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Transcription Text'))
      ->setRequired(FALSE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'transcription_source_field' => NULL,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::fieldSettingsForm($form, $form_state);

    $entity = FieldItemBase::getEntity();
    $fields = [];
    $types = ['file'];
    foreach ($entity as $key => $value) {
      $field_type = $entity->get($key)->getFieldDefinition()->getType();
      if (strpos($key, 'field_') !== FALSE) {
        if (in_array($field_type, $types)) {
          $fields[$key] = $entity->$key->getFieldDefinition()->getLabel();
        }
      }
    }
    $element['transcription_source_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Transcription Source Field'),
      '#options' => $fields,
      '#default_value' => $this->getSetting('transcription_source_field'),
      '#description' => $this->t('Choose the source field to generate transcription from.'),
      '#required' => TRUE,
    ];
    return $element;
  }

}
