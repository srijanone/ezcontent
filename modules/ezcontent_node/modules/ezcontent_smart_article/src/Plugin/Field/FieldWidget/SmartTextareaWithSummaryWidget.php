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
    // Removed js-text-summary class to hide edit summary link.
    $element['summary']['#attributes']['class'] = ["text-summary"];
    $element['summary']['#prefix'] = '<div class="visually-hidden">';
    $element['summary_container'] = [
      '#type' => 'details',
      '#title' => $this->t('Generate Summary'),
      '#description' => $this->t('Generate summary from text entered in body field above.'),
      '#weight' => 1,
      '#open' => FALSE,
      // Controls the HTML5 'open' attribute. Defaults to FALSE.
    ];
    $element['summary_container']['summary_area'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Summary'),
      '#default_value' => $items[$delta]->summary,
      '#rows' => $this->getSetting('summary_rows'),
      '#description' => $this->t('Leave blank to use trimmed value of full text as the summary.'),
      '#attributes' => ['class' => ['text-summary']],
      '#prefix' => '<div class="text-summary-wrapper">',
      '#suffix' => '</div>',
    ];
    $element['summary_container']['summary_type'] = [
      '#type' => 'radios',
      '#title' => $this->t("Summary type"),
      '#options' => [
        'abstractive' => $this->t('Abstractive'),
        'extractive' => $this->t('Extractive'),
      ],
      '#default_value' => 'abstractive',
    ];
    $element['summary_container']['number_of_sentences'] = [
      '#type' => 'number',
      '#title' => $this->t("Number of sentences"),
      '#min' => 1,
      '#default_value' => 5,
    ];
    $element['summary_container']['generate_smart_summary'] = [
      '#type' => 'button',
      '#name' => 'generate_smart_summary',
      '#value' => $this->t('Generate Smart Summary'),
    ];
    return $element;
  }

}
