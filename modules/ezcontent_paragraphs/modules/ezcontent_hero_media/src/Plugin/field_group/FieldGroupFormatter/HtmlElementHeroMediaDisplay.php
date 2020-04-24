<?php

namespace Drupal\ezcontent_hero_media\Plugin\field_group\FieldGroupFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormState;
use Drupal\Core\Template\Attribute;
use Drupal\field_group\Plugin\field_group\FieldGroupFormatter\HtmlElement;
use Drupal\field_group\Element\HtmlElement as HtmlElementFormatter;

/**
 * Plugin implementation of the 'html_element_hero_media_display' formatter.
 *
 * @FieldGroupFormatter(
 *   id = "html_element_hero_media_display",
 *   label = @Translation("HTML element hero media display"),
 *   description = @Translation("This fieldgroup renders the inner content in a
 *   HTML element with classes and attributes."), supported_contexts = {
 *     "form",
 *     "view",
 *   }
 * )
 */
class HtmlElementHeroMediaDisplay extends HtmlElement {

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    parent::preRender($element, $rendering_object);
    $paragraph = $rendering_object['#paragraph'];
    $element_attributes = new Attribute();

    if ($this->getSetting('attributes')) {

      // This regex split the attributes string so that we can pass that
      // later to drupal_attributes().
      preg_match_all('/([^\s=]+)="([^"]+)"/', $this->getSetting('attributes'), $matches);

      // Put the attribute and the value together.
      foreach ($matches[1] as $key => $attribute) {
        $element_attributes[$attribute] = $matches[2][$key];
      }

    }

    // Add the id to the attributes array.
    if ($this->getSetting('id')) {
      $element_attributes['id'] = Html::getId($this->getSetting('id'));
    }

    // Add the classes to the attributes array.
    $classes = $this->getClasses();
    array_push($classes, $paragraph->field_text_position->value);
    // Intialize $bg as an array with default values.
    $bg = [];
    $bg_color_field_config = \Drupal::entityTypeManager()
      ->getStorage('field_config')
      ->load('paragraph.card.field_text_background_color');
    $default_value = 'default_value';
    $bg_defaults = $bg_color_field_config->get($default_value);
    $bg = $bg_defaults[0];
    if (!empty($paragraph->get("field_text_background_color")->first())) {
      $bg = $paragraph->get("field_text_background_color")->first()->getValue();
    }
    // @todo use DI instead of global service container.
    $hex2rgba = \Drupal::service('ezcontent_hero_media.hex2rgba');
    $bg_color = $hex2rgba->hex2rgba($bg['color'], $bg['opacity']);
    $element_attributes['style'] = "background-color: " . $bg_color . ";";
    if (!empty($classes)) {
      if (!isset($element_attributes['class'])) {
        $element_attributes['class'] = [];
      }
      // If user also entered class in the attributes textfield,
      // force it to an array.
      else {
        $element_attributes['class'] = [$element_attributes['class']];
      }
      $element_attributes['class'] = array_merge($classes, $element_attributes['class']->value());
    }

    $element['#effect'] = $this->getSetting('effect');
    $element['#speed'] = $this->getSetting('speed');
    $element['#type'] = 'field_group_html_element';
    $element['#wrapper_element'] = $this->getSetting('element');
    $element['#attributes'] = $element_attributes;
    if ($this->getSetting('show_label')) {
      $element['#title_element'] = $this->getSetting('label_element');
      $element['#title'] = Html::escape($this->getLabel());
    }

    $form_state = new FormState();
    HtmlElementFormatter::processHtmlElement($element, $form_state);

    if ($this->getSetting('required_fields')) {
      $element['#attributes']['class'][] = 'field-group-html-element';
      $element['#attached']['library'][] = 'field_group/formatter.html_element';
      $element['#attached']['library'][] = 'field_group/core';
    }
  }

}
