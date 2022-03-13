<?php

namespace PMC\Facebook_Instant_Articles;

use PMC\Global_Functions\Traits\Singleton;
use PMC;
use PMC_Ads;
use Google_Publisher_Provider;
use PMC_TimeMachine;

class Modifier {

	use Singleton;

	/**
	 * class var to get the dfp-ad iframe url
	 *
	 * @since 1.0
	 */
	protected $_dfp_ad_url = 'https://pubads.g.doubleclick.net/gampad/adx';

	/**
	 * class var to get the dfp-ad iframe source
	 *
	 * @since 1.0
	 */
	protected $_dfp_ad_source;

	/**
	 * class var to get the dfp-ad iframe width
	 *
	 * @since 1.0
	 */
	protected $_dfp_ad_width = 300;

	/**
	 * class var to get the dfp-ad iframe height
	 *
	 * @since 1.0
	 */
	protected $_dfp_ad_height = 250;

	/**
	 * class var to hold the recirculation ad meta property
	 *
	 * @since 1.0
	 */
	protected $_meta_property = 'fb:op-recirculation-ads';

	/**
	 * class var to hold the recirculation ad meta property Placement Id
	 *
	 * @since 1.0
	 */
	protected $_placement_id;

	/**
	 * Setup hooks and filters
	 *
	 * @since 1.0
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {

		add_action( 'instant_articles_after_transform_post', array( $this, 'add_recirculation_ads_meta' ) );
		add_action( 'instant_articles_after_transform_post', array( $this, 'add_dfp_ads' ) );

		// Need to remove read more tag before we inject additional content to IA.
		// So adding filter at 5th priority.
		add_filter( 'instant_articles_content', array( $this, 'process_content' ), 5 );

	}

	/**
	 * Action hook that ads meta tag to the FB IA markup if a placement ID is provided via filter
	 *
	 * @since 1.0
	 */
	public function add_recirculation_ads_meta( $ia_post ) {

		if ( ! $this->_is_valid_instant_article( $ia_post ) ) {
			return;
		}

		$this->_placement_id = apply_filters( 'pmc_fbia_recirculation_ad_placement_id', false );

		if ( empty( $this->_placement_id ) ) {
			return;
		}

		// Get the IA article.
		$instant_article = $ia_post->instant_article;

		$placement_id = 'placement_id=' . sanitize_key( trim( $this->_placement_id ) );

		// Create our FBIA meta property in the head
		$instant_article = $instant_article->addMetaProperty( $this->_meta_property, $placement_id );

	}

	/**
	 * Add DFP ad code markup to the Header of an InstantArticle.
	 *
	 * @since 1.0
	 */
	public function add_dfp_ads( $ia_post ) {

		if ( ! $this->_is_valid_instant_article( $ia_post ) ) {
			return;
		}

		// If we have valid PMC Ad that is active and is in current date range
		// If yes this sets all the class variables to be passed to Instant articles object
		$found_ad = $this->_fetch_ad();

		if ( false === $found_ad ) {
			return;
		}

		// Get the IA article.
		$instant_article = $ia_post->instant_article;

		$header = $instant_article->getHeader();

		$fbia_ad = \Facebook\InstantArticles\Elements\Ad::create()
														->enableDefaultForReuse()
														->withWidth( $this->_dfp_ad_width )
														->withHeight( $this->_dfp_ad_height )
														->withSource( $this->_dfp_ad_source );

		$header->addAd( $fbia_ad );

		$instant_article->enableAutomaticAdPlacement();

	}

	/**
	 * Check if we have all the Instant Articles classes loaded
	 * and we have a valid Instant Articles object
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	protected function _is_valid_instant_article( $ia_post ) {

		// Check if Instant Articles classes exist.
		if ( ! class_exists( 'Instant_Articles_Post' )
			|| ! class_exists( '\Facebook\InstantArticles\Elements\InstantArticle' )
			|| ! class_exists( '\Facebook\InstantArticles\Elements\Ad' )
		) {
			return false;
		}

		if ( ! ( $ia_post instanceof \Instant_Articles_Post ) ) {
			return false;
		}

		if ( ! property_exists( $ia_post, 'instant_article' ) || ! ( $ia_post->instant_article instanceof \Facebook\InstantArticles\Elements\InstantArticle ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Fetch the Active Ad and build the iframe source from the Ad
	 * Also get width and height of the Ad and set class variables
	 * Check if we have valid iframe source code and Ad HTML width and height for the Instant Articles object
	 *
	 * @since 1.0
	 *
	 * @return bool
	 *
	 */
	protected function _fetch_ad() {

		if ( ! class_exists( PMC_Ads::class ) ) {
			// We can't cover this code.  Make this test compatible with other plugin unit test where pmc adm might not be loaded
			return false; // @codeCoverageIgnore
		}

		$gpt_provider = PMC_Ads::get_instance()->get_provider( 'google-publisher' );

		// check if gpt registered
		if ( empty( $gpt_provider ) || ! ( $gpt_provider instanceof Google_Publisher_Provider ) ) {
			// gpt is not been registered, bail out
			return false;
		}

		$key = $gpt_provider->get_key();

		if ( empty( $key ) ) {
			return false;
		}

		$ad_content = $this->_get_active_ad_content();

		if ( empty( $ad_content ) || ! is_array( $ad_content ) ) {
			return false;
		}

		$ad_content['key'] = $key;

		$sitename = defined( 'PMC_SITE_NAME' ) ? PMC_SITE_NAME : \PMC::get_current_site_name();
		$sitename = empty( $ad_content['sitename'] ) ? $sitename : $ad_content['sitename'];
		$zone     = empty( $ad_content['zone'] ) ? '' : $ad_content['zone'];

		if ( ! empty( $ad_content['width'] ) ) {
			$this->_dfp_ad_width = $ad_content['width'];
		}

		if ( ! empty( $ad_content['height'] ) ) {
			$this->_dfp_ad_height = $ad_content['height'];
		}

		$slot = apply_filters( 'pmc_adm_google_publisher_slot', sprintf( '/%s/%s/%s', $key, $sitename, $zone ), $ad_content );

		$this->_dfp_ad_source = $this->_get_iframe_source( $slot );

		return true;
	}

	/**
	 * Get the Ad that is active and the one that is set active for the current date range.
	 * If no date range for that ad, just get the active ad or if no active ad found return false
	 *
	 * @since 1.0
	 *
	 * @return array $post_content of the active ad
	 */
	protected function _get_active_ad_content() {

		// get all ads for location 'facebook-instant-articles'
		$ads = PMC_Ads::get_instance()->get_ads( false, 'facebook-instant-articles' );

		if ( empty( $ads ) || ! is_array( $ads ) ) {
			return false;
		}

		foreach ( $ads as $ad ) {
			$ad_post_content = $ad->post_content;
			if ( 'active' === strtolower( $ad_post_content['status'] ) ) {
				if ( empty( $ad_post_content['start'] ) && empty( $ad_post_content['end'] ) ) {
					return $ad_post_content;
				} else {

					$today = strtotime( PMC_TimeMachine::create( PMC_Ads::get_instance()->timezone )->now() );
					$start = strtotime( $ad_post_content['start'] );
					$end   = strtotime( $ad_post_content['end'] );
					if ( $today > $start && $today < $end ) {
						return $ad_post_content;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Build the iframe url for the dfp Ad by adding all the query strings
	 *
	 * @since 1.0
	 *
	 * @param $slot string
	 *
	 * @return string iframe url for the Ad
	 */
	protected function _get_iframe_source( $slot ) {

		if ( empty( $slot ) ) {
			return false;
		}

		$args = array(
			'iu'   => $slot,
			'sz'   => $this->_dfp_ad_width . 'x' . $this->_dfp_ad_height,
			'c'    => strtotime( 'now' ),
			'tile' => 1,
		);

		$args    = array_map( 'rawurlencode', (array) $args );
		$dfp_url = add_query_arg( $args, $this->_dfp_ad_url );

		return $dfp_url;
	}

	/**
	 * Modify the instant article content
	 *
	 * @param string $content Post content.
	 *
	 * @return string
	 */
	public function process_content( $content ) {

		if ( empty( $content ) ) {
			return $content;
		}

		// @since 2017-11-15 CDWE-835 - Remove <!--more--> tag.
		$content = preg_replace( '/<p><!--more(.*?)?--><\/p>/', '', $content );

		// @ticket REV-8: add missing feature video
		if ( class_exists( \PMC_Featured_Video_Override::class ) ) {
			$video_html = \PMC_Featured_Video_Override::get_video_html( get_the_ID() );
			if ( ! empty( $video_html ) ) {
				$content = sprintf( "<p>%s</p>\n%s", $video_html, $content );
			}
		}

		return $content;

	}

}
