(function () {
    'use strict';

    (function ($) {
      var ggmp_timeout = null;
      var GGMP_Admin = {
        init: function init() {
          GGMP_Admin.sortableInit();
          GGMP_Admin.searchProducts();
          GGMP_Admin.addResults();
          GGMP_Admin.dependencies();
          GGMP_Admin.removeProduct();
          GGMP_Admin.refreshIds();
        },
        sortableInit: function sortableInit() {
          var $ul = $('#ggmp_selected ul');

          if ($ul.length) {
            $ul.sortable({
              cursor: 'move',
              stop: function stop(event, ui) {
                GGMP_Admin.getIds();
              }
            }).disableSelection();
          }
        },
        searchProducts: function searchProducts() {
          $('#ggmp_search').keyup(function () {
            var keyword = $('#ggmp_search').val();
            var $loading = $('#ggmp_loading'),
                $results = $('#ggmp_results');

            if (keyword !== '') {
              $loading.show();

              if (ggmp_timeout != null) {
                clearTimeout(ggmp_timeout);
              }

              ggmp_timeout = setTimeout(function () {
                ggmp_timeout = null;
                var data = {
                  action: 'ggmp_get_search_results',
                  keyword: keyword,
                  id: $('#ggmp_id').val(),
                  ids: $('#ggmp_ids').val()
                };
                $.ajax({
                  url: ajaxurl,
                  method: 'POST',
                  data: data
                }).done(function (res) {
                  $results.show();
                  $results.html(res);
                  $loading.hide();
                  GGMP_Admin.addResults();
                });
              }, 300);
              return false;
            } else {
              $results.hide();
            }
          });
        },
        addResults: function addResults() {
          $('#ggmp_results li').on('click', function () {
            var $el = $(this);
            var id = $(this).data('id');
            $.ajax({
              url: ajaxurl,
              method: 'POST',
              data: {
                action: 'ggmp_add_result_product_meta',
                id: id
              }
            }).done(function (res) {
              if (res) {
                $el.remove();
                $('#ggmp_selected ul').append(res);
                $('#ggmp_results').hide();
                $('#ggmp_results li').remove();
                $('#ggmp_search').val('');
                GGMP_Admin.reInitWCToolTip();
                GGMP_Admin.sortableInit();
                GGMP_Admin.removeProduct();
                GGMP_Admin.getIds();
              }
            });
          });
        },
        reInitWCToolTip: function reInitWCToolTip() {
          $('.tips, .help_tip, .woocommerce-help-tip').tipTip({
            'attribute': 'data-tip',
            'fadeIn': 50,
            'fadeOut': 50,
            'delay': 200
          });
        },
        getIds: function getIds() {
          var ids = new Array();
          var $ids = $('#ggmp_ids');
          $('#ggmp_selected li').each(function () {
            if (!$(this).hasClass('ggmp_default')) {
              ids.push($(this).attr('data-id') + '/' + $(this).find('input.ggmp_price').val() + '/' + $(this).find('input.ggmp_qty').val());
            }
          });

          if (ids.length) {
            $ids.val(ids.join(','));
          } else {
            $ids.val('');
          }
        },
        refreshIds: function refreshIds() {
          $('#ggmp_selected').on('keyup change click', 'input', function () {
            GGMP_Admin.getIds();
            return false;
          });
        },
        dependencies: function dependencies() {
          $('#ggmp_custom_qty').on('click', function () {
            if ($(this).is(':checked')) {
              $('.ggmp_tr_show_if_custom_qty').show();
              $('.ggmp_tr_hide_if_custom_qty').hide();
            } else {
              $('.ggmp_tr_show_if_custom_qty').hide();
              $('.ggmp_tr_hide_if_custom_qty').show();
            }
          });
        },
        removeProduct: function removeProduct() {
          $('#ggmp_selected span.remove').on('click', function () {
            $(this).closest('li').remove();
            GGMP_Admin.getIds();
            return false;
          });
        }
      };
      $(GGMP_Admin.init);
    })(jQuery);

}());

//# sourceMappingURL=admin.js.map
