<?php

/**
 * A Product/Service instance.
 *
 * Represents a Service entity.
 *
 * @since      1.0.0
 * @package    TXGB
 * @subpackage TXGB/includes
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_Object_Service
{
	public $id;

	public $name = '';
	public $summary = '';
	public $description = '';
	public $phone = '';
	public $email = '';
	public $website = '';

	public $address;
	public $geocoding;

	public $categories = [];
	public $images = [];

	public $parent_id;

	public static function make_from_response($raw_entity, $raw_providers = null)
	{
		$self = new static;

		$self->id = $raw_entity->Id;
		$self->name = $raw_entity->Name;
		$self->description = property_exists($raw_entity, 'LongDescription')
			? $raw_entity->LongDescription
			: '';
		$self->summary = property_exists($raw_entity, 'ShortDescription')
			? $raw_entity->ShortDescription
			: '';
		$self->email = $raw_entity->PublicEmail;
		$self->website = $raw_entity->Website;

		$self->parent_id = $raw_entity->ParentId;

		if ($raw_providers) {
			if (!is_array($raw_providers)) {
				$raw_provider = $raw_providers;
			} else {
				foreach ($raw_providers as $p) {
					if ($p->Id == $self->parent_id) {
						$raw_provider = $p;
						break;
					}
				}
			}

			$self->provider = (object)[
				'id' => $raw_entity->ParentId,
				'short_name' => $raw_provider->Code,
			];
		}

		$self->products = [];

		// If Children are available, their Entity child is either an object or an array
		$raw_products = !property_exists($raw_entity, 'Children') ? [] : $raw_entity->Children->Entity;
		$raw_products = is_array($raw_products) ? $raw_products : [$raw_products];

		if ($raw_products) {
			foreach ($raw_products as $raw_product) {
				$self->products[] = TXGB_Object_Product::make_from_response($raw_product);
			}
		}

		if (property_exists($raw_entity, 'MainPhone')) {
			if (property_exists($raw_entity->MainPhone, 'FullPhoneNumberLocalised')) {
				$self->phone = $raw_entity->MainPhone->FullPhoneNumberLocalised;
			}
		}

		self::parse_address($self, $raw_entity->PhysicalAddress);

		if ($raw_entity->HasGeocodes) {
			self::parse_geocoding($self, $raw_entity->Geocodes);
		}

		if (property_exists($raw_entity, 'Images')) {
			self::parse_images($self, $raw_entity->Images);
		}

		self::parse_categories($self, $raw_entity->IndustryCategoryGroups);

		return $self;
	}

	protected static function parse_address(&$self, $rawAddress)
	{
		if ($rawAddress) {
			$self->address = [
				'line_1'      => $rawAddress->Line1,
				'line_2'      => $rawAddress->Line2,
				'city'        => $rawAddress->City,
				'state'       => $rawAddress->State,
				'postal_code' => $rawAddress->PostCode,
				'country'     => $rawAddress->CountryName,
			];
		}
	}

	protected static function parse_categories(&$self, $rawCategories)
	{
		$rawCategoryArray = is_array($rawCategories)
			? $rawCategories
			: [$rawCategories];

		foreach ($rawCategoryArray as $rawCategory) {
			$self->categories[] = $rawCategory->IndustryCategoryGroupEnum;
		}
	}

	protected static function parse_geocoding(&$self, $rawGeocode)
	{
		$geocode = is_array($rawGeocode->Geocode) ? $rawGeocode->Geocode[0] : $rawGeocode->Geocode;

		$self->geocoding = [
			'latitude' => !property_exists($geocode, 'Geocode')
				? null
				: $geocode->Geocode->Latitude,
			'longitude' => !property_exists($geocode, 'Geocode')
				? null
				: $geocode->Geocode->Longitude,
		];
	}

	protected static function parse_images(&$self, $rawImages)
	{
		$rawImageArray = !is_array($rawImages->Image)
			? [$rawImages->Image]
			: $rawImages->Image;

		foreach ($rawImageArray as $rawImage) {
			if (is_object($rawImage)) {
				$self->images[] = TXGB_Object_Image::make_from_response($rawImage);
			}
		}
	}
}
