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
      $('#tag-field-wrapper .tag-wrapper li', context).click(function(e) {
        //e.preventDefault();
        var text = $(this).text();
        var x = $('#tag-field-wrapper').prev().attr('id');
        var panel= $("#"+x+"-target-id").val();
        var conc = '';
        if(panel == '') {
          conc = panel + text;
        } else {
          conc = panel + ', ' + text;
        }
        $("#"+x+"-target-id").val(conc);
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

})(jQuery, Drupal, drupalSettings);
