<?php
/**
 * Class PMC_Custom_Feed_Amazon_Deals
 *
 * This class implement override for Amazon deals feed specific requirements.
 */

use PMC\Global_Functions\Traits\Singleton;
use PMC\Amzn_Onsite\Setup;

class PMC_Custom_Feed_Amazon_Deals {

	use Singleton;

	/**
	 * Current feed's options.
	 *
	 * @var array
	 */
	protected $_feed_options;

	/**
	 * Slug for this template.
	 */
	const TEMPLATE_SLUG = 'feed-amazon-deals';

	/**
	 * Class initialization routine.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	protected function _setup_hooks() {

		add_filter( 'pmc_custom_feed_start', [ $this, 'action_pmc_custom_feed_start' ], 10, 3 );

		// hooked to priority 11:: because product_description appends to post->content at priority 10.
		add_filter( 'pmc_custom_feed_content', [ $this, 'maybe_strip_inline_image_tag_from_product_description' ], 11, 5 );

	}

	/**
	 * This function loads current feed's options if not already loaded
	 *
	 * @return void
	 */
	function action_pmc_custom_feed_start( $feed, $feed_options, $template_name ) {
		// only continue if the feed is feed-amazon-deals feed
		if ( 'feed-amazon-deals.php' !== $template_name ) {
			return;
		}

		add_filter( 'pmc_custom_feed_rss_author', [ $this, 'pmc_custom_feed_rss_author' ], 10, 2 );
	}

	/**
	 * Filters the RSS feed author.
	 *
	 * @param string $rss_author Existing RSS <author> value.
	 * @param object $author     Author object.
	 * @return string
	 */
	public function pmc_custom_feed_rss_author( $rss_author, $author ) {
		return sprintf( '<author>%1$s</author>', esc_html( $author->display_name ) );
	}

	/**
	 * Gets the Amazon products for a given post.
	 *
	 * @param integer $post_id ID for the post.
	 * @return array
	 */
	public function get_post_amazon_products( $post_id ) {

		/**
		 * Each theme must implement this filter to correctly return Amazon products.
		 *
		 * Product should follow this format:
		 * [
		 *     'url'      => $amazon_url,
		 *     'headline' => $editorial_title,
		 *     'summary'  => $editorial_summary,
		 *     'award'    => $amazon_award,
		 *     'rank'     => $amazon_rank,
		 * ]
		 *
		 * @param array   $products Amazon products in post.
		 * @param integer $post_id  Id for the post.
		 */

		$post_id = intval( $post_id );

		// @todo revisit themes using this filter to add products to feed. Likely the filter the theme is using can be removed.
		$products = apply_filters( 'pmc_custom_feed_amazon_deals_products', [], $post_id );

		if ( ! is_array( $products ) ) {
			$products = [];
		}

		$cleaned_products = [];

		foreach ( $products as $product ) {
			$product = array_intersect_key(
				$product,
				[
					'url'      => '',
					'headline' => '',
					'summary'  => '',
					'award'    => '',
					'rank'     => '',
				]
			);

			if ( ! empty( $product ) && is_array( $product ) ) {
				if ( ! empty( $product['summary'] ) ) {
					$product['summary'] = wptexturize( $product['summary'] );
				}

				$cleaned_products[] = $product;
			}
		}

		return $cleaned_products;

	}

	/**
	 * Strip inline images if 'strip-inline-images' custom feed option checked.
	 *
	 * Note: this is already taken care by `pmc_custom_feed_post_start()` but only for $post->post_content,
	 *       here done for other content that is appended/updated to it.
	 *
	 * @param string   $content         post content for feed
	 * @param \string  $feed            current feed name
	 * @param \WP_Post $post            post object being process
	 * @param array    $feed_options    array of option for current feed
	 * @param string   $feed_template   name of feed-tempalte
	 *
	 * @return string
	 */
	public function maybe_strip_inline_image_tag_from_product_description( $content, $feed, $post, $feed_options, $feed_template = '' ): string {

		if ( 'feed-amazon-deals.php' === $feed_template && ! empty( $feed_options['strip-inline-images'] ) ) {

			$content = wp_kses( $content, array(
				'p'      => array(), # paragraph tags are allowed
				'i'      => array(), # italic tags are allowed
				'em'     => array(), # emphasis tags are allowed
				'b'      => array(), # bold tags are allowed
				'strong' => array(), # strong tags are allowed
				'a'      => array(   # anchor tags are allowed
					'href'   => array(),
					'title'  => array(),
					'target' => array(),
				),
				'div'   => array(   # div tags are allowed
					'id'            => array(),
					'class'         => array(),
					'style'         => array(),
					'data-itemtype' => array()
				),
			) );

			$content = preg_replace( '/(<div id="attachment_.*\s+.*\s+.*\s+.*\s+<\/div>)/i', '', $content );
			// Remove empty paragraphs created by the above operation
			$content = str_replace( '<p></p>', '', $content );

			// above operation will remove tracking image tag(if added), so lets add it back if its enable
			if ( ! empty( $feed_options['tracking'] ) && 'on' === $feed_options['tracking'] ) {

				$feed_tracking = PMC_Custom_Feed_Helper::get_feed_tracking();
				$feed_tracking = empty( $feed_tracking ) ? '' : $feed_tracking;

				$content .= $feed_tracking;

			}

			// Strip any leading/trailing whitespace
			$content = trim( $content );

		}

		return $content;

	}

}

PMC_Custom_Feed_Amazon_Deals::get_instance();
