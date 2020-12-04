<?php

namespace Drupal\ezcontent_card_list\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;

/**
 * Plugin implementation of the 'options_select' widget.
 *
 * @FieldWidget(
 *   id = "ezcontent_cards_view_modes_options_select",
 *   label = @Translation("EzContent Cards View Mode Select list"),
 *   field_types = {
 *     "entity_reference",
 *     "list_integer",
 *     "list_float",
 *     "list_string"
 *   },
 *   multiple_values = TRUE
 * )
 */
class CardsViewModeOptionsSelectWidget extends OptionsSelectWidget {

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel() {
    if ($this->multiple) {
      // Multiple select: add a 'none' option for non-required fields.
      if (!$this->required) {
        return t('Cards Grid (1xn)');
      }
    }
    else {
      // Single select: add a 'none' option for non-required fields,
      // and a 'select a value' option for required fields that do not come
      // with a value selected.
      if (!$this->required) {
        return t('Cards Grid (1xn)');
      }
      if (!$this->has_value) {
        return t('- Select a value -');
      }
    }
  }

}
