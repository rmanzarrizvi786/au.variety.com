<?php
/**
 * Connections service class for PMC Post Options plugin
 *
 * @author Amit Gupta <agupta@pmc.com>
 */

namespace PMC\Post_Options\Service;


use \PMC\Global_Functions\Traits\Singleton;
use PMC\Post_Options\Taxonomy;
use PMC\Post_Options\API;

class Connections {

	use Singleton;

	/**
	 * @var \PMC\Post_Reviewer\Config
	 */
	protected $_config;

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		if ( class_exists( '\PMC\Post_Reviewer\Config' ) ) {

			$this->_config = \PMC\Post_Reviewer\Config::get_instance();

			$this->_setup_hooks();

		}

	}

	/**
	 * Method to set up listeners to WP hooks
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() : void {

		/*
		 * Actions
		 */
		add_action( 'init', [ $this, 'setup_with_post_reviewer' ], 11 );

		/*
		 * Filters
		 */
		add_filter( 'pmc_post_reviewer_admin_ui_add_metaboxes', [ $this, 'setup_post_reviewer_mb' ] );
		add_filter( 'pmc_page_meta', [ $this, 'filter_pmc_page_meta' ] );

	}

	/**
	 * Method called on init hook to setup taxonomy in Post Reviewer plugin
	 *
	 * @return void
	 */
	public function setup_with_post_reviewer() : void {

		$this->_config->add_taxonomies(
			[
				Taxonomy::NAME => 'Post Options',
			],
			false
		);

	}

	/**
	 * Method to setup post options taxonomy metabox UI in Post Reviewer
	 *
	 * @param array $metaboxes
	 *
	 * @return array
	 */
	public function setup_post_reviewer_mb( array $metaboxes = [] ) : array {

		if ( empty( $metaboxes[ Taxonomy::NAME ] ) ) {
			return $metaboxes;
		}

		$metaboxes[ Taxonomy::NAME ]['template'] = sprintf(
			'%s/templates/post-reviewer-metabox.php',
			PMC_POST_OPTIONS_ROOT
		);

		return $metaboxes;

	}

	/**
	 * Adds permitted post options associated with post to GA custom dimension.
	 *
	 * @param array $meta
	 *
	 * @return array
	 */
	public function filter_pmc_page_meta( $meta = [] ) : array {

		if ( ! is_array( $meta ) ) {
			$meta = [];
		}

		if ( ! is_singular() ) {
			return $meta;
		}

		$api                = API::get_instance()->post( get_post() );
		$post_options       = $api->get_post_options();
		$post_options_slugs = wp_list_pluck( array_values( $post_options ), 'slug' );

		if ( is_array( $post_options_slugs ) ) {
			// Use `pmc_post_options_custom_dims` filter to add a post option term slug to custom dims.
			$post_options_custom_dim = apply_filters( 'pmc_post_options_custom_dims', [] );

			if ( ! is_array( $post_options_custom_dim ) ) {
				$post_options_custom_dim = [];
			}

			foreach ( $post_options_custom_dim as $key => $value ) {
				if ( ! in_array( $value, (array) $post_options_slugs, true ) ) {
					unset( $post_options_custom_dim[ $key ] );
				}
			}

			$post_options_custom_dim = array_unique( (array) $post_options_custom_dim );
			$meta['post-options']    = implode( ',', $post_options_custom_dim );
		}

		return $meta;
	}

}    //end class


//EOF
