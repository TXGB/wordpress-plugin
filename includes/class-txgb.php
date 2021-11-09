<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.txgb.co.uk/
 * @since      1.0.0
 *
 * @package    TXGB
 * @subpackage TXGB/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    TXGB
 * @subpackage TXGB/includes
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      TXGB_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('TXGB_VERSION')) {
			$this->version = TXGB_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		if (!defined('TXGB_DEFAULT_STYLES')) {
			define('TXGB_DEFAULT_STYLES', true);
		}

		$this->plugin_name = 'txgb';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - TXGB_Loader. Orchestrates the hooks of the plugin.
	 * - TXGB_i18n. Defines internationalization functionality.
	 * - TXGB_Admin. Defines all hooks for the admin area.
	 * - TXGB_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-txgb-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-txgb-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-txgb-admin.php';

		/**
		 * The public front end pages.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/pages/class-txgb-view.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/pages/class-txgb-page-venue.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/pages/class-txgb-page-availability.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/pages/class-txgb-page-payment.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'helpers.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-txgb-router.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-txgb-public.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-txgb-api.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-txgb-api-entity.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-txgb-api-search.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/objects/class-txgb-availability.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'api/requests/interface-txgb-api-request.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'api/requests/class-txgb-api-availability-query.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'api/requests/class-txgb-api-product-availability-query.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'api/requests/class-txgb-api-provider-query.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'api/requests/class-txgb-api-service-query.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'api/objects/class-txgb-object-image.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'api/objects/class-txgb-object-product.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'api/objects/class-txgb-object-provider-product-ids.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'api/objects/class-txgb-object-rate.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'api/objects/class-txgb-object-service.php';

		// require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-txgb-availability.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-txgb-booking.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/tables/class-txgb-service-table.php';

		$this->loader = new TXGB_Loader();

		$booking = new TXGB_Booking();
		$booking->register();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the TXGB_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new TXGB_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new TXGB_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		$this->loader->add_action('admin_menu', $plugin_admin, 'init_pages');

		$this->loader->add_action('admin_post_txgb_import_providers', $plugin_admin, 'handle_import_services');
		$this->loader->add_action('admin_service_product_sync', $plugin_admin, 'handle_service_product_sync');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new TXGB_Public($this->get_plugin_name(), $this->get_version());

		if (TXGB_DEFAULT_STYLES) {
			$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		}

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		$this->loader->add_action('txgb_filter_availability_params', $plugin_public, 'handle_query_post_filter');
		$this->loader->add_action('txgb_filter_product_availability_params', $plugin_public, 'handle_query_product_filter');

		$this->loader->add_action('txgb_query_post_availability', $plugin_public, 'handle_query_post_availability');
		$this->loader->add_action('txgb_show_availability', $plugin_public, 'handle_show_availability');
		$this->loader->add_action('txgb_show_availability_form', $plugin_public, 'handle_show_availability_form');

		$this->loader->add_action('pre_get_posts', $plugin_public, 'handle_pre_get_posts');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{

		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{

		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    TXGB_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{

		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{

		return $this->version;
	}
}
