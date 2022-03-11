<?php
/**
 * Class Article
 *
 * Handlers for the Article templates.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;
use \PMC;
use \PMC_Cache;
use Variety\Plugins\Variety_VIP\VIP;

/**
 * Class Article
 *
 * @since 2017.1.0
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Article {

	use Singleton;

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		// Note: The Image_Captions class is currently in this theme,
		// but will be moved to pmc-core-v2, so these filters are named in preparation for that.
		add_filter( 'pmc_core_image_captions_json', [ $this, 'filter_pmc_core_image_captions_json' ] );
		add_filter(
			'pmc_core_image_captions_shortcode_width',
			[ $this, 'filter_pmc_core_image_captions_shortcode_width' ]
		);
		add_filter(
			'pmc_core_image_captions_template_path',
			[ $this, 'filter_pmc_core_image_captions_template_path' ]
		);

	}

	/**
	 * The Image Captions class Filters
	 *
	 * By default uses the JSON from Larva, override this with local JSON
	 * and add check if it is the featured article.
	 *
	 * @return string - JSON for post-content-image module
	 */
	public function filter_pmc_core_image_captions_json( $json_file ) {

		if ( \Variety\Inc\Featured_Article::get_instance()->is_featured_article() ) {
			return 'modules/post-content-image.featured-article';
		}

		return $json_file;
	}

	/**
	 * Only add inline width to the shortcode, if it is not the featured article, which has
	 * images that span the full site width.
	 **/
	public function filter_pmc_core_image_captions_shortcode_width( $width ) {
		if ( true === \Variety\Inc\Featured_Article::get_instance()->is_featured_article() ) {
			return false;
		} else {
			return $width;
		}
	}

	/**
	 * Override the PHP controller for the post-content-image with one from this child theme.
	 */
	public function filter_pmc_core_image_captions_template_path() {
		return CHILD_THEME_PATH . '/template-parts/article/post-content-image.php';
	}

	/**
	 * Return if normal post is vip or not.
	 *
	 * @param $post_id
	 *
	 * @return bool
	 */
	public function is_article_vip( $post_id ) {

		if ( empty( $post_id ) ) {
			return false;
		}

		$vip = get_post_meta( $post_id, 'variety_post_vip', true );

		if ( 'Y' === $vip ) {
			return true;
		}

		return false;

	}

	/**
	 *
	 * @return string|void
	 */
	public function get_end_of_article_subscribe_url() {

		$url = '/subscribe-us/?utm_source=site&utm_medium=VAR_EndArticle&utm_campaign=DualShop';

		if ( \Variety\Plugins\Variety_VIP\Content::get_instance()->is_vip_page() ) {
			$url = '/vip-subscribe/?utm_source=site&utm_medium=VIP_EndArticle&utm_campaign=VIPShop&utm_content=unlocked';
		}

		return $url;

	}

}

//EOF
