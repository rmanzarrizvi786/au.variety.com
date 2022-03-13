<?php

namespace PMC\Affiliate_Links;

use \PMC;

class Amazon extends Base {

	public $conf = array(
		'tag_key'              => 'tag',
		'tag_value'            => null,
		'affiliate_pattern'    => 'amazon.com',
		'status'               => 'disabled',
		'cheez_tag_option'     => 'pmc_affiliate_links_amazon_tag',
		'cheez_status_option'  => 'pmc_affiliate_links_amazon_status'
	);

}	//end class


//EOF
