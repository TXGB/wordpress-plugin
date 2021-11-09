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
class TXGB_API_Availability_Query implements TXGB_API_Request
{
	protected $type;
	protected $short_name;

	protected $ids = array();
	protected $output;
	protected $availability;
	protected $exclude_unavailable = false;

	protected $sort_by;
	protected $sort_direction = 'Ascending';

	public function __construct($short_name)
	{
		$this->short_name = $short_name;
	}

	public function id(string $id)
	{
		$this->ids[] = $id;

		return $this;
	}

	public function ids(array $ids)
	{
		$this->ids = array_merge($this->ids, $ids);

		return $this;
	}

	public function show_availability(DateTime $from_date, DateTime $to_date, int $guests)
	{
		$nights = $from_date->diff($to_date)->days;

		$this->availability = array(
			'from' => $from_date,
			'nights' => $nights,
			'guests' => $guests,
		);

		return $this;
	}

	public function exclude_unavailable()
	{
		$this->exclude_unavailable = true;

		return $this;
	}

	public function with_output(array $output)
	{
		$this->output = $output;

		return $this;
	}

	public function sort_by(string $field, string $direction)
	{
		$this->sort_by = $field;
		$this->sort_direction = $direction;

		return $this;
	}

	public function to_request_args()
	{
		$args = array(
			'EntitySearch_RQ' => array(
				'Shortname' => $this->short_name,
				'Filter' => array(
					'Type' => 'Service',
					'MustBeInAdCampaign' => false,
					'MustBeInDealCampaign' => false,
					'Reviews' => array('IncludeFullDescription' => true, 'IncludeShortReview' => true),
				),
				'Output' => $this->output,
			),
		);

		if (count($this->ids)) {
			$id_params = array();
			foreach ($this->ids as $id) {
				$id_params[] = array('Id' => $id);
			}
			$args['EntitySearch_RQ']['Filter']['Ids'] = $this->ids;
		}

		if ($this->availability) {
			$nights = $this->availability['nights'];
			$args['EntitySearch_RQ']['Availability'] = array(
				'MergeMethod' => 'NoMerge',
			);

			if ($nights > 0) {
				$args['EntitySearch_RQ']['Availability']['Window'] = array(
					'StartDate' => $this->availability['from']->format('Y-m-d'),
					'Size' => $nights,
				);
			} else {
				$args['EntitySearch_RQ']['Availability']['Specific'] = array(
					'Date' => $this->availability['from']->format('Y-m-d'),
					'Duration' => 1,
				);
			}

			$args['EntitySearch_RQ']['Filter']['Bookability'] = array(
				'BlockUnavailableResults' => $this->exclude_unavailable
					? true
					: array('xsi:nil' => true),
				'GuestsCapability' => $this->availability['guests'],
				'NightsCapability' => $nights,
				'IncludeOnRequest' => false,
				'ExcludeInstantConfirmation' => false,
			);
			$args['EntitySearch_RQ']['Output']['Availability'] = array(
				'StartDate' => $this->availability['from']->format('Y-m-d'),
				'NumberOfDays' => $nights,
				'LowestRateOnly' => false,
				'MergeMethod' => 'LowestRate',
			);
			$args['EntitySearch_RQ']['Output']['Bookability'] = array();
			$args['EntitySearch_RQ']['Output']['Children'] = array(
				'Filter' => array(
					'Type' => 'Product',
					'MustBeInAdCampaign' => false,
					'MustBeInDealCampaign' => false,
					'Names' => array(),
				),
				'Output' => array(
					'AdvancedContent' => true,
					'Features' => true,
					'Availability' => array(
						'StartDate' => $this->availability['from']->format('Y-m-d'),
						'NumberOfDays' => $nights,
						'LowestRateOnly' => false,
						'MergeMethod' => 'LowestRate',
					),
					'Bookability' => array(),
					'CommonContent' => array('All' => true),
				),
			);
		}

		if ($this->sort_by) {
			$args['EntitySearch_RQ']['Sorting'] = array(
				'Sort' => array(
					'By' => $this->sort_by,
					'Direction' => $this->sort_direction,
					'PositionOfNull' => 'AlwaysOnBottom',
				),
			);
		}

		return $args;
	}
}
