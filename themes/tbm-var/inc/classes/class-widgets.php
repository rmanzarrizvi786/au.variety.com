<?php

/**
 * Class Widgets
 *
 * Implements widgets.
 *
 * @package pmc-variety
 * @since   2017.1.0
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;

class Widgets
{

	use Singleton;

	/**
	 * Class constructor.
	 *
	 * @since 2017.1.0
	 */
	protected function __construct()
	{

		add_filter('widgets_init', [$this, 'register_sidebars']);
		add_filter('widgets_init', [$this, 'load_widgets'], 11);
	}

	/**
	 * Register Sidebars
	 *
	 * Register sidebars for the site.
	 *
	 * @since 2017.1.0
	 *
	 */
	public function register_sidebars()
	{

		// Unregister PMC Core sidebars that we do not need.
		unregister_sidebar('home_right_1');
		unregister_sidebar('home_right_1');
		unregister_sidebar('archive_right_1');
		unregister_sidebar('article_right_sidebar');
		unregister_sidebar('gallery-right');

		register_sidebar(
			[
				'name'          => __('Homepage Top', 'pmc-variety'),
				'id'            => 'homepage-top',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			]
		);

		register_sidebar(
			[
				'name'          => __('Homepage Bottom', 'pmc-variety'),
				'id'            => 'homepage-bottom',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			]
		);

		register_sidebar(
			[
				'name'          => __('Homepage Sidebar', 'pmc-variety'),
				'id'            => 'homepage-sidebar',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			]
		);

		register_sidebar(
			[
				'name'          => __('Global Sidebar', 'pmc-variety'),
				'id'            => 'global-sidebar',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			]
		);

		register_sidebar(
			[
				'name'          => __('Vertical Sidebar', 'pmc-variety'),
				'id'            => 'vertical-sidebar',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			]
		);

		register_sidebar(
			[
				'name'          => __('Editorial Sidebar', 'pmc-variety'),
				'id'            => 'editorial-sidebar',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			]
		);

		register_sidebar(
			[
				'name'          => __('Editorial Awards Sidebar', 'pmc-variety'),
				'id'            => 'editorial-awards-sidebar',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			]
		);

		register_sidebar(
			[
				'name'          => __('Executive Sidebar', 'pmc-variety'),
				'id'            => 'executive-sidebar',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			]
		);

		register_sidebar(
			[
				'name'          => __('Video Sidebar', 'pmc-variety'),
				'id'            => 'video-sidebar',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			]
		);

		register_sidebar(
			[
				'name'          => __('Editorial Hub', 'pmc-variety'),
				'id'            => 'editorial-hub',
				'before_widget' => false,
				'after_widget'  => false,
				'before_title'  => false,
				'after_title'   => false,
			]
		);
	}

	/**
	 * Load Widgets
	 *
	 * Load our custom widgets.
	 *
	 * @since 2017.1.0
	 *
	 */
	public function load_widgets()
	{

		// Unregister any core widgets we don't need.
		unregister_widget('\PMC\Core\Inc\Widgets\Trending_Now');

		// Register custom widgets.
		// register_widget( '\Variety\Inc\Widgets\Attend' );
		register_widget('\Variety\Inc\Widgets\Awards_Contenders');
		register_widget('\Variety\Inc\Widgets\Breaking_News_Alerts');
		register_widget('\Variety\Inc\Widgets\Breaking_News_Sidebar');
		register_widget('\Variety\Inc\Widgets\Cxense');
		register_widget('\Variety\Inc\Widgets\Coming_Soon');
		register_widget('\Variety\Inc\Widgets\Iheart');
		register_widget('\Variety\Inc\Widgets\Most_Viewed');
		register_widget('\Variety\Inc\Widgets\Must_Read');
		register_widget('\Variety\Inc\Widgets\Newsletter_Signup');
		register_widget('\Variety\Inc\Widgets\Reviews');
		register_widget('\Variety\Inc\Widgets\Recommended_For_You');
		register_widget('\Variety\Inc\Widgets\Special_Coverage');
		register_widget('\Variety\Inc\Widgets\Section_Artisans_Tech_Exposure_Politics');
		register_widget('\Variety\Inc\Widgets\Section_Awards');
		register_widget('\Variety\Inc\Widgets\Section_Lists_IHeartRadio');
		register_widget('\Variety\Inc\Widgets\Section_TV_Film_Music_Theater');
		register_widget('\Variety\Inc\Widgets\Section_VIP');
		register_widget('\Variety\Inc\Widgets\Special_Report');
		register_widget('\Variety\Inc\Widgets\The_Magazine');
		register_widget('\Variety\Inc\Widgets\Top_Stories');
		register_widget('\Variety\Inc\Widgets\Trending');
		register_widget('\Variety\Inc\Widgets\Videos');
		register_widget('\Variety\Inc\Widgets\VIP_Banner');
		register_widget('\Variety\Inc\Widgets\Voices');
		register_widget('\Variety\Inc\Widgets\What_To_Watch');
		register_widget('\Variety\Inc\Widgets\Stories_Row');
		// register_widget('\Variety\Inc\Widgets\Streamers_Section_Header');
	}
}
