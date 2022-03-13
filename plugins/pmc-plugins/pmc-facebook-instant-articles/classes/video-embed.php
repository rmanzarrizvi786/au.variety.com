<?php
namespace PMC\Facebook_Instant_Articles;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Fix video embed for Facebook Instant Article
 *
 * Class Video_Embed
 * @package PMC\Facebook_Instant_Articles
 */
class Video_Embed {

	use Singleton;

	function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		add_action( 'init', [ $this, 'action_init' ] );
	}

	public function action_init() {
		add_action( 'instant_articles_before_transform_post', [ $this, 'action_instant_articles_before_transform_post' ] );
		add_action( 'instant_articles_after_transform_post', [ $this, 'action_instant_articles_after_transform_post' ] );
	}

	/**
	 * Action to trigger before article transform into instant article object
	 */
	public function action_instant_articles_before_transform_post() {
		add_filter( 'wp_lazy_loading_enabled', '__return_false', 999 );
		add_filter( 'video_embed_html', [ $this, 'fix_video_embed_html' ] );
		add_filter( 'instant_articles_social_embed_vimeo', [ $this, 'fix_video_embed_html' ] );
		add_filter( 'instant_articles_social_embed_youtube', [ $this, 'fix_video_embed_html' ] );
		add_filter( 'embed_oembed_html', [ $this, 'fix_video_embed_html' ], PHP_INT_MAX );

		add_filter( 'embed_defaults', [ $this, 'filter_embed_defaults' ] );
		add_filter( 'youtube_width', [ $this, 'filter_youtube_width' ] );
		add_filter( 'youtube_height', [ $this, 'filter_youtube_height' ] );
		Plugin::get_instance()->add_rules(
			[
				'.op-interactive' => 'PassThroughRule',
			]
		);
	}

	/**
	 * Action to trigger after article had been transformed into instance article object
	 */
	public function action_instant_articles_after_transform_post() {
		remove_filter( 'wp_lazy_loading_enabled', '__return_false', 999 );
		remove_filter( 'video_embed_html', [ $this, 'fix_video_embed_html' ] );
		remove_filter( 'instant_articles_social_embed_vimeo', [ $this, 'fix_video_embed_html' ] );
		remove_filter( 'instant_articles_social_embed_youtube', [ $this, 'fix_video_embed_html' ] );
		remove_filter( 'embed_oembed_html', [ $this, 'fix_video_embed_html' ], PHP_INT_MAX );
		remove_filter( 'embed_defaults', [ $this, 'filter_embed_defaults' ] );
		remove_filter( 'youtube_width', [ $this, 'filter_youtube_width' ] );
		remove_filter( 'youtube_height', [ $this, 'filter_youtube_height' ] );
	}

	public function filter_embed_defaults( $attr ) {
		return wp_parse_args(
			[
				'width'  => $this->filter_youtube_width(),
				'height' => $this->filter_youtube_height(),
			],
			$attr
		);
	}

	public function filter_youtube_width() {
		return 640;
	}

	public function filter_youtube_height() {
		return 480;
	}

	/**
	 * Filter to extract the iframe video embed and transform into fbia recognized tag
	 * @param $html
	 * @return mixed|string
	 */
	function fix_video_embed_html( $html ) {
		if ( preg_match( '@<figure class="op-interactive">.*?</figure>@', $html, $matches ) ) {
			$html = $matches[0];
		} elseif ( preg_match( '@<iframe.*?</iframe>@', $html, $matches ) ) {
			$html = sprintf( '<figure class="op-interactive">%s</figure>', $matches[0] );
		}
		return $html;
	}

}
