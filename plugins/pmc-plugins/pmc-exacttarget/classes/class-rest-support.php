<?php
/**
 * This class is responsible to process all REST API requests for Folders ( Categories ) in Content Builder.
 */

namespace PMC\Exacttarget;

use PMC\Exacttarget\Rest_Request;

class Rest_Support {

	/**
	 * @var string API path for the resource.
	 * Override this in child class as per the resource. Default is set to assets as it's the most common resource used in Content Builder.
	 */
	protected $path = 'asset/v1/content/assets/';

	/**
	 * @var int Default page size for results.
	 */
	public $page_size = 50;

	/**
	 * @var string Base URL ( hostname ) of ET API.
	 */
	public $base_url;

	/**
	 * @var string Acess token used for authentication.
	 */
	private $access_token;

	/**
	 * Contructor.
	 */
	function __construct() {

		$this->access_token = \PMC\Exacttarget\Api::get_access_token();
		$this->base_url     = \PMC\Exacttarget\Api::get_base_url();
	}

	/**
	 * Sends a HTTP GET request to $this->path using a baseUrl from ET_Client object.
	 *
	 * @param array $query       An array containing arguments in key value format to be used as a payload for GET request.
	 * @param array $path_params An array containing parameters to be used in path,
	 * i.e. [ 'ID', '233' ] will be converted to 'ID/233 and appended to $this->path
	 *
	 * @return Rest_Request
	 */
	public function get( array $query = [], array $path_params = [] ) {

		$url = $this->get_endpoint();

		return new Rest_Request( $url, 'GET', $this->access_token, $query, $path_params );
	}

	/**
	 * Sends a HTTP POST request to $this->path using a baseUrl from ET_Client object.
	 *
	 * @param array $query       An array containing arguments in key value format to be used as a payload for POST request.
	 * @param array $path_params An array containing parameters to be used in path,
	 * i.e. [ 'ID', '233' ] will be converted to 'ID/233 and appended to $this->path
	 *
	 * @return Rest_Request
	 */
	public function post( array $query = [], array $path_params = [] ) {

		$url = $this->get_endpoint();

		return new Rest_Request( $url, 'POST', $this->access_token, $query, $path_params );
	}

	/**
	 * Sends a HTTP PATCH request to $this->path using a baseUrl from ET_Client object.
	 *
	 * @param array $resource_id ID of the resource to update
	 * @param array $updates     An array containing payload for updating the resource.
	 *
	 * @return Rest_Request
	 */
	public function patch( $resource_id, $updates ) {

		$url         = $this->get_endpoint();
		$path_params = [ $resource_id ];

		return new Rest_Request( $url, 'PATCH', $this->access_token, $updates, $path_params );
	}

	/**
	 * Sends a HTTP DELETE request to $this->path using a baseUrl from ET_Client object.
	 *
	 * @param array $resource_id ID of the resource to delete.
	 *
	 * @return Rest_Request
	 */
	public function delete( $resource_id ) {

		$url         = $this->get_endpoint();
		$path_params = [ $resource_id ];

		return new Rest_Request( $url, 'DELETE', $this->access_token, [], $path_params );
	}

	/**
	 * Helper for building endpoint for current request.
	 *
	 * @return string
	 */
	protected function get_endpoint() : string {
		return trailingslashit( $this->base_url ) . $this->path;
	}
}
