<?php
/**
 * Configuration for PMC Swiftype plugin
 *
 * @since 2017.1.0
 *
 * @package pmc-variety-2017
 */

namespace Variety\Plugins\Config;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;
use \Variety_Hollywood_Executives_Profile;
use \Variety_Hollywood_Executives_Profiles_API;

class PMC_Swiftype {

	use Singleton;

	/**
	 * Class constructor
	 *
	 *
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Method to setup listeners to WP hooks
	 * @version 2018-01-10 brandoncamenisch - feature/PMCER-143:
	 * - Adding pmc_tags_head action and running through phpcbf
	 *
	 * @return void
	 */
	protected function _setup_hooks(): void {

		/*
		 * Actions
		 */

		// This is needed at priority one as it it outputs a Swiftype robots meta tag
		// which should be as high as possible in the <head> container
		// otherwise it is ignored by Swiftype crawl bot
		add_action( 'wp_head', [ $this, 'archive_noindex' ], 1 );

		/*
		 * Filters
		 */
		add_filter( 'pmc_swiftype_configs', [ $this, 'filter_pmc_swiftype_configs' ] );
		add_filter( 'pmc_swiftype_plugin_add_meta_tags_short_circuit', [ $this, 'maybe_short_circuit' ], 10, 2 );
		add_filter( 'pmc_swiftype_meta_tags_body_post_content', [ $this, 'body_content' ], 10, 2 );

	}

	/**
	 * Called on 'pmc_swiftype_configs' filter, this method
	 * customizes Swiftype config
	 *
	 * @param array $config Array containing Swiftype configuration.
	 *
	 * @return array
	 */
	public function filter_pmc_swiftype_configs( $config = [] ) {

		global $post;

		$vip_post_types = [
			'variety_vip_post',
			'variety_vip_report',
			'variety_vip_video',

		];

		if ( ! is_array( $config ) ) {
			$config = [];
		}

		if ( ! empty( $config['date_filters'] ) && is_array( $config['date_filters'] ) ) {
			array_splice( $config['date_filters'], 1, 0, 'content_type_facet:checkbox-facet' );
		}

		if ( taxonomy_exists( 'print-issues' ) ) {
			$config['meta_tags']['appeared_in_print'] = has_term( '', 'print-issues', $post ) ? 'Yes' : 'No';
		}

		//@codeCoverageIgnoreStart
		if ( in_array( $post->post_type, (array) $vip_post_types, true ) ) {
			$config['meta_tags']['topics'] = [ 'variety_vip_tag' ];
		}

		//@codeCoverageIgnoreEnd

		return array_replace_recursive(
			$config,
			[
				/**
				Swiftype Engine Key
				A filter that can be used to change the Swiftype Engine key if it differs
				from the main engine. Variety 500 implements this as it contains it's own
				search index.
				@param string $engine_key The default engine key.
				 */
				'engine_key'        => apply_filters( 'variety_swiftype_engine_key', '1byyzyzxQM-Y595mXFkG' ),
				'placeholder_image' => 'https://pmcvariety.files.wordpress.com/2015/10/logo_variety.png',
				'image_size'        => 'river-small',
				'autocomplete'      => [
					'tags'     => [
						'include' => false,
					],
					'articles' => [
						'name' => 'Content',
					],
				],
				'meta_tags'         => [
					'post_types' => [
						'pmc-gallery'        => 'Gallery',
						'pmc_list'           => 'List',
						'variety_top_video'  => 'Video',
						Variety_Hollywood_Executives_Profile::$a__options['post_type'] => 'Hollywood Exec Profile',
						'variety_vip_post'   => 'VIP Article',
						'variety_vip_report' => 'VIP Special Report',
						'variety_vip_video'  => 'VIP Video',
					],
				],
				'trans'             => [
					'search'        => __( 'Search', 'pmc-variety' ),
					'search_button' => __( 'Go', 'pmc-variety' ),
				],
			]
		);

	}

	/**
	 * Hooked on 'pmc_swiftype_plugin_add_meta_tags_short_circuit', this method
	 * short circuits meta tag addition to page for certain cases.
	 *
	 * @param bool     $do_short_circuit
	 * @param \WP_Post $post
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function maybe_short_circuit( $do_short_circuit, $post ) {

		if ( empty( $post ) || ! is_object( $post ) ) {
			return $do_short_circuit;
		}

		$short_circuit = 0;

		$exec_profile_tags = $this->_maybe_get_exec_profile_meta_tags( $post );

		if ( ! empty( $exec_profile_tags ) ) {

			// The HTML coming here is escaped in the template itself
			// Hence no need to escape here again
			echo $exec_profile_tags; // xss ok
			$short_circuit++;

		}

		if ( $short_circuit > 0 ) {
			$do_short_circuit = true;
		}

		return $do_short_circuit;

	}

	/**
	 * Method to get meta tags for Hollywood Exec Profile if current page
	 * is for that object.
	 *
	 * @param \WP_Post $post
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function _maybe_get_exec_profile_meta_tags( $post ) {

		if ( empty( $post->ID ) || intval( $post->ID ) < 1 ) {
			return '';
		}

		$profile = new Variety_Hollywood_Executives_Profiles_API( $post->ID );

		$variety_500_year = get_option( 'variety_500_year', date( 'Y' ) );

		if ( ! $profile->is_valid() ) {
			return '';
		}

		return PMC::render_template(
			sprintf(
				'%s/templates/pmc-swiftype/variety-hollywood-executives/profile-swiftype-meta-tags.php',
				untrailingslashit( dirname( __DIR__ ) )
			),
			array(
				'post'    => $post,
				'profile' => $profile->get(),
				'in_500'  => $profile->is_in_500( $variety_500_year ),
			)
		);

	}

	/**
	 * Archive_noindex | plugins/_config/class-pmc-swiftype.php
	 *
	 * @since 2018-01-10 - Adds meta tags to archive pages to not index.
	 *
	 * @author brandoncamenisch
	 * @version 2018-01-10 - feature/PMCER-143:
	 * - Adding method
	 *
	 * @version 2018-09-11 - Sagar Bhatt - PMCEED-659
	 * - Removing noindex,nofollow for hollwood_exec archive
	 *
	 * @return void
	 */
	public function archive_noindex() : void {

		if ( is_post_type_archive( 'hollywood_exec' ) ) {
			return; // bail early.
		}

		if ( is_archive() ) {

			// phpcs:disable
			echo '<!-- Swiftype custom VY Meta Tags -->' . PHP_EOL;
			echo '<meta name="st:robots" content="noindex, nofollow">';
			echo PHP_EOL;
			// phpcs:enable

		}

	}

	/**
	 *
	 *
	 * @param $body_content
	 *
	 * @return mixed|string
	 */
	public function body_content( $body_content, $current_post ) {

		if ( 'variety_vip_post' === $current_post->post_type ) {
			return \PMC::truncate( $body_content, 750, '' );
		} else {
			return $body_content;
		}
	}

}    // end of class

// EOF
