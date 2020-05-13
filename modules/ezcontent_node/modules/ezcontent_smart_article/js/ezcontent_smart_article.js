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
      $('.field--type-ezcontent-smart-image-tags .image-tag-field-wrapper .tag-wrapper li', context).click(function (e) {
        var tagName = $(this).text();
        var existingTags = $('.field--type-ezcontent-smart-image-tags .ui-autocomplete-input').val();
        var finalTags = '';
        if (existingTags === '') {
          // If empty set val attribute.
          finalTags = existingTags + tagName;
        } else {
          finalTags = existingTags + ', ' + tagName;
        }
        $('.field--type-ezcontent-smart-image-tags .ui-autocomplete-input').val(finalTags);
        $(this).addClass('list-disabled');
      });
      // Handle change event.
      $('.field--type-ezcontent-smart-image-tags .ui-autocomplete-input', context).on('change mouseleave', function (e) {
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
        $('.field--type-ezcontent-smart-image-tags .image-tag-field-wrapper .tag-wrapper li').each(function (i) {
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
    var tag_input = $('.field--type-ezcontent-smart-image-tags .ui-autocomplete-input');
    tag_input.mouseenter();
    tag_input.mouseleave();
  };

})(jQuery, Drupal, drupalSettings);
