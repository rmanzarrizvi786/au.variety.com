<?php

namespace Variety\Plugins\Config;

use \PMC\Global_Functions\Traits\Singleton;
use Variety\Plugins\Variety_VIP\Content;
use Variety\Inc\Article;

class PMC_Cxense {
	use Singleton;

	const PROD_SITE_ID     = '1138564545378095582';
	const STAGE_SITE_ID    = '1136312745665585336';
	const PROD_PAYWALL_ID  = 'fc0ebc7ea5ea0abc2372928e66fa9b084d397de6';
	const STAGE_PAYWALL_ID = '7f303ebfaed9eda638ef8468f5cdd40f692dec8c';

	/**
	 * Construct Method.
	 */
	public function __construct() {

		$this->_setup_hooks();
	}

	/**
	 * Setup hooks and filters
	 */
	public function _setup_hooks(): void {
		add_filter( 'pmc_cxense_site_id', [ $this, 'get_cxense_site_id' ] );
		add_filter( 'pmc_cxense_custom_parameters', [ $this, 'get_custom_parameters' ] );
		add_filter( 'pmc_cxense_meta_tags', [ $this, 'get_meta' ] );
		add_filter( 'pmc_cxense_paywall_module', [ $this, 'get_paywall_module_id' ] );
		add_filter( 'pmc_cxense_modules', [ $this, 'get_modules' ] );
	}

	/**
	 * Return paywall module id.
	 *
	 * @return string
	 */
	public function get_paywall_module_id(): string {
		if ( \PMC::is_production() ) {
			return self::PROD_PAYWALL_ID;
		}

		return self::STAGE_PAYWALL_ID;
	}

	/**
	 * @return string
	 */
	public function get_cxense_site_id(): string {
		if ( \PMC::is_production() ) {
			$site_id = self::PROD_SITE_ID;
		} else {
			$site_id = self::STAGE_SITE_ID;
		}

		return $site_id;
	}

	/**
	 * @param array $parameters
	 *
	 * @return array
	 */
	public function get_custom_parameters( array $parameters ): array {
		if ( false !== stripos( $parameters['pmc-subscriber-type'], 'vip' ) ) {
			$parameters['pmc-subscriber-type'] = 'variety-vip';
		} elseif ( false !== stripos( $parameters['pmc-subscriber-type'], 'vy.reg' ) ) {
			$parameters['pmc-subscriber-type'] = 'vy-reg';
		}

		if ( is_singular() ) {
			$pmc_meta                           = \PMC_Page_Meta::get_page_meta();
			$parameters['pmc-primary-category'] = $pmc_meta['primary-category'] ?? '';
			$parameters['pmc-primary-vertical'] = $pmc_meta['primary-vertical'] ?? '';
			return array_merge( $parameters, $this->get_singular_meta() );
		}

		return $parameters;
	}

	/**
	 * @param $meta
	 *
	 * @return array
	 */
	public function get_meta( array $meta ): array {
		if ( is_page_template( 'page-vip.php' ) ) {
			return [
				'pmc-lob'       => $meta['pmc-lob'] ?? '',
				'pmc-page_type' => $meta['pmc-page_type'] ?? '',
				'title'         => wp_get_document_title(),
				'pageclass'     => 'frontpage',
			];
		}

		if ( is_singular() ) {
			$pmc_meta                     = \PMC_Page_Meta::get_page_meta();
			$meta['pmc-primary-category'] = $pmc_meta['primary-category'] ?? '';
			$meta['pmc-primary-vertical'] = $pmc_meta['primary-vertical'] ?? '';

			return array_merge( $meta, $this->get_singular_meta() );
		}

		return $meta;
	}

	/**
	 * @return array
	 */
	public function get_singular_meta(): array {
		$article = get_post();

		if ( Content::get_instance()->is_vip_page() ) {
			return $this->get_vip_meta( $article );
		}

		return $this->get_non_vip_meta( $article );
	}

	/**
	 * @param $article
	 * @return array
	 */
	public function get_non_vip_meta( $article ): array {
		return [
			'pmc-is_free'          => '',
			'pmc-vip_category'     => '',
			'pmc-vip_tag'          => '',
			'pmc-vip_playlist'     => '',
			'pmc-is_vyvip_article' => ( Article::get_instance()->is_article_vip( $article->ID ) ) ? 'yes' : 'no',
		];
	}

	/**
	 * @param $article
	 * @return array
	 */
	public function get_vip_meta( $article ): array {
		return [
			'pmc-is_free'          => ( Content::get_instance()->is_article_free( $article->ID ) ) ? 'yes' : 'no',
			'pmc-vip_category'     => $this->check_vip_post_term( $article, Content::VIP_CATEGORY_TAXONOMY ),
			'pmc-vip_tag'          => $this->check_vip_post_term( $article, Content::VIP_TAG_TAXONOMY ),
			'pmc-vip_playlist'     => $this->check_vip_post_term( $article, Content::VIP_PLAYLIST_TAXONOMY ),
			'pmc-is_vyvip_article' => '',
		];
	}

	/**
	 * @param $article
	 * @param $taxonomy
	 *
	 * @return array|string
	 */
	public function check_vip_post_term( $article, $taxonomy ) {
		$terms = get_the_terms( $article, $taxonomy );

		if ( is_array( $terms ) ) {
			return $this->get_vip_terms( $terms );
		}

		return '';
	}

	/**
	 * @param $terms
	 *
	 * @return array
	 */
	public function get_vip_terms( array $terms ): array {
		if ( $terms && ! is_wp_error( $terms ) ) {
			return wp_list_pluck( $terms, 'name' );
		}

		return [];
	}

	public function get_modules( ?array $modules ): array {
		$modules[] = [
			'div_id'    => 'cx-module-300x250',
			'module_id' => '3709cfe00faa9f1734597f7b3943d9261cc440eb',
		];

		$modules[] = [
			'div_id'    => 'cx-module-300x250-mobile',
			'module_id' => '3709cfe00faa9f1734597f7b3943d9261cc440eb',
		];

		$modules[] = [
			'div_id'    => 'cx-module-970x100',
			'module_id' => '7d2c86fd877f93428513ea3b83165e194a29b1d7',
		];

		$modules[] = [
			'div_id'    => 'cx-module-article-end',
			'module_id' => '8d35c6038a49f3e99d051243999578d07e10962e',
		];

		$modules[] = [
			'div_id'    => 'cx-module-header-link-vy',
			'module_id' => 'dcf66348a712e9b561a376c3647f229e35a33dc2',
		];

		$modules[] = [
			'div_id'    => 'cx-module-header-link-vip',
			'module_id' => '6b2197719a022d5e0bec07e958c16cb5f7a8ef74',
		];

		$modules[] = [
			'div_id'    => 'cx-module-introducing',
			'module_id' => '1a9745d218bdc0e7e68752cc8cf1c2cd91851729',
		];

		$modules[] = [
			'div_id'    => 'cx-module-magazine',
			'module_id' => '012fddbb799a100c738bb6b0f92f9b2f8fedf6ea',
		];

		$modules[] = [
			'div_id'    => 'cx-module-mid-river',
			'module_id' => '4b071ba2f372a950cd9e8c9a456b21b0f8992676',
		];

		$modules[] = [
			'div_id'    => 'cx-module-sticky-header',
			'module_id' => '6996107c86461678b52d49c88a085fad7461fcb3',
		];

		$modules[] = [
			'div_id'    => 'cx-module-events-300x250',
			'module_id' => '3709cfe00faa9f1734597f7b3943d9261cc440eb',
		];

		$modules[] = [
			'div_id'    => 'cx-module-interstitial',
			'module_id' => '62a40175a65c80dae8fe7e4acf3acc104fd5ad23',
		];

		$modules[] = [
			'div_id'    => 'cx-module-top-stories-carousel',
			'module_id' => 'a7573613f482cfef41c174d36b45b07736f84a90',
		];

		$modules[] = [
			'div_id'    => 'cx-fly-out-vip',
			'module_id' => 'e1ce64a2c6b557b2088db4f2913360ce1f232f34',
		];

		$modules[] = [
			'div_id'    => 'cx-fly-out-variety',
			'module_id' => 'b6607462959fbe85fefbc364f1f4a8fe5d8eef8c',
		];

		$modules[] = [
			'div_id'    => 'cx-sticky-footer',
			'module_id' => '16d129d66820247692165d3e58ecb5321b3cebc8',
		];

		$modules[] = [
			'div_id'    => 'cx-subscribe-to-vip-tease',
			'module_id' => '9c3a9f50fb1622e75a3a9845daf4ace637eeec60',
		];

		$modules[] = [
			'div_id'    => 'cx-header-email',
			'module_id' => '1623db82fb8e59ec084748df5b552f2d515b5c77',
		];

		return $modules;
	}
}
