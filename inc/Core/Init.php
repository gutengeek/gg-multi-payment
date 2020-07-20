<?php
namespace GGMP\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use GGMP\Admin\Admin;
use GGMP\Admin\Metabox as Metabox;
use GGMP\Common\Interfaces\Intergration;
use GGMP\Common\Module\Paypal\Paypal_Hook;
use GGMP\Common\Module\Stripe\Stripe_Hook;
use GGMP\Common\Module\Report\Report;
use GGMP\Common\Posttypes;
use GGMP\Libraries\Form\Form;

/**
 * The core plugin class.
 * Defines internationalization, admin-specific hooks, and public-facing site hooks.
 */
class Init {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @var      Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_base_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_basename;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * The text domain of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $plugin_text_domain;

	public $form;

	/**
	 * Initialize and define the core functionality of the plugin.
	 */
	public function __construct() {
		$this->plugin_name        = GGMP;
		$this->version            = GGMP_VERSION;
		$this->plugin_basename    = GGMP_PLUGIN_BASENAME;
		$this->plugin_text_domain = GGMP_PLUGIN_TEXT_DOMAIN;

		Install::init();

		$this->load_dependencies();
		$this->set_locale();

		$this->define_global_init();

		if ( is_admin() ) {
			$this->define_admin_hooks();
		}

		$this->define_core_hook();

		// add_action( 'wp_loaded', [$this, 'define_core_hook'] );

		$this->define_intergrations();


		$this->form = Form::get_instance();
		$this->load_modules();
	}

	/**
	 * Loads the following required dependencies for this plugin.
	 *
	 * - Loader - Orchestrates the hooks of the plugin.
	 * - Internationalization_I18n - Defines internationalization functionality.
	 * - Admin - Defines all hooks for the admin area.
	 * - Frontend - Defines all hooks for the public side of the site.
	 *
	 * @access    private
	 */
	private function load_dependencies() {
		$this->loader = new Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Internationalization_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access    private
	 */
	private function set_locale() {
		$plugin_i18n = new Internationalization_I18n( $this->plugin_text_domain );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		$this->loader->add_action( 'plugins_loaded', $this, 'load_vendors' );
	}

	public function load_modules() {
		new Report();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Internationalization_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access    private
	 */
	public function define_intergrations() {

		// define list of intergration to make rich features
		$intergrations = [

		];

		foreach ( $intergrations as $intergration ) {
			$class  = "GGMP\\Common\\Integrations\\" . $intergration;
			$object = new   $class();
			if ( $object instanceof Intergration ) {
				$this->loader->add_action( 'init', $object, 'register_frontend_actions', 1, 2 );
				$this->loader->add_action( 'admin_init', $object, 'register_admin_actions', 1, 2 );

				if ( method_exists( $object, "register_ajax_hook_callbacks" ) ) {
					$object->register_ajax_hook_callbacks();
				}
			}
		}
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Internationalization_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access    private
	 */
	public function load_vendors() {

	}

	/**
	 * Define Taxonomies and Postypes Using for global
	 *
	 * Uses the Internationalization_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access    private
	 */
	private function define_global_init() {
		global $ggmp_options;

		$settings          = get_option( 'ggmp_settings' );
		$ggmp_options = apply_filters( 'ggmp_get_settings', $settings );

		$posttypes = new Posttypes();

		$this->loader->add_action( 'init', $posttypes, 'definition' );
	}

	/**
	 * Define Taxonomies and Postypes Using for global
	 *
	 * Uses the Internationalization_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access    private
	 */
	public function define_controllers() {
		$controllers = [
			'checkout' => 'Checkout@register_hook_callbacks',
		];

		foreach ( $controllers as $kye => $class ) {
			$this->load_controller( $class );
		}
	}

	/**
	 * Load frontend controller.
	 *
	 */
	public function load_controller( $call ) {
		@list( $controller, $action ) = explode( '@', $call );
		$controller = "\GGMP\Frontend\Controller\\" . $controller;
		$object     = $controller::get_instance();
		$object->{$action}();
	}

	/**
	 * Load admin controller.
	 *
	 * @access    private
	 */
	public function load_admin_controller( $call ) {
		@list( $controller, $action ) = explode( '@', $call );
		$controller = "\GGMP\Admin\Controller\\" . $controller;
		$object     = $controller::get_instance();
		$object->{$action}();
	}

	/**
	 * Define Metabox fields for Post Types and Taxonomies
	 *
	 * Uses the Internationalization_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access    private
	 */
	private function define_admin_metabox() {
		// Register post type metabox
		$metaboxes = [
			'admin/metabox/ggmp_paypal' => Metabox\Paypal_Account_Metabox::class,
			'admin/metabox/ggmp_stripe' => Metabox\Stripe_Account_Metabox::class,
		];

		// register metaxbox for custom post type ;
		foreach ( $metaboxes as $key => $metabox_class ) {

			$metabox = ( new $metabox_class() );

			if ( $metabox->get_mode() == 'taxonomy' ) {
				// disable
				if ( isset( $_GET['taxonomy'] ) ) {
					foreach ( $metabox->get_types() as $item ) {
						$this->loader->add_action( $item . '_edit_form', $metabox, 'output', 10, 2 );
						$this->loader->add_action( $item . '_add_form_fields', $metabox, 'output', 10, 2 );
					}
				}

				$this->loader->add_action( 'created_term', $metabox, 'save', 10, 3 );
				$this->loader->add_action( 'edited_terms', $metabox, 'save', 10, 3 );

				$this->loader->add_action( 'edited_terms', $metabox, 'delete', 10, 3 );

				add_action( 'created_term', [ $metabox, 'save_term' ], 10, 3 );
				add_action( 'edited_terms', [ $metabox, 'save_term' ], 10, 2 );
				add_action( 'delete_term', [ $metabox, 'delete' ], 10, 3 );

			} elseif ( $metabox->get_mode() == 'user' ) {
				add_action( 'show_user_profile', [ $metabox, 'output' ], 10 );
				add_action( 'edit_user_profile', [ $metabox, 'output' ], 10 );
				add_action( 'personal_options_update', [ $metabox, 'save' ] );
				add_action( 'edit_user_profile_update', [ $metabox, 'save' ] );
			} else {
				foreach ( $metabox->get_types() as $item ) {
					$this->loader->add_action( 'add_meta_boxes_' . $item, $metabox, 'setup' );
					$this->loader->add_action( 'save_post_' . $item, $metabox, 'save', 10, 2 );
				}
			}
		}
	}

	public function define_core_hook() {
		new Paypal_Hook();
		new Stripe_Hook();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @access    private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Admin( $this->get_plugin_name(), $this->get_version(), $this->get_plugin_text_domain() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'update_settings' );

		$this->define_admin_metabox();

		$controllers = [
			'product' => 'Product@register_hook_callbacks',
		];

		foreach ( $controllers as $kye => $class ) {
			$this->load_admin_controller( $class );
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access    private
	 */
	private function load_shortcodes_hooks() {

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve the text domain of the plugin.
	 *
	 * @return    string    The text domain of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_text_domain() {
		return $this->plugin_text_domain;
	}
}
