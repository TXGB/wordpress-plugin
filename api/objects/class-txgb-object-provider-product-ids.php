<?php

/**
 * A Product instance.
 *
 * Represents a Provider Product entity when looking up availability.
 *
 * @since      1.0.0
 * @package    TXGB
 * @subpackage TXGB/includes
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_Object_Provider_Product_Ids
{

	public $id;
	public $obx_id;

	static function make_from_response($entity)
	{
		$self = new static;

		$self->id = $entity->id;
		$self->obx_id = $entity->obx_id;

		return $self;
	}
}
