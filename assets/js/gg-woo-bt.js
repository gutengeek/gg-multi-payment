(function () {
    'use strict';

    (function ($) {
      var GGMP = {
        init: function init() {
          GGMP.run();
          GGMP.access();
          GGMP.changeQty();
          GGMP.hookFoundVariation();
          GGMP.hookResetData();
        },
        run: function run() {
          $(document).ready(function ($) {
            var $wraper = $('.ggmp-wrap');

            if (!$wraper.length) {
              return;
            }

            $wraper.each(function () {
              GGMP.prepareData($(this));
            });
          });
        },
        access: function access() {
          $(document).on('click touch', '.single_add_to_cart_button', function (e) {
            if ($(this).hasClass('ggmp-disabled')) {
              e.preventDefault();
            }
          });
          $(document).on('change', '.ggmp-checkbox', function () {
            var $wrap = $(this).closest('.ggmp-wrap');
            GGMP.prepareData($wrap);
          });
          $(document).on('change keyup mouseup', '.ggmp-main-qty', function () {
            var value = $(this).val();
            $(this).closest('.ggmp-product-main').attr('data-qty', value);
            $(this).closest('.summary').find('form.cart .quantity .qty').val(value).trigger('change');
          });
          $(document).on('change keyup mouseup', '.ggmp-qty', function () {
            var $el = $(this);
            var $wraper = $el.closest('.ggmp-wrap');
            var $product = $el.closest('.ggmp-product');
            var $checkbox = $product.find('.ggmp-checkbox');
            var value = parseFloat($el.val());

            if ($checkbox.prop('checked')) {
              var min_val = parseFloat($el.attr('min'));
              var max_val = parseFloat($el.attr('max'));

              if (value < min_val) {
                $el.val(min_val);
              }

              if (value > max_val) {
                $el.val(max_val);
              }

              $product.attr('data-qty', $el.val());
              GGMP.prepareData($wraper);
            }
          });
        },
        prepareData: function prepareData($wrap) {
          var wrap_id = $wrap.attr('data-id');

          if (wrap_id !== undefined && parseInt(wrap_id) > 0) {
            var container = GGMP.getContainer(wrap_id);
            var $container = $wrap.closest(container);
            GGMP.isReady($container);
            GGMP.calcPrice($container);
            GGMP.saveIds($container);

            if (ggmp_params.counter !== 'hide') {
              GGMP.updateCount($container);
            }
          }
        },
        hookFoundVariation: function hookFoundVariation() {
          $(document).on('found_variation', function (e, t) {
            var $wrap = $(e['target']).closest('.ggmp-wrap');
            var $products = $(e['target']).closest('.ggmp-products');
            var $product = $(e['target']).closest('.ggmp-product');

            if ($product.length) {
              var new_price = $product.attr('data-new-price');

              if (isNaN(new_price)) {
                new_price = t['display_price'] * parseFloat(new_price) / 100;
              }

              $product.find('.ggmp-price-ori').hide();
              $product.find('.ggmp-price-new').html(GGMP.priceHtml(t['display_price'], new_price)).show();

              if (t['is_purchasable'] && t['is_in_stock']) {
                $product.attr('data-id', t['variation_id']);
                $product.attr('data-price', t['display_price']);
              } else {
                $product.attr('data-id', 0);
              }

              if (t['availability_html'] && t['availability_html'] !== '') {
                $product.find('.ggmp-availability').html(t['availability_html']).show();
              } else {
                $product.find('.ggmp-availability').html('').hide();
              }

              if (t['image']['url'] && t['image']['srcset']) {
                $product.find('.ggmp-thumb-ori').hide();
                $product.find('.ggmp-thumb-new').html('<img src="' + t['image']['url'] + '" srcset="' + t['image']['srcset'] + '"/>').show();
              }

              $('.product_meta .sku').text($products.attr('data-product-sku'));
              $(e['target']).closest('.variations_form').trigger('reset_image');
            } else {
              $wrap = $(e['target']).closest('.summary').find('.ggmp-wrap');
              $products = $(e['target']).closest('.summary').find('.ggmp-products');
              $products.attr('data-product-id', t['variation_id']);
              $products.attr('data-product-sku', t['sku']);
              $products.attr('data-product-price', t['display_price']);
            }

            GGMP.prepareData($wrap);
          });
        },
        hookResetData: function hookResetData() {
          $(document).on('reset_data', function (e) {
            var $wrap = $(e['target']).closest('.ggmp-wrap');
            var $products = $(e['target']).closest('.ggmp-products');
            var $product = $(e['target']).closest('.ggmp-product');

            if ($product.length) {
              $product.attr('data-id', 0);
              $(e['target']).closest('.variations_form').find('p.stock').remove();
              $('.product_meta .sku').text($products.attr('data-product-sku'));
              $product.find('.ggmp-availability').html('').hide();
              $product.find('.ggmp-thumb-new').hide();
              $product.find('.ggmp-thumb-ori').show();
              $product.find('.ggmp-price-new').hide();
              $product.find('.ggmp-price-ori').show();
            } else {
              $wrap = $(e['target']).closest('.summary').find('.ggmp-wrap');
              $products = $(e['target']).closest('.summary').find('.ggmp-products');
              $products.attr('data-product-id', 0);
              $products.attr('data-product-price', 0);
              $products.attr('data-product-sku', $products.attr('data-product-o-sku'));
            }

            GGMP.prepareData($wrap);
          });
        },
        getContainer: function getContainer(id) {
          var $wrap_el = $('.ggmp-wrap-' + id);

          if ($wrap_el.closest('#product-' + id).length) {
            return '#product-' + id;
          }

          if ($wrap_el.closest('.product.post-' + id).length) {
            return '.product.post-' + id;
          }

          if ($wrap_el.closest('div.product').length) {
            return 'div.product';
          }

          return 'body.single-product';
        },
        isReady: function isReady($wrap) {
          var $products = $wrap.find('.ggmp-products');
          var $notice = $wrap.find('.ggmp-notice');
          var $ids = $wrap.find('.ggmp-ids');
          var $btn = $wrap.find('.single_add_to_cart_button');
          var is_selection = false;
          var selection_name = '';
          var optional = $products.attr('data-optional');

          if (optional === 'on' && $products.find('.ggmp-product-main').length > 0) {
            $('form.cart > .quantity').hide();
            $('form.cart .woocommerce-variation-add-to-cart > .quantity').hide();
          }

          if (ggmp_params.position === 'before_add_to_cart' && $products.attr('data-product-type') === 'variable' && $products.attr('data-variables') === 'no') {
            $products.closest('.ggmp-wrap').insertAfter($ids);
            $products.find('.ggmp-qty').removeClass('qty');
          }

          $products.find('.ggmp-product-together').each(function () {
            var $el = $(this);
            var is_checked = $el.find('.ggmp-checkbox').prop('checked');
            var val_id = parseInt($el.attr('data-id'));

            if (!is_checked) {
              $el.addClass('ggmp-unchecked');

              if (!$el.hasClass('show-variation-select')) {
                $el.find('.variations_form').hide();
              }
            } else {
              $el.removeClass('ggmp-unchecked');

              if (!$el.hasClass('show-variation-select')) {
                $el.find('.variations_form').show();
              }
            }

            if (is_checked && val_id == 0) {
              is_selection = true;

              if (selection_name === '') {
                selection_name = $el.attr('data-name');
              }
            }
          });

          if (is_selection) {
            $btn.addClass('ggmp-disabled ggmp-selection');
            $notice.html(ggmp_params.text.variation_notice.replace('%s', '<strong>' + selection_name + '</strong>')).slideDown();
          } else {
            $btn.removeClass('ggmp-disabled ggmp-selection');
            $notice.html('').slideUp();
          }
        },
        changeQty: function changeQty() {
          $(document).on('change', 'form.cart .qty', function () {
            var $el = $(this);
            var qty = parseFloat($el.val());

            if ($el.hasClass('ggmp-qty')) {
              return;
            }

            if (!$el.closest('form.cart').find('.ggmp-ids').length) {
              return;
            }

            var wrap_id = $el.closest('form.cart').find('.ggmp-ids').attr('data-id');
            var $wrap = $('.ggmp-wrap-' + wrap_id);
            var $products = $wrap.find('.ggmp-products');
            var optional = $products.attr('data-optional');
            var sync_qty = $products.attr('data-sync-qty');
            $products.find('.ggmp-product-main').attr('data-qty', qty);

            if (optional !== 'on' && sync_qty === 'on') {
              $products.find('.ggmp-product-together').each(function () {
                var _qty = parseFloat($(this).attr('data-qty-ori')) * qty;

                $(this).attr('data-qty', _qty);
                $(this).find('.ggmp-qty-num .ggmp-qty').html(_qty);
              });
            }

            GGMP.prepareData($wrap);
          });
        },
        calcPrice: function calcPrice($wrap) {
          var $products = $wrap.find('.ggmp-products');
          var $product_this = $products.find('.ggmp-product-main');
          var $total = $wrap.find('.ggmp-total');
          var $btn = $wrap.find('.single_add_to_cart_button');
          var count = 0,
              total = 0;
          var total_html = '';
          var discount = parseFloat($products.attr('data-discount'));
          var ori_price = parseFloat($products.attr('data-product-price'));
          var ori_price_suffix = $products.attr('data-product-price-suffix');
          var ori_qty = parseFloat($btn.closest('form.cart').find('input.qty').val());
          var total_ori = ori_price * ori_qty;
          var main_price_selector = ggmp_params.main_price_selector;
          var show_price = $products.attr('data-show-price');
          var fix = Math.pow(10, Number(ggmp_params.price_decimals) + 1);
          $products.find('.ggmp-product-together').each(function () {
            var $el = $(this);

            var _checked = $el.find('.ggmp-checkbox').prop('checked');

            var _id = parseInt($el.attr('data-id'));

            var _qty = parseFloat($el.attr('data-qty'));

            var _price = $el.attr('data-new-price');

            var _price_suffix = $el.attr('data-price-suffix');

            var origin_price = $el.attr('data-price');
            var regular_price = $el.attr('data-regular-price');
            var origin_total = 0,
                _total = 0;

            if (_qty > 0 && _id > 0) {
              origin_total = _qty * origin_price;

              if (isNaN(_price)) {
                if (_price == '100%') {
                  origin_total = _qty * regular_price;
                  _total = _qty * origin_price;
                } else {
                  _total = origin_total * parseFloat(_price) / 100;
                }
              } else {
                _total = _qty * _price;
              }

              if (show_price === 'total') {
                $el.find('.ggmp-price-ori').hide();
                $el.find('.ggmp-price-new').html(GGMP.priceHtml(origin_total, _total) + _price_suffix).show();
              }

              if (_checked) {
                count++;
                total += _total;
              }
            }
          });
          total = Math.round(total * fix) / fix;

          if ($product_this.length) {
            var _qty = parseFloat($product_this.attr('data-qty'));

            var _price_suffix = $product_this.attr('data-price-suffix');

            if (total > 0) {
              var _price = $product_this.attr('data-new-price');

              var origin_price = $product_this.attr('data-price');

              var origin_total = _qty * origin_price,
                  _total = _qty * _price;

              $product_this.find('.ggmp-price-ori').hide();
              $product_this.find('.ggmp-price-new').html(GGMP.priceHtml(origin_total, _total) + _price_suffix).show();
            } else {
              var _price = $product_this.attr('data-price');

              var regular_price = $product_this.attr('data-regular-price');

              var origin_total = _qty * regular_price,
                  _total = _qty * _price;

              $product_this.find('.ggmp-price-ori').hide();
              $product_this.find('.ggmp-price-new').html(GGMP.priceHtml(origin_total, _total) + _price_suffix).show();
            }
          }

          if (count > 0) {
            total_html = GGMP.formatPrice(total);
            $total.html(ggmp_params.text.additional_price + ' ' + total_html + ori_price_suffix).slideDown();

            if (isNaN(discount)) {
              discount = 0;
            }

            total_ori = total_ori * (100 - discount) / 100 + total;
          } else {
            $total.html('').slideUp();
          }

          if (ggmp_params.recal_price !== 'off') {
            if (parseInt($products.attr('data-product-id')) > 0) {
              $(main_price_selector).html(GGMP.formatPrice(total_ori) + ori_price_suffix);
            } else {
              $(main_price_selector).html($products.attr('data-product-price-html'));
            }
          }

          $(document).trigger('calcPrice', [total, total_html]);
          $wrap.find('.ggmp-wrap').attr('data-total', total);
        },
        saveIds: function saveIds($wrap) {
          var $products = $wrap.find('.ggmp-products');
          var $ids = $wrap.find('.ggmp-ids');
          var items = [];
          $products.find('.ggmp-product-together').each(function () {
            var $el = $(this);

            var _checked = $el.find('.ggmp-checkbox').prop('checked');

            var _id = parseInt($el.attr('data-id'));

            var _qty = parseFloat($el.attr('data-qty'));

            var _price = $el.attr('data-new-price');

            if (_checked && _qty > 0 && _id > 0) {
              items.push(_id + '/' + _price + '/' + _qty);
            }
          });

          if (items.length > 0) {
            $ids.val(items.join(','));
          } else {
            $ids.val('');
          }
        },
        updateCount: function updateCount($wrap) {
          var $products = $wrap.find('.ggmp-products');
          var $btn = $wrap.find('.single_add_to_cart_button');
          var qty = 0;
          var num = 1;
          $products.find('.ggmp-product-together').each(function () {
            var $el = $(this);

            var _checked = $el.find('.ggmp-checkbox').prop('checked');

            var _id = parseInt($el.attr('data-id'));

            var _qty = parseFloat($el.attr('data-qty'));

            if (_checked && _qty > 0 && _id > 0) {
              qty += _qty;
              num++;
            }
          });

          if ($btn.closest('form.cart').find('input.qty').length) {
            qty += parseFloat($btn.closest('form.cart').find('input.qty').val());
          }

          if (num > 1) {
            if (ggmp_params.counter === 'individual') {
              $btn.text(ggmp_params.text.add_to_cart + ' (' + num + ')');
            } else {
              $btn.text(ggmp_params.text.add_to_cart + ' (' + qty + ')');
            }
          } else {
            $btn.text(ggmp_params.text.add_to_cart);
          }

          $(document.body).trigger('updateCount', [num, qty]);
        },
        priceHtml: function priceHtml(regular_price, sale_price) {
          var price_html = '';

          if (sale_price < regular_price) {
            price_html = '<del>' + GGMP.formatPrice(regular_price) + '</del> <ins>' + GGMP.formatPrice(sale_price) + '</ins>';
          } else {
            price_html = GGMP.formatPrice(regular_price);
          }

          return price_html;
        },
        formatPrice: function formatPrice(total) {
          var total_html = '<span class="woocommerce-Price-amount amount">';
          var total_formatted = GGMP.formatMoney(total, ggmp_params.price_decimals, '', ggmp_params.price_thousand_separator, ggmp_params.price_decimal_separator);

          switch (ggmp_params.price_format) {
            case '%1$s%2$s':
              total_html += '<span class="woocommerce-Price-currencySymbol">' + ggmp_params.currency_symbol + '</span>' + total_formatted;
              break;

            case '%1$s %2$s':
              total_html += '<span class="woocommerce-Price-currencySymbol">' + ggmp_params.currency_symbol + '</span> ' + total_formatted;
              break;

            case '%2$s%1$s':
              total_html += total_formatted + '<span class="woocommerce-Price-currencySymbol">' + ggmp_params.currency_symbol + '</span>';
              break;

            case '%2$s %1$s':
              total_html += total_formatted + ' <span class="woocommerce-Price-currencySymbol">' + ggmp_params.currency_symbol + '</span>';
              break;

            default:
              total_html += '<span class="woocommerce-Price-currencySymbol">' + ggmp_params.currency_symbol + '</span> ' + total_formatted;
          }

          total_html += '</span>';
          return total_html;
        },
        formatMoney: function formatMoney(number, places, symbol, thousand, decimal) {
          number = number || 0;
          places = !isNaN(places = Math.abs(places)) ? places : 2;
          symbol = symbol !== undefined ? symbol : '$';
          thousand = thousand !== undefined ? thousand : ',';
          decimal = decimal !== undefined ? decimal : '.';
          var negative = number < 0 ? '-' : '',
              i = parseInt(number = Math.abs(+number || 0).toFixed(places), 10) + '',
              j = 0;

          if (i.length > 3) {
            j = i.length % 3;
          }

          return symbol + negative + (j ? i.substr(0, j) + thousand : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + thousand) + (places ? decimal + Math.abs(number - i).toFixed(places).slice(2) : '');
        }
      };
      $(GGMP.init);
    })(jQuery);

}());

//# sourceMappingURL=ggmp.js.map
