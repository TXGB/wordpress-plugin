<?php

class TXGB_Object_Availability
{
	public $starts_at;
	public $ends_at;

	public $adults;
	public $children;
	public $concessions;

	public $nights;
	public $guests;

	public function __construct(
		DateTime $starts_at = null,
		DateTime $ends_at = null,

		int $adults = 0,
		int $children = 0,
		int $concessions = 0
	) {
		$this->starts_at = $starts_at;
		$this->ends_at = $ends_at;

		$this->adults = $adults;
		$this->children = $children;
		$this->concessions = $concessions;

		$this->nights = !is_null($ends_at) ? $starts_at->diff($ends_at)->days : 0;
		$this->guests = $adults + $children + $concessions;
	}
}
