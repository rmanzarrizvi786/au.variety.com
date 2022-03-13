<?php
/**
 * Class to add "Sponsored Content" post option and to render the badge for posts on frontend
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2019-07-19
 */

namespace PMC\Frontend_Components\Badges;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Post_Options\API;
use \PMC;


class Sponsored_Content {

	use Singleton;

	const SLUG = 'add-sponsored-content-badge';

	/**
	 * @var \PMC\Post_Options\API
	 */
	protected $_api;

	/**
	 * @var array Array containing Global Post Options that are to be registered
	 */
	protected $_global_options = [

		// NOTE: Terms MUST, at the very least; include a label.
		//       Though, a description is also preferred.

		// Option to add sponsored content badge on a post
		self::SLUG => [
			'label'       => 'Add Sponsored Content Badge',
			'description' => 'Posts with this term will display a badge indicating that they contain sponsored content.',
		],

	];

	/**
	 * Class constructor
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		$this->_api = API::get_instance();

		$this->_setup_hooks();

	}

	/**
	 * Method to setup listeners on WP hooks
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	protected function _setup_hooks() : void {

		/*
		 * Actions
		 */
		add_action( 'admin_init', [ $this, 'register_global_options' ], 12 );    // set on 12 because post-options plugin adds defaults at 11
		add_action( 'pmc_frontend_components_badges_sponsored_content', [ $this, 'render_badge' ] );

	}

	/**
	 * Called on 'admin_init' hook, this method adds post options
	 *
	 * @return bool
	 */
	public function register_global_options() : bool {

		if ( empty( $this->_global_options ) ) {
			return false;
		}

		$this->_api->register_global_options( $this->_global_options );

		return true;

	}

	/**
	 * Method to render the markup for the badge if post has the option selected
	 *
	 * @param int $post_id ID of the post for which badge is to be rendered
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function render_badge( $post_id = 0 ) : void {

		/*
		 * Added this instead of typecasting in method signature because
		 * this method is called by a WP hook and if no value is passed
		 * to WP hook then it passes a string to attached listeners
		 * which would cause a fatal error if this method expects an integer.
		 */
		$post_id = intval( $post_id );

		if ( $post_id < 1 ) {

			$post_id = get_the_ID();

			if ( empty( $post_id ) || intval( $post_id ) < 1 ) {

				// Not a valid post ID
				// bail out
				return;

			}

		}

		$post = get_post( $post_id );

		if ( empty( $post ) || ! is_a( $post, \WP_Post::class ) ) {

			// Not a valid post ID
			// bail out
			return;

		}

		if ( ! $this->_api->post( $post )->has_option( self::SLUG ) ) {

			// Post does not have option selected
			// bail out
			return;

		}

		$badge_label = __( 'Sponsored Content', 'pmc-frontend-components' );
		$badge_label = apply_filters( 'pmc_frontend_components_badges_sponsored_content_label', $badge_label, $post );

		if ( empty( $badge_label ) || ! is_string( $badge_label ) ) {

			// Invalid value returned by filter
			// bail out
			return;

		}

		PMC::render_template(
			sprintf( '%s/templates/badges/sponsored-content.php', PMC_FRONTEND_COMPONENTS_ROOT ),
			[
				'badge_label' => $badge_label,
			],
			true
		);

	}

}    //end class

//EOF
