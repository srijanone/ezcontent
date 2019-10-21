/**
 * @file
 */
(function ($, Drupal) {

  Drupal.behaviors.ezcontent_preview = {
    attach: function (context, settings) {
      var iframe_obj = $(".decoupled-content--preview");
      iframe_obj.height($(window).height());
    }
  };

})(jQuery, Drupal);
