/*
 * @file ezcontent_block_cards.js
 * Contains js functionality related to card block.
 */
(function (Drupal, $) {

  Drupal.behaviors.ezcontent_paragraph_cards = {
    attach: function (context, settings) {
      //On load.
      var viewMode = $(".field--name-view-mode-selection select").val();
      var itemSelectParent = $(".field--name-layout-selection");
      var itemSelect = $(".field--name-layout-selection select");
      if (viewMode === 'paragraph.cards_grid'){
        itemSelect.val('_none');
        itemSelect.attr("disabled","disabled");
        itemSelect.hide();
      } else {
        itemSelect.removeAttr('disabled');
        itemSelectParent.show();
      }

      // On field Change
      $("body").on("change", ".field--name-view-mode-selection select", function () {
        var viewMode = $(this).val();
        if (viewMode === 'paragraph.cards_grid'){
          itemSelect.val('_none');
          itemSelect.attr("disabled","disabled");
          itemSelectParent.hide();
        } else {
          itemSelect.removeAttr('disabled');
          itemSelectParent.show();
        }
      });
    }
  };

})(Drupal, jQuery);
