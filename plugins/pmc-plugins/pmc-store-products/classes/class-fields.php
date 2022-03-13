<?php

namespace PMC\Store_Products;
use CheezCapGroup;
use CheezCapTextOption;
use \PMC\Global_Functions\Traits\Singleton;

use PMC;

class Fields {

	use Singleton;

	public function __construct() {
		add_filter( 'pmc_cheezcap_groups', array( $this, 'filter_pmc_cheezcap_groups' ) );
	}

	public function filter_pmc_cheezcap_groups( $groups = array() ) {
		if ( empty( $groups ) ) {
			$groups = array();
		}

		$options = array(
			new CheezCapTextOption(
				__( 'Average Revenue', 'pmc-store-products' ),
				__( 'Used to set the revenue sent in a click event to Google Analytics.', 'pmc-store-products' ),
				'pmc_store_average_revenue',
				''
			),
		);

		$groups[] = new CheezCapGroup( __( 'PMC Store Products', 'pmc-store-products' ), 'pmc_store_products', $options );

		return $groups;
	}

}
