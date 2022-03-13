<?php
namespace PMC\Subscription;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC\Subscription_V2\Plugin as SubscriptionV2Plugin;

class Paywall_Posts {

	use Singleton;

	var $paywall_post_feed_config = null;

	/**
	 * Class instantiation
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		add_action( 'widgets_init', [ $this, 'action_register_widget' ] );

		add_filter( 'pmc_delayed_save_post_tasks', [ $this, 'instruct_save_post_task_runner_to_recache' ] );
		add_filter( 'rest_prepare_post', [ $this, 'filter_rest_prepare_post' ], 1, 2 );

		// We need the action init to run at priority 15 > Cheezcap init at 11 in theme.
		// Any cheezcap option dependencies in action_init must run after cheezcap register finished.
		add_action( 'init', [ $this, 'action_init' ], 15 );

	}

	public function action_init() {
		// Note: requires priority of 100 so meta data can save before assigning taxonomy term.
		add_action( 'save_post', [ $this, 'set_post_subscription_status' ], 100 );
		add_filter( 'pmc_custom_feed_posts_filter', [ $this, 'custom_paywall_bypass_feed' ], 10, 2 );
		add_filter( 'pmc_custom_feed_options_toggles', [ $this, 'paywall_bypass_feed_option' ] );
	}

	/**
	 * Register the Paywalled Paywall_Posts Widget
	 *
	 * @codeCoverageIgnore
	 */
	public function action_register_widget() {
		register_widget( '\PMC\Subscription\Paywall_Posts_Widget' );
	}

	/**
	 * Add our paywalled post id recache to the list of delayed save post tasks.
	 *
	 * @param array $tasks An array of existing delayed tasks.
	 *
	 * @return array The *possibly* modified list of delayed save post tasks to be run.
	 */
	public function instruct_save_post_task_runner_to_recache( $tasks = [] ) {
		$num_paywalled_posts_to_return = 3;

		$tasks['\PMC\Subscription\Paywall_Posts_Widget::get_post_ids'] = [
			'callback' => [ $this, 'maybe_do_cache_refresh' ],
			'params'   => [ $num_paywalled_posts_to_return ],
		];

		return $tasks;
	}

	/**
	 * Perform a cache refresh on post save (delayed)
	 *
	 * @param int $post_id
	 *
	 * @return array Empty array on failure. Array with one paywalled
	 */
	public function maybe_do_cache_refresh( $limit = 3, $post_id = 0 ) {
		if ( empty( $limit ) || empty( $post_id ) ) {
			return [];
		}

		$post_ids = [];

		// Only do the cache refresh if a paywalled post was recently modified.
		if ( pmc_paywall_article_access_required( $post_id ) ) {
			$post_ids = $this->get_post_ids( $limit, true );
		}

		return $post_ids;
	}

	/**
	 * Return an array of paid article post IDs.
	 *
	 * This method may be called both publically and statically.
	 *
	 * @param int $limit    Number of posts required.
	 * @param bool $recache False by default. Pass true to force a recache (for invalidation).
	 *
	 * @return array An array of paywalled post Ids. Empty array on failure.
	 */
	public function get_post_ids( $limit = 3, $recache = false ) {
		$cache_key = 'post-ids';

		$paywalled_post_ids = wp_cache_get( $cache_key, PMC_SUBSCRIPTION_CACHE_GROUP );

		if ( ! $paywalled_post_ids || $recache ) {
			$paywalled_post_ids = [];

			$posts = new \WP_Query(
				[
					'fields'         => 'ids',
					'post_status'    => 'publish',
					'post_type'      => 'post',
					'posts_per_page' => 25,
				]
			);

			if ( is_wp_error( $posts ) || empty( $posts->posts ) ) {
				return [];
			}

			foreach ( $posts->posts as $post_id ) {
				if ( \pmc_paywall_article_access_required( $post_id ) ) {
					array_push( $paywalled_post_ids, $post_id );
				}
			}

			wp_cache_set( $cache_key, $paywalled_post_ids, PMC_SUBSCRIPTION_CACHE_GROUP );
		}

		return array_slice( $paywalled_post_ids, 0, $limit );
	}

	/**
	 * Set pmc-subscription term for either `not-behind-paywall` or `behind-paywall`
	 * based on status of post at time of saving.
	 *
	 * @return void
	 */
	public function set_post_subscription_status( $post_id ) : void {
		// Bail on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return; // @codeCoverageIgnore
		}

		// Check that we are in the admin and that current user can edit a post.
		if ( ! is_admin() || ! current_user_can( 'edit_posts' ) ) {
			return; // @codeCoverageIgnore
		}

		$taxonomy_instance = Paywall_Taxonomy::get_instance();
		$taxonomy          = $taxonomy_instance->taxonomy;

		// If post type is not permitted to use taxonomy, then return.
		if ( ! in_array( get_post_type( $post_id ), (array) $taxonomy_instance->get_permitted_post_types(), true ) ) {
			return;
		}

		// If post is behind paywall we add the `behind-paywall` term
		// (and remove `not-behind-paywall` term) and vice versa.
		if ( pmc_paywall_article_access_required( $post_id ) ) {
			wp_set_object_terms( $post_id, 'behind-paywall', $taxonomy, true );
			wp_remove_object_terms( $post_id, 'not-behind-paywall', $taxonomy );
		} else {
			wp_set_object_terms( $post_id, 'not-behind-paywall', $taxonomy, true );
			wp_remove_object_terms( $post_id, 'behind-paywall', $taxonomy );
		}
	}

	/**
	 * Limit paid post content to teaser in WP REST API.
	 *
	 * @param  object $response
	 * @param  object $post
	 *
	 * @return object
	 */
	function filter_rest_prepare_post( $response, $post ) {
		if ( pmc_paywall_roadblock( $post->ID ) ) {

			$content = apply_filters( 'the_content', $post->post_content );
			$teaser  = pmc_paywall_get_roadblock_teaser_copy( 1, $content );

			if ( ! empty( $teaser ) && is_array( $teaser ) ) {

				$teaser = reset( $teaser );

				if ( $response->data['content']['rendered'] ) {
					$response->data['content']['rendered'] = wp_kses_post( $teaser );
				}

				if ( $response->data['content']['raw'] ) {
					$response->data['content']['raw'] = wp_kses_post( $teaser );
				}
			}
		}

		return $response;
	}

	/**
	 * Set option for Paywall Bypass for PMC Custom Feeed.
	 * This option includes posts in the `behind-paywall` term of `pmc-subscription` taxonomy
	 * that were published within the last 10 days.
	 *
	 * @param array $options
	 * @return array
	 */
	public function paywall_bypass_feed_option( $options ) : array {
		$options['pmc-subscription-paywall-bypass-feed'] = __( 'Paywall Bypass Feed - Paid', 'pmc-subscription' );
		$options['pmc-subscription-paywall-bypass-free'] = __( 'Paywall Bypass Feed - Free', 'pmc-subscription' );

		// Complementary settings
		$options['pmc-subscription-keywee-facebook-utm-setting'] = __( 'Paywall Bypass Feed: Add Acquisition UTM Params', 'pmc-subscription' );
		$options['pmc-subscription-kwfb-subscriber-utm-setting'] = __( 'Paywall Bypass Feed: Add Subscriber UTM Params', 'pmc-subscription' );

		return $options;
	}

	/**
	 * Make adjustments to feed when Paywall Bypass option is checked.
	 * Adjustments include only including posts with `not-/behind-paywall` term
	 * of the `pmc-subscription` taxonomy. It also manipulates the post link
	 * to a ULS URL that is capable of bypassing the paywall.
	 *
	 * @param array $args
	 * @param array $config
	 * @return array
	 */
	public function custom_paywall_bypass_feed( $args, $config ) : array {
		$terms = [];

		if ( ! empty( $config['pmc-subscription-paywall-bypass-feed'] ) ) {
			array_push( $terms, 'behind-paywall' );
		}

		if ( ! empty( $config['pmc-subscription-paywall-bypass-free'] ) ) {
			array_push( $terms, 'not-behind-paywall' );
		}
		// If bypass feed options not selected, return arguments.
		if ( empty( $terms ) ) {
			return $args;
		}

		$this->paywall_post_feed_config = $config;

		if ( ! has_filter( 'post_link', [ $this, 'uls_bypass_link' ] ) ) {
			add_filter( 'post_link', [ $this, 'uls_bypass_link' ] );
		}

		$taxonomy_instance = Paywall_Taxonomy::get_instance();

		// Get from the pmc-subscription taxonomy.
		$args['tax_query'] = [ // phpcs:ignore
			'relation' => 'AND',
			[
				'taxonomy' => $taxonomy_instance->taxonomy,
				'field'    => 'slug',
				'terms'    => $terms,
			],
		];

		// Include posts 1 year old or less.
		$args['date_query'] = [
			[
				'after'     => date( 'F d, Y', strtotime( '-1 year' ) ), // Remove time for date comparison.
				'inclusive' => true,
			],
		];

		return $args;
	}

	/**
	 * Replace links to articles in bypass feed with ULS links that are capable of
	 * bypassing the paywall.
	 *
	 * @param string $url
	 * @param array  $config
	 * @return string
	 */
	public function uls_bypass_link( $url ) : string {
		global $post;

		$config = $this->paywall_post_feed_config;

		$uls_key    = \PMC\Uls\Plugin::get_instance()->uls_key();
		$uls_secret = \PMC\Uls\Plugin::get_instance()->uls_secret();
		$timestamp  = strtotime( $post->post_date );
		$campaign   = '';

		// PMCS-1950: skip TokenAuth in ULS for now
		// $url = sprintf( '%1$s/article/%2$d/', esc_url_raw( \PMC\Uls\Plugin::get_instance()->uls_url() ), intval( $post->ID ) );

		// Refer to documentation: https://confluence.pmcdev.io/pages/viewpage.action?pageId=42566764
		$params = [ 'token' => md5( (string) $post->ID . $uls_key . $uls_secret . (string) $timestamp ) ];

		if ( ! empty( $config['pmc-subscription-keywee-facebook-utm-setting'] ) ) {
			$campaign = sprintf( 'subac_%d_social', date( 'Y' ) );
		}
		if ( ! empty( $config['pmc-subscription-kwfb-subscriber-utm-setting'] ) ) {
			$campaign = 'reteng_sub_social';
		}

		if ( ! empty( $campaign ) ) {
			$params['utm_source']   = 'facebook';
			$params['utm_medium']   = 'keywee';
			$params['utm_campaign'] = $campaign;
			$params['utm_content']  = sanitize_title( $post->post_title );
		}

		return add_query_arg( $params, $url );
	}

}

// EOF
