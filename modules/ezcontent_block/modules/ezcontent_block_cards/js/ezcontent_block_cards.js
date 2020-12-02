/*
 * @file ezcontent_block_cards.js
 * Contains js functionality related to card block.
 */
(function (Drupal, $) {

    Drupal.behaviors.ezcontent_block_cards = {
      attach: function (context, settings) {
        // On load.     
        var viewMode = $("select[name='settings[block_form][view_mode_selection]']").val();
        var itemSelectParent = $(".field--name-layout-selection");
        var itemSelect = $(".field--name-layout-selection select");
        if (viewMode === 'block_content.cards_grid_3xn'){
            itemSelect.val('_none');
            itemSelect.attr("disabled","disabled");
            itemSelectParent.hide();
          } else {
            itemSelect.removeAttr('disabled');
            itemSelectParent.show();
          }

        // On field Change
        $("body").on("change", "select[name='settings[block_form][view_mode_selection]']", function () {
            var viewMode = $(this).val();
            if (viewMode === 'block_content.cards_grid_3xn'){
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
