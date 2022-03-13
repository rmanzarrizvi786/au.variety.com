<?php

namespace PMC\Piano;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Responsible for creating and rendering customer and subscriber pages.
 */
class Pages {

	use Singleton;

	/**
	 * The pages which will be created/rendered by this class.
	 */
	const PAGE_TITLES = [
		'My Account',       // site.com/my-account
		'Password Reset',   // site.com/password-reset
	];

	const CREATED_PAGES_OPTION = 'pmc_piano_pages_created';

	/**
	 * Pages constructor.
	 */
	protected function __construct() {

		// The following filter will be used by sites during subscription development
		// Allowing them to prevent subs pages from launching until the site/theme
		// decide to launch them. For example, Sportico will use it's subs launch
		// cheezcap to set this filter.
		if ( apply_filters( 'pmc_piano_do_pages', true ) ) {
			add_action( 'admin_init', [ $this, 'create_pages' ] );
			add_filter( 'the_content', [ $this, 'render_page' ] );
		}
	}

	/**
	 * Automatically create Piano pages.
	 *
	 * When a logged-in admin user visits the site or wp-admin,
	 * and when the pages do not already exist.
	 */
	public function create_pages(): void {

		if ( ! current_user_can( 'edit_posts' ) || wp_doing_ajax() ) {
			return;
		}

		$are_pages_already_created = \pmc_get_option( self::CREATED_PAGES_OPTION );

		if ( '1' === $are_pages_already_created ) {
			return;
		}

		foreach ( self::PAGE_TITLES as $page_title ) {

			$page = wpcom_vip_get_page_by_path( sanitize_title( $page_title ) );

			if ( ! empty( $page->ID ) ) {
				continue;
			}

			$page_args = [
				'post_title'   => $page_title,
				'post_name'    => sanitize_title( $page_title ),
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => __( "This page's content is automatically generated. Edits here will have no effect.", 'pmc-piano' ),
			];

			wp_insert_post( $page_args );

		}

		\pmc_add_option( self::CREATED_PAGES_OPTION, '1' );
	}

	/**
	 * Render our page's templates when someone visits one of our pages.
	 *
	 * Ex. A page titled "My Account" will be rendered with the content
	 * defined within templates/my-account.php.
	 *
	 * @throws \Exception
	 */
	public function render_page( string $content ) : string {

		if ( ! is_page( self::PAGE_TITLES ) ) {
			return $content;
		}

		return \PMC::render_template(
			sprintf(
				'%s/templates/%s.php',
				untrailingslashit( PMC_PIANO_ROOT ),
				get_queried_object()->post_name
			),
			[],
			false
		);

	}

	/**
	 * Get the URL to a specific page.
	 *
	 * @param string $page The page title or slug to get the URL for.
	 *                     E.g. 'My Account' or 'my-account'.
	 *                     Ex. \PMC\Piano\Pages::get_instance()->get_page_url( 'My Account' );
	 *                     Ex. \PMC\Piano\Pages::get_instance()->get_page_url( 'my-account' );
	 *
	 *                     Note: The output of this function must be escaped.
	 *                     Ex. <a href="<?php esc_attr_e( \PMC\Piano\Pages::get_instance()->get_page_url( 'My Account' ) ) ?>">My Account</a>
	 *
	 * @return string
	 */
	public function get_page_url( string $page ) : string {

		$page_obj = wpcom_vip_get_page_by_path( sanitize_title( $page ) );

		if ( $page_obj ) {
			return get_permalink( $page_obj );
		}

		return get_site_url();
	}

}
