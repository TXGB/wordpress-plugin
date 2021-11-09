<?php

/**
 * A Product/Service Image instance.
 *
 * Represents a Service Image entity.
 *
 * @since      1.0.0
 * @package    TXGB
 * @subpackage TXGB/includes
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_Object_Image
{

	public $id;

	public $url = '';

	public $srcSet = array();

	static function make_from_response($entity)
	{
		$self = new static;

		$self->id = property_exists($entity, 'Id') ? $entity->Id : null;
		$self->url = property_exists($entity, 'Url') ? $entity->Url : $entity->relative_url;
		$self->name = property_exists($entity, 'Name') ? $entity->Name : '';
		$self->description = property_exists($entity, 'Description') ? $entity->Description : '';

		if (property_exists($entity, 'Sizes')) {
			$rawSizes = !is_array($entity->Sizes->Size)
				? array($entity->Sizes->Size)
				: $entity->Sizes->Size;

			foreach ($rawSizes as $rawSize) {
				$self->srcSet[] = array(
					'size' => $rawSize->TargetWidth,
					'url' => $rawSize->Url,
				);
			}
		}

		return $self;
	}
}
