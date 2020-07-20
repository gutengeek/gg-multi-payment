<?php
namespace GGMP\Common;

/**
 * Fired during plugin deactivation
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @link       http://wpopal.com
 * @since      1.0.0
 *
 * @author     WpOpal
 **/
class Posttypes {

	/**
	 * Register all post types using for this project
	 *
	 * @since    1.0.0
	 */
	public function definition() {

		if ( ! is_blog_installed() || post_type_exists( 'ggmp_job' ) ) {
			return;
		}

		static::register_pp_account();
		static::register_stripe_account();
	}

	/**
	 * Register the Candidate Post Type
	 *
	 * @since    1.0.0
	 */
	public static function register_pp_account() {
		$labels = [
			'name'               => esc_html__( 'PayPal Account', 'ggmp' ),
			'singular_name'      => esc_html__( 'PayPal Account', 'ggmp' ),
			'add_new'            => esc_html__( 'Add New PayPal Account', 'ggmp' ),
			'add_new_item'       => esc_html__( 'Add New PayPal Account', 'ggmp' ),
			'edit_item'          => esc_html__( 'Edit PayPal Account', 'ggmp' ),
			'new_item'           => esc_html__( 'New PayPal Account', 'ggmp' ),
			'all_items'          => esc_html__( 'PayPal Accounts', 'ggmp' ),
			'view_item'          => esc_html__( 'View PayPal Account', 'ggmp' ),
			'search_items'       => esc_html__( 'Search PayPal Account', 'ggmp' ),
			'not_found'          => esc_html__( 'No PayPal Account found', 'ggmp' ),
			'not_found_in_trash' => esc_html__( 'No PayPal Account found in Trash', 'ggmp' ),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html__( 'PayPal Account', 'ggmp' ),
		];

		$labels = apply_filters( 'ggmp_postype_paypal_labels', $labels );

		register_post_type( 'ggmp_paypal',
			apply_filters( 'ggmp_paypal_post_type_parameters', [
				'labels'              => $labels,
				'public'              => false,
				'hierarchical'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => 'ggmp',
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => true,
				'map_meta_cap'        => true,
				'supports'            => [ 'title' ],
				'rewrite'             => false,
				'has_archive'         => false,
			] )
		);
	}

	/**
	 * Register the Candidate Post Type
	 *
	 * @since    1.0.0
	 */
	public static function register_stripe_account() {
		$labels = [
			'name'               => esc_html__( 'Stripe Account', 'ggmp' ),
			'singular_name'      => esc_html__( 'Stripe Account', 'ggmp' ),
			'add_new'            => esc_html__( 'Add New Stripe Account', 'ggmp' ),
			'add_new_item'       => esc_html__( 'Add New Stripe Account', 'ggmp' ),
			'edit_item'          => esc_html__( 'Edit Stripe Account', 'ggmp' ),
			'new_item'           => esc_html__( 'New Stripe Account', 'ggmp' ),
			'all_items'          => esc_html__( 'Stripe Accounts', 'ggmp' ),
			'view_item'          => esc_html__( 'View Stripe Account', 'ggmp' ),
			'search_items'       => esc_html__( 'Search Stripe Account', 'ggmp' ),
			'not_found'          => esc_html__( 'No Stripe Account found', 'ggmp' ),
			'not_found_in_trash' => esc_html__( 'No Stripe Account found in Trash', 'ggmp' ),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html__( 'Stripe Account', 'ggmp' ),
		];

		$labels = apply_filters( 'ggmp_postype_stripe_labels', $labels );

		register_post_type( 'ggmp_stripe',
			apply_filters( 'ggmp_stripe_post_type_parameters', [
				'labels'              => $labels,
				'public'              => false,
				'hierarchical'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => 'ggmp',
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => true,
				'map_meta_cap'        => true,
				'supports'            => [ 'title' ],
				'rewrite'             => false,
				'has_archive'         => false,
			] )
		);
	}
}
