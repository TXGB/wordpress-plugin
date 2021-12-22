<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.txgb.co.uk/
 * @since             1.0.0
 * @package           Txgb
 *
 * @wordpress-plugin
 * Plugin Name:       TXGB API
 * Plugin URI:        https://www.txgb.co.uk/
 * Description:       Connect your site to the TXGB API.
 * Version:           1.0.0
 * Author:            Tourism Exchange Great Britain
 * Author URI:        https://www.txgb.co.uk/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       txgb
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (!defined('TXGB_ENABLE_UPDATER')) {
	define('TXGB_ENABLE_UPDATER', true);
}

if (TXGB_ENABLE_UPDATER) {
	require_once plugin_dir_path(__FILE__) . 'lib/wp-package-updater/class-wp-package-updater.php';

	$txgb_updater = new WP_Package_Updater(
		'https://wordpress.infrastructure.incrementby.one',
		wp_normalize_path(__FILE__),
		wp_normalize_path(plugin_dir_path(__FILE__)),
	);
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('TXGB_VERSION', '1.0.5');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-txgb-activator.php
 */
function activate_txgb()
{

	require_once plugin_dir_path(__FILE__) . 'includes/class-txgb-activator.php';
	TXGB_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-txgb-deactivator.php
 */
function deactivate_txgb()
{

	require_once plugin_dir_path(__FILE__) . 'includes/class-txgb-deactivator.php';
	TXGB_Deactivator::deactivate();
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-txgb-activator.php
 */
function init_txgb()
{

	require_once plugin_dir_path(__FILE__) . 'includes/class-txgb-types.php';

	TXGB_Types::register();
}

register_activation_hook(__FILE__, 'activate_txgb');
register_deactivation_hook(__FILE__, 'deactivate_txgb');

add_action('init', 'init_txgb');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-txgb.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_txgb()
{
	$plugin = new TXGB();
	$plugin->run();
}
run_txgb();
