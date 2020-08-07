<?php
/**
 * Update feed status via AJAX.
 */
function ggmp_update_account_status() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_send_json_error( esc_html__( 'Unauthorized Action.', 'gg-woo-feed' ) );
		wp_die();
	}

	if ( ! empty( $_POST['account_id'] ) ) {
		$account_id = absint( $_POST['account_id'] );
		$enable     = isset( $_POST['enable'] ) && 1 == $_POST['enable'] ? 1 : 0;
		update_post_meta( $account_id, GGMP_METABOX_PREFIX . 'enable', $enable );
		wp_send_json_success( [ 'status' => true ] );
	} else {
		wp_send_json_error( [ 'status' => false ] );
	}
	wp_die();
}

add_action( 'wp_ajax_ggmp_update_account_status', 'ggmp_update_account_status' );
