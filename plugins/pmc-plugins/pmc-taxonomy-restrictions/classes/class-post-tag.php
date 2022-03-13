<?php
/**
 * Restrict post tag creation
 *
 * @package pmc-taxonomy-restrictions
 */

namespace PMC\Taxonomy_Restrictions;

use \PMC;
use \PMC_Cheezcap;
use \CheezCapDropdownOption;

class Post_Tag extends Taxonomy_Restrictions {

	const TAXONOMY = 'post_tag';

	const OPTION_NAME = 'pmc_restrict_post_tag_creation';

	/**
	 * Register various hooks
	 */
	protected function __construct() {

		parent::__construct();

		$this->_setup_hooks();

	}

	/**
	 * Register hooks
	 */
	protected function _setup_hooks() {

		add_filter( 'pmc_taxonomy_restrictions_cheezcap_options', array( $this, 'add_cheezcap_option' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );

	}

	/**
	 * Add 'Restrict Post Tag Creation' option to enable/disabled tag creation
	 *
	 * @param array $cheezcap_options An array of Cheezcap options.
	 *
	 * @return array
	 */
	public function add_cheezcap_option( $cheezcap_options ) {

		if ( empty( $cheezcap_options ) || ! is_array( $cheezcap_options ) ) {
			$cheezcap_options = array();
		}

		$cheezcap_options[] = new CheezCapDropdownOption(
			'Restrict Post Tag Creation',
			'If this option enabled then certain user roles not able to create new post tag.',
			self::OPTION_NAME,
			array( 'disabled', 'enabled' ),
			0, // Default option => Disabled.
			array( 'Disabled', 'Enabled' )
		);

		return $cheezcap_options;

	}

	/**
	 * Return true if 'Restrict Post Tag creation' option is enabled else false
	 *
	 * @return bool
	 */
	protected function _is_term_creation_restricted() {

		return ( 'enabled' === PMC_Cheezcap::get_instance()->get_option( self::OPTION_NAME ) );

	}

	/**
	 * Enqueue scripts only on tags edit page.
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_assets( $hook ) {

		if ( 'edit-tags.php' !== $hook || ! $this->_is_term_creation_restricted() ) {
			return;
		}

		$user = wp_get_current_user();

		if ( ! empty( $user->roles ) && empty( array_intersect( $this->_get_user_roles_whitelist(), $user->roles ) ) ) {

			$suffix = ( PMC::is_production() ) ? '.min' : '';

			wp_enqueue_script( 'pmc-post-tag-restrictions-js', plugins_url( 'assets/js/post-tag' . $suffix . '.js', __DIR__ ), array( 'jquery' ), false, true );

		}

	}

}
