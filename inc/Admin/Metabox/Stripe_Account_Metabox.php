<?php
namespace GGMP\Admin\Metabox;


use GGMP\Core\Metabox;

class Stripe_Account_Metabox extends Metabox {
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->set_types(
			[
				'ggmp_stripe',
			]
		);

		$this->metabox_id    = 'ggmp-metabox-form-data';
		$this->metabox_label = esc_html__( 'Account Settings', 'ggmp' );
	}

	/**
	 * Callback function save
	 *
	 * All data of post parameters will be updated for each metadata of the post and stored in post_meta table
	 *
	 * @param string $post_id The id of current post.
	 * @param string $post    The instance of Post having post typo ggmp
	 */
	public function save( $post_id, $post ) {
		// $post_id and $post are required.
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		// Don't save meta boxes for revisions or autosaves.
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check user has permission to edit.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['ggmp_meta_nonce'] ) ) {
			$this->save_fields_data( 'post', $post_id );
		}
	}

	/**
	 * Get settings.
	 *
	 * @return array
	 */
	public function get_settings() {
		$prefix = GGMP_METABOX_PREFIX;

		$settings = [
			[
				'name'        => esc_html__( 'Priority', 'ggmp' ),
				'id'          => $prefix . 'priority',
				'type'        => 'text_number',
				'description' => esc_html__( 'Priority for this account. The accounts will be taken based on higher priority.', 'ggmp' ),
				'default'     => 1,
			],
			// Live
			[
				'name'        => esc_html__( 'Live Mode', 'ggmp' ),
				'id'          => $prefix . 'live_title',
				'type'        => 'title',
				'description' => sprintf( __( 'You must add the following webhook endpoint <strong style="background-color:#ddd;">&nbsp;%s&nbsp;</strong> to your <a href="https://dashboard.stripe.com/account/webhooks" target="_blank">Stripe account settings</a>. This will enable you to receive notifications on the charge statuses.', 'ggmp' ), \WC_Stripe_Helper::get_webhook_url() ),
			],
			[
				'name'        => esc_html__( 'Live Publishable Key', 'ggmp' ),
				'id'          => $prefix . 'publishable_key',
				'type'        => 'text',
				'description' => esc_html__( 'Get your API keys from your stripe account. Invalid values will be rejected. Only values starting with "pk_live_" will be saved.', 'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Live Secret Key', 'ggmp' ),
				'id'          => $prefix . 'secret_key',
				'type'        => 'password',
				'description' => esc_html__( 'Get your API keys from your stripe account. Invalid values will be rejected. Only values starting with "sk_live_" or "rk_live_" will be saved.', 'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Webhook Secret', 'ggmp' ),
				'id'          => $prefix . 'webhook_secret',
				'type'        => 'password',
				'description' => esc_html__( 'Get your webhook signing secret from the webhooks section in your stripe account.', 'ggmp' ),
			],
			// Test
			[
				'name'        => esc_html__( 'Test Mode', 'ggmp' ),
				'id'          => $prefix . 'test_title',
				'type'        => 'title',
				'description' => sprintf( __( 'You must add the following webhook endpoint <strong style="background-color:#ddd;">&nbsp;%s&nbsp;</strong> to your <a href="https://dashboard.stripe.com/account/webhooks" target="_blank">Stripe account settings</a>. This will enable you to receive notifications on the charge statuses.', 'ggmp' ), \WC_Stripe_Helper::get_webhook_url() ),
			],
			[
				'name'        => esc_html__( 'Test Publishable Key', 'ggmp' ),
				'id'          => $prefix . 'test_publishable_key',
				'type'        => 'text',
				'description' => esc_html__( 'Get your API keys from your stripe account. Invalid values will be rejected. Only values starting with "pk_test_" will be saved.', 'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Test Secret Key', 'ggmp' ),
				'id'          => $prefix . 'test_secret_key',
				'type'        => 'password',
				'description' => esc_html__( 'Get your API keys from your stripe account. Invalid values will be rejected. Only values starting with "sk_test_" or "rk_test_" will be saved.', 'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Test Webhook Secret', 'ggmp' ),
				'id'          => $prefix . 'test_webhook_secret',
				'type'        => 'password',
				'description' => esc_html__( 'Get your webhook signing secret from the webhooks section in your stripe account.', 'ggmp' ),
			],
			// Rules
			[
				'name'        => esc_html__( 'Rules', 'ggmp' ),
				'id'          => $prefix . 'rules_title',
				'type'        => 'title',
				'description' => esc_html__( 'Set rules for this account.', 'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Limit money per day', 'ggmp' ),
				'id'          => $prefix . 'limit_money_per_day',
				'type'        => 'text_number',
				'description' => esc_html__( 'Limit money per day. The default limit money is used if empty.', 'ggmp' ),
			],
		];

		return apply_filters( 'ggmp_stripe_fields_options', $settings );
	}
}
