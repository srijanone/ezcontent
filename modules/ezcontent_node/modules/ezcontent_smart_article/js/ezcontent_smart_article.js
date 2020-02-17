/**
 * @file
 */

(function ($) {

  "use strict";

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
   * Show edit summary field if generate summary is clicked .
   */
  $.fn.update_edit_summary = function (data, target_editor) {
    if ($(target_editor).hasClass('edit_summary_open')) {
      $(target_editor).removeClass('edit_summary_open');
    }
    else {
      $(target_editor).addClass('edit_summary_open');
      $(target_editor).click();
    }
  };

})(jQuery, drupalSettings);
