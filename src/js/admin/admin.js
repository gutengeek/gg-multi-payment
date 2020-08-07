( function ( $ ) {
  var GGMP_Admin = {
    init: function () {
      GGMP_Admin.update_status();
    },
    update_status: function () {
      $( '.js-ggmp-change-status' ).on( 'change', function () {
        var account_id = $( this ).attr( 'id' ),
          enable = ( $( this )[ 0 ].checked ) ? 1 : 0;

        $.ajax( {
          url: ajaxurl,
          method: 'POST',
          data: {
            action: 'ggmp_update_account_status',
            account_id: account_id,
            enable: enable,
          },
          beforeSend: () => {}
        } ).always( () => {

        } ).done( ( res ) => {
        } ).fail( ( err ) => {
        } );
      } );
    },
  };

  $( GGMP_Admin.init );

} )( jQuery );
