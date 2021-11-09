<?php

if (!function_exists('txgb_delete_transients_with_prefix')) {

	/**
	 * Delete all transients from the database whose keys have a specific prefix.
	 *
	 * @param string $prefix The prefix. Example: 'my_cool_transient_'.
	 */
	function txgb_delete_transients_with_prefix($prefix)
	{
		foreach (txgb_get_transient_keys_with_prefix($prefix) as $key) {
			delete_transient($key);
		}
	}
}

if (!function_exists('txgb_get_transient_keys_with_prefix')) {
	/**
	 * Gets all transient keys in the database with a specific prefix.
	 *
	 * Note that this doesn't work for sites that use a persistent object
	 * cache, since in that case, transients are stored in memory.
	 *
	 * @param  string $prefix Prefix to search for.
	 * @return array          Transient keys with prefix, or empty array on error.
	 */
	function txgb_get_transient_keys_with_prefix($prefix)
	{
		global $wpdb;

		$prefix = $wpdb->esc_like('_transient_' . $prefix);
		$sql    = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";
		$keys   = $wpdb->get_results($wpdb->prepare($sql, $prefix . '%'), ARRAY_A);

		if (is_wp_error($keys)) {
			return [];
		}

		return array_map(function ($key) {
			// Remove '_transient_' from the option name.
			return ltrim($key['option_name'], '_transient_');
		}, $keys);
	}
}

if (!function_exists('txgb_get_post_by_service_id')) {
	/**
	 * Return a Wordpress Post by the TXGB Service ID
	 *
	 * @param string $service_id
	 * @return WP_Post|array|null
	 */
	function txgb_get_post_by_service_id(string $service_id)
	{
		return get_post([
			'post_type'     => 'txgb_venue',
			'post_status'   => 'any',
			'meta_key'      => 'uuid',
			'meta_value'    => $service_id,
			'fields'        => 'ids',
		]);
	}
}

if (!function_exists('txgb_create_product_mapping')) {
	/**
	 * Create a Product mapping cache for a Service
	 *
	 * @param array[TXGB_Object_Product] $products
	 * @return array
	 */
	function txgb_create_product_mapping(array $products)
	{
		$product_mapping = [
			'by_id' => [],
			'by_obx_id' => [],
		];

		foreach ($products as $product) {
			$id = $product->id;
			$obx_id = $product->obx_id;

			$product_mapping['by_id'][$id] = $obx_id;
			$product_mapping['by_obx_id'][$obx_id] = $id;
		}

		return $product_mapping;
	}
}

if (!function_exists('txgb_update_product_mapping_obx_ids')) {
	/**
	 * Update a Product mapping cache with OBX IDs
	 *
	 * @param object $maps
	 * @return array
	 */
	function txgb_update_product_mapping_obx_ids(string $service_id, array $maps)
	{
		$product_mapping = txgb_get_product_mapping($service_id);

		foreach ($maps as $map) {
			$id = $map->id ?: $map->product->id;
			$obx_id = $map->obx_id;

			$product_mapping['by_id'][$id] = $obx_id;
			$product_mapping['by_obx_id'][$obx_id] = $id;
		}

		return $product_mapping;
	}
}

if (!function_exists('txgb_delete_product_mapping')) {
	/**
	 * Delete the Product mapping cache for a Service
	 *
	 * @param string $service_id
	 * @return array
	 */
	function txgb_delete_product_mapping(string $service_id)
	{
		return delete_transient('txgb_product_map_for_service_' . $service_id);
	}
}

if (!function_exists('txgb_get_distributor_credentials')) {
	/**
	 * Get the Distributor's API credentials
	 *
	 * @param string
	 * @return object
	 */
	function txgb_get_distributor_credentials()
	{
		$options = get_option('txgb_options', []);

		return (object)[
			'key' => $options['key'],
			'short_name' => $options['shortname'],
		];
	}
}

if (!function_exists('txgb_get_product_mapping')) {
	/**
	 * Get the Product mapping cached for a Service
	 *
	 * @param string $service_id
	 * @return array
	 */
	function txgb_get_product_mapping(string $service_id)
	{
		return get_transient('txgb_product_map_for_service_' . $service_id);
	}
}

if (!function_exists('txgb_get_product_cache')) {
	/**
	 * Get the cached Product information
	 *
	 * @param string $id
	 * @return object
	 */
	function txgb_get_product_cache(string $id)
	{
		return get_transient('txgb_product_cache_' . $id);
	}
}

if (!function_exists('txgb_set_product_cache')) {
	/**
	 * Cache Product information
	 *
	 * @param string $id
	 * @return array
	 */
	function txgb_set_product_cache(TXGB_Object_Product $product)
	{
		return set_transient('txgb_product_cache_' . $product->id, $product);
	}
}

if (!function_exists('txgb_handle_exception')) {
	/**
	 * Handle an Exception from the TXGB API
	 *
	 * @param Exception $e
	 */
	function txgb_handle_exception(Exception $e = null)
	{
		var_dump($e);
	}
}

if (!function_exists('txgb_set_product_mapping')) {
	/**
	 * Set the Product mapping cached for a Service
	 *
	 * @param string $service_id
	 * @param array $mapping
	 * @return bool
	 */
	function txgb_set_product_mapping(string $service_id, $mapping)
	{
		return set_transient('txgb_product_map_for_service_' . $service_id, $mapping);
	}
}

if (!function_exists('txgb_is_production')) {
	/**
	 * Is the app running in a production setting
	 *
	 * @return bool
	 */
	function txgb_is_production()
	{
		if (defined('TXGB_IN_PRODUCTION')) {
			return TXGB_IN_PRODUCTION;
		}
		return true;
	}
}

if (!function_exists('txgb_venues_with_geo')) {
	function txgb_venues_with_geo($post_status = 'publish')
	{
		return new WP_Query(array(
			'post_type' => 'txgb_venue',
			'post_status' => $post_status,

			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'latitude',
					'value' => '',
					'compare' => '!=',
				),
				array(
					'key' => 'longitude',
					'value' => '',
					'compare' => '!=',
				),
			),
		));
	}
}
