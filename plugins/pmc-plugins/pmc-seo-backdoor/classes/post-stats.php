<?php

namespace PMC\SEO_Backdoor;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class for the PMC Post Stats taxonomy which is used for stats purposes of SEO Overrides
 *
 * @version 2015-08-04
 * @since 2015-08-04 - Mike Auteri - PPT-5223: Track use of SEO Overrides
 */
class Post_Stats {

	use Singleton;

	/**
	 * Taxonomy name as a constant
	 */
	const _taxonomy = 'pmc_post_stats';

	/**
	 * Array for taxonomy terms when SEO Backdoor makes a change
	 * @var array
	 */
	protected $_seo_backdoor_array = array(
		'title'       => 'seo_backdoor_override_title',
		'description' => 'seo_backdoor_override_desc',
		'keywords'    => 'seo_backdoor_override_kw',
		'url'         => 'seo_backdoor_override_url',
	);

	/**
	 * Array for taxonomy terms when Editor makes a change
	 * @var array
	 */
	protected $_seo_editor_array = array(
		'title'       => 'seo_editor_override_title',
		'description' => 'seo_editor_override_desc',
		'keywords'    => 'seo_editor_override_kw',
		'url'         => 'seo_editor_override_url',
	);

	/**
	 * Contruct
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register pmc_post_stats taxonomy
	 *
	 * @since 08-05-2015 - Mike Auteri - PPT-5235: Track use of SEO Overrides
	 * @version 08-05-2015
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'      => 'Post Stats',
			'menu_name' => 'Post Stats',
		);
		$args   = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => false,
			'rewrite'           => false,
			'show_admin_column' => false,
			'query_var'         => false,
			'public'            => false,
		);
		register_taxonomy( self::_taxonomy, array( 'post' ), $args );
	}

	/**
	 * Helper function to pull from correct array of terms
	 *
	 * @since 08-05-2015 - Mike Auteri - PPT-5235: Track use of SEO Overrides
	 * @version 08-01-2015
	 *
	 * @param string $who
	 *
	 * @return array or false
	 */
	public function get_taxonomy_array( $who = '' ) {
		switch ( $who ) {
			case 'seo':
				return $this->_seo_backdoor_array;
			case 'editor':
				return $this->_seo_editor_array;
		}

		return false;
	}

	/**
	 * Helper function to place the correct term with the correct action
	 *
	 * @since 08-05-2015 - Mike Auteri - PPT-5235: Track use of SEO Overrides
	 * @version 08-01-2015
	 *
	 * @param integer $post_id
	 * @param string $post_meta
	 * @param string $who
	 */
	public function add_post_tracker( $post_id = 0, $post_meta = '', $who = '' ) {
		// Check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! intval( $post_id ) || empty( $post_meta ) || empty( $who ) ) {
			return false;
		}

		$taxonomy_values = $this->get_taxonomy_array( $who );
		if ( $taxonomy_values ) {
			switch ( $post_meta ) {
				case 'mt_seo_title':
				case '_yoast_wpseo_title':
					wp_set_object_terms( $post_id, $taxonomy_values['title'], self::_taxonomy, true );
					break;
				case 'mt_seo_description':
				case '_yoast_wpseo_metadesc':
					wp_set_object_terms( $post_id, $taxonomy_values['description'], self::_taxonomy, true );
					break;
				case 'mt_seo_keywords':
				case '_yoast_wpseo_metakeywords':
					wp_set_object_terms( $post_id, $taxonomy_values['keywords'], self::_taxonomy, true );
					break;
				case 'post_name':
					wp_set_object_terms( $post_id, $taxonomy_values['url'], self::_taxonomy, true );
					break;
			}
		}
	}

	/**
	 * Helper function for Editor check
	 *
	 * @since 08-05-2015 - Mike Auteri - PPT-5235: Track use of SEO Overrides
	 * @version 08-01-2015
	 *
	 * @param integer $post_id
	 * @param string $key
	 * @param object $posted_meta
	 *
	 * @return boolean
	 */
	public function editor_check( $post_id = 0, $key = '', $posted_meta, $original_status ) {
		if ( intval( $post_id ) && ! empty( $key ) && isset( $posted_meta ) ) {
			$seo_post = $posted_meta;
			$seo_meta = get_post_meta( $post_id, $key, true );
			// Need to check original status of post. auto-draft we are assuming is a brand new post.
			// New posts will have $seo_post and $seo_meta match.
			if ( ! empty( $seo_post ) && ( $seo_post !== $seo_meta || $original_status === 'auto-draft' ) ) {
				return true;
			}
		}

		return false;
	}
}

//EOF
