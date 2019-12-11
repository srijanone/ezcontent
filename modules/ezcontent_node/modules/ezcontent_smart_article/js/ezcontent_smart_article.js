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

})(jQuery, drupalSettings);
