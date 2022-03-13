<?php

namespace PMC\Cxense;

use PMC\Frontend_Components\Badges\Sponsored_Content;
use PMC\Global_Functions\Evergreen_Content;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class to manage default data sent to Cxense, used to reduce amount of customization done in theme.
 * This class contains custom parameters and meta tags that all themes should send to Cxense.
 * These values are overridable in applicable filters as a fallback.
 * In future, may implement specific filters for values, like `subscriber-type` which currently is set in theme.
 * @package pmc-cxense
 */
class Cxense_Common_Data {
	use Singleton;

	const RSS_TOKEN_KEY    = '5fc34527ee2aebb6dd107909d3af377c';
	const RSS_TOKEN_SECRET = '8d4a1fc91ddb5da332618b086c08a4a9';

	/**
	 * Gets default parameters that should be needed, regardless of brand.
	 * NOTE: All of the parameters below must be sent in PHP because they are used in AMP pages,
	 * which is over half of our traffic. All of the parameters below are passed through the vary cache function
	 *
	 * @return array
	 */
	public function get_default_custom_parameters(): array {
		$pmc_meta = \PMC_Page_Meta::get_page_meta();

		$parameters = [
			'pmc-logged-in'        => $pmc_meta['logged-in'],
			'pmc-country'          => $pmc_meta['country'], // may not always be up to date due to page caching
			'pmc-device'           => $pmc_meta['env'], // may not always be up to date due to page caching
			'pmc-is_eu'            => $pmc_meta['is_eu'],
			'pmc-subscriber-type'  => $pmc_meta['subscriber-type'],
			'pmc-concurrency_rest' => 'no',
			'pmc-paywall_bypass'   => $this->check_paywall_bypass(),
			'pmc-page_type'        => $pmc_meta['page-type'],
		];

		if ( is_singular() ) {
			$singular_meta                  = $this->get_singular_tags( $pmc_meta );
			$parameters['pmc-post_type']    = $singular_meta['pmc-post_type'];
			$parameters['pmc-category']     = $singular_meta['pmc-category'];
			$parameters['pmc-over_1_year']  = $singular_meta['pmc-over_1_year'];
			$parameters['pmc-page-subtype'] = $singular_meta['pmc-page-subtype'];
			$parameters['pmc-tag']          = $singular_meta['pmc-tag'];
			$parameters['pmc-vertical']     = $singular_meta['pmc-vertical'];
			$parameters['pmc-is_free']      = $singular_meta['pmc-is_free'];
			$parameters['pmc-evergreen']    = $singular_meta['pmc-evergreen'];
		}

		return $parameters;
	}

	/**
	 * Gets meta tags that are always needed, regardless of brand.
	 * Since singular and non-singular pages can have different kinds of values,
	 * there are separate functions to accommodate those differences.
	 *
	 * @return array
	 */
	public function get_default_meta_tags(): array {
		$pmc_meta = \PMC_Page_Meta::get_page_meta();
		$tags     = [
			'pmc-lob'       => $pmc_meta['lob'],
			'pmc-page_type' => $pmc_meta['page-type'],
		];

		if ( is_singular() ) {
			return array_merge( $tags, $this->get_singular_tags( $pmc_meta ) );
		}

		return array_merge( $tags, $this->get_non_singular_tags() );
	}

	/**
	 * Gets meta tags needed for singular pages.
	 *
	 * @param array $pmc_meta
	 *
	 * @return array
	 */
	public function get_singular_tags( array $pmc_meta ): array {
		$article       = get_post();
		$ga_dimensions = \PMC_Google_Universal_Analytics::get_instance()->get_mapped_dimensions();

		return [
			'articleid'           => $article->ID,
			'author'              => $pmc_meta['author'],
			'pmc-category'        => $pmc_meta['category'],
			'pmc-over_1_year'     => ( get_the_time( 'U', $article ) < time() - ( YEAR_IN_SECONDS ) ) ? 'yes' : 'no',
			'pmc-page-subtype'    => $ga_dimensions['dimension2'] ?? '',
			'pmc-post_type'       => $article->post_type,
			'recs:publishtime'    => get_the_date( 'Y-m-d\TH:i:s', $article ),
			'pmc-tag'             => $pmc_meta['tag'],
			'title'               => wp_kses( apply_filters( 'the_title', $article->post_title, $article->ID ), [] ),
			'pageclass'           => 'article',
			'pmc-vertical'        => $pmc_meta['vertical'],
			'pmc-is_free'         => ( ( \PMC\Post_Options\API::get_instance()->post( get_the_ID() )->has_option( 'subs-free-content' ) ) ? 'yes' : 'no' ),
			'pmc-branded-content' => ( ( \PMC\Post_Options\API::get_instance()->post( get_the_ID() )->has_option( Sponsored_Content::SLUG ) ) ? 'yes' : 'no' ),
			'pmc-evergreen'       => ( ( \PMC\Post_Options\API::get_instance()->post( get_the_ID() )->has_option( Evergreen_Content::SLUG ) ) ? 'yes' : 'no' ),
		];
	}

	/**
	 * Gets meta tags needed for non-singular pages.
	 *
	 * @return array
	 */
	public function get_non_singular_tags(): array {
		return [
			'title'     => wp_get_document_title(),
			'pageclass' => 'frontpage',
		];
	}

	/**
	 * Checks any special conditions that may be needed to bypass the paywall.
	 * Currently we are not paywalling:
	 * 1) Bots that need access to the full content of an article for crawling
	 * 2) Users viewing articles from feeds with a valid token
	 *
	 * @return bool
	 */
	public function check_paywall_bypass(): bool {
		if ( Bot::get_instance()->check_if_allowed_bot() ) {
			return true;
		}

		if ( pmc_subscription_user_has_valid_token() ) {
			return true;
		}

		return false;
	}
}
