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
      var uuidArray = drupalSettings.uuid;
      $.each(uuidArray, function(uuidKey, uuidValue) {
      var dynamicId = 'auto-image-tags-' + uuidValue;
      var dynamicName = uuidValue + '[field_smart_image_tags][target_id]';
      $('.field--type-ezcontent-smart-image-tags [id="' + dynamicId + '"] .tag-wrapper li', context).click(function (e) {
        var tagName = $(this).text();
        var existingTags = $('.field--type-ezcontent-smart-image-tags [name="' + dynamicName + '"]').val();
        var finalTags = '';
        if (existingTags === '') {
          // If empty set val attribute.
          finalTags = existingTags + tagName;
        } else {
          finalTags = existingTags + ', ' + tagName;
        }
        $('.field--type-ezcontent-smart-image-tags [name="' + dynamicName + '"]').val(finalTags);
        $(this).addClass('list-disabled');
      });
      // Handle change event.
      $('.field--type-ezcontent-smart-image-tags [name="' + dynamicName + '"]', context).on('change mouseleave', function (e) {
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
        $('.field--type-ezcontent-smart-image-tags [id="' + dynamicId + '"] .tag-wrapper li').each(function (i) {
          if ($.inArray($(this).text(), newFormattedTags) !== -1) {
            $(this).addClass('list-disabled');
          } else {
            $(this).removeClass('list-disabled');
          }
        });
      });

      $('.field--type-ezcontent-smart-tags [id="' + dynamicId + '"] .tag-wrapper li', context).click(function (e) {
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
    var tag_input = $('.field--type-ezcontent-smart-image-tags').find(target_editor + '[field_smart_image_tags][target_id]');
    tag_input.mouseenter();
    tag_input.mouseleave();
  };

})(jQuery, Drupal, drupalSettings);
