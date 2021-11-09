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
interface TXGB_API_Request
{
	public function to_request_args();
}
