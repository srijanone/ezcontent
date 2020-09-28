/*
 * @file ezcontent_block_cards.js
 * Contains js functionality related to card block.
 */
(function (Drupal, $) {

    Drupal.behaviors.ezcontent_block_cards = {
      attach: function (context, settings) {

        // On load.     
        var viewMode = $("select[name='settings[block_form][view_mode_selection]']").val();
        if (viewMode == 'block_content.cards_grid_3xn'){
            $(".field--name-layout-selection select").val('_none');
            $(".field--name-layout-selection select").attr("disabled","disabled");
            $(".field--name-layout-selection").hide();
          } else {
            $(".field--name-layout-selection select").removeAttr('disabled');
            $(".field--name-layout-selection").show();  
          }

        // On field Change
        $("body").on("change", "select[name='settings[block_form][view_mode_selection]']", function () {
            var viewMode = $(this).val();
            if (viewMode == 'block_content.cards_grid_3xn'){
              $(".field--name-layout-selection select").val('_none');
              $(".field--name-layout-selection select").attr("disabled","disabled");
              $(".field--name-layout-selection").hide();
            } else {
              $(".field--name-layout-selection select").removeAttr('disabled');
              $(".field--name-layout-selection").show();  
            }
        });
      }
    };
  
  })(Drupal, jQuery);
