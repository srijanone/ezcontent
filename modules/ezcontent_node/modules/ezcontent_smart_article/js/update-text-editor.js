(function ($) {
  $.fn.update_text_editor = function (data, target_editor) {
    CKEDITOR.instances[target_editor].setData(data);
  };
})(jQuery);