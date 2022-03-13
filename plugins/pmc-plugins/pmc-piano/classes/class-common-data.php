<?php


namespace PMC\Piano;

use PMC\Frontend_Components\Badges\Sponsored_Content;
use PMC\Global_Functions\Evergreen_Content;
use PMC\Global_Functions\Traits\Singleton;

class Common_Data {
	use Singleton;

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
			'pmc-is_free'         => ( ( \PMC\Post_Options\API::get_instance()->post( get_the_ID() )->has_option( Paid_Content::FREE_CONTENT_OPTION ) ) ? 'yes' : 'no' ),
			'pmc-always-paywall'  => ( ( \PMC\Post_Options\API::get_instance()->post( get_the_ID() )->has_option( Paid_Content::ALWAYS_PAYWALL_CONTENT_OPTION ) ) ? 'yes' : 'no' ),
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
	 * Gets default parameters that should be needed, regardless of brand.
	 * NOTE: All of the parameters below must be sent in PHP because they are used in AMP pages,
	 * which is over half of our traffic. All of the parameters below are passed through the vary cache function
	 *
	 * @return array
	 */
	public function get_default_custom_parameters(): array {
		$pmc_meta = \PMC_Page_Meta::get_page_meta();

		$parameters = [
			'cp_pmc-logged-in'        => $pmc_meta['logged-in'],
			'cp_pmc-country'          => $pmc_meta['country'], // may not always be up to date due to page caching
			'cp_pmc-device'           => $pmc_meta['env'], // may not always be up to date due to page caching
			'cp_pmc-is_eu'            => $pmc_meta['is_eu'],
			'cp_pmc-subscriber-type'  => $pmc_meta['subscriber-type'],
			'cp_pmc-concurrency_rest' => 'no',
			'cp_pmc-page_type'        => $pmc_meta['page-type'],
		];

		if ( is_singular() ) {
			$singular_meta                       = $this->get_singular_tags( $pmc_meta );
			$parameters['cp_pmc-post_type']      = $singular_meta['pmc-post_type'];
			$parameters['cp_pmc-category']       = $singular_meta['pmc-category'];
			$parameters['cp_pmc-over_1_year']    = $singular_meta['pmc-over_1_year'];
			$parameters['cp_pmc-page-subtype']   = $singular_meta['pmc-page-subtype'];
			$parameters['cp_pmc-tag']            = $singular_meta['pmc-tag'];
			$parameters['cp_pmc-vertical']       = $singular_meta['pmc-vertical'];
			$parameters['cp_pmc-is_free']        = $singular_meta['pmc-is_free'];
			$parameters['cp_pmc-always-paywall'] = $singular_meta['pmc-always-paywall'];
			$parameters['cp_pmc-evergreen']      = $singular_meta['pmc-evergreen'];
		}

		return $parameters;
	}
}
