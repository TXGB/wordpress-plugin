<?php

require_once __DIR__ . '/../../api/class-txgb-api-product-availability.php';

class TXGB_Page_Availability
{
	public function action()
	{
		global $post;
		$availability_vars = apply_filters('txgb_filter_product_availability_params', null, null);

		$service_id = $availability_vars->service_id;
		$starts_at = $availability_vars->starts_at
			? $availability_vars->starts_at
			: '';
		$ends_at = $availability_vars->ends_at
			? $availability_vars->ends_at
			: '';
		$guests = $availability_vars->guests;

		$options = get_option('txgb_options', []);

		// Generate a hash of the vars for caching
		$hash_vars = array(
			$service_id,
			$starts_at ? $starts_at->format('Y-m-d H:i:s') : '',
			$ends_at ? $ends_at->format('Y-m-d H:i:s') : '',
			$guests,
		);
		$availability_hash = sha1(join('|', $hash_vars));

		// Check to see if this request has been made recently
		$products = get_transient('txgb_product_availability-' . $availability_hash);

		if (!$products) {
			// If not, call the API
			$api = new TXGB_API_Search($options['shortname'], $options['key']);
			$availability = new TXGB_Object_Availability(
				$starts_at,
				$ends_at,
				$availability_vars->adults,
				$availability_vars->children,
				$availability_vars->concessions
			);
			$provider_name = get_post_meta($post->ID, 'provider_short_name', true);
			$category = get_post_meta($post->ID, 'category', true);
			$products = $api->get_available_provider_products($provider_name, $availability, $service_id, $category);

			// Cache them for next time
			set_transient('txgb_product_availability-' . $availability_hash, $products, 2 * MINUTE_IN_SECONDS);
		}

		return new TXGB_View('booking/availability', compact('products'));
	}
}
