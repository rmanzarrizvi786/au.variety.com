<?php
/**
 * Homepage section widget (Artisan, Tech, Exposure, Politics)
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

/**
 * Class Section_Artisans_Tech_Exposure_Politics
 */
class Section_Artisans_Tech_Exposure_Politics extends Variety_Base_Widget {

	const ID = 'section-artisans-tech-exposure-politics';

	const CACHE_LIFE = 0;     // no caching for this widget since the function already caches data.

	/**
	 * Count - How many posts to display.
	 *
	 * @var int
	 */
	private $_count = 5;

	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct() {
		parent::__construct(
			static::ID,
			__( 'Variety Section - Artisans, Tech, Exposure, Politics', 'pmc-variety' ),
			[
				'description' => __( 'Displays the Artisans, Tech, Exposure, Politics section', 'pmc-variety' ),
			]
		);
	}

	/**
	 * Method which returns data to be displayed on the front-end.
	 * The data here is not cached & this method is not meant to be called directly.
	 *
	 * @param  array $data Widget data, from global data or overrides.
	 * @return mixed
	 *
	 */
	public function get_uncached_data( $data = [] ) {

		$taxonomies = [
			'exposure' => 'scene',
			'artisans' => 'artisans',
			'tech'     => 'digital',
			'politics' => 'politics',
		];

		foreach ( $taxonomies as $key => $vertical ) {
			$data['articles'][ $key ] = \PMC\Core\Inc\Helper::get_vertical_top_stories( get_term_by( 'slug', $vertical, 'vertical' ), $this->_count );
		}

		return $data;

	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return string|void
	 * @throws \Exception
	 * @since 2017.1.0
	 * @see   WP_Widget::form()
	 *
	 */
	public function form( $instance ) {
		?>
		<p class="no-options-widget">
		<?php esc_html_e( 'There are no options for this widget.', 'pmc-variety' ); ?>
		</p>
		<?php
		return 'noform';
	}
}
