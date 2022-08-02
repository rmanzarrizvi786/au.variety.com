<?php

/**
 * Homepage section widget (TV, Film, Music, Radio)
 *
 * @package pmc-variety
 */

namespace Variety\Inc\Widgets;

/**
 * Class Section_TV_Film_Music_Radio
 */
class Section_TV_Film_Music_Radio extends Variety_Base_Widget
{

	const ID = 'section-tv-film-music-radio';

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
	public function __construct()
	{
		parent::__construct(
			static::ID,
			__('Variety Section - TV, Film, Music, Radio', 'pmc-variety'),
			[
				'description' => __('Displays the TV, Film, Music, Radio section', 'pmc-variety'),
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
	public function get_uncached_data($data = [])
	{

		$verticals = [
			'tv'      => 'tv',
			'film'    => 'film',
			'music'   => 'music',
			'radio' => 'radio',
		];

		foreach ($verticals as $key => $slug) {
			$data['articles'][$key] = \PMC\Core\Inc\Helper::get_vertical_top_stories(get_term_by('slug', $slug, 'vertical'), $this->_count);
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
	public function form($instance)
	{
		echo wp_kses('<p class="no-options-widget">There are no options for this widget.</p>', ['p' => []]);
		return 'noform';
	}
}
