<?php
namespace GGMP\Admin\Metabox;


use GGMP\Core\Metabox;

class Paypal_Account_Metabox extends Metabox {
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->set_types(
			[
				'ggmp_paypal',
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
				'name'        => esc_html__( 'Live API Credentials', 'ggmp' ),
				'id'          => $prefix . 'live_title',
				'type'        => 'title',
				'description' => esc_html__( 'Live API from PayPal.', 'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Live API Username', 'ggmp' ),
				'id'          => $prefix . 'api_username',
				'type'        => 'text',
				'description' => esc_html__( 'Get your API credentials from PayPal.', 'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Live API Password', 'ggmp' ),
				'id'          => $prefix . 'api_password',
				'type'        => 'password',
				'description' => esc_html__( 'Get your API credentials from PayPal.', 'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Live API Signature', 'ggmp' ),
				'id'          => $prefix . 'api_signature',
				'type'        => 'text',
				'description' => esc_html__( 'Get your API credentials from PayPal.', 'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Live API Subject', 'ggmp' ),
				'id'          => $prefix . 'api_signature',
				'type'        => 'text',
				'description' => esc_html__( 'Get your API credentials from PayPal.', 'ggmp' ),
			],
			// Sandbox
			[
				'name'        => esc_html__( 'Sandbox API Credentials', 'ggmp' ),
				'id'          => $prefix . 'sandbox_title',
				'type'        => 'title',
				'description' => esc_html__( 'Your account setting is set to sandbox, no real charging takes place. To accept live payments, switch your environment to live and connect your PayPal account.',
					'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Sandbox API Username', 'ggmp' ),
				'id'          => $prefix . 'sandbox_api_username',
				'type'        => 'text',
				'description' => esc_html__( 'Get your API credentials from PayPal.', 'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Sandbox API Password', 'ggmp' ),
				'id'          => $prefix . 'sandbox_api_password',
				'type'        => 'password',
				'description' => esc_html__( 'Get your API credentials from PayPal.', 'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Sandbox API Signature', 'ggmp' ),
				'id'          => $prefix . 'sandbox_api_signature',
				'type'        => 'text',
				'description' => esc_html__( 'Get your API credentials from PayPal.', 'ggmp' ),
			],
			[
				'name'        => esc_html__( 'Sandbox API Subject', 'ggmp' ),
				'id'          => $prefix . 'sandbox_api_subject',
				'type'        => 'text',
				'description' => esc_html__( 'Get your API credentials from PayPal.', 'ggmp' ),
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

		return apply_filters( 'ggmp_paypal_fields_options', $settings );
	}
}
