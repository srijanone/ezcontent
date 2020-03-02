<?php

namespace Drupal\ezcontent_smart_article\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteTagsWidget;
use Drupal\Component\Utility\NestedArray;

/**
 * Plugin implementation of the 'ezcontent_smart_tags_autocomplete_tags' widget.
 *
 * @FieldWidget(
 *   id = "ezcontent_smart_tags_autocomplete_tags",
 *   label = @Translation("Autocomplete (Tags style)"),
 *   description = @Translation("An autocomplete text field with tagging support."),
 *   field_types = {
 *     "ezcontent_smart_tags"
 *   },
 *   multiple_values = TRUE,
 * )
 */
class EntityReferenceSmarttagsAutocompleteTagsWidget extends EntityReferenceAutocompleteTagsWidget {

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

    // Check that there aren't duplicate entity_id values.
    if (count($ids) !== count(array_flip($ids))) {
      $form_state->setError($element, 'Field "' . $element['target_id']['#title'] . '" doesn\'t allow duplicates.');
    }

  }

}
