<?php

/**
 * Videos Widget
 *
 * Displays the latest videos.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc\Widgets;

use \Variety\Inc\Carousels;

/**
 * Class Videos
 *
 * @since 2017.1.0
 * @see Global_Curateable
 */
class Videos extends Variety_Base_Widget
{

	const ID = 'videos';

	/**
	 * Count - How many posts to display.
	 * Change from 4 to 8 - BR-185
	 *
	 * @var int
	 */
	private $count = 8;

	/**
	 * Register widget with WordPress.
	 *
	 * @since 2017.1.0
	 */
	public function __construct()
	{
		parent::__construct(
			static::ID,
			__('Variety - Latest Videos', 'pmc-variety'),
			['description' => __('Featured/Latest videos', 'pmc-variety')]
		);
	}

	/**
	 * Method which returns data to be displayed on the front-end.
	 * The data here is not cached & this method is not meant to be called directly.
	 *
	 * @since 2017-10-04 Amit Gupta - CDWE-702
	 *
	 * @param  array $data Widget data, from global data or overrides.
	 * @return mixed
	 *
	 */
	public function get_uncached_data($data = array())
	{

		if (!empty($data['count'])) {
			$this->count = $data['count'];
		}

		// Add filters.
		$post_type_filter = function ($args) {
			$args['post_type'] = 'variety_top_video';
			return $args;
		};

		add_filter('pmc_carousel_items_fallback_args', $post_type_filter);
		add_filter('pmc_carousel_items_fallback_latest_args', $post_type_filter);

		if (!empty($data['carousel'])) {
			$data['videos'] = Carousels::get_carousel_posts($data['carousel'], $this->count, false, 'post');
		} else {
			$data['videos'] = [];
		}

		// Remove filters.
		remove_filter('pmc_carousel_items_fallback_args', $post_type_filter);
		remove_filter('pmc_carousel_items_fallback_latest_args', $post_type_filter);

		return $data;
	}

	/**
	 * Get Fields.
	 *
	 * Get the option fields for this widget.
	 *
	 * @since 2017.1.0
	 * @return array
	 */
	protected function get_fields()
	{
		return array(
			'title'     => array(
				'label' => __('Title', 'pmc-variety'),
				'type'  => 'text',
			),
			'more_text' => array(
				'label' => __('More Link Text', 'pmc-variety'),
				'type'  => 'text',
			),
			'more_link' => array(
				'label' => __('More Link URL', 'pmc-variety'),
				'type'  => 'url',
			),
			'carousel'  => array(
				'label'   => __('Carousel', 'pmc-variety'),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			),
			'count'     => array(
				'label' => __('Number Of Articles', 'pmc-variety'),
				'type'  => 'text',
			),
		);
	}
}
