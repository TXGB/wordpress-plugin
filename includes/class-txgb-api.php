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
class TXGB_API
{

	/**
	 * A PHP SoapClient object that will connect us to the API and allow us to run
	 * queries
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      SoapClient    $client    Our PHP SOAP client to make requests
	 */
	protected $client;

	/**
	 * The site's access credentials: TXGB's Shortname
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $short_name    The saved short name for the API
	 */
	protected $short_name;

	/**
	 * The site's access credentials: TXGB's access key
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $key    The saved access key for the API
	 */
	protected $key;

	/**
	 * The URL to the WSDL that we're going to use.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $wsdl    The URL of the WSDL we're using
	 */
	protected $wsdl;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @var string $short_name
	 * @var string $key
	 */
	public function __construct(string $short_name, string $key, string $wsdl = '')
	{

		// Save the user's credentials
		$this->short_name = $short_name;
		$this->key = $key;

		if (!$this->wsdl) {
			$this->wsdl = $wsdl;
		}

		// Instantiate the client
		$options = [
			'soap_version' => SOAP_1_2,
			'login'        => $this->short_name,
			'trace' => true,
		];

		// Nesting the "if" to prevent Intelephense open issue: https://github.com/bmewburn/vscode-intelephense/issues/952
		if (defined('TXGB_DISABLE_SSL_VERIFY_PEER')) {
			if (TXGB_DISABLE_SSL_VERIFY_PEER) {
				$options['stream_context'] = stream_context_create(
					array(
						'ssl' => array(
							'verify_peer' => false,
							'verify_peer_name' => false,
							'allow_self_signed' => true
						)
					)
				);
			}
		}

		$this->client = new SoapClient($this->wsdl, $options);
	}

	protected function call($function, $args)
	{
		$response = $this->client->$function($args);

		return $response;
	}
}
