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
class TXGB_Public_Router {

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
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->templates = [];

		$this->createPages();
	}

	public function createPages()
	{
		$this->templates['venue'] = new TXGB_Page_Venue;
		$this->templates['booking/availability'] = new TXGB_Page_Availability;
		$this->templates['booking/payment'] = new TXGB_Page_Payment;
	}

	public function dispatch($bool, \WP $wp)
	{
		$paths = explode('&', trim(trim( $this->getPathInfo(), '/' ), '?'));

		foreach ($paths as $path) {
			if (array_key_exists($path, $this->templates)) {
				$page = $this->templates[$path];

				do_action( 'parse_request', $wp );
				do_action( 'template_redirect' );

				$page->action()->render();

				do_action( 'wp', $wp );
			}
		}

		return $bool;
	}

		private function getPathInfo() {
				$home_path = parse_url( home_url(), PHP_URL_PATH );

				return preg_replace( "#^/?{$home_path}/#", '/', esc_url( add_query_arg(array()) ) );
		}
}
