
<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.txgb.co.uk/
 * @since      1.0.0
 *
 * @package    TXGB
 * @subpackage TXGB/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    TXGB
 * @subpackage TXGB/includes
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_Menus {

	/**
	 * Initialises our Custom Post Types.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public static function register() {

		add_menu_page( 'TXGB', 'TXGB', 'edit_posts', 'txgb_index', '', 'dashicons-location-alt', 100 );
		// add_submenu_page( 'txgb_index', 'Import Venues', 'Import Venues', 'edit_posts', 'txgb/import-venues', array( __CLASS__, 'init_import_page' ) );

	}

	public static function init_import_page() {

		include __DIR__ . '/../admin/partials/txgb-admin-display.php';

	}

}
