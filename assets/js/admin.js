(function () {
  'use strict';

  (function ($) {
    var GGMP_Admin = {
      init: function init() {
        GGMP_Admin.update_status();
      },
      update_status: function update_status() {
        $('.js-ggmp-change-status').on('change', function () {
          var account_id = $(this).attr('id'),
              enable = $(this)[0].checked ? 1 : 0;
          $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
              action: 'ggmp_update_account_status',
              account_id: account_id,
              enable: enable
            },
            beforeSend: function beforeSend() {}
          }).always(function () {}).done(function (res) {}).fail(function (err) {});
        });
      }
    };
    $(GGMP_Admin.init);
  })(jQuery);

}());

//# sourceMappingURL=admin.js.map
