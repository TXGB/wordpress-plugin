<?php

/**
 * A Product/Service Rate instance.
 *
 * Represents a rate/price in a structured way.
 *
 * @since      1.0.0
 * @package    TXGB
 * @subpackage TXGB/includes
 * @author     Tourism Exchange Great Britain <hello@txgb.co.uk>
 */
class TXGB_Object_Rate
{
	public $raw = 0.0;
	public $formatted = '&pound;0.00';

	static function make($raw_value, $currency = 'GBP')
	{
		$self = new static;
		$self->raw = floatval($raw_value);
		$self->value = $self->raw * 100;

		if (class_exists('NumberFormatter')) {
			$currency_formatter = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
			$formatted = $currency_formatter->formatCurrency($self->value / 100, $currency);
		} else {
			$formatted = '&pound;' . number_format($self->value / 100, 2);
		}

		$self->formatted = $formatted;

		return $self;
	}
}
