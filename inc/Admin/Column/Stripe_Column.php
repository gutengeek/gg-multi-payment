<?php
namespace GGMP\Admin\Column;

class Stripe_Column {

	/**
	 * Stripe_Column constructor.
	 */
	public function __construct() {
		add_filter( 'manage_ggmp_stripe_posts_columns', [ $this, 'set_custom_edit_columns' ] );
		add_action( 'manage_ggmp_stripe_posts_custom_column', [ $this, 'custom_column' ], 10, 2 );
	}

	public function set_custom_edit_columns( $columns ) {
		unset( $columns['date'] );
		$columns['enable']               = __( 'Enable', 'ggmp' );
		$columns['publishable_key']      = __( 'Publishable Key', 'ggmp' );
		$columns['test_publishable_key'] = __( 'Test Publishable Key', 'ggmp' );
		$columns['deposited_today']      = __( 'Deposited today', 'ggmp' );
		$columns['date']                 = __( 'Date', 'ggmp' );

		return $columns;
	}

	// Add the data to the custom columns for the book post type:
	public function custom_column( $column, $post_id ) {
		$account = ggmp_stripe( $post_id );
		switch ( $column ) {
			case 'enable' :
				$checked = $account->is_enable() ? 'checked' : '';
				$switch  = '<label class="ggmp-enable-switch-input">';
				$switch  .= '<input type="checkbox" id="' . esc_attr( $post_id ) . '" value="on" class="js-ggmp-change-status ggmp-enable-switch form-control" ' . $checked . ' />';
				$switch  .= '<span class="slider round"></span>';
				$switch  .= '</label>';

				echo $switch;
				break;

			case 'publishable_key' :
				echo '<code>' . $account->get_truncated_pk() ? $account->get_truncated_pk() : '---' . '</code>';
				break;

			case 'test_publishable_key' :
				echo '<code>' . $account->get_truncated_test_pk() ? $account->get_truncated_test_pk() : '---' . '</code>';
				break;

			case 'deposited_today' :
				$deposit = function_exists( 'wc_price' ) ? wc_price( $account->get_deposit() ) : $account->get_deposit();
				$limit   = function_exists( 'wc_price' ) ? wc_price( $account->get_limit_per_day() ) : $account->get_limit_per_day();
				$status  = ( (float) $account->get_deposit() >= (float) $account->get_limit_per_day() ) ? 'limited' : '';
				echo '<span class="ggmp-deposit-status ' . $status . '">' . $deposit . '/' . $limit . '</span>';
				break;
		}
	}
}
