<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.txgb.co.uk/
 * @since      1.0.0
 *
 * @package    TXGB
 * @subpackage TXGB/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    TXGB
 * @subpackage TXGB/public
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_Public
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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$router = new TXGB_Public_Router($plugin_name, $version);

		add_filter('do_parse_request', array($router, 'dispatch'), PHP_INT_MAX, 2);
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in TXGB_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The TXGB_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/txgb-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in TXGB_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The TXGB_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name . '-date-fns', plugin_dir_url(__FILE__) . 'js/date-fns.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/txgb-public.js', array('jquery'), $this->version, false);
	}

	public function handle_query_post_filter()
	{
		$type = array_key_exists('type', $_REQUEST) ? intval($_REQUEST['type']) : '';
		$city = array_key_exists('city', $_REQUEST) ? $_REQUEST['city'] : '';

		$adults = !array_key_exists('adults', $_REQUEST)
			? 1
			: intval($_REQUEST['adults']);
		$children = !array_key_exists('children', $_REQUEST)
			? 0
			: intval($_REQUEST['children']);
		$concessions = !array_key_exists('concessions', $_REQUEST)
			? 0
			: intval($_REQUEST['concessions']);

		$starts_at = !array_key_exists('starts_at', $_REQUEST)
			? ''
			: $_REQUEST['starts_at'];
		$ends_at = !array_key_exists('ends_at', $_REQUEST)
			? ''
			: $_REQUEST['ends_at'];

		$tomorrow_date = (new DateTime())->add(new DateInterval('P1D'));
		$overmorrow_date = (new DateTime())->add(new DateInterval('P2D'));
		$starts_at = $starts_at ? new DateTime($starts_at) : $tomorrow_date;
		$ends_at = $ends_at ? new DateTime($ends_at) : $overmorrow_date;

		$booking_params = array(
			'booking/availability' => '',
			'starts_at' => $starts_at->format('Y-m-d'),
			'ends_at' => $ends_at->format('Y-m-d'),
			'adults' => $adults,
			'children' => $children,
			'concessions' => $concessions,
		);

		return (object) array(
			'is_querying' => array_key_exists('venues/availability', $_REQUEST)
				|| array_key_exists('venue/availability', $_REQUEST),
			'type' => $type,
			'city' => $city,
			'starts_at' => $starts_at,
			'ends_at' => $ends_at,
			'adults' => $adults,
			'children' => $children,
			'concessions' => $concessions,
			'guests' => $adults + $children + $concessions,
			'booking_params' => $booking_params,
			'booking_params_string' => http_build_query($booking_params),
		);
	}

	public function handle_query_product_filter()
	{
		global $post;

		$filter_vars = $this->handle_query_post_filter();

		$filter_vars->is_querying = array_key_exists('booking/availability', $_REQUEST);
		$filter_vars->service_id = get_post_meta($post->ID, 'uuid', true);

		return $filter_vars;
	}

	public function handle_pre_get_posts($query)
	{
		if (
			!$query->is_admin
			&& $query->is_main_query()
			&& $query->is_post_type_archive
			&& 'txgb_venue' === $query->query_vars['post_type']
		) {
			$filter_vars = $this->handle_query_post_filter();

			if ($filter_vars->is_querying) {
				// Init API class
				$options = get_option('txgb_options', []);
				$api = new TXGB_API_Entity($options['shortname'], $options['key']);

				$availability = new TXGB_Object_Availability(
					$filter_vars->starts_at,
					$filter_vars->ends_at,

					$filter_vars->adults,
					$filter_vars->children,
					$filter_vars->concessions,
				);

				// Create new request for Service UUIDs
				$uuids = $api->search_by_availability($availability);

				if (!$uuids || count($uuids) == 0) {
					$uuids = array('TXGB_NO_RESULTS');
				}

				if ($filter_vars->type) {
					$query->set('tax_query', array(
						array(
							'taxonomy' => 'txgb_venue_type',
							'terms' => $filter_vars->type,
						),
					));
				}

				$meta_queries = array(
					array(
						'key' => 'uuid',
						'compare' => 'IN',
						'value' => $uuids,
					),
				);

				if ($filter_vars->city) {
					$meta_queries[] = array(
						'key' => 'address_city',
						'compare' => '=',
						'value' => $filter_vars->city,
					);
					$meta_queries['relation'] = 'AND';
				}

				// Query posts on matched UUIDs with availability
				$query->set('meta_query', $meta_queries);
			}
		}

		return $query;
	}

	public function handle_show_availability_form($for_service_with_id = null)
	{
		if ($for_service_with_id) {
			include plugin_dir_path(dirname(__FILE__)) . 'templates/booking/venue.php';
		} else {
			include plugin_dir_path(dirname(__FILE__)) . 'templates/availability-form.php';
		}
	}

	public function handle_show_availability($hide_form = false)
	{
		global $wp;

		$home_path = parse_url(home_url(), PHP_URL_PATH);

		$p = preg_replace("#^/?{$home_path}/#", '/', esc_url(add_query_arg(array())));

		$p = strpos($p, '?') === false
			? 'venue'
			: substr($p, strpos($p, '?'));
		$paths = explode('&', trim(trim($p, '/'), '?'));

		$templates = array(
			'booking/availability' => new TXGB_Page_Availability,
			'booking/payment' => new TXGB_Page_Payment,
		);

		if (!$hide_form) {
			$templates['venue'] = new TXGB_Page_Venue;
		}

		foreach ($paths as $path) {
			if (array_key_exists($path, $templates)) {
				$page = $templates[$path];

				do_action('parse_request', $wp);
				do_action('template_redirect');

				$page->action()->render();

				do_action('wp', $wp);
			}
		}
	}
}
