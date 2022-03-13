<?php
/**
 * Inject ads into stories when rendered in Full View.
 *
 * @package pmc-digital-daily
 */

namespace PMC\Digital_Daily;

use DOMDocument;
use DOMNodeList;
use DOMXPath;
use PMC;
use PMC_Cheezcap;
use PMC_DOM;
use PMC\Global_Functions\Traits\Singleton;

/**
 * Class Full_View_Ad_Injection.
 */
class Full_View_Ad_Injection {
	use Singleton;

	/**
	 * Name of tag containing content within DOMDocument object.
	 */
	protected const DOM_CONTAINING_TAG = 'body';

	/**
	 * Blocks to consider for ad injection, keyed by block name, with value that
	 * specifies CSS class of container to constrain injection to.
	 *
	 * For example:
	 *     `[ 'pmc/story-digital-daily' => 'block-story-the-content' ],`
	 *
	 * @var array
	 */
	protected array $_blocks;

	/**
	 * Count character length already processed.
	 *
	 * @var int
	 */
	protected int $_elapsed_characters = 0;

	/**
	 * Name of unit inserted after first two named slots.
	 */
	protected string $_repeated_unit_slug = 'inline-article-ad-x';

	/**
	 * List of ad locations and their relative insertion positions.
	 *
	 * @var array
	 */
	protected array $_locations_and_positions;

	/**
	 * Index for unit inserted after first two named slots.
	 *
	 * @var int
	 */
	protected int $_repeated_unit_index = 3;

	/**
	 * Full_View_Ad_Injection constructor.
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	/**
	 * Register hooks.
	 */
	protected function _setup_hooks(): void {
		add_action( 'init', [ $this, 'init_hooks' ] );
	}

	/**
	 * Register remaining hooks if conditions warrant doing so.
	 *
	 * Cheezcap cannot be referenced before the `init` hook.
	 */
	public function init_hooks(): void {
		if ( ! function_exists( 'pmc_adm_render_ads' ) ) {
			// Cannot unload function.
			return; // @codeCoverageIgnore
		}

		if (
			'enabled' !== PMC_Cheezcap::get_instance()->get_option(
				'pmc-ad-placeholders-enable'
			)
		) {
			return;
		}

		add_action( 'wp_loaded', [ $this, 'set_parameters' ] );
		// Hooked after blocks are registered for insertion.
		add_action( 'wp_loaded', [ $this, 'hook_block_rendering' ], 11 );
	}

	/**
	 * Set up information needed to insert ads into block content.
	 */
	public function set_parameters(): void {
		$this->_blocks = apply_filters(
			'pmc_digital_daily_full_view_ad_injection_allowed_blocks',
			[]
		);

		$this->_repeated_unit_slug = apply_filters(
			'pmc_digital_daily_full_view_ad_injection_repeated_unit_slug',
			$this->_repeated_unit_slug
		);

		$this->_repeated_unit_index = apply_filters(
			'pmc_digital_daily_full_view_ad_injection_repeated_unit_starting_index',
			$this->_repeated_unit_index
		);

		$this->_locations_and_positions = $this->_get_locations_and_positions();
	}

	/**
	 * Hook into rendering for support blocks.
	 */
	public function hook_block_rendering(): void {
		// Property is strictly typed.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		foreach ( array_keys( $this->_blocks ) as $block ) {
			add_filter(
				'render_block_' . $block,
				[ $this, 'parse_block_content_and_inject_ads' ],
				99,
				2
			);
		}
	}

	/**
	 * Parse block content for ad-unit injection.
	 *
	 * For now, we assume that we only care about the P tags within the given
	 * selector, and that multiple blocks can exist, so only classes are
	 * supported when querying for where to insert ads. If greater control is
	 * needed, we'll turn the filtered array's values into an array of selectors
	 * that can be used to drill down.
	 *
	 * @param string $block_content Rendered block content.
	 * @param array $block_attrs    Block attributes.
	 * @return string
	 */
	public function parse_block_content_and_inject_ads(
		string $block_content,
		array $block_attrs
	): string {
		if ( empty( $block_content ) ) {
			return $block_content;
		}

		if ( ! Full_View::is() ) {
			return $block_content;
		}

		$dom_document = PMC_DOM::load_dom_content( $block_content );

		if ( false === $dom_document ) {
			// Unable to create test data that triggers this condition.
			return $block_content; // @codeCoverageIgnore
		}

		$xpath = new DOMXPath( $dom_document );
		$query = static::DOM_CONTAINING_TAG
			// PHPCS is confused by leading slashes.
			// phpcs:ignore PmcWpVip.Usage.EnforceHttps
			. '//div[starts-with(@class,"'
			. $this->_blocks[ $block_attrs['blockName'] ]
			. '")]';

		$containers = $xpath->query( $query );

		if ( false === $containers || 0 === $containers->length ) {
			return $block_content;
		}

		$ads_inserted = $this->_inject_ads( $dom_document, $containers );

		if ( ! $ads_inserted ) {
			return $block_content;
		}

		$updated_content = PMC_DOM::domnode_get_innerhtml(
			$dom_document
				->getElementsByTagName( static::DOM_CONTAINING_TAG )
				->item( 0 )
		);

		if ( is_string( $updated_content ) && ! empty( $updated_content ) ) {
			return $updated_content;
		}

		// Unable to create test data that triggers this condition.
		return $block_content; // @codeCoverageIgnore
	}

	/**
	 * Inject ad units into parsed block content.
	 *
	 * @param DOMDocument $dom_document Block content loaded in DOMDocument.
	 * @param DOMNodeList $containers   Node list of block containers that can
	 *                                  receive ad units.
	 * @return bool
	 */
	protected function _inject_ads(
		DOMDocument $dom_document,
		DOMNodeList $containers
	): bool {
		$ads_inserted = false;

		foreach ( $containers as $container ) {
			foreach ( $container->getElementsByTagName( 'p' ) as $p_tag ) {
				// PSR-1 calls for camelCase.
				// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$this->_elapsed_characters += strlen( $p_tag->textContent );

				if (
					$this->_elapsed_characters
					< $this->_locations_and_positions[0][1]
				) {
					continue;
				}

				$unit = $dom_document->createDocumentFragment();
				$unit->appendXML( $this->_get_unit_markup() );

				if ( null === $p_tag->nextSibling ) {
					$p_tag->parentNode->appendChild( $unit );
				} else {
					$p_tag->parentNode->insertBefore(
						$unit,
						$p_tag->nextSibling
					);
				}
				// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

				$this->_elapsed_characters = 0;
				$ads_inserted              = true;
			}
		}

		return $ads_inserted;
	}

	/**
	 * Retrieve insertion-frequency settings from Cheezcap.
	 *
	 * TODO: refactor `\PMC\Ad_Placeholders\Injection()` to share this logic,
	 *       but only after determining if settings can be shared.
	 *
	 * @return array
	 */
	protected function _get_locations_and_positions(): array {
		$cheezcap = PMC_Cheezcap::get_instance();

		if ( PMC::is_mobile() ) {
			$ad_1 = absint(
				$cheezcap
					->get_option( 'pmc-ad-placeholders-first-pos-mobile' )
			);

			$ad_2 = absint(
				$cheezcap
					->get_option( 'pmc-ad-placeholders-second-pos-mobile' )
			);

			$ad_x = absint(
				$cheezcap
					->get_option( 'pmc-ad-placeholders-x-pos-mobile' )
			);
		} else {
			$ad_1 = absint(
				$cheezcap
					->get_option( 'pmc-ad-placeholders-first-pos' )
			);

			$ad_2 = absint(
				$cheezcap
					->get_option( 'pmc-ad-placeholders-second-pos' )
			);

			$ad_x = absint(
				$cheezcap
					->get_option( 'pmc-ad-placeholders-x-pos' )
			);
		}

		if ( ! $ad_x ) {
			$ad_x = $ad_2;
		}

		$ad_1 = max( 500, $ad_1 );
		$ad_2 = max( 2300, $ad_2 );
		$ad_x = max( 2300, $ad_x );

		return apply_filters(
			'pmc_digital_daily_full_view_ad_locations_and_positions',
			[
				[
					'inline-article-ad-1',
					$ad_1,
				],
				[
					'inline-article-ad-2',
					$ad_2,
				],
				[
					$this->_repeated_unit_slug,
					$ad_x,
				],
			]
		);
	}

	/**
	 * Retrieve ad-unit markup.
	 *
	 * @return string
	 */
	protected function _get_unit_markup(): string {
		if (
			$this->_repeated_unit_slug
				=== $this->_locations_and_positions[0][0]
		) {
			$slot = $this->_locations_and_positions[0][0];
		} else {
			[ $slot ] = array_shift( $this->_locations_and_positions );
		}

		$unit = pmc_adm_render_ads( $slot, '', false );

		if ( empty( $unit ) ) {
			return '';
		}

		if ( $this->_repeated_unit_slug === $slot ) {
			$unit = str_replace(
				'adm-' . $this->_repeated_unit_slug,
				'adm-'
					. $this->_repeated_unit_slug
					. '-'
					. $this->_repeated_unit_index,
				$unit
			);

			$this->_repeated_unit_index++;
		}

		return $unit;
	}
}
