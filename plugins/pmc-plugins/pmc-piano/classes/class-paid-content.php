<?php

namespace PMC\Piano;

use ErrorException;
use PMC\Facebook_Instant_Articles\Plugin as PMC_FBIA_Plugin;
use PMC\Frontend_Components\Badges\Sponsored_Content;
use PMC\Global_Functions\Evergreen_Content;
use PMC\Global_Functions\Traits\Singleton;
use PMC\Structured_Data\Article_Data;
use PMC\Post_Options\API as Post_Options;
use WP_Post;
use WP_REST_Posts_Controller;
use WP_REST_Request;

class Paid_Content {

	use Singleton;

	const FREE_CONTENT_OPTION           = 'subs-free-content';
	const ALWAYS_PAYWALL_CONTENT_OPTION = 'subs-always-paywall-content';
	const PAID_CONTENT_CSS_CLASS        = '.pmc-paywall';
	const FILTER_PRIORITY_THE_CONTENT   = 19;

	protected function __construct() {
		Article_Data::get_instance()->activate_on_all_post_types(); // activate for all post type
		add_action( 'init', [ $this, 'action_init' ], 15 );
	}

	/**
	 * Perform actions when WordPress initializes
	 */
	public function action_init() {

		/**
		 * Create the "Free Content" Post Option.
		 *
		 * Selecting this option ensures that a post is 'free'.
		 *
		 * This option influences how and when article structured data
		 * and the .pmc-paywall div are rendered.
		 */
		Post_Options::get_instance()->register_global_options(
			[
				self::FREE_CONTENT_OPTION => [
					'label'       => 'Free Content',
					'description' => 'Posts with this term will be set as Free Content.',
				],
			]
		);

		/**
		 * Create the "Always Paywall" Post Option.
		 *
		 * Selecting this option ensures that a post is 'paywalled'.
		 *
		 * This option:
		 * + influences how and when article structured data and the .pmc-paywall div are rendered
		 * + sends indicators to Cxense and Piano to paywall articles always.
		 */
		Post_Options::get_instance()->register_global_options(
			[
				self::ALWAYS_PAYWALL_CONTENT_OPTION => [
					'label'       => 'Always Paywall',
					'description' => 'Posts with this term will always paywalled.',
				],
			]
		);

		/**
		 * IMPORTANT: FBIA transformation rules must be added before FBIA start processing the content
		 * FBIA response to ia_markup request during wp action. The plugin also interact with post save event in wp-admin
		 *
		 * Tell Facebook Instance Articles how to parse our div
		 * <div class="pmc-paywall"> which wraps paid content.
		 */
		PMC_FBIA_Plugin::get_instance()->add_rules(
			[
				self::PAID_CONTENT_CSS_CLASS => 'PassThroughRule',
			] 
		);

		if ( is_admin() ) {
			return;
		}

		/**
		 * Tell Google (via structured data) how to crawl paid content.
		 */
		add_filter( Article_Data::FILTER_DATA, [ $this, 'filter_pmc_plugin_structured_data_article_data' ] );
		add_filter( 'amp_post_template_metadata', [ $this, 'filter_pmc_plugin_structured_data_article_data' ] ); // Update structured data for subscriptions module.

		/**
		 * Wrap paid content in a div so that we may hide/show it
		 * dynamically based on user/subscriber state.
		 *
		 * Make this late priority so all content is wrapped in div tags.
		 * 19 has been used since pmc-subscription v1, unsure if it's still accurate.
		 * https://bitbucket.org/penskemediacorp/pmc-plugins/commits/dcd442d258b71a1900f9d00ef66c97b5c27db934
		 */
		add_filter( 'the_content', [ $this, 'adjust_paid_content' ], static::FILTER_PRIORITY_THE_CONTENT );
		add_filter( 'rest_request_before_callbacks', [ $this, 'allow_content_in_rest_response' ], 10, 3 );
	}

	/**
	 * Wrap paywall content in div tag with class of paid_content_selector.
	 *
	 * @param string $content
	 *
	 * @return string
	 * @throws ErrorException
	 */
	public function adjust_paid_content( string $content ): string {

		if ( $this->_is_paid_content() ) {
			$content = sprintf(
				'<div class="%s">%s</div>',
				esc_attr( ltrim( self::PAID_CONTENT_CSS_CLASS, '.' ) ),
				$content
			);
		}

		return $content;
	}

	/**
	 * Leave untouched post content returned in REST API responses for edit
	 * context, lest paywall div appear in Gutenberg.
	 *
	 * @param mixed           $response REST response override.
	 * @param array           $handler  REST endpoint handler data.
	 * @param WP_REST_Request $request  REST request data.
	 * @return mixed
	 */
	public function allow_content_in_rest_response(
		$response,
		array $handler,
		WP_REST_Request $request
	) {
		if (
			$handler['callback'][0] instanceof WP_REST_Posts_Controller
			&& 'edit' === $request->get_param( 'context' )
			&& is_user_logged_in()
		) {
			remove_filter(
				'the_content',
				[ $this, 'adjust_paid_content' ],
				static::FILTER_PRIORITY_THE_CONTENT
			);
		}

		return $response;
	}

	/**
	 * Output paid content structured data.
	 *
	 * @param mixed $metadata
	 *
	 * @return mixed
	 * @throws ErrorException
	 */
	public function filter_pmc_plugin_structured_data_article_data( $metadata ) {

		if ( ! is_array( $metadata ) ) {
			return $metadata;
		}

		$post_id         = get_queried_object_id();
		$is_paid_content = $this->_is_paid_content( $post_id );

		if ( $is_paid_content ) {
			$metadata['hasPart'] = [
				'@type'               => 'WebPageElement',
				'isAccessibleForFree' => 'False',
				'cssSelector'         => self::PAID_CONTENT_CSS_CLASS,
			];
		}

		/**
		 * Add `isPartOf` key for amp-subscriptions module.
		 *
		 * Reference links:
		 *  - https://github.com/Financial-Times/next-json-ld
		 *  - https://github.com/bitmovin/amphtml/blob/master/extensions/amp-subscriptions/amp-subscriptions.md#json-ld-markup
		 */
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {

			$blog_info = wp_strip_all_tags( get_bloginfo( 'name', 'display' ), true );

			$metadata['isPartOf'] = [
				'@type' => [ 'CreativeWork', 'Product' ],
				'name'  => $blog_info,
			];

			$metadata['isPartOf']['productID'] = $blog_info . ( $is_paid_content ? ':subscribed' : ':free' );

		}

		$metadata['isAccessibleForFree'] = $is_paid_content ? 'False' : 'True';

		return $metadata;
	}

	/**
	 * Determine if a post is considered 'paid' content.
	 *
	 * @param int|WP_Post|null $post
	 *
	 * @return bool
	 * @throws ErrorException
	 */
	private function _is_paid_content( $post = null ): bool {

		$is_paid         = false;
		$post            = get_post( $post );
		$post_type       = get_post_type( $post );
		$paid_post_types = apply_filters( 'pmc_piano_paid_post_types', [] );
		
		$is_sponsored_content = Post_Options::get_instance()
			->post( $post )
			->has_option( Sponsored_Content::SLUG );

		if ( ( in_array( $post_type, (array) $paid_post_types, true ) ) && ! $is_sponsored_content ) {
			$is_paid = true;
		}

		return apply_filters(
			'pmc_piano_is_paid_content',
			$is_paid,
			$post,
			$is_sponsored_content,
		);
	}
}
