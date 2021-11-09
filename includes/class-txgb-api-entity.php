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
class TXGB_API_Entity extends TXGB_API
{

	/**
	 * The URL to the WSDL that we're going to use.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $wsdl    The URL of the WSDL we're using
	 */
	protected $wsdl = 'https://book.txgb.co.uk/v4/Services/EntityService.asmx?WSDL';

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @var string $short_name
	 * @var string $key
	 */
	public function __construct(string $short_name, string $key)
	{
		if (defined('TXGB_WSDL_ENTITY')) {
			$this->wsdl = TXGB_WSDL_ENTITY;
		} elseif (!txgb_is_production()) {
			$this->wsdl = 'https://uatbook.txgb.co.uk/v4/Services/EntityService.asmx?WSDL';
		}

		parent::__construct($short_name, $key);
	}

	/**
	 * Fetch the public content for a number of Services
	 */
	public function get_services_for_import(array $service_ids)
	{
		// Create a search object for our IDs
		$query = new TXGB_API_Service_Query($this->short_name);
		$args = $query->ids($service_ids)
			->with_content()
			->with_provider()
			->with_products()
			->to_request_args();

		try {
			// Run the call to TXGB
			$response = $this->client->search($args);

			// If there's 1 result, we don't get an array on the return
			$raw_entities = !is_array($response->EntitySearch_RS->Entities->Entity)
				? [$response->EntitySearch_RS->Entities->Entity]
				: $response->EntitySearch_RS->Entities->Entity;
			$raw_providers = !is_array($response->EntitySearch_RS->Parents->ParentEntity)
				? [$response->EntitySearch_RS->Parents->ParentEntity]
				: $response->EntitySearch_RS->Parents->ParentEntity;

			// Process the XML return into something nicer to work with...
			$services = [];
			foreach ($raw_entities as $raw_entity) {
				$services[] = TXGB_Object_Service::make_from_response($raw_entity, $raw_providers);
			}

			// ...and return
			return $services;
		} catch (Exception $e) {
			txgb_handle_exception($e);
		}
	}

	/**
	 * Search for Services on the Distributor's account. Used when importing.
	 */
	public function get_all_services(string $type = '', string $query = '')
	{
		$query = new TXGB_API_Service_Query($this->short_name);
		$query->sort_by('Name', 'Ascending')->with_short_content();
		$args = $query->to_request_args();

		$services = array();
		try {
			$response = $this->client->search($args);
			$rawServices = $response->EntitySearch_RS->Entities;

			foreach ($rawServices->Entity as $rawService) {
				$services[] = TXGB_Object_Service::make_from_response($rawService);
			}
		} catch (Exception $e) {
			txgb_handle_exception($e);
		}

		return $services;
	}

	/**
	 * Used for a cross-service availability search on the Custom Post archive page
	 *
	 * "Which services are available on these dates for these people?"
	 */
	public function search_by_availability(
		TXGB_Object_Availability $availability,
		$exclude_unavailable = true
	) {
		$query = new TXGB_API_Service_Query($this->short_name);
		$query->show_availability($availability->starts_at, $availability->ends_at, $availability->guests);

		if ($exclude_unavailable) {
			$query->exclude_unavailable();
		}

		$args = $query->to_request_args();

		try {
			$response = $this->client->search($args);

			$raw_entity = $response->EntitySearch_RS->Entities->Entity;
			$ids = array();

			if ($raw_entity && count($raw_entity) > 0) {
				foreach ($raw_entity as $service) {
					$ids[] = $service->Id;
				}
			}

			return $ids;
		} catch (Exception $e) {
			txgb_handle_exception($e);
		}
	}

	/**
	 * Fetch a single Service by ID.
	 */
	public function get_service(string $id)
	{
		throw new Exception('TXGB deprecation: get_service');

		// Create a search object for ourID
		$query = new TXGB_API_Service_Query($this->short_name);
		$args = $query->id($id)
			->with_products()
			->with_provider()
			->to_request_args();

		try {
			// Run the call to TXGB
			$response = $this->client->search($args);

			// Fetch the result and return it in a nicer format
			$entity = $response->EntitySearch_RS->Entities->Entity;

			return TXGB_Object_Service::make_from_response($entity);
		} catch (Exception $e) {
			txgb_handle_exception($e);
		}
	}
}
