<?php
/**
 * Configuration for pmc-tags plugin.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @since 2018-03-29 READS-1141
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Primary_Taxonomy;

class PMC_Tags {

	use Singleton;

	/**
	 * Class Constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Add action and filters hooks.
	 */
	protected function _setup_hooks() {
		/**
		 * Filters.
		 */
		add_filter( 'pmc_tags_skimlink_domain_id', array( $this, 'filter_pmc_tags_skimlink_domain_id' ) );
		add_filter( 'pmc-tags-filter-tags', array( $this, 'filter_pmc_tags_filter_tags' ) );
	}

	/**
	 * Filter skimlinks domain id as per the site.
	 *
	 * @return string
	 */
	public function filter_pmc_tags_skimlink_domain_id() {
		return '87443X1540253';
	}

	/**
	 * Filter to enable skimlinks tag on site.
	 *
	 * @param array $options list of tags.
	 *
	 * @return array
	 */
	public function filter_pmc_tags_filter_tags( $options ) {

		if ( ! empty( $options ) && is_array( $options ) && isset( $options['skimlinks'] ) ) {
			$options['skimlinks']['enabled'] = true;
		}

		if ( isset( $options['digioh'] ) ) {
			$options['digioh']['enabled']      = true;
			$options['digioh']['values']['id'] = '54fc2134-b361-4697-a2de-ca735b8070e2';
		}

		if ( isset( $options['bombora'] ) ) {
			$options['bombora']['enabled']      = true;
			$options['bombora']['values']['id'] = 'UA-1915907-80'; // GA ID
		}

		if ( isset( $options['instinctive'] ) ) {
			$options['instinctive']['enabled'] = false;
		}

		if ( isset( $options['venatus'] ) ) {
			$options['venatus']['enabled']           = true;
			$options['venatus']['values']['site_id'] = '58dcc70546e0fb0001b87f36';
		}

		if ( isset( $options['keywee'] ) ) {
			$options['keywee']['enabled'] = false;
		}

		if ( isset( $options['permutive'] ) ) {
			$options['permutive']['enabled']              = true;
			$options['permutive']['values']['project-id'] = '3d2fb0bd-52fc-4b75-aaf5-2d436c172540';
			$options['permutive']['values']['api-key']    = '2aed5ae2-5875-450b-9e5e-34ac932123da';
		}

		if ( isset( $options['trackonomics'] ) ) {
			$options['trackonomics']['enabled']               = true;
			$options['trackonomics']['values']['customer_id'] = 'pmc_0aaa4_variety';
		}

		if ( isset( $options['habu'] ) ) {
			$options['habu']['enabled']      = true;
			$options['habu']['values']['id'] = '2d4e594f-bf73-47e0-8e0b-b5ef0d87ffa4';
		}

		if ( isset( $options['scroll'] ) ) {
			$options['scroll']['enabled'] = true;
		}

		return $options;
	}
}
