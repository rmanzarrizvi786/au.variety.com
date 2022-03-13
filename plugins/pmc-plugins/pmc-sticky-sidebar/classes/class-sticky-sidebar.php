<?php
/**
 * Sticky Sidebar
 *
 * @author Jignesh Nakrani <jignesh.nakrani@rtcamp.com>
 *
 * @group pmc-sticky-sidebar
 */

namespace PMC\Sticky_Sidebar;

use PMC\Global_Functions\Traits\Singleton;

class Sticky_Sidebar {

	use Singleton;

	/**
	 * Initialising Sticky sidebar
	 */
	protected function __construct() {
		add_action( 'widgets_init', [ $this, 'register_sticky_sidebar' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

	}

	/**
	 * Register new sticky sidebar
	 */
	public function register_sticky_sidebar() {

		$defaults = [
			'name'          => 'Sticky Sidebar',
			'id'            => 'sticky-sidebar-rail',
			'description'   => '',
			'class'         => '',
			'before_widget' => '<li id="%1$s" class="widget %2$s">',
			'after_widget'  => '</li>',
			'before_title'  => '',
			'after_title'   => '',
		];

		$configs = apply_filters( 'pmc_sticky_sidebar_defaults', $defaults );

		register_sidebar( $configs );

	}

	/**
	 * Enqueueing scripts that are required for sticky Sidebar
	 */
	public function enqueue_scripts() {

		if ( \PMC::is_desktop() ) {

			$script_extension = ( \PMC::is_production() ) ? '.min.js' : '.js';

			pmc_js_libraries_enqueue_script( 'pmc-scrolltofixed' );
			wp_enqueue_script(
				'pmc_sticky_sidebar_js',
				PMC_STICKY_SIDEBAR_URL . 'assets/js/sticky-sidebar' . $script_extension,
				[
					'jquery',
					'underscore',
					'pmc-scrolltofixed',
				],
				'1.1',
				true
			);

			$config = apply_filters( 'pmc_sticky_sidebar_js_config', [ 'footer_selector' => 'footer.site-footer' ] );

			wp_localize_script( 'pmc_sticky_sidebar_js', 'pmc_sticky_sidebar_js', $config );

			wp_enqueue_style( 'pmc_sticky_sidebar_css', PMC_STICKY_SIDEBAR_URL . 'assets/css/sticky-sidebar.css', [], '1.0' );

		}
	}

	/**
	 * render sticky sidebar if it is active.
	 */
	public static function render_sticky_sidebar() {

		if ( is_active_sidebar( 'sticky-sidebar-rail' ) ) {
			echo '<div class="pmc-sticky-sidebar sidebar">';
			dynamic_sidebar( 'sticky-sidebar-rail' );
			echo '</div>';
		}

	}
}
