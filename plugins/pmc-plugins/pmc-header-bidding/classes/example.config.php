<?php

/**
 * Class: LOB_Extend_Header_Bidder
 *
 */

use \PMC\Global_Functions\Traits\Singleton;

class LOB_Extend_Header_Bidder {

	use Singleton;

	/**
	* _init
	*
	*/
	protected function __construct() {
		$this->_setup_hooks();
	}


	/**
	* _setup_hooks
	*
	*/
	protected function _setup_hooks() {
		add_filter( 'pmc_header_bidder_filter_vendors', array( $this, 'filter_vendors' ), 11, 1 );
		add_filter( 'pmc_header_bidder_filter_bidder_params', array( $this, 'filter_params' ), 11, 1 );
	}


	/**
	* filter_params
	*
	* @param mixed $params
	*/
	public function filter_params( $params )
	{
		return array(
			'appnexus' => array(
				'desktop' => array(
					'atf' => 'desktop',
					'btf' => 'btf desktop'
				),
				'mobile' => array(
					'atf' => 'atf mobile',
					'btf' => 'btf mobile'
				)
			)
		);
	}


	/**
	* filter_vendors
	*
	* @param mixed $vendors
	*/
	public function filter_vendors( $vendors )
	{
		return array(
			'appnexus' => array(
				'placementId' => '',
			),
			'indexExchange' => array(
				'id' => '',
				'siteID' => ''
			),
			'pubmatic' => array(
				'publisherId' => '',
				'adSlot' => ''
			),
			'rubicon' => array(
				'accountId' => '',
				'siteId' => '',
				'zoneId' => ''
			),
			'sovrn' => array(
				'tagid' => ''
			)
		);
	}


}

LOB_Extend_Header_Bidder::get_instance();
