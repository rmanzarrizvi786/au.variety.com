<?php
namespace PMC\Subscription;

/**
 * WWD.com highlight paywalled articles.
 */
class Paywall_Posts_Widget extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		parent::__construct(
			'paywall-plugin-posts-widget',
			__( 'Paywalled Paywall_Posts', 'pmc-subscription' ),
			[ 'description' => __( 'Shows posts behind the paywall.', 'pmc-subscription' ) ]
		);
	}

	/**
	 * Output widget.
	 *
	 * @param type $args Widget arguments.
	 * @param type $instace Instance of widget.
	 */
	public function widget( $args, $instance ) {

		if ( pmc_paywall_user_has_entitlements( [ 'year', 'archive' ], 'or' ) ) {
			return;
		}

		$paywalled_post_ids = Paywall_Posts::get_instance()->get_post_ids();

		if ( empty( $paywalled_post_ids ) ) {
			return;
		}

		\PMC::render_template(
			sprintf( '%s/templates/paywall-posts-widget.php', untrailingslashit( PMC_SUBSCRIPTION_ROOT ) ),
			[ 'paywalled_post_ids' => $paywalled_post_ids ],
			true
		);
	}
}
