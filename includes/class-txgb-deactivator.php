<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.txgb.co.uk/
 * @since      1.0.0
 *
 * @package    TXGB
 * @subpackage TXGB/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    TXGB
 * @subpackage TXGB/includes
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_Deactivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate()
	{

		// Run our deactivation tasks
		self::unregister_post_types();
		self::unregister_taxonomies();

		// Flush the URL rules to update
		flush_rewrite_rules();
	}

	protected static function unregister_post_types()
	{

		// Load our Custom Post Type configuration
		require_once plugin_dir_path(__FILE__) . '/class-txgb-types.php';
		$post_types = TXGB_Types::post_type_configuration();

		// Loop each Custom Post Type and unregister it
		foreach ($post_types as $post_type => $config) {
			unregister_post_type($post_type);
		}
	}

	protected static function unregister_taxonomies()
	{

		// Load our Custom Taxonomy configuration
		require_once plugin_dir_path(__FILE__) . '/class-txgb-types.php';
		$taxonomies = TXGB_Types::taxonomy_configuration();

		// Loop each Custom Taxonomy and unregister it
		foreach ($taxonomies as $taxonomy => $config) {
			unregister_taxonomy($taxonomy);
		}
	}
}
