<?php
/**
 * Badge,
 * Handler for Badge functionality.
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcamp.com>
 *
 * @since 2017-08-04
 *
 * @package pmc-variety-2017
 */

namespace Variety\Inc\Badges;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Badge.
 */
abstract class Badge {

	use Singleton;

	/**
	 * Construct method.
	 */
	protected function __construct() {

		/**
		 * Setting up late.
		 * because pmc-core is registering 'editorial' taxonomy on init hook
		 * with default (10) priority. So we won't be able detect
		 * If 'editorial' taxonomy is registerd or not before that.
		 */
		add_action( 'init', array( $this, 'setup_hooks' ), 15 );

	}

	/**
	 * To check if Taxonomy for badge is registerd or not.
	 *
	 * @return bool TRUE if badge taxonomy is exists otherwise FALSE
	 */
	protected function _is_taxonomy_exists() {
		return taxonomy_exists( $this::TAXONOMY_SLUG );
	}

	/**
	 * To setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		if ( ! $this->_is_taxonomy_exists() ) {
			return;
		}

		$this->_maybe_insert_term();

	}

	/**
	 * Create badger term if not exists.
	 *
	 * @return array|bool Term data if create other will FALSE
	 */
	protected function _maybe_insert_term() {

		$term = false;

		if ( ! wpcom_vip_term_exists( $this::TERM_SLUG, $this::TAXONOMY_SLUG ) ) {

			$term = wp_insert_term(
				$this::TERM_NAME,
				$this::TAXONOMY_SLUG, array(
					'slug'        => $this::TERM_SLUG,
					'description' => 'Badge Term',
				)
			);

		}

		return $term;
	}

	/**
	 * Check if post have badge or not.
	 *
	 * @param int|WP_Post $post Post ID or Object.
	 *
	 * @return bool
	 */
	public function exists_on_post( $post ) {

		if ( empty( $post ) || ( ! is_object( $post ) && ! is_numeric( $post ) ) ) {
			return false;
		}

		$post = get_post( $post );

		if ( ! is_a( $post, 'WP_Post' ) ) {
			return false;
		}

		return has_term( $this::TERM_SLUG, $this::TAXONOMY_SLUG, $post );
	}

	/**
	 * To get badge link.
	 *
	 * @return bool|string Link of badge.
	 */
	public function get_link() {

		$link = get_term_link( $this::TERM_SLUG, $this::TAXONOMY_SLUG );

		if ( ! empty( $link ) && ! is_wp_error( $link ) ) {
			return $link;
		}

		return false;
	}

}
