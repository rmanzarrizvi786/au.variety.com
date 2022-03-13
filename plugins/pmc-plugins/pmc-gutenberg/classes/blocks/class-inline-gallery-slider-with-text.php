<?php
/**
 * Render inline gallery with accompanying rich text.
 *
 * @package pmc-gallery.
 */

namespace PMC\Gutenberg\Blocks;

use PMC;
use PMC\Gutenberg\Block_Base;
use PMC\Gutenberg\Interfaces\Block_Base\With_Larva_Data;
use PMC\Larva;

/**
 * Class Inline_Gallery_Slider_With_Text.
 *
 * @codeCoverageIgnore Class will be refactored into Larva controllers after
 *                     Patterns are moved to `pmc-larva`.
 */
class Inline_Gallery_Slider_With_Text extends Block_Base implements With_Larva_Data {
	/**
	 * Custom image sizes used by this block.
	 *
	 * Should contain only two keys, the first being the desktop size, the
	 * second the mobile size. If additional sizes are added, the `image_size`
	 * argument used in the `O_Figure` controller in `self::larva_data()` must
	 * be updated to accommodate the extra sizes.
	 */
	protected const IMAGE_SIZES = [
		'pmc-gutenberg-inline-slider'        => [ 450, 600 ],
		'pmc-gutenberg-inline-slider-mobile' => [ 338, 450 ],
	];

	/**
	 * Set up block.
	 */
	public function __construct() {
		$this->_block = 'inline-gallery-slider-with-text';

		$this->template = 'modules/block-' . $this->_block;

		add_action( 'after_setup_theme', [ $this, 'register_image_sizes' ] );
		add_action(
			'wpcom_thumbnail_editor_args',
			[ $this, 'set_image_aspect_ratios' ]
		);
	}

	/**
	 * Register image sizes for this block.
	 *
	 * @return void
	 */
	public function register_image_sizes(): void {
		foreach ( static::IMAGE_SIZES as $name => $dimensions ) {
			[ $width, $height ] = $dimensions;

			add_image_size(
				$name,
				$width,
				$height,
				true
			);
		}
	}

	/**
	 * Specify aspect ratio for custom image sizes.
	 *
	 * @param array $editor_args Thumbnail editor arguments.
	 * @return array
	 */
	public function set_image_aspect_ratios( array $editor_args ): array {
		$ratio = '2:3';

		if ( ! is_array( $editor_args['image_ratio_map'] ) ) {
			$editor_args['image_ratio_map'] = [];
		}

		if ( ! isset( $editor_args['image_ratio_map'][ $ratio ] ) ) {
			$editor_args['image_ratio_map'][ $ratio ] = [];
		}

		// Constant is defined as an array.
		// phpcs:ignore PmcWpVip.Functions.StrictArrayParameters.NoTypeCastParam
		foreach ( array_keys( static::IMAGE_SIZES ) as $name ) {
			$editor_args['image_ratio_map'][ $ratio ][] = $name;
		}

		return $editor_args;
	}

	/**
	 * Populate Larva data.
	 *
	 * @param string $block_content Block's inner content, generally empty.
	 * @param array  $block Block details, including attributes.
	 * @return array|null
	 */
	public function larva_data( string $block_content, array $block ): ?array {
		$pattern_data = Larva\Pattern::get_json_data(
			$this->template . '.prototype'
		);

		if ( empty( $block['attrs']['svgSlug'] ) ) {
			$pattern_data['c_svg'] = false;
		} else {
			$details = SVG::get_instance()->get_details(
				$block['attrs']['svgSlug']
			);

			if ( null === $details ) {
				$pattern_data['c_svg'] = false;
			}

			$pattern_data['c_svg']['c_svg_name'] = pathinfo(
				$details['url'],
				PATHINFO_FILENAME
			);
		}

		if ( empty( $block['attrs']['heading'] ) ) {
			$pattern_data['m_heading'] = false;
		} else {
			$pattern_data['m_heading']['heading_markup'] =
				$block['attrs']['heading'];
		}

		$pattern_data['text_markup'] = empty( $block['attrs']['text'] )
			? ''
			: $block['attrs']['text'];


		$ids = $this->_get_gallery_ids( $block['innerBlocks'] );

		if ( null === $ids ) {
			$pattern_data['gallery_items'] = false;
		} else {
			$o_figure_template             = array_shift(
				$pattern_data['gallery_items']
			);
			$pattern_data['gallery_items'] = [];

			foreach ( $ids as $id ) {
				$o_figure = $o_figure_template;

				Larva\add_controller_data(
					Larva\Controllers\Objects\O_Figure::class,
					[
						'post_id'    => 0,
						'image_id'   => $id,
						'image_size' => PMC::is_mobile()
							? array_key_last( static::IMAGE_SIZES )
							: array_key_first( static::IMAGE_SIZES ),
					],
					$o_figure
				);

				$o_figure['c_lazy_image']['c_lazy_image_crop_class'] =
					'lrv-a-crop-2x3';
				$o_figure['c_lazy_image']['c_lazy_image_link_url']   = false;
				$o_figure['o_figure_link_url']                       = false;
				$pattern_data['gallery_items'][]                     =
					$o_figure;
			}
		}

		return $pattern_data;
	}

	/**
	 * Extract image IDs from gallery block.
	 *
	 * Prior to WP 5.9, IDs were available in the gallery block's attributes
	 * under an `ids` key. Starting with WP 5.9, IDs must be extracted from the
	 * image blocks contained within the gallery.
	 *
	 * @param array $inner_blocks Blocks nested within our custom block.
	 * @return array|null
	 */
	protected function _get_gallery_ids( $inner_blocks ): ?array {
		if ( empty( $inner_blocks ) ) {
			return null;
		}

		$gallery_block = array_shift( $inner_blocks );

		if ( isset( $gallery_block['attrs']['ids'] ) ) {
			return empty( $gallery_block['attrs']['ids'] )
				? null
				: $gallery_block['attrs']['ids'];
		}

		$ids = [];

		foreach ( $gallery_block['innerBlocks'] as $image_block ) {
			if ( isset( $image_block['attrs']['id'] ) ) {
				$ids[] = $image_block['attrs']['id'];
			}
		}

		return empty( $ids ) ? null : $ids;
	}
}
