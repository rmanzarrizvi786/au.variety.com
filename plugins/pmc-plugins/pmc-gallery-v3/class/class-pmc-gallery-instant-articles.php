<?php
/**
 * Inject linked gallery into instant articles
 *
 * @since 2017-09-07 CDWE-565
 *
 * @author Chandra Patel <chandrakumar.patel@rtcamp.com>
 *
 * @package pmc-gallery
 */

namespace PMC\Gallery;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC_Cheezcap;
use \CheezCapDropdownOption;


class PMC_Gallery_Instant_Articles {

	use Singleton;

	const OPTION_NAME = 'pmc_gallery_fbia_toggle';

	/**
	 * Add hooks and filters function
	 */
	protected function __construct() {

		add_filter( 'pmc_gallery_cheezcap_options', array( $this, 'add_cheezcap_options' ) );

		add_filter( 'instant_articles_transformer_rules_loaded', array( $this, 'load_custom_transformer_rules' ) );

		add_action( 'init', array( $this, 'add_instant_articles_content_filter' ) );

	}

	/**
	 * Add instant articles content filter
	 */
	public function add_instant_articles_content_filter() {

		// Added plugin version check because 'post_id' param added in 'instant_articles_content' filter in 3.3.5 version.
		if ( defined( 'IA_PLUGIN_VERSION' ) && version_compare( IA_PLUGIN_VERSION, '3.3.5', '>=' ) ) {

			add_filter( 'instant_articles_content', array( $this, 'inject_associated_gallery_link' ), 10, 2 );

		}

	}

	/**
	 * Add cheezcap options for Gallery
	 *
	 * @param array $cheezcap_options An array of cheezcap options.
	 *
	 * @return array
	 */
	public function add_cheezcap_options( $cheezcap_options = array() ) {

		if ( empty( $cheezcap_options ) || ! is_array( $cheezcap_options ) ) {
			$cheezcap_options = array();
		}

		$cheezcap_options[] = new CheezCapDropdownOption(
			__( 'Inject associated gallery link in instant articles', 'pmc-plugins' ),
			__( 'If enabled, associated gallery link will inject in instant articles', 'pmc-plugins' ),
			self::OPTION_NAME,
			array( 'disabled', 'enabled' ),
			0, // Default is disabled.
			array( 'Disabled', 'Enabled' )
		);

		return $cheezcap_options;

	}

	/**
	 * Load related articles rule
	 *
	 * @param object $transformer \Facebook\InstantArticles\Transformer\Transformer class object.
	 *
	 * @return mixed
	 */
	public function load_custom_transformer_rules( $transformer ) {

		if ( ! is_object( $transformer ) ) {
			return $transformer;
		}

		$related_articles_rule = wp_json_encode(
			array(
				'rules' => array(
					array(
						'class'      => 'RelatedArticlesRule',
						'selector'   => 'ul.related-articles',
						'properties' => array(
							'related.title' => array(
								'type'      => 'string',
								'selector'  => 'ul.related-articles',
								'attribute' => 'title',
							),
						),
					),
					array(
						'class'      => 'RelatedItemRule',
						'selector'   => 'ul.related-articles li',
						'properties' => array(
							'related.url' => array(
								'type'      => 'string',
								'selector'  => 'a',
								'attribute' => 'href',
							),
						),
					),
				),
			)
		);

		$transformer->loadRules( $related_articles_rule );

		return $transformer;

	}

	/**
	 * Inject associated Gallery link in instant article
	 *
	 * @param string $content  The post content.
	 * @param int    $post_id  The post ID.
	 *
	 * @return string
	 */
	public function inject_associated_gallery_link( $content, $post_id ) {

		if ( 'enabled' !== PMC_Cheezcap::get_instance()->get_option( self::OPTION_NAME ) ) {
			return $content;
		}

		if ( empty( $post_id ) ) {
			return $content;
		}

		$linked_gallery = get_post_meta( $post_id, 'pmc-gallery-linked-gallery', true );

		if ( empty( $linked_gallery ) ) {
			return $content;
		}

		$linked_gallery = json_decode( $linked_gallery, true );

		if ( empty( $linked_gallery['id'] ) || ! is_numeric( $linked_gallery['id'] ) ) {
			return $content;
		}

		$gallery_post = get_post( intval( $linked_gallery['id'] ) );

		if ( empty( $gallery_post ) ) {
			return $content;
		}

		// Inject gallery as related article.
		$launch_gallery_html = sprintf(
			'<ul class="related-articles" title="%1$s"><li><a href="%2$s">%3$s</a></li></ul>',
			esc_html__( 'Launch Gallery', 'pmc-plugins' ),
			esc_url( get_permalink( intval( $gallery_post->ID ) ) ),
			wp_kses_post( $gallery_post->post_title )
		);

		$content = \PMC_DOM::inject_paragraph_content(
			$content,
			[
				'autop'                                  => false,
				'should_append_after_tag'                => true,
				'should_apply_pmc_dom_insertions_filter' => false,
				'paragraphs'                             => [
					1 => [ // Inject gallery as related article between 1st and 2nd paragraph.
						$launch_gallery_html,
					],
				],
			]
		);

		return $content;

	}

}

PMC_Gallery_Instant_Articles::get_instance();
