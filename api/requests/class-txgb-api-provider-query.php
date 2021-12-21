<?php

/**
 * The API Query class.
 *
 * Acts as a formatted object to build a SOAP query
 *
 * @since      1.0.0
 * @package    TXGB
 * @subpackage TXGB/includes
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_API_Provider_Query implements TXGB_API_Request
{
	protected $short_name;
	protected $key;

	protected $ids = array();
	protected $provider_name;
	protected $output;

	protected $include_products = false;
	protected $exclude_unavailable = false;

	protected $sort_by;
	protected $sort_direction = 'Ascending';

	public function __construct($name = '')
	{
		$this->provider_name = $name;
	}

	public function for_distributor(string $short_name, string $key)
	{
		$this->short_name = $short_name;
		$this->key = $key;

		return $this;
	}

	public function with_products($include = true)
	{
		$this->include_products = $include;

		return $this;
	}

	public function to_request_args()
	{
		if (!$this->short_name || !$this->key) {
			throw new Exception('Provide a Distributor short_name and key for this request.');
		}

		$args = [
			'Channels' => [
				'CO_DistributionChannelRQType' => [
					'id' => $this->short_name,
					'key' => $this->key,
				],
			],

			'Query' => [
				'SearchGroup' => [
					'SearchCriteriaShortName' => ['exact' => $this->provider_name],
				],
				'SearchCriteriaIncludeTestProviders' => [
					'value' => true,
				],
			],
		];

		if ($this->include_products) {
			$args['Response'] = [
				'IncludeProducts' => ['include' => true],
				'IncludeProductDescription' => ['include' => true],
				'IncludeProductRates' => ['include' => true],
				'IncludeProductImages' => ['include' => true],
				'IncludeProductMarketingDetails' => ['include' => true],
			];
		}

		return $args;
	}
}
