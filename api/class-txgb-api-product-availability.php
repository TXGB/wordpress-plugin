<?php

require_once __DIR__ . '/../includes/class-txgb-api.php';
require_once __DIR__ . '/../includes/objects/class-txgb-availability.php';

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
class TXGB_API_ProductAvailability extends TXGB_API
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
		}

		parent::__construct($short_name, $key);
	}

	public function search($provider, $from, $nights, $adults)
	{
		$function = 'ProductAvailability';
		$args = array(
			'Channels' => array(
				'DistributionChannelRQ' => array(
					'id' => $this->short_name,
					'key' => $this->key,
				),
			),
			'Providers' => array(
				'ProviderRQ' => array(
					'short_name' => $provider,
					'content_id' => $provider,
				),
			),
			'Query' => array(
				'IndustryCategory' 		=> 'Accommodation',
				'IndustryCategoryGroup'	=> 'Accommodation',
				'SearchCriteria'		=> array(
					'LengthNights' => array(
						'minimum'	=> $nights,
						'maximum'	=> $nights,
					),
					'CommencingSpecific' => array(
						'date'	=> $from,
					),
					'Consumers' => array(
						'Consumer' => array(
							'adults'	=> $adults,
						),
					),
				),
			),
		);

		try {
			$response = $this->call($function, $args);

			$products = [];

			$providers = $response->Channels
				->PA_DistributionChannelRSType
				->Providers;

			foreach ($providers as $provider) {
				foreach ($provider->ProductGroups as $group) {
					foreach ($group->Products as $product) {
						if (!is_array($product)) {
							$product = array($product);
						}
						foreach ($product as $option) {
							$products[] = TXGB_Object_ProductAvailability::makeFromProductResponse($option);
						}
					}
				}
			}
		} catch (Exception $e) {
			txgb_handle_exception($e);
		}

		return $products;
	}
}
