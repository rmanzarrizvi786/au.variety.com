<?php
/*
 * This class handles the post screen UI of PMC Genre plugin in wp-admin
 *
 * @author Amit Gupta <agupta@pmc.com>
 */

namespace PMC\Genre;


class Post_UI extends Base {

	/**
	 * @var PMC\Genre\Taxonomy Object of the PMC\Genre\Taxonomy class
	 */
	protected $_taxonomy;

	/**
	 * @var PMC\Genre\Settings Object of the PMC\Genre\Settings class
	 */
	protected $_settings;


	protected function __construct() {
		$this->_taxonomy = Taxonomy::get_instance();
		$this->_settings = Settings::get_instance();

		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		/*
		 * Actions
		 */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_stuff' ) );		//setup our script enqueuing for post screen in wp-admin
	}

	public function get_mapped_term_ids() {
		$terms_map = $this->_settings->get_mapped_terms( true );

		$mapped_terms_ids = array();

		/*
		 * Loop over both category & vertical mappings.
		 * One or two iterations of this loop.
		 */
		foreach ( $terms_map as $collection ) {
			/*
			 * Loop over each term in this collection to fetch
			 * IDs of genres mapped to each term
			 */
			foreach ( $collection as $id => $map ) {
				$mapped_terms_ids[ $id ] = array_keys( $map );
			}
		}

		return $mapped_terms_ids;
	}

	public function get_unmapped_term_ids() {
		return array_keys( $this->_settings->get_unmapped_terms() );
	}

	/**
	 * Enqueue the scripts etc
	 */
	public function enqueue_stuff( $hook ) {
		$pages_to_check = array(
			'post.php',
			'post-new.php',
		);

		if ( ! in_array( $hook, $pages_to_check ) ) {
			//not our page
			return;
		}

		wp_enqueue_script( 'jquery' );

		//load our script & stylesheet
		wp_enqueue_style( self::PLUGIN_ID . '-post-ui-css', Helper::get_asset_url( 'css/post-ui.css' ) );
		wp_enqueue_script( self::PLUGIN_ID . '-post-ui-js', Helper::get_asset_url( 'js/post-ui.js' ), array( 'jquery' ) );

		wp_localize_script( self::PLUGIN_ID . '-post-ui-js', 'pmc_genre_vars', array(
			'plugin_id'     => self::PLUGIN_ID,
			'mapped_genres' => $this->get_mapped_term_ids(),
			'unmapped_genres' => $this->get_unmapped_term_ids(),
		) );
	}

}	//end of class


//EOF
