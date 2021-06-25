/**
 * @file
 */

(function ($, Drupal, drupalSettings) {

  "use strict";

  /**
   * Custom code for autocomplete deluxe smart tags.
   */
  Drupal.behaviors.ez_auto_deluxe_smart_tags = {
    attach: function (context, settings) {
      // Remove tag.
      $('.autocomplete-deluxe-item-delete', context).on('click', function () {
        $(this).parent().remove();
      });
    }
  };

  /**
   * Places the tags in the autocomplete deluxe value field.
   */
  $.fn.update_tags = function (data, target_editor) {
    var existingTags = $(this).val();
    if (existingTags === '"" ""') {
      $(this).val(data);
    }
    else {
      $(this).val(existingTags + ' ' + data);
    }
  };

})(jQuery, Drupal, drupalSettings);
