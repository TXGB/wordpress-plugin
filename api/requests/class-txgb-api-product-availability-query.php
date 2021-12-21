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
class TXGB_API_Product_Availability_Query implements TXGB_API_Request
{
	protected $short_name;
	protected $key;

	protected $provider_name;
	protected $output;
	protected $category = 'None';
	protected $category_group = 'None';
	protected $availability = null;
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

	public function for_category(string $category)
	{
		$allowed_categories = [
			'Accommodation',
			'Attraction',
			'Events',
			'Adventure',
			'Charter or Rental',
			'Epicurean',
			'Function Centre',
			'Tours',
			'Non-Serviced Accommodation',
			'None',
		];

		if (!in_array($category, $allowed_categories)) {
			throw new Exception(
				'Invalid category group: ' . $category . ' is not in ' . implode(', ', $allowed_categories)
			);
		}

		$this->category = $category;

		return $this;
	}

	public function for_category_group(string $category_group)
	{
		$allowed_category_groups = ['None', 'Accommodation', 'Activities'];

		if (!in_array($category_group, $allowed_category_groups)) {
			throw new Exception(
				'Invalid category group: ' . $category_group . ' is not in ' . implode(', ', $allowed_category_groups)
			);
		}

		$this->category_group = $category_group;

		return $this;
	}

	public function with_availability(TXGB_Object_Availability $availability)
	{
		$this->availability = $availability;

		return $this;
	}

	public function to_request_args()
	{
		if (!$this->short_name || !$this->key) {
			throw new Exception('Provide a Distributor short_name and key for this request.');
		}

		$args = [
			'Channels' => [
				'DistributionChannelRQ' => [
					'id' => $this->short_name,
					'key' => $this->key,
				],
			],
			'Providers' => [
				'ProviderRQ' => [
					'short_name' => $this->provider_name,
				],
			],
			'Query' => [
				'SearchCriteria' => [
					'Consumers' => [
						'Consumer' => [
							'adults' => $this->availability->adults,
							'children' => $this->availability->children,
							'concessions' => $this->availability->concessions
						],
					]
				],
				'SearchCriteriaInclude' => [
					'TestProviders' => true,
				],
			],
		];

		// if ($this->category != 'None') {
		$args['Query']['IndustryCategory'] = $this->category;
		// } else {
		$args['Query']['IndustryCategoryGroup'] = $this->category_group;
		// }

		// if ($this->availability->nights == 0) {
		$key = 'CommencingSpecific';
		$value = ['date' => $this->availability->starts_at->format('Y-m-d')];
		// } else {
		// 	$key = 'CommencingWindow';
		// 	$value = [
		// 		'start_date' => $this->availability->starts_at->format('Y-m-d'),
		// 		'finish_date' => $this->availability->starts_at->format('Y-m-d'),
		// 	];
		// }

		if ($this->category == 'Accommodation' || $this->category_group == 'Accommodation') {
			$args['Query']['SearchCriteria']['LengthNights'] = [
				'minimum' => $this->availability->nights,
				'maximum' => $this->availability->nights,
			];
		} else {
			$args['Query']['SearchCriteria']['LengthDays'] = [];
		}

		$args['Query']['SearchCriteria'][$key] = $value;

		return $args;
	}
}
