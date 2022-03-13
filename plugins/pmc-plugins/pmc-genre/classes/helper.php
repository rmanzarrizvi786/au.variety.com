<?php
/*
 * This class contains misc helper methods for the plugin
 *
 * @author Amit Gupta <agupta@pmc.com>
 */

namespace PMC\Genre;

use \PMC;

class Helper {

	/**
	 * This method returns the URL of an asset if relative path to asset is passed else
	 * the URL to assets folder.
	 *
	 * @param string $asset_path Optional asset path relative from assets folder
	 * @return string URL to asset or asset folder
	 */
	public static function get_asset_url( $asset_path = '' ) {
		return plugins_url( sprintf( '/assets/%s', ltrim( $asset_path, '/' ) ), dirname( __FILE__ ) );
	}

	public static function get_terms( $taxonomy = 'category', array $args = array() ) {
		if ( empty( $args ) ) {
			$args = array(
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false,
			);
		}

		$terms = get_terms( $taxonomy, $args );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return false;
		}

		return $terms;
	}

	public static function get_terms_array( $taxonomy = 'category' ) {
		$terms = static::get_terms( $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			$terms = array();
		}

		if ( empty( $terms ) ) {
			return $terms;
		}

		$terms_organized = array();

		foreach ( $terms as $term ) {
			$terms_organized[ intval( $term->term_id ) ] = PMC::untexturize( $term->name );
		}

		return $terms_organized;
	}

}	//end of class


//EOF