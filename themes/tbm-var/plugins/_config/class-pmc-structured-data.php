<?php
/**
 * Configuration for pmc-structured-data plugin.
 */

namespace Variety\Plugins\Config;

use PMC\Global_Functions\Traits\Singleton;
use \Variety\Inc\Article;
use \Variety\Plugins\Variety_VIP\Content;

class PMC_Structured_Data {

	use Singleton;

	/**
	 *
	 *
	 * Class Constructor.
	 */
	protected function __construct() {

		add_filter( 'pmc_plugin_structured_data_article_post_type_whitelist', [ $this, 'post_type_whitelists' ] );
		add_filter( 'pmc_plugin_structured_data_article_data', [ $this, 'paywalled_article_data' ] );
	}

	/**
	 *
	 *
	 * @param $post_types
	 *
	 * @return mixed
	 */
	public function post_type_whitelists( $post_types ) {

		$paywalled_posts = [ 'variety_vip_post', 'variety_vip_video', 'variety_vip_report' ];

		return array_merge( $post_types, $paywalled_posts );
	}

	/**
	 *
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function paywalled_article_data( $payload ) {

		if ( ! is_singular() ) {
			return;
		}

		$article = get_queried_object();

		if ( ! in_array( $article->post_type, [ 'variety_vip_post', 'post' ], true ) ) {
			return $payload;
		}

		if ( 'post' === $article->post_type && ! Article::get_instance()->is_article_vip( $article->ID ) ) {
			return $payload;
		}

		if ( 'variety_vip_post' === $article->post_type && Content::get_instance()->is_article_free( $article->ID ) ) {
			return $payload;
		}

		$payload['isAccessibleForFree'] = 'False';

		$payload['hasPart'] = [
			'@type'               => 'WebPageElement',
			'isAccessibleForFree' => 'False',
			'cssSelector'         => 'a-content',
		];

		return $payload;
	}
}
