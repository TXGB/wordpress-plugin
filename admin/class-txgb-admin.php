<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.txgb.co.uk/
 * @since      1.0.0
 *
 * @package    Txgb
 * @subpackage Txgb/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Txgb
 * @subpackage Txgb/admin
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $slug = 'txgb';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function init_pages()
	{

		add_menu_page('TXGB', 'TXGB', 'edit_posts', 'txgb_index', '', 'dashicons-location-alt', 100);

		require_once __DIR__ . '/pages/txgb-admin-settings.php';
		require_once __DIR__ . '/pages/txgb-admin-import.php';

		$settings = new Txgb_Admin_Settings($this->slug);
		$import = new Txgb_Admin_Import($this->slug . '_import');
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Txgb_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Txgb_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/txgb-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Txgb_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Txgb_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/txgb-admin.js', array('jquery'), $this->version, false);
	}

	public function handle_import_services($service_ids)
	{
		$nonce = isset($_POST['txgb_import_services_form_action_nonce'])
			? $_POST['txgb_import_services_form_action_nonce']
			: null;
		if (
			isset($_POST['txgb_import_services_form_action_nonce'])
			&& wp_verify_nonce(
				$nonce,
				'txgb_import_services_form_action_nonce'
			)
		) {
			$imported_count = 0;

			include(plugin_dir_path(__FILE__) . 'partials/txgb-admin-import-intro.php');

			$venue_types = array();
			$existing_venue_types = get_terms([
				'taxonomy' => 'txgb_venue_type',
				'hide_empty' => false,
			]);
			foreach ($existing_venue_types as $term) {
				$venue_types[$term->name] = $term;
			}

			$options = get_option('txgb_options', []);
			$api = new TXGB_API_Entity($options['shortname'], $options['key']);

			// Fetch the Service information from the API
			$services = $api->get_services_for_import($service_ids);
			$existing_posts = get_posts([
				'post_type'     => 'txgb_venue',
				'post_status'   => 'any',
				'meta_query' => [
					'key' => 'uuid',
					'compare' => 'IN',
					'value' => $service_ids,
				],
			]);
			$existing_post_map = [];
			foreach ($existing_posts as $existing_post) {
				$existing_post_map[$existing_post->uuid] = $existing_post;
			}

			foreach ($services as $service) {
				$terms = [];
				$existing_id = !array_key_exists($service->id, $existing_post_map)
					? null
					: $existing_post_map[$service->id]->ID;

				foreach ($service->categories as $category) {
					if (!array_key_exists($category, $venue_types)) {
						$venue_types[$category] = wp_create_term($category, 'txgb_venue_type');
					}
					$terms[] = $venue_types[$category]->term_id;
				}

				$insert_data = [
					'ID'           => $existing_id,
					'post_type'    => 'txgb_venue',
					'post_title'   => $service->name,
					'post_content' => $service->description,
					'post_excerpt' => $service->summary,
					'post_status'  => 'draft',
					'meta_input'   => [
						'uuid'                 => $service->id,
						'latitude'             => is_null($service->geocoding) ? null : $service->geocoding['latitude'],
						'longitude'            => is_null($service->geocoding) ? null : $service->geocoding['longitude'],
						'address_line_1'       => $service->address['line_1'],
						'address_line_2'       => $service->address['line_2'],
						'address_city'         => $service->address['city'],
						'address_state'        => $service->address['state'],
						'address_post_code'    => $service->address['postal_code'],
						'address_country_code' => $service->address['country'],
						'email'                => $service->email,
						'provider_id'          => $service->provider->id,
						'category'             => $service->categories[0],
						'provider_short_name'  => $service->provider->short_name,
						'last_product_sync'    => null,
					],
					'tax_input' => ['txgb_venue_type' => $terms],
				];
				$inserted_id = wp_insert_post($insert_data, true);

				if ($inserted_id) {
					$imported_count++;

					// Initialise Product ID cache
					// $this->initialise_service_product_cache($service);
				}
			}
			include(plugin_dir_path(__FILE__) . 'partials/txgb-admin-import-end.php');
		} else {
			wp_die(__('Invalid nonce specified', $this->plugin_name), __('Error', $this->plugin_name), array(
				'response'  => 403,
				'back_link' => 'admin.php?page=' . $this->plugin_name,
			));
		}
	}

	/**
	 * Creates an empty initial cache of Products for a Service
	 */
	public function initialise_service_product_cache(TXGB_Object_Service $service)
	{
		$product_mapping = txgb_create_product_mapping($service->products);

		txgb_set_product_mapping($service->id, $product_mapping);

		foreach ($service->products as $product) {
			txgb_set_product_cache($product);
		}

		return $product_mapping;
	}

	/**
	 * Reinitialise a cache if the transient has been deleted after import
	 */
	public function get_service_products(string $service_id)
	{
		$credentials = txgb_get_distributor_credentials();
		$api = new TXGB_API_Entity($credentials->short_name, $credentials->key);

		$services = $api->get_services_for_import([$service_id]);
		if (!$services) {
			throw new Exception('No service found for ID: ' . $service_id);
		}

		return $this->initialise_service_product_cache($services[0]);
	}

	/**
	 * WP_Cron handler to run the process of updating a Product cache over the
	 * course of a day.
	 */
	public function handle_service_product_sync()
	{
		$now = new DateTime();
		$end_of_day = new DateTime($now->format('Y-m-d') . ' 23:59:59');

		// Services that are running stale (null matches against the condition)
		$services = get_posts([
			'post_type'     => 'txgb_venue',
			'post_status'   => ['publish', 'pending', 'draft', 'future', 'private'],
			'meta_query' => [
				'relation' => 'OR',
				[
					'key' => 'last_product_sync',
					'compare' => 'IN',
					'value' => ['null', null, ''],
				],
				[
					'key' => 'last_product_sync',
					'compare' => '<',
					'type' => 'DATETIME',
					'value' => $end_of_day->format(DateTimeInterface::W3C),
				],
			],
			'numberposts' => -1,
		]);
		$service_id_to_post_ids = [];

		$credentials = txgb_get_distributor_credentials();
		$api = new TXGB_API_Search($credentials->short_name, $credentials->key);

		/*
			Compile a list of Provider names and their services

			[
				'foo_provider' => [ 'service1_id', 'service4_id' ],
				'bar_provider' => [ 'service2_id', 'service3_id' ],
				'baz_provider' => [ 'service5_id' ],
			]
		*/
		$provider_names = [];
		foreach ($services as $service) {
			if (!array_key_exists($service->provider_short_name, $provider_names)) {
				$provider_names[$service->provider_short_name] = [];
			}
			$service_id = get_post_meta($service->ID, 'uuid', true);
			$provider_names[$service->provider_short_name][] = $service_id;
			$service_id_to_post_ids[$service_id] = $service->ID;

			// Initialise a cache of product information / descriptions / images
			$this->get_service_products($service_id);
		}

		// Loop over the list and compile several service caches at once
		foreach ($provider_names as $provider_name => $provider_service_ids) {
			// Get all the Product IDs for the Provider
			$products = $api->get_provider_products($provider_name);

			// Loop the Product list and sort them into arrays by Service Id
			$products_grouped_by_service_id = [];
			foreach ($products as $product) {
				$cached_product = txgb_get_product_cache($product->id);

				// Only process the Product if they're in our Service list
				if ($cached_product) {
					if (in_array($cached_product->service_id, $provider_service_ids)) {
						if (!array_key_exists($cached_product->service_id, $products_grouped_by_service_id)) {
							$products_grouped_by_service_id[$cached_product->service_id] = [];
						}

						$products_grouped_by_service_id[$cached_product->service_id][] = (object)[
							'id' => $product->id,
							'obx_id' => $product->obx_id,
							'product' => $cached_product,
						];
					}
				}
			}

			// Cache each Service's Product mapping
			foreach ($products_grouped_by_service_id as $service_id => $service_products) {
				$mapping = txgb_update_product_mapping_obx_ids($service_id, $service_products);

				txgb_set_product_mapping($service_id, $mapping);
				update_post_meta($service_id_to_post_ids[$service_id], 'last_product_sync', (new DateTime)->format(DateTimeInterface::W3C));
			}
		}
	}
}
