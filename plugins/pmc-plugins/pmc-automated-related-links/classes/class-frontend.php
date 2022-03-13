<?php
/**
 * Class Frontend
 *
 * Handlers for the Frontend functionality.
 *
 * @package pmc-automated-related-links
 */

namespace PMC\Automated_Related_Links;

use PMC\Global_Functions\Traits\Singleton;

class Frontend {

	use Singleton;

	/**
	 * Injection constructor.
	 *
	 * @codeCoverageIgnore
	 */
	protected function __construct() {

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_style' ] );

		// Adding at 11 priority, To make sure inline content ads inject first
		// And then related links
		add_filter( 'pmc_inject_content_paragraphs', [ $this, 'inject' ], 11 );

		// Adding at 11 priority because some of theme just replace whole style,
		// and does not honor previous style changes. so adding after that.
		add_filter( 'pmc-google-amp-styles', [ $this, 'get_amp_style' ], 11 );

		add_filter( 'pmc_ga_event_tracking', [ $this, 'add_event_tracking' ] );
	}

	/**
	 * To enqueue styles
	 *
	 * @return void
	 */
	public function enqueue_style() {

		if ( is_single() && ! $this->is_related_box_hidden( get_the_ID() ) ) {

			wp_enqueue_style( 'pmc-automated-related-link-style', sprintf( '%s/assets/build/css/style.css', PMC_AUTOMATED_RELATED_LINKS_PLUGIN_URL ) );
			wp_enqueue_script(
				'pmc-automated-related-link-script',
				sprintf( '%s/assets/build/js/app.js', PMC_AUTOMATED_RELATED_LINKS_PLUGIN_URL ),
				[
					'pmc-hooks',
				]
			);
		}
	}

	/**
	 * To check related module is hidden for given post or not.
	 *
	 * @param int $post_id Post ID
	 *
	 * @return bool returns true if Related module is set to hide for current post else false.
	 */
	public function is_related_box_hidden( $post_id = 0 ) {

		$return_value = false;
		$post_id      = intval( $post_id );

		if ( $post_id < 1 ) {
			$post_id = get_the_ID();
		}

		if ( intval( $post_id ) > 1 ) {

			$linked_data = get_post_meta( $post_id, Plugin::POST_META_NAME, true );

			if ( ! empty( $linked_data['settings']['hide_box'] ) ) {
				$return_value = $linked_data['settings']['hide_box'];
			}
		}

		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return apply_filters( 'pmc_global_amp_override_related_box', $return_value );
		}

		return apply_filters( 'pmc_global_override_related_box', $return_value );
	}

	/**
	 * To add style for related link module for AMP pages.
	 *
	 * @param  string $style AMP page style
	 *
	 * @return string
	 */
	public function get_amp_style( $style ) {

		$template = sprintf( '%s/templates/amp-style.php', untrailingslashit( PMC_AUTOMATED_RELATED_LINKS_PLUGIN_PATH ) );

		return $style . \PMC::render_template( $template );
	}

	/**
	 * Inject related links into post content based on character count if its singular post.
	 *
	 * @filter pmc_inject_content_paragraphs
	 *
	 * @param array $paragraphs An array of paragraphs.
	 *
	 * @return array Updated array of paragraphs.get_related_links
	 */
	public function inject( $paragraphs = [] ) {

		// If it's not single post types. bail out.
		if ( ! is_singular() || $this->is_related_box_hidden( get_the_ID() ) ) {
			return $paragraphs;
		}

		$paragraphs = $this->inject_related_links( $paragraphs );

		return $paragraphs;

	}

	/**
	 * Inserts related links into post content based on character count.
	 *
	 * @filter pmc_inject_content_paragraphs
	 *
	 * @param array $paragraphs An array of paragraphs.
	 *
	 * @return array Updated array of paragraphs.get_related_links
	 */
	public function inject_related_links( $paragraphs = [] ) {

		global $post;

		$allow_on_amp = apply_filters( 'pmc_automated_related_links_allow_on_amp', true );

		if ( true !== $allow_on_amp && function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return $paragraphs;
		}

		$clean_content  = $this->clean_up_content( $post->post_content );
		$content_length = strlen( $clean_content ); // This is an approximation.
		$content_array  = explode( '</p>', $clean_content );
		$char_count     = 0;

		$first_ad_pos = \PMC_Cheezcap::get_instance()->get_option( 'pmc-ad-placeholders-first-pos' );

		$injection_args_pos = ( ! empty( $first_ad_pos ) && 0 < intval( $first_ad_pos ) ) ? intval( $first_ad_pos ) + 100 : 300;
		$injection_args_pos = apply_filters( 'pmc_automated_related_links_position', $injection_args_pos, $post );

		$injection_args_min = $injection_args_pos + 1200;

		/**
		 * Definitions:
		 * 'pos'      = number of characters before displaying the injection
		 * 'min'      = minimum content length of the article, in characters
		 * 'inserted' = if this injection has already been used
		 * 'callback' = a non-static callback function located within this class
		 *
		 * Note that 1 word ≈ 6 characters.
		 *
		 * first ad.
		 */
		$injection_args = array(
			'related' => array(
				'pos'      => $injection_args_pos,
				'min'      => $injection_args_min,
				'inserted' => false,
				'callback' => array( $this, 'inject_related_card' ),
			),
		);

		$injection_args = apply_filters( 'pmc_automated_related_links_injection_args', $injection_args, $post );

		foreach ( $content_array as $index => $content ) {
			if ( ! empty( $content ) ) {
				$char_count = $char_count + ( strlen( $content ) - 3 );
				foreach ( $injection_args as $label => $atts ) {
					$atts['min'] = ( $atts['min'] < $atts['pos'] ) ? $atts['pos'] : $atts['min'];
					if ( $char_count > $atts['pos'] && $content_length > $atts['min'] && true !== $atts['inserted'] ) {
						$injection_args[ $label ]['inserted'] = true;

						if ( ! empty( $atts['callback'] ) && is_callable( $atts['callback'] ) ) {
							$paragraphs[ $index + 1 ][] = call_user_func( $atts['callback'] );
						}
					}
				}
			}
		}

		return $paragraphs;

	}

	/**
	 * Clean up Content
	 * Helper function to Strips shortcodes and tags from content,
	 * leaving only paragraphs.
	 *
	 * @param string $content Some content.
	 *
	 * @return string Cleaned content.
	 */
	public function clean_up_content( $content ) {
		$content = wpautop( $content );
		$content = strip_shortcodes( $content );
		$content = strip_tags( $content, '<p>' );

		return $content;
	}

	/**
	 * Inject Related links
	 * Inserts the Related links into the content.
	 *
	 * @throws \Exception
	 *
	 * @return string The card markup.
	 */
	public function inject_related_card() {

		$path = apply_filters( 'pmc_automated_related_links_template', sprintf( '%s/templates/related-links.php', PMC_AUTOMATED_RELATED_LINKS_PLUGIN_PATH ) );

		if ( empty( $path ) || ! file_exists( $path ) ) {
			return;
		}

		$plugin_instance = Plugin::get_instance();

		$items = $plugin_instance->get_related_links();
		$items = apply_filters( 'pmc_automated_related_links_filter_related_articles', $items );
		$items = ( ! empty( $items ) && is_array( $items ) ) ? $items : [];

		if ( empty( $items ) ) {
			return;
		}

		$items['title'] = $plugin_instance->get_module_name();

		return \PMC::render_template(
			$path,
			[
				'items' => $items,
			]
		);
	}

	/**
	 * To enable event tracking for related links.
	 *
	 * @param array $events Event tracking selectors.
	 *
	 * @return array Event tracking selectors
	 */
	public function add_event_tracking( $events = [] ) {

		$events = ( ! empty( $events ) && is_array( $events ) ) ? $events : [];

		$events[] = [
			'selector' => '.c-related__list-item a',
			'category' => 'article-page',
			'label'    => 'related-article',
			'action'   => 'click',
			'url'      => true,
		];

		return $events;
	}

}
