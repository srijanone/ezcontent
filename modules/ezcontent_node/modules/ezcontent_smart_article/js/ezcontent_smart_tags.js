/**
 * @file
 */

(function ($, Drupal, drupalSettings) {

  "use strict";

  /**
   * Custom code for autocomplete tags style smart tags.
   */
  Drupal.behaviors.ezcontent_smart_tags = {
    attach: function (context, settings) {
      // On click of tags suggestions.
      $('.field--type-ezcontent-smart-tags .tag-field-wrapper .tag-wrapper li', context).click(function (e) {
        var tagName = $(this).text();
        var existingTags = $('.field--type-ezcontent-smart-tags .tags-link-field .ui-autocomplete-input').val();
        var finalTags = '';
        if (existingTags === '') {
          finalTags = existingTags + tagName;
        } else {
          finalTags = existingTags + ', ' + tagName;
        }
        $('.field--type-ezcontent-smart-tags .tags-link-field .ui-autocomplete-input').val(finalTags);
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
