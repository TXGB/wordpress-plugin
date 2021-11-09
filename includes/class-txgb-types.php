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
class TXGB_Types {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function register() {

        // Run our activation tasks
        self::setup_post_types();
        self::setup_taxonomies();

        // Flush the URL rules to update
        flush_rewrite_rules();

    }

    /**
     * Central configuration for our Custom Post Types.
     *
     * @since    1.0.0
     * @return   array    An array of settings keyed by Custom Post Type name
     */
    public static function post_type_configuration() {

			return array(
				'txgb_venue' => array(
					'public'       => true,
					'has_archive'  => true,
					'show_in_menu' => 'txgb_index',
					'labels'       => array(
						'name'          => __( 'Venues', 'txgb' ),
						'singular_name' => __( 'Venue', 'txgb' ),
					),
					'rewrite'      => array( 'slug' => 'venues' ),
					'supports'     => array(
						'title',
						'excerpt',
						'editor',
						'custom-fields',
						'thumbnail',
					),
				),
			);

    }

    /**
     * Central configuration for our Custom Taxonomies.
     *
     * @since    1.0.0
     * @return   array    An array of settings keyed by Taxonomy name
     */
    public static function taxonomy_configuration() {

        return array(
            'txgb_venue_type' => array(
                'applies_to'    => array( 'txgb_venue' ),
                'configuration' => array(
                    'hierarchical'      => true, // Category
                    'show_ui'           => true,
                    'show_admin_column' => true,
                    'query_var'         => true,
                    'rewrite'           => array( 'slug' => 'venue-type' ),
                    'labels'            => array(
                        'name'              => _x( 'Venue Types', 'taxonomy general name' ),
                        'singular_name'     => _x( 'Venue Type', 'taxonomy singular name' ),
                        'search_items'      => __( 'Search Venue Types' ),
                        'all_items'         => __( 'All Venue Types' ),
                        'parent_item'       => __( 'Parent Venue Type' ),
                        'parent_item_colon' => __( 'Parent Venue Type:' ),
                        'edit_item'         => __( 'Edit Venue Type' ),
                        'update_item'       => __( 'Update Venue Type' ),
                        'add_new_item'      => __( 'Add New Venue Type' ),
                        'new_item_name'     => __( 'New Course Name' ),
                        'menu_name'         => __( 'Venue Type' ),
                    )
                ),
            ),
        );

    }

    /**
     * Initialises our Custom Post Types.
     *
     * @since    1.0.0
     * @return   void
     */
    protected static function setup_post_types() {

        // Fetch our centralised configuration
        $post_types = self::post_type_configuration();

        // Loop each Custom Post Type and register it
        foreach ( $post_types as $post_type => $options ) {
            register_post_type( $post_type, $options );
        }

    }

    /**
     * Initialises our Custom Taxonomies.
     *
     * @since    1.0.0
     * @return   void
     */
    protected static function setup_taxonomies() {

        // Fetch our centralised configuration
        $taxonomies = self::taxonomy_configuration();

        // Loop each Taxonomy and register it
        foreach ( $taxonomies as $taxonomy => $options ) {
            register_taxonomy( $taxonomy, $options['applies_to'], $options['configuration'] );
        }

    }

}
