<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteTagsWidget;
use Drupal\file\Entity\File;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Plugin implementation of the 'entity_reference_autocomplete_tags' widget.
 *
 * @FieldWidget(
 *   id = "smart_entity_reference_autocomplete_tags",
 *   label = @Translation("Smart Autocomplete (Tags style)"),
 *   description = @Translation("An autocomplete text field with tagging
 *   support."), field_types = {
 *    "ezcontent_smart_image_tags",
 *   },
 *   multiple_values = TRUE
 * )
 */
class SmartEntityReferenceAutocompleteTagsWidget extends EntityReferenceAutocompleteTagsWidget {

  /**
   * {@inheritdoc}
   */
  public static function afterBuild(array $element, FormStateInterface $form_state) {
    parent::afterBuild($element, $form_state);
    $class = get_class();
    $element['#element_validate'][] = [$class, 'validateNoDuplicates'];
    return $element;
  }

  /**
   * Set a form error if there are duplicate entity ids.
   */
  public static function validateNoDuplicates(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $input = NestedArray::getValue($form_state->getValues(), $element['#parents']);
    $ids = array_column($input['target_id'], 'target_id');
    if (count($ids) !== count(array_flip($ids))) {
      $form_state->setError($element, t("Field @field_title doesn\'t allow duplicates.", ['@field_title' => $element['target_id']['#title']]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['target_id']['#tags'] = TRUE;
    $element['target_id']['#default_value'] = $items->referencedEntities();
    $entity = $items->getEntity();
    $field_settings = $items->getFieldDefinition()->getSettings();
    $image_field_name = $field_settings['image_fields'];
    $uuid = $entity->uuid();
    $element['#attached']['library'][] = 'ezcontent_smart_article/ezcontent_smart_article_libs';
    $element['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];
    // To handle form rebuild with.
    if ($form_state->getFormObject()->getFormId() == 'entity_browser_smart_image_browser_form') {
      $element['smart_tag_hidden'] = [
        "#type" => 'textfield',
        '#required' => TRUE,
        '#prefix' => '<div class="hidden" id="smart_tag_hidden-text-id">',
        '#suffix' => '</div>',
        '#weight' => 0,
      ];
    }
    // Add generate tags button.
    $element['auto_image_tags'] = [
      '#prefix' => '<div class="image-tag-field-wrapper" id="auto-image-tags">',
      '#suffix' => '</div>',
      '#weight' => 0,
    ];
    $element['generate_image_tags'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Image Tags'),
      '#weight' => 11,
      '#data' => $uuid,
      '#image_field_name' => $image_field_name,
      '#prefix' => '<div>',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => [$this, 'ezcontentSmartImageTagsGenerateCallback'],
        'wrapper' => 'auto-image-tags',
        'effect' => 'fade',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => 'Please wait...',
        ],
      ],
    ];
    return $element;
  }

  /**
   * Image Tags generator callback.
   */
  public function ezcontentSmartImageTagsGenerateCallback(array &$form, FormStateInterface $form_state) {
    $uuid = $form_state->getTriggeringElement()['#data'];
    $image_field_name = $form_state->getTriggeringElement()['#image_field_name'];
    $media = $form_state->getValue([]);
    $field_data = $media[$uuid][$image_field_name];
    $fid = $field_data[0]['fids'][0];
    if (empty($fid) && !empty($form_state->getValue($image_field_name))) {
      $file_id = reset($form_state->getValue($image_field_name));
      $fid = $file_id['fids'][0];
    }
    if (!empty($fid)) {
      // @todo: fetch file object from form_state.
      $file = File::load($fid);
      $imageTaggingManager = \Drupal::service('plugin.manager.image_tagging');
      $serviceType = \Drupal::config('summary_generator.settings')
        ->get('image_tagging_service');
      $plugin = $imageTaggingManager->createInstance($serviceType);
      $tags = $plugin->getImageTags($file);
      if ($tags) {
        $response = new AjaxResponse();
        $renderer = \Drupal::service('renderer');
        $auto_tags = [
          '#theme' => 'smarttag_template',
          '#tags' => $tags,
        ];
        $rendered_field = $renderer->render($auto_tags);
        $response->addCommand(new HtmlCommand('#auto-image-tags', $rendered_field));
        $arguments = [NULL, ''];
        $response->addCommand(new InvokeCommand(NULL, "update_image_tags", $arguments));
        return $response;
      }

    }
  }

}
