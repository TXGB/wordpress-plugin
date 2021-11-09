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
class TXGB_Admin_Import {

	private $slug;

	public function __construct( $slug )
	{
		$this->slug = $slug;

		$this->register_page();
	}

	public function register_page() {

		add_submenu_page( 'txgb_index', 'Import Venues', 'Import Venues', 'edit_posts', $this->slug, array( $this, 'render' ) );

	}

	public function render() {

		include __DIR__ . '/../partials/txgb-admin-import.php';

	}
}
