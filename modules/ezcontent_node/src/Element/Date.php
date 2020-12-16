<?php

namespace Drupal\ezcontent_node\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Date as CoreDate;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Entity\ContentEntityForm;

/**
 * Provides a form element for date selection.
 */
class Date extends CoreDate {

  /**
   * {@inheritdoc}
   */
  public static function processDate(&$element, FormStateInterface $form_state, &$complete_form) {
    // Process only in case this element is being rendered in a node form and
    // case where scheduler is enabled and is past dates are not allowed.
    $formObject = $form_state->getFormObject();
    if ($formObject instanceof ContentEntityForm &&
      !empty($formObject->getEntity()->type->entity) &&
      $formObject->getEntity()->type->entity->getThirdPartySetting('scheduler', 'publish_past_date') == 'error' &&
      ($element['#parents'][0] == 'publish_on' || $element['#parents'][0] == 'unpublish_on')
    ) {
      // Add min to Publish and UnPublish date and time.
      $date = new DrupalDateTime();
      if ($element['#attributes']['type'] == 'date') {
        $dateFormat = !empty($element['#date_date_format']) ? $element['#date_date_format'] : DateFormat::load('html_date')->getPattern();
        $element['#attributes']['min'] = $date->format($dateFormat);
        // Placeholder for browsers not supporting date element, like Safari.
        $element['#attributes']['placeholder'] = $date->format($dateFormat);
        // Attach library.
        $element['#attached']['library'][] = 'ezcontent_node/ezcontent_node_datetime_libs';
        $element['#attached']['drupalSettings']['currentDate'] = $date->format($dateFormat);
      }
      elseif ($element['#attributes']['type'] == 'time') {
        $dateFormat = !empty($element['#date_time_format']) ? $element['#date_time_format'] : DateFormat::load('html_time')->getPattern();
        $element['#attributes']['min'] = $date->format($dateFormat);
        // Placeholder for browsers not supporting time element, like Safari.
        $element['#attributes']['placeholder'] = $date->format($dateFormat);
      }
    }

    return $element;
  }

}
