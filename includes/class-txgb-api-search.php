<?php

/**
 * The API integration class.
 *
 * Acts as a service to the API allowing the plugin to make calls via SOAP.
 *
 * @since      1.0.0
 * @package    TXGB
 * @subpackage TXGB/includes
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_API_Search extends TXGB_API
{

	/**
	 * The URL to the WSDL that we're going to use.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $wsdl    The URL of the WSDL we're using
	 */
	protected $wsdl = 'https://apis.txgb.co.uk/CABS.WebServices/SearchService.asmx?WSDL';

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @var string $short_name
	 * @var string $key
	 */
	public function __construct(string $short_name, string $key)
	{
		if (defined('TXGB_WSDL_SEARCH')) {
			$this->wsdl = TXGB_WSDL_SEARCH;
		} elseif (!txgb_is_production()) {
			$this->wsdl = 'https://uatapis.txgb.co.uk/CABS.WebServices/SearchService.asmx?WSDL';
		}

		parent::__construct($short_name, $key);
	}

	public function get_available_provider_products(
		string $provider_name,
		TXGB_Object_Availability $availability,
		string $for_service_id,
		string $venue_type = ''
	) {
		$products = [];

		switch ($venue_type) {
			case 'Accommodation':
				$category_group = $venue_type;
				break;

			case 'Activity':
			case 'Epicurean':
			case 'Tour':
				$category_group = 'Activities';
				break;

			default:
				$category_group = 'None';
		}

		$query = new TXGB_API_Product_Availability_Query($provider_name);
		$args = $query->with_availability($availability)
			->for_category_group($category_group)
			->for_distributor($this->short_name, $this->key)
			->to_request_args();

		try {
			$response = $this->client->ProductAvailability($args);

			if ($response && property_exists($response->Status, 'Success')) {
				// Use the Service ID map to verify ownership
				$mapping = txgb_get_product_mapping($for_service_id);

				// Convert each raw Product into a formatted object
				if (!property_exists($response->Channels->PA_DistributionChannelRSType->Providers, 'Provider')) {
					txgb_handle_exception(new Exception('Fatal: No Provider returned on API availability call.'));
				}

				$raw_provider = $response->Channels->PA_DistributionChannelRSType->Providers->Provider;
				$raw_products = $raw_provider->ProductGroups->ProductGroup->Products->Product;
				$raw_products = !is_array($raw_products) ? [$raw_products] : $raw_products;

				foreach ($raw_products as $raw_product) {
					// Check the owning Service before adding to the list
					if (array_key_exists($raw_product->id, $mapping['by_obx_id'])) {
						$cached_product = txgb_get_product_cache($mapping['by_obx_id'][$raw_product->id]);

						$cached_product->setAvailabilityFromProvider($raw_product->Availability);
						$products[] = $cached_product;
					}
				}
			}
		} catch (Exception $e) {
			txgb_handle_exception($e);
		}

		return $products;
	}

	/**
	 * Fetches all the Products across a Provider's Services
	 */
	public function get_provider_products(string $provider_name)
	{
		$product_ids = [];
		$credentials = txgb_get_distributor_credentials();

		$query = new TXGB_API_Provider_Query($provider_name);
		$request = $query
			->for_distributor($credentials->short_name, $credentials->key)
			->with_products()
			->to_request_args();

		try {
			$response = $this->client->ProviderSearch($request);

			if ($response && property_exists($response->Status, 'Success')) {
				$channel = $response->Channels->Channel;

				if (property_exists($channel, 'Providers') && property_exists($channel->Providers, 'Provider')) {
					// Convert each raw Product into a formatted object
					$raw_products = $channel->Providers->Provider->Products->Product;
					$raw_products = !is_array($raw_products) ? [$raw_products] : $raw_products;

					foreach ($raw_products as $raw_product) {
						$product_ids[] = TXGB_Object_Provider_Product_Ids::make_from_response($raw_product);
					}
				}
			}
		} catch (Exception $e) {
			txgb_handle_exception($e);
		}

		return $product_ids;
	}
}
