<?php

namespace PMC\Affiliate_Links;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;
use \PMC_Cheezcap;
use \PMC\Affiliate_Links\Contracts\Affiliate;

abstract class Base implements Affiliate {

	use Singleton;

	/**
	 * Construct
	 */
	protected function __construct() {
		$this->get_cheez_options();
	}

	/**
	 * Return Cheezcap settings:
	 */
	public function get_cheez_options(){
		$this->conf[ 'tag_value' ] = PMC_Cheezcap::get_instance()->get_option( $this->get_config( 'cheez_tag_option' ) );
		$this->conf[ 'status' ]    = PMC_Cheezcap::get_instance()->get_option( $this->get_config( 'cheez_status_option' ) );
	}

	/**
	 * simple getter
	 *
	 * @return bool
	 */
	public function is_affiliate_enabled(){
		return (bool) $this->get_config( 'status' );
	}

	/**
	 * Return config values if value exists. False if it doesn't.
	 *
	 * @param $field
	 * @return bool
	 */
	public function get_config( $field ){

		if( !empty( $this->conf[ $field ] ) ){
			return $this->conf[ $field ];
		}

		return false;

	}

}	//end class


//EOF
