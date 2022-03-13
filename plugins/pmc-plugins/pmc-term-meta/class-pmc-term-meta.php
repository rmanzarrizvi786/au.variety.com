<?php

/**
 * PMC_Term_Meta class which implements the API for storing/fetching meta
 * info into/from any term of a taxonomy. Not all taxonomies are supported
 * by default, a taxonomy not supported by default has to be whitelisted
 * using 'pmc_term_meta_taxonomy_whitelist' filter.
 *
 * @author Amit Gupta
 * @since 2013-09-24
 * @version 2013-10-04 Amit Gupta
 * @version 2013-12-17 Amit Gupta
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Term_Meta {

	use Singleton;

	const plugin_id = 'pmc-term-meta';

	private static $__instance;

	protected $_taxonomies = array( 'editorial', 'print-issues' );

	/**
	 * Initialization routine
	 */
	protected function __construct() {
		self::$__instance = $this;

		$this->_setup_taxonomy_whitelist();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_stuff' ), 9, 1 );
	}

	protected function _setup_taxonomy_whitelist() {
		$this->_taxonomies = apply_filters( 'pmc_term_meta_taxonomy_whitelist', $this->_taxonomies );
		$this->_taxonomies = array_filter( array_unique( (array) $this->_taxonomies ) );

		if ( empty( $this->_taxonomies ) ) {
			$this->_taxonomies = array();
		}
	}

	public function enqueue_stuff( $hook ) {
		if ( $hook !== 'term.php' || ! in_array( $GLOBALS['taxonomy'], $this->_taxonomies ) ) {
			return;
		}

		//load scripts bundled in WordPress
		wp_enqueue_script( 'jquery' );

		//load our css
		wp_enqueue_style( self::plugin_id . '-admin-css', plugins_url( 'css/admin-edit-tags.css', __FILE__ ), array() );

		//load our script
		wp_enqueue_script( self::plugin_id . '-admin-js', plugins_url( 'js/admin-edit-tags-api.js', __FILE__ ), array( 'jquery' ) );
	}

	/**
	 * This function returns all the meta keys of a term
	 * in an associative array
	 */
	public function get_all_meta( $term ) {
		$default = array();

		$term = apply_filters( 'pmc_term_meta_get_all', $term );

		if ( empty( $term ) || ! is_object( $term ) || empty( $term->description ) ) {
			return $default;
		}

		$term_meta = json_decode( PMC::untexturize( $term->description ), true );

		if ( ! empty( $term_meta ) ) {
			return $term_meta;
		}

		return $default;
	}

	/**
	 * This function facilitates saving of one or more meta keys of a term
	 * as it accepts the meta data in an associative array
	 */
	public function save_multiple( $term_id, $taxonomy, $data = array() ) {
		/**
		 * If term object is passed then setup data in its var
		 * and extract taxonomy name and term ID from it
		 */
		if ( is_object( $term_id ) && ! empty( $term_id->term_id ) && ! empty( $term_id->taxonomy ) && ! empty( $taxonomy ) && is_array( $taxonomy ) ) {
			$data = $taxonomy;
			$taxonomy = $term_id->taxonomy;
			$term_id = $term_id->term_id;
		}

		$term_id = intval( $term_id );

		if ( $term_id < 1 || empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) || empty( $data ) || ! is_array( $data ) ) {
			return false;
		}

		/**
		 * Get fresh data, don't want to fall into the trap of
		 * race conditions or stale objects
		 * Better be safe than sorry, always assume users are dumb! ;)
		 */
		$term = get_term( $term_id, $taxonomy );

		if ( empty( $term ) ) {
			return false;
		}

		$term_meta = $this->get_all_meta( $term );

		if ( empty( $term_meta ) ) {
			$term_meta = array();
		}

		foreach ( $data as $key => $value ) {
			$term_meta[ $key ] = wp_strip_all_tags( $value );	//don't allow HTML etc in meta info
		}

		$term_meta = json_encode( $term_meta );

		wp_update_term( $term_id, $taxonomy, array(
			'description' => $term_meta
		) );

		return true;
	}

	/**
	 * This function is for fetching meta value of a single key of a term
	 */
	public function get_single( $term, $key, $default = false ) {
		if ( empty( $term ) || empty( $key ) || ! is_string( $key ) ) {
			return $default;
		}

		$term_meta = $this->get_all_meta( $term );

		if ( ! empty( $term_meta ) && array_key_exists( $key, $term_meta ) ) {
			return $term_meta[ $key ];
		}

		return $default;
	}

	/**
	 * This function is for saving a single key and its value in a term meta.
	 */
	public function save_single( $term_id, $taxonomy, $key = '', $value = '' ) {
		/**
		 * If term object is passed then assign key & value
		 * and extract taxonomy name and term ID from it
		 */
		if ( is_object( $term_id ) && ! empty( $term_id->term_id ) && ! empty( $term_id->taxonomy ) && ! empty( $taxonomy ) ) {
			$value = $key;
			$key = $taxonomy;
			$taxonomy = $term_id->taxonomy;
			$term_id = $term_id->term_id;
		}

		if ( empty( $term_id ) || empty( $taxonomy ) || empty( $key ) ) {
			return false;
		}

		$data = array(
			$key => $value
		);

		return $this->save_multiple( $term_id, $taxonomy, $data );
	}

	/**
	 * This is a magic function which implements a Facade for our API
	 * so that the API can be called as
	 * PMC_Term_Meta::get( $term, $key );
	 * PMC_Term_Meta::set( $term_id, $taxonomy, $key, $value ); OR PMC_Term_Meta::set( $term_obj, $key, $value );
	 * PMC_Term_Meta::get_all( $term );
	 * PMC_Term_Meta::set_all( $term_id, $taxonomy, $data ); OR PMC_Term_Meta::set_all( $term_obj, $data );
	 */
	public static function __callStatic( $method, $args = array() ) {
		//all API functions need atleast one paramater, so this can't be empty
		if ( empty( $args ) || ! is_array( $args ) ) {
			return false;
		}

		//determine which function to call
		switch( $method ) {
			case 'set_all':
				$method_to_call = 'save_multiple';
				break;
			case 'get_all':
				$method_to_call = 'get_all_meta';
				break;
			case 'set':
				$method_to_call = 'save_single';
				break;
			case 'get':
			default:
				$method_to_call = 'get_single';
				break;
		}

		//limit number of arguments passed to 4
		return call_user_func_array( array( self::$__instance, $method_to_call ), array_slice( $args, 0, 4 ) );
	}

//end of class
}


//EOF
