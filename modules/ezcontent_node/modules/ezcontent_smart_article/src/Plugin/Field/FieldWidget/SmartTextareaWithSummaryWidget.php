<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\text\Plugin\Field\FieldWidget\TextareaWithSummaryWidget;

/**
 * Plugin implementation of the 'text_textarea_with_summary' widget.
 *
 * @FieldWidget(
 *   id = "smart_text_textarea_with_summary",
 *   label = @Translation("Smart Text area with a summary"),
 *   field_types = {
 *     "smart_text_with_summary"
 *   }
 * )
 */
class SmartTextareaWithSummaryWidget extends TextareaWithSummaryWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['summary_type'] = [
      '#type' => 'radios',
      '#title' => t("Summary type"),
      '#options' => [
        'abstractive' => 'Abstractive',
        'extractive' => 'Extractive',
      ],
      '#default_value' => 'abstractive',
      '#weight' => -9,
    ];
    $element['number_of_sentences'] = [
      '#type' => 'number',
      '#title' => t("Number of sentences"),
      '#min' => 1,
      '#default_value' => 5,
      '#weight' => -8,
    ];
    $element['generate_smart_summary'] = [
      '#type' => 'button',
      '#name' => 'generate_smart_summary',
      '#value' => t('Generate Smart Summary'),
      '#weight' => -7,
      '#attributes' => ['class' => ['link',]],
    ];
    return $element;
  }

}
