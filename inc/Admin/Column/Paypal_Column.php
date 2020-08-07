<?php
namespace GGMP\Admin\Column;

class Paypal_Column {

	/**
	 * Paypal_Column constructor.
	 */
	public function __construct() {
		add_filter( 'manage_ggmp_paypal_posts_columns', [ $this, 'set_custom_edit_columns' ] );
		add_action( 'manage_ggmp_paypal_posts_custom_column', [ $this, 'custom_column' ], 10, 2 );
	}

	public function set_custom_edit_columns( $columns ) {
		unset( $columns['date'] );
		$columns['enable']               = __( 'Enable', 'ggmp' );
		$columns['api_username']         = __( 'Live API Username', 'ggmp' );
		$columns['sandbox_api_username'] = __( 'Sandbox API Username', 'ggmp' );
		$columns['deposited_today']      = __( 'Deposited today', 'ggmp' );
		$columns['date']                 = __( 'Date', 'ggmp' );

		return $columns;
	}

	// Add the data to the custom columns for the book post type:
	public function custom_column( $column, $post_id ) {
		$account = ggmp_paypal( $post_id );
		switch ( $column ) {
			case 'enable' :
				$checked = $account->is_enable() ? 'checked' : '';
				$switch  = '<label class="ggmp-enable-switch-input">';
				$switch  .= '<input type="checkbox" id="' . esc_attr( $post_id ) . '" value="on" class="js-ggmp-change-status ggmp-enable-switch form-control" ' . $checked . ' />';
				$switch  .= '<span class="slider round"></span>';
				$switch  .= '</label>';

				echo $switch;
				break;

			case 'api_username' :
				echo '<code>' . $account->get_api_username() ? $account->get_api_username() : '---' . '</code>';
				break;

			case 'sandbox_api_username' :
				echo '<code>' . $account->get_sandbox_api_username() ? $account->get_sandbox_api_username() : '---' . '</code>';
				break;

			case 'deposited_today' :
				echo function_exists( 'wc_price' ) ? wc_price( $account->get_deposit() ) : $account->get_deposit();
				break;
		}
	}
}
