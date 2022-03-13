<?php

namespace PMC\Facebook_Instant_Articles;

use PMC\Global_Functions\Traits\Singleton;
use PMC_Ads;

class Boomerang {
	use Singleton;

	const DEFAULT_AD_WIDTH  = 300;
	const DEFAULT_AD_HEIGHT = 250;

	protected function __construct() {
		add_action( 'instant_articles_after_transform_post', [ $this, 'monetize' ] );
	}

	public function monetize( $ia_post ) : bool {

		if ( ! class_exists( \Instant_Articles_Post::class )
			|| ! ( $ia_post instanceof \Instant_Articles_Post )
			|| ! class_exists( PMC_Ads::class )
			) {
			return false;
		}

		$ads_to_render = PMC_Ads::get_instance()->get_ads_to_render( 'facebook-instant-articles', '', 'boomerang' );

		if ( empty( $ads_to_render ) || ! is_array( $ads_to_render ) ) {
			return false;
		}

		$height = 0;
		$width  = 0;
		$html   = '';

		foreach ( $ads_to_render as $ad ) {
			$provider = PMC_Ads::get_instance()->get_provider( 'boomerang' );
			if ( ! ( $provider instanceof \Boomerang_Provider ) ) {
				// We will never reach this code because PMC_Ads::should_render_ad would prevent invalid ad to return if provider is not valid
				continue;  // @codeCoverageIgnore
			}

			$html .= $provider->render_ad( $ad, false );
			if ( ! empty( $ad['height'] ) && intval( $ad['height'] ) > $height ) {
				$height = intval( $ad['height'] );
			}
			if ( ! empty( $ad['width'] ) && intval( $ad['width'] ) > $width ) {
				$width = intval( $ad['width'] );
			}
		}

		if ( empty( $html ) ) {
			// We will never reach this code because there is always ads generate some html code
			return false; // @codeCoverageIgnore
		}

		if ( empty( $height ) ) {
			$height = self::DEFAULT_AD_HEIGHT;
		}

		if ( empty( $width ) ) {
			$width = self::DEFAULT_AD_WIDTH;
		}

		$html = \PMC::render_template( PMC_FACEBOOK_INSTANT_ARTICLES_ROOT . '/templates/boomerang-script.php', [], false ) . $html;

		$fbia_ad = \Facebook\InstantArticles\Elements\Ad::create()
			->enableDefaultForReuse()
			->withWidth( $width )
			->withHeight( $height )
			->withHTML( $html );

		$ia_post->instant_article->getHeader()->addAd( $fbia_ad );
		$ia_post->instant_article->enableAutomaticAdPlacement();

		return true;
	}

}
