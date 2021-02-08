/**
 * @file
 * JQuery to allow past time for future date.
 */

(function ($, drupalSettings) {

  'use strict';

  /**
   * Allow past time for future date.
   */
  Drupal.behaviors.allowPastTimeForFutureDate = {
    attach: function (context) {
      // Handle onload.
      var publishedId = 'edit-publish-on-0-value-time';
      var unpublishedId = 'edit-unpublish-on-0-value-time';

      if ($('input#edit-publish-on-0-value-date').val() != drupalSettings.currentDate) {
        $('#' + publishedId).attr('min', '00:00:00');
      } else {
        var dt = new Date();
        var time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
        $('#' + publishedId).attr('min', time);
      }

      if ($('input#edit-unpublish-on-0-value-date').val() != drupalSettings.currentDate) {
        $('#' + unpublishedId).attr('min', '00:00:00');
      } else {
        var dt = new Date();
        var time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
        $('#' + unpublishedId).attr('min', time);
      }

      // Handle onchange.
      $('input#edit-publish-on-0-value-date , input#edit-unpublish-on-0-value-date', context).once().change(function () {
        var timeElementId = $(this).attr('id') === 'edit-publish-on-0-value-date'
          ? 'edit-publish-on-0-value-time' : 'edit-unpublish-on-0-value-time';
        if ($(this).val() != drupalSettings.currentDate) {
          $('#' + timeElementId).attr('min', '00:00:00');
        } else {
          var dt = new Date();
          var time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
          $('#' + timeElementId).attr('min', time);
        }
      });
    }
  };
})(jQuery, drupalSettings);
