<?php

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
class TXGB_Booking
{
	public function register()
	{
		if (!defined('TXGB_PAYMENT_URL')) {
			define('TXGB_PAYMENT_URL', 'https://book.txgb.co.uk/v4/Services/Injection.aspx');
		}

		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'helpers/booking.php';
	}
}
