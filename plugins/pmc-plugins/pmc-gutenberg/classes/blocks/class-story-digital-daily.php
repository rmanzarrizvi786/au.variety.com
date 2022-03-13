<?php
/**
 * Story block for Digital Daily feature.
 *
 * TODO: port Patterns to `pmc-larva` from `wwd-2021`, introduce module controllers.
 * TODO: deprecate legacy style names in favor of names based on Larva variants, provide mapping to support existing posts.
 *
 * @package pmc-gutenberg
 */

namespace PMC\Gutenberg\Blocks;

use PMC;
use PMC_Featured_Video_Override;
use PMC\Digital_Daily;
use PMC\Gutenberg\Story_Block_Engine;
use PMC\Gutenberg\Interfaces\Block_Base\With_Dependencies;
use PMC\Gutenberg\Traits\Block_Base\Digital_Daily_Common_Elements;
use PMC\Larva;
use WWD\Inc\Larva\Controllers\Modules\Featured_Gallery;

/**
 * @codeCoverageIgnore Class will be refactored into Larva controllers after
 *                     Patterns are moved to `pmc-larva`.
 */
class Story_Digital_Daily extends Story_Block_Engine implements With_Dependencies {
	/**
	 * Reusable pattern elements shared across Digital Daily blocks.
	 */
	// Bug in WPCS when `use` is used: https://github.com/WordPress/WordPress-Coding-Standards/issues/1071.
	// phpcs:ignore WordPress.WhiteSpace.ControlStructureSpacing.NoSpaceAfterOpenParenthesis
	use Digital_Daily_Common_Elements {
		Digital_Daily_Common_Elements::_get_larva_module_with_variant
			as trait__get_larva_module_with_variant;
	}

	/**
	 * Regex pattern to extract aspect ratio from Larva cropping algorithm.
	 */
	protected const CROP_CLASS_REGEX = '#lrv-a-crop-(\d+x\d+)#';

	/**
	 * Configure block.
	 */
	public function __construct() {
		$this->larva_module = 'block-story';

		$this->story_block_config = [
			'post' => [
				'postType'     => 'post',
				'taxonomySlug' => 'category',
				'viewMoreText' => '',
			],
		];


		$this->create_story_block( 'story-digital-daily' );

		$this->_set_styles( 13 );
	}

	/**
	 * Return block's Larva module and variant.
	 *
	 * @param array $attrs Block attributes.
	 * @return string
	 */
	protected function _get_larva_module_with_variant( array $attrs ): string {
		$module = $this->trait__get_larva_module_with_variant( $attrs );

		if ( 'block-story-full' !== $this->larva_module ) {
			return $module;
		}

		if (
			PMC_Featured_Video_Override::get_instance()
				->has_featured_video(
					$attrs['postID']
				)
		) {
			$module = str_replace(
				'.prototype',
				'.featured-video',
				$module
			);
		} elseif (
			PMC::has_linked_gallery(
				$attrs['postID']
			)
		) {
			$module = str_replace(
				'.prototype',
				'.featured-gallery',
				$module
			);
		}

		return $module;
	}

	/**
	 * Populate pattern from post data.
	 *
	 * @param string $block_content
	 * @param array  $block
	 * @return array|null
	 */
	public function larva_data( string $block_content, array $block ): ?array {
		$data = parent::larva_data( $block_content, $block );

		if ( null === $data ) {
			return null;
		}

		// Remove extraneous data from parent method.
		unset( $data['c_link'], $data['c_span'] );

		$this->_add_common_data( $data );

		switch ( $this->larva_module ) {
			case 'block-story':
				switch ( $this->larva_module_variant ) {
					// Variants with all elements.
					case 'prototype':
					case '2':
					case '3':
					case '4':
					case '5':
					case '6':
					case '9':
					case '10':
					case '12':
					case '13':
						$this->_conditionally_add_c_author( $data );

						$this->_add_c_button( $data['c_button'] );
						$this->_add_c_paragraph( $data['c_paragraph'], 1 );

						if ( $this->_attributes['hasDisplayedPrimaryTerm'] ) {
							$this->_add_c_tag_span( $data['c_tag_span'] );
						} else {
							$data['c_tag_span'] = false;
						}

						$this->_add_o_figure( $data['o_figure'] );
						break;

					// Variants without author displayed.
					case '7':
						if ( $this->_attributes['hasDisplayedPrimaryTerm'] ) {
							$this->_add_c_tag_span( $data['c_tag_span'] );
						} else {
							$data['c_tag_span'] = false;
						}

						$this->_add_c_button( $data['c_button'] );
						$this->_add_c_paragraph( $data['c_paragraph'], 1 );
						$this->_add_o_figure( $data['o_figure'] );
						break;

					// Variants without featured image.
					case '8':
						$this->_conditionally_add_c_author( $data );

						$this->_add_c_button( $data['c_button'] );
						$this->_add_c_paragraph( $data['c_paragraph'], 1 );

						if ( $this->_attributes['hasDisplayedPrimaryTerm'] ) {
							$this->_add_c_tag_span( $data['c_tag_span'] );
						} else {
							$data['c_tag_span'] = false;
						}
						break;

					// Variants without breadcrumb (term link).
					case '11':
						$this->_conditionally_add_c_author( $data );

						$this->_add_c_button( $data['c_button'] );
						$this->_add_c_paragraph( $data['c_paragraph'], 1 );
						$this->_add_o_figure( $data['o_figure'] );
						break;

					default:
						$data = null;
						break;
				}
				break;

			case 'block-story-full':
				$this->_add_c_article_tags( $data['c_article_tags'] );
				$this->_add_c_author( $data['c_author'] );
				$this->_add_c_tag_span( $data['c_tag_span'] );
				$this->_add_c_timestamp( $data['c_timestamp'] );

				// Empty condition exists to maintain override precedence.
				// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
				if (
					class_exists( 'PMC_Featured_Video_Override', false )
					&& PMC_Featured_Video_Override::get_instance()
						->has_featured_video(
							$this->_attributes['postID']
						)
				) {
					// Output via `pmc_larva_do_featured_video_override` hook.
				} elseif (
					PMC::has_linked_gallery(
						$this->_attributes['postID']
					)
				) {
					$is_beauty_inc = has_term(
						'beauty-industry-news',
						'vertical',
						$this->_attributes['postID']
					);

					$controller = Featured_Gallery::get_instance()->init(
						[
							'data'    => [
								'post_id' => $this->_attributes['postID'],
							],
							'variant' => $is_beauty_inc
								? 'beauty'
								: 'prototype',
						]
					);

					$data['featured_gallery'] = $controller->larva_data();
				} else {
					$this->_add_o_figure( $data['o_figure'] );
				}

				$data['c_title']['c_title_url'] = false;

				$data['story_post_id'] = $this->_attributes['postID'];

				$this->_populate_sharing_buttons( $data );
				break;

			default:
				$data = null;
				break;
		}

		$this->_apply_brand_overrides( $data );

		return $data;
	}

	/**
	 * Add post data shared across all variations.
	 *
	 * @param array $data Larva Pattern data.
	 */
	protected function _add_common_data( array &$data ): void {
		$this->_add_c_title( $data['c_title'] );
		$this->_set_permalink_attr( $data['story_permalink_attr'] );
		$this->_set_title_attr( $data['story_title_attr'] );

		if ( $this->_attributes['hasDisplayedExcerpt'] ) {
			$this->_add_c_dek( $data['c_dek'] );
		}

		if ( isset( $this->_attributes['backgroundColorClassSuffix'] ) ) {
			$data['story_classes'] .= ' lrv-u-background-color-' .
				$this->_attributes['backgroundColorClassSuffix'];
		}
	}

	/**
	 * Populate the `o-figure` object regardless of its key name in prototype.
	 *
	 * @param array $data `o_figure` Pattern data.
	 */
	protected function _add_o_figure( array &$data ): void {
		if (
			isset(
				$this->_attributes['imageCropClass'],
				$data['c_lazy_image']['c_lazy_image_crop_class']
			)
		) {
			$data['c_lazy_image']['c_lazy_image_crop_class'] = preg_replace(
				static::CROP_CLASS_REGEX,
				$this->_attributes['imageCropClass'],
				$data['c_lazy_image']['c_lazy_image_crop_class']
			);
		}

		Larva\add_controller_data(
			Larva\Controllers\Objects\O_Figure::class,
			[
				'image_id'   => $this->_attributes['featuredImageID'],
				'image_size' => $this->_parse_image_size(
					$data['c_lazy_image']['c_lazy_image_crop_class']
				),
				'post_id'    => $this->_attributes['postID'],
			],
			$data
		);
	}

	/**
	 * Determine image size from Larva crop class.
	 *
	 * @param string $crop_classes Larva cropping classes.
	 * @return string
	 */
	protected function _parse_image_size( string $crop_classes ): string {
		$aspect_ratio = '16:9';

		if (
			preg_match(
				static::CROP_CLASS_REGEX,
				$crop_classes,
				$aspect_ratio
			)
		) {
			$aspect_ratio = str_replace( 'x', ':', $aspect_ratio[1] );
		}

		$sizes = wp_list_filter(
			Digital_Daily\Images::SIZES,
			[
				'ratio' => $aspect_ratio,
			]
		);

		return empty( $sizes )
			? 'digital-daily-16-9'
			: array_key_first( $sizes );
	}

	/**
	 * Allow brands to override specific keys in the block's pattern data.
	 *
	 * The supported keys are generally those that pertain to brand-specific
	 * features and rely on classes that are only present in the brand's theme.
	 * As a defensive measure, only explicitly-allowed keys can be modified via
	 * the provided filter.
	 *
	 * @param array $data Pattern data.
	 */
	protected function _apply_brand_overrides( array &$data ): void {
		$allowed_keys = array_flip(
			[
				'show_content_eye_badge',
				'is_article_badge',
				'is_sponsored',
				'o_sponsored',
			]
		);

		$brand_overrides = apply_filters(
			'pmc_gutenberg_story_digital_daily_brand_overrides',
			array_intersect_key(
				$data,
				$allowed_keys
			),
			$this->_attributes,
			$this->larva_module,
			$this->larva_module_variant,
		);

		$brand_overrides = array_intersect_key(
			$brand_overrides,
			$allowed_keys
		);

		$data = array_replace( $data, $brand_overrides );
	}

	/**
	 * Specify classes that must be loaded for this block to be available.
	 *
	 * @return array
	 */
	public function get_dependent_classes(): array {
		return [
			Digital_Daily\CPT::class,
		];
	}
}
