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
