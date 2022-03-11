<?php
namespace PMC\Core\Inc;

/**
 * Class FM_Taxonomy Taxonomy/Terms related functions should go in this class
 * @package PMC\Core\Inc
 */
class Taxonomy {

	use \PMC\Global_Functions\Traits\Singleton;

	private $_meta_name = '_pmc_core_hide_child';

	/**
	 * Taxonomy constructor.
	 */
	protected function __construct() {

		add_action( 'init', [ $this, 'add_fm_term_actions' ], 11 );

	}

	/**
	 * Add Term meta.
	 */
	public function add_fm_term_actions() {

		if ( ! is_admin() ) {
			return;
		}

		$args = [
			'public' => true,
		];

		$taxonomies = get_taxonomies( $args, 'objects', 'and' );

		if ( ! empty( $taxonomies ) ) {
			$this->_public_tax = $taxonomies;
			foreach ( $taxonomies as $taxonomy ) {
				add_action( "fm_term_{$taxonomy->name}", [ $this, 'add_term_meta' ] );
			}
		}

	}

	/**
	 * Add Fieldmanager Term meta that will control if term should be shown as child in the archive pages.
	 *
	 * @param $name
	 *
	 * @return \Fieldmanager_Checkbox
	 */
	public function add_term_meta( $name ) {

		$fm = new \Fieldmanager_Checkbox( [
			'label' => 'Check to hide as child term in archive pages.',
			'name'  => $this->_meta_name,
		] );
		$fm->add_term_meta_box( 'Hide Child in archive pages', $name );

		return $fm;
	}

	/**
	 * Get Child terms for a term that excludes child terms marked to be excluded using field manager.
	 *
	 * @param $parent_term
	 *
	 * @return array|bool
	 */
	public function get_child_terms( $parent_term ) {

		if ( empty( $parent_term->term_id ) ) {
			$parent_term = get_queried_object();
		}

		if ( empty( $parent_term->term_id ) ) {
			return false;
		}

		$terms = get_terms( [
			'taxonomy'   => (string) ( $parent_term->taxonomy ),
			'parent'     => intval( $parent_term->term_id ),
			'hide_empty' => 1,
			'orderby'    => 'name',
		] );

		$term_arr = [];

		if ( empty( $terms ) || is_wp_error( $terms ) || ! is_array( $terms ) ) {
			return false;
		}

		foreach ( $terms as $term ) {

			$term_meta = fm_get_term_meta( $term->term_id, $term->taxonomy, $this->_meta_name, true );

			//check meta directly
			if ( empty( $term_meta ) ) {
				$term_meta = get_term_meta( $term->term_id, $this->_meta_name, true );
			}

			if ( empty( $term_meta ) || '1' !== $term_meta ) {
				$term_arr[] = $term;
			}

		}

		if ( ! empty( $term_arr ) ) {
			return $term_arr;
		}

		return false;

	}

}

//EOF
