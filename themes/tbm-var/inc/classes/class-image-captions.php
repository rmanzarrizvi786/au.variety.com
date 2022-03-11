<?php
/**
 * Image Captions Shortcode Parser
 *
 * This class handles image captions and credits for all images inserted into posts.
 * It overrides default WP functionality and force enables caption shortcode even
 * when caption for an image is not specified.
 *
 * It has been updated to support a Larva pattern from pmc-core-v2, and includes
 * filters so that this and its corresponding tests should be easily moved into the
 * pmc-core-v2.
 *
 * This ticket is for moving this class to pmc-core-v2:
 * https://jira.pmcdev.io/browse/PMCP-1882
 *
 * @author Amit Gupta (2016), Lara Schenck (2020)
 * @since  2016-05-25, 2020-02-24
 *
 * @package pmc-variety-2020
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

class Image_Captions {

	use Singleton;

	const DUMMY_CAPTION = '<span class="hidden">.</span>';

	protected function __construct() {

		$this->_setup_hooks();
	}

	protected function _setup_hooks() {

		add_filter( 'disable_captions', '__return_false', 99 ); // force enable image captions.

		add_filter(
			'image_add_caption_text',
			[ $this, 'force_add_caption' ],
			99,
			2
		); // force add captions.

		add_filter(
			'img_caption_shortcode',
			[ $this, 'parse_img_caption_shortcode' ],
			1,
			3
		); // custom parser for caption shortcode.

		// @codeCoverageIgnoreStart
		/**
		 * Launch Day Bug [L11]: Hiding photo captions temporarily until they can be cleaned up.
		 */
		add_filter(
			'wp_get_attachment_caption',
			'__return_empty_string'
		);
		// @codeCoverageIgnoreEnd

	}

	/**
	 * Add dummy caption if it doesn't exist for an image so as to always enable
	 * caption shortcode for images inserted into posts.
	 *
	 * @param string $caption The original caption text.
	 * @param int    $id      The attachment ID.
	 *
	 * @return string
	 */
	public function force_add_caption( $caption, $id ) {

		if ( empty( $caption ) ) {
			/*
			 * Add a dummy caption which can be stripped by shortcode parser
			 * or hidden by CSS because adding white space here works with
			 * Text mode editor in WP but having white space for caption is
			 * not able to fool TinyMCE and [caption] shortcode is not sent
			 * which prevents image credit from showing up as well.
			 *
			 * The only other way to make this work would be to fork TinyMCE
			 * JS & make changes in it so that it does not trim caption and allow
			 * empty caption on image with [caption] shortcode. This would not be
			 * an ideal solution, hence this hackish way of forcing [caption] shortcode.
			 *
			 * @ticket PMCVIP-1696
			 */
			$caption = self::DUMMY_CAPTION;
		}

		return $caption;

	}

	/**
	 * @codeCoverageIgnore
	 * Called on 'img_caption_shortcode' filter, this function displays images
	 * inserted into posts in custom markup with image caption & credits
	 *
	 * @param string $empty
	 * @param array  $attr    Attributes of the [caption] shortcode.
	 * @param string $content Content from [caption] shortcode.
	 *
	 * @return string HTML markup to display image with caption and credit
	 * @throws \Exception
	 */
	public function parse_img_caption_shortcode( $empty, $attr, $content ) {

		// Display WordPress default if rendering Apple News.
		if ( function_exists( 'is_apple_news_rendering_content' ) && is_apple_news_rendering_content() ) {
			return $empty;
		}

		if ( is_feed() ) {
			// we're not going to have custom image display HTML in feed, return
			// whatever WP cooked up.
			return $empty;
		}

		// parse attributes with defaults.
		$attr = shortcode_atts(
			[
				'id'      => '',
				'align'   => '',
				'width'   => '',
				'caption' => '',
			],
			$attr
		);

		if ( intval( $attr['width'] ) < 1 ) {
			// width not set, bail out.
			// let WordPress do its own thing.
			return $empty;
		}

		$attr['caption'] = ( ! isset( $attr['caption'] ) ) ? '' : trim( $attr['caption'] );

		/*
		 * If image caption is dummy caption then we'll remove it, because we won't be
		 * displaying it on front-end (it'll be hidden by CSS). It'll be a good idea to
		 * not add a hidden element on page.
		 */
		if ( strtolower( $attr['caption'] ) === strtolower( self::DUMMY_CAPTION ) ) {
			$attr['caption'] = '';
		}

		$image_id = 0;

		if ( ! empty( $attr['id'] ) ) {
			$image_id = intval( str_replace( 'attachment_', '', $attr['id'] ) );
		}

		if ( $image_id < 1 ) {
			// invalid Image ID, bail out.
			// let WordPress do its own thing.
			return $empty;
		}

		$json_file = apply_filters( 'pmc_core_image_captions_json', 'modules/post-content-image.prototype' );

		$json = \PMC\Core\Inc\Larva::get_instance()->get_json( $json_file, false );

		$post_content_image = $json;

		$image_credit = wp_strip_all_tags( get_post_meta( $image_id, '_image_credit', true ) );

		$dom = new \DOMDocument( null, 'UTF-8' );
		$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );
		$dom->encoding = 'utf-8';
		$imgs          = $dom->getElementsByTagName( 'img' );
		$links         = $dom->getElementsByTagName( 'a' );

		$image_link   = '';
		$image_src    = '';
		$image_height = '';
		$image_width  = '';
		$image_class  = '';

		if ( ! empty( $imgs[0] ) ) {
			$image_src    = $imgs[0]->getAttribute( 'src' ) ?? '';
			$image_height = $imgs[0]->getAttribute( 'height' ) ?? '';
			$image_width  = $imgs[0]->getAttribute( 'width' ) ?? '';
			$image_class  = $imgs[0]->getAttribute( 'class' ) ?? '';
		}

		if ( 0 < $links->length ) {
			$image_link = $links[0]->getAttribute( 'href' ) ?? '';
		}

		$figure_classes = sprintf( ' %1$s %2$s', $attr['align'], $image_class );

		$image_caption = $attr['caption'];


		/**
		 * Captions for embedded images in variety-2017 were not shown,
		 * and therefore were not maintained. Hiding captions for posts
		 * published before variety-2020 launch on 4/2/2020.
		 */
		if ( strtotime( get_the_date() ) < strtotime( 'April 2, 2020' ) ) {
			$image_caption = '';
		}

		$image_data = [
			'post_content_image' => $post_content_image,
			'image_credit'       => $image_credit,
			'figure_classes'     => $figure_classes,
			'image_src'          => $image_src,
			'image_caption'      => $image_caption,
			'image_id'           => $image_id,
			'shortcode_width'    => apply_filters( 'pmc_core_image_captions_shortcode_width', $attr['width'] ),
			'image_width'        => $image_width,
			'image_height'       => $image_height,
			'image_markup'       => do_shortcode( $content ),
			'image_link'         => $image_link,
		];

		$template_path = apply_filters( 'pmc_core_image_captions_template_path', CHILD_THEME_PATH . '/template-parts/article/post-content-image.php' );

		return \PMC::render_template(
			$template_path,
			$image_data
		);

	}

} //end of class

// EOF
