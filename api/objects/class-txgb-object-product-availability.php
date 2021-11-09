<?php

/**
 * A Product Availability instance.
 *
 * Represents a Product Availability entitiy.
 *
 * @since      1.0.0
 * @package    TXGB
 * @subpackage TXGB/includes
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_Object_ProductAvailability
{
	public $name;

	public $starts_at;

	public $ends_at;

	public $price;

	public $nights;

	static function makeFromProductResponse($product)
	{
		$period = $product->Availability->Nights;

		$self = new static;
		$self->id 			= $product->id;
		$self->name 		= $product->name;
		$self->starts_at 	= new DateTime($period->start_date);
		$self->ends_at 		= new DateTime($period->finish_date);
		$self->nights 		= $period->nights;
		$self->price 		= $period->price;

		return $self;
	}

	public function costPerNight()
	{
		return $this->price / $this->nights;
	}
}
