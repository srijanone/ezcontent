<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemBase;

/**
 * Plugin implementation of the 'ezcontent_smart_podcast' field type.
 *
 * @FieldType(
 *   id = "ezcontent_smart_podcast",
 *   label = @Translation("Smart Podcast"),
 *   description = @Translation("Generate podcast from text fields added to the entity"),
 *   category = @Translation("Reference"),
 *   default_widget = "smart_podcast_entity_reference_widget",
 *   default_formatter = "smart_podcast_entity_view",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList"
 * )
 */
class SmartPodcast extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions() {
    return [];
  }

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
    $schema = parent::schema($field_definition);
    $schema['columns']['convert_text_to_speech'] = [
      'type' => 'int',
      'size' => 'tiny',
    ];
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);
    $properties['convert_text_to_speech'] = DataDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('convert_text_to_speech'))
      ->setRequired(FALSE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if (parent::isEmpty() && empty($this->get('convert_text_to_speech')->getValue())) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'text_to_speech_fields' => NULL,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::fieldSettingsForm($form, $form_state);

    $entity = FieldItemBase::getEntity();
    $fields = [];
    $types = [
      'string',
      'string_long',
      'text',
      'text_default',
      'text_long',
      'text_with_summary',
      'smart_text_with_summary',
    ];
    foreach ($entity->getFieldDefinitions() as $key => $value) {
      if (strpos($key, 'field_') !== FALSE ||
        in_array($key, ['title', 'body'])) {
        if (in_array($value->getType(), $types)) {
          $fields[$key] = $value->getLabel();
        }
        if ($value->getType() === 'entity_reference_revisions') {
          $handler_settings = $value->getSetting('handler_settings');
          $negate = $handler_settings['negate'];
          $target_bundles = $handler_settings['target_bundles'];
          $paragraph_types = \Drupal::service('entity_type.bundle.info')->getBundleInfo('paragraph');
          foreach ($paragraph_types as $paragraph_type_key => $paragraph_type) {
            $paragraph_types[$paragraph_type_key] = $paragraph_type['label'];
          }
          if ($negate) {
            $reference_revision_fields[$key] = array_diff_key($paragraph_types, $target_bundles);
          }
          else {
            $reference_revision_fields[$key] = array_intersect_key($paragraph_types, $target_bundles);
          }
          $fields[$key] = $value->getLabel();
        }
      }
    }
    $elements['handler']['text_to_speech_fields']['content'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Text to Speech Fields'),
      '#options' => $fields,
      '#default_value' => $this->getSetting('text_to_speech_fields')['content'],
      '#description' => $this->t('List of text fields (Long Text, Long Text with Summary, etc.) that are used in the content type. Please select the fields that should be used for creating the podcast.'),
      '#required' => TRUE,
      '#multiple' => TRUE,
    ];
    if (isset($reference_revision_fields)) {
      foreach ($reference_revision_fields as $key => $reference_revision_field_paragraphs) {
        $elements['handler']['text_to_speech_fields']['paragraphs'][$key] = [
          '#type' => 'select',
          '#title' => $this->t('Paragraphs'),
          '#options' => $reference_revision_field_paragraphs,
          '#default_value' => $this->getSetting('text_to_speech_fields')['paragraphs'][$key],
          '#multiple' => TRUE,
          '#states' => [
            'visible' => [
              ':input[name="settings[text_to_speech_fields][content][' . $key . ']"]' => ['checked' => TRUE],
            ],
          ],
        ];
      }
    }
    return $elements;
  }

}
