<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'ez_smart_transcribe_widget' widget.
 *
 * @FieldWidget(
 *   id = "ez_smart_transcribe_widget",
 *   label = @Translation("Smart Transcribe"),
 *   description = @Translation("A textarea field to hold the transcription text."),
 *   field_types = {
 *     "ezcontent_smart_transcribe"
 *   }
 * )
 */
class SmartTranscribeWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $items->getFieldDefinition()->getName();
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '';
    $element['convert_speech_to_text'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create Transcription'),
      '#description' => $this->t('This will queue the content for transcribe creation and will be complete on the next cron run.'),
      '#default_value' => !empty($items[$delta]->convert_speech_to_text),
      '#parent_entity_uuid' => $items[$delta]->getEntity()->uuid(),
    ];
    $element['value'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Transcription text'),
      '#default_value' => $value,
      '#states' => [
        'disabled' => [
          ':input[name="' . $field_name . '[' . $delta . '][convert_speech_to_text]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Proceed if the convert speech to text option is selected.
    if ($values[0]['convert_speech_to_text']) {
      // Fetch the data needed for queuing the current entity to be processed
      // later on cron execution.
      $field_definition = $this->fieldDefinition;
      $field_name = $field_definition->getName();
      $data['entity_type_id'] = $field_definition->getTargetEntityTypeId();
      $data['entity_uuid'] = $form[$field_name]['widget'][0]['convert_speech_to_text']['#parent_entity_uuid'];
      $data['field_name'] = $field_name;
      $data['source_field_name'] = $this->getFieldSetting('transcription_source_field');
      // Use key value to record an entry for the entity being queued for
      // speech-to-text conversion.
      $key_value = \Drupal::keyValue('speech_to_text');
      // If key_value has an entry for the entity, it means the entity is
      // already added into the queue for conversion, then fetch the
      // corresponding queue item id and update the data in the queue.
      if ($key_value->has($data['entity_uuid'])) {
        $item_id = $key_value->get($data['entity_uuid'])['item_id'];
        \Drupal::database()->update('queue')
          ->fields(['data' => serialize($data)])
          ->condition('name', 'speech_to_text_queue')
          ->condition('item_id', $item_id)
          ->execute();
      }
      // The current entity is being queued for the first time, so post queue
      // creation set the entity uuid and the queue item id in the key_value.
      else {
        $queue = \Drupal::queue('speech_to_text_queue');
        $item_id = $queue->createItem($data);
        $key_value->set($data['entity_uuid'], ['item_id' => $item_id]);
      }
    }
    return $values;
  }

}
