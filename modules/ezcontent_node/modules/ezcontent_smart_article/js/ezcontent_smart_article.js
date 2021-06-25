/**
 * @file
 */

(function ($, Drupal, drupalSettings) {

  "use strict";

  /**
   * Attaches the JS test behavior to to weight div.
   */
  Drupal.behaviors.ezcontent_smart_article = {
    attach: function (context, settings) {

      // For Smart Image tags.
      $('.image-tag-field-wrapper .tag-wrapper li', context).once().click(function () {
        var tagName = $(this).text();
        var imageWrapper = $(this).parents().eq(3);
        var tagsField = imageWrapper.find('.ui-autocomplete-input');
        var existingTags = tagsField.val();
        var finalTags = '';
        if (existingTags === '') {
          // If empty set val attribute.
          finalTags = existingTags + tagName;
        } else {
          finalTags = existingTags + ', ' + tagName;
        }
        tagsField.val(finalTags);
        $(this).addClass('list-disabled');
      });

      // Handle change event for smart image tags.
      $('.field--type-ezcontent-smart-image-tags .ui-autocomplete-input', context).on('change mouseleave', function () {
        var originalTags = $(this).val().split(', ');
        var newFormattedTags = [];
        $.map(originalTags, function (tag) {
          if (tag.indexOf('(') > -1) {
            tag = $.trim(tag.substring(0, tag.indexOf('(')));
            newFormattedTags.push(tag);
          } else {
            newFormattedTags.push(tag);
          }
        });
        $(this).parents().eq(2).find('.image-tag-field-wrapper .tag-wrapper li').each(function (i) {
          if ($.inArray($(this).text(), newFormattedTags) !== -1) {
            $(this).addClass('list-disabled');
          } else {
            $(this).removeClass('list-disabled');
          }
        });
      });
      // Handle hidden field on submit.
      $('.entity-browser-smart-image-browser-form .is-entity-browser-submit').click(function () {
        $("#smart_tag_hidden-text-id input").val("hidden");
      });

      // Open invalid subscription dialog.
      $('#edit-overlay-link', context).once('ezcontent_smart_article').trigger('click');
      // Generate tags as soon as image is uploaded.
      if (drupalSettings.imageTagOption == 'auto' && $(".image-preview")[0]) {
        $('.generate-tags-button', context).once('ezcontent_smart_article').trigger('click');
      }
    }
  };

  /**
   * Places the content in the text editor.
   */
  $.fn.update_text_editor = function (data, target_editor) {
    CKEDITOR.instances[target_editor].setData(data);
  };
  /**
   * Places the content in the summary field.
   */
  $.fn.update_summary_text = function (data, target_editor) {
    $('#' + target_editor).val(data);
  };
  /**
   * Places the content in the summary field.
   */
  $.fn.update_image_tags = function (data, target_editor) {
    var tag_input = $(this).parent().find('.ui-autocomplete-input');
    tag_input.mouseenter();
    tag_input.mouseleave();
  };

})(jQuery, Drupal, drupalSettings);
