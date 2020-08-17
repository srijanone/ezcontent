<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteTagsWidget;

/**
 * Plugin implementation of the 'smart_podcast_entity_reference_widget' widget.
 *
 * @FieldWidget(
 *   id = "smart_podcast_entity_reference_widget",
 *   label = @Translation("Autocomplete (Tags style)"),
 *   description = @Translation("An autocomplete entity reference field with creating podcast support."),
 *   field_types = {
 *     "ezcontent_smart_podcast"
 *   },
 *   multiple_values = TRUE,
 * )
 */
class SmartPodcastWidget extends EntityReferenceAutocompleteTagsWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $field_name = $items->getFieldDefinition()->getName();
    $element['target_id']['#states'] = [
      'disabled' => [
        ':input[name="' . $field_name . '[convert_text_to_speech]"]' => ['checked' => TRUE],
      ],
    ];
    $element['convert_text_to_speech'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create Podcast'),
      '#description' => $this->t('This will queue the content for podcast creation and will be complete on the next cron run.'),
      '#default_value' => !empty($items[$delta]->convert_text_to_speech),
      '#weight' => 1,
      '#states' => [
        'disabled' => [
          ':input[name="' . $field_name . '[target_id]"]' => ['filled' => TRUE],
        ],
      ],
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    if ($values['convert_text_to_speech']) {
      $massaged_values['target_id'] = 0;
      $massaged_values['convert_text_to_speech'] = $values['convert_text_to_speech'];
      return $massaged_values;
    }
    return $values['target_id'];
  }

}
