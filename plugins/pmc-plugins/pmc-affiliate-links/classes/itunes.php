<?php

namespace PMC\Affiliate_Links;

use \PMC;

class Itunes extends Base {

	public $conf = array(
		'tag_key'              => 'at',
		'tag_value'            => null,
		'affiliate_pattern'    => 'itunes.apple.com/us/app',
		'status'               => 'disabled',
		'cheez_tag_option'     => 'pmc_affiliate_links_itunes_tag',
		'cheez_status_option'  => 'pmc_affiliate_links_itunes_status'
	);

}	//end class


//EOF