<?php
/*
 * @since 2015-06-01 Hau Vong
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Term_Split {

	use Singleton;

	const OPTION_PREFIX = 'pmc-term-split-';
	protected function __construct() {
		add_action( 'split_shared_term', array( $this, 'action_split_shared_term' ), 10, 4 );
	}

	public function action_split_shared_term( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {
		PMC_Options::get_instance( self::OPTION_PREFIX . $taxonomy )->update_option( $term_id, $new_term_id );
	}

	public function get_term_id( $old_term_id, $taxonomy ) {
		if ( $old_term_id ) {
			$term_id = wp_get_split_term( $old_term_id, $taxonomy );
			if ( false === $term_id ) {
				return PMC_Options::get_instance( self::OPTION_PREFIX . $taxonomy )->get_option( $old_term_id );
			}
			return $term_id;
		}
		return false;
	}


}

PMC_Term_Split::get_instance();

