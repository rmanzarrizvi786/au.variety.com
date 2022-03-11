<?php

/**
 * Top Stories widget
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Inc\Widgets;

use \PMC_Cache;
use Variety\Inc\Carousels;

class Top_Stories extends Variety_Base_Widget
{

	const ID = 'top-stories';

	/**
	 * Count - How many posts to display.
	 *
	 * @var int
	 */
	private $count = 6;

	/**
	 * Register widget with WordPress.
	 *
	 */
	public function __construct()
	{
		parent::__construct(
			static::ID,
			__('Variety - Top Stories', 'pmc-variety'),
			array('description' => __('Displays most recent articles in TV, Film, Awards, and Digital', 'pmc-variety'))
		);
	}

	/**
	 * Method which returns data to be displayed on the front-end.
	 * The data here is not cached & this method is not meant to be called directly.
	 *
	 * @since 2017-10-04 Amit Gupta - CDWE-702
	 *
	 * @return array
	 *
	 */
	public function get_uncached_data($data = [])
	{

		if (!empty($data['count'])) {
			$this->count = $data['count'];
		}

		if (!empty($data['carousel'])) {
			$data['articles'] = Carousels::get_carousel_posts($data['carousel'], $this->count);
		} else {
			$data['articles'] = [];
		}

		return $data;
	}

	/**
	 * Get Fields.
	 *
	 * Get the option fields for this widget.
	 *
	 * @return array
	 *
	 */
	protected function get_fields()
	{
		return [
			'carousel'      => [
				'label'   => __('Carousel', 'pmc-variety'),
				'type'    => 'select',
				'options' => Carousels::get_carousels(),
			],
			'popular_count' => [
				'label' => __('Number Of Sidebar Popular Articles', 'pmc-variety'),
				'type'  => 'text',
			],
		];
	}
}

//EOF
