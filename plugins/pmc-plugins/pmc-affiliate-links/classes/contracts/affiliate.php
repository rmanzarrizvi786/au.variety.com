<?php

namespace PMC\Affiliate_Links\Contracts;

interface Affiliate {

	public function get_cheez_options();

	public function is_affiliate_enabled();

	public function get_config( $field );

}	//end interface



//EOF