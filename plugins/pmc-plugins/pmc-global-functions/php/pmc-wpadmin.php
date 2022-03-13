<?php
/**
 * WordPress Admin hooks and filters
 *
 * @package WordPress
 * @subpackage pmc-plugins
 *
 * @since 2014-12-09 Corey Gilmore
 *
 */


/**
 * Add information about the current screen to the admin body class
 *
 * @see PPT-3825
 * @see http://codex.wordpress.org/Function_Reference/get_current_screen
 *
 * @since 2014-12-09 Corey Gilmore
 *
 */
function pmc_admin_screen_body_class( $classes ) {
	$screen = get_current_screen();

	$attrs = array(
		'action',
		'base',
		'id',
		'parent_base',
		'parent_file',
		'post_type',
		'taxonomy',
	);

	// Loop through all of the named attributes
	foreach( $attrs as $attr ) {
		if( isset( $screen->$attr ) && ( is_string( $screen->$attr ) || is_numeric( $screen->$attr ) ) && $screen->$attr != '' ) {
			$val = $screen->$attr;

			// Normalize the screen value a bit
			// First remove any invalid CSS characters to avoid inadvertantly adding an unwanted class name
			$val = '--' . preg_replace( '/[^_a-zA-Z0-9-]+/is', '-', $val );

			$val = preg_replace( '/--+/', '-', $val ); // remove multiple hyphens
			$val = trim( $val, '-' ); // trim any leading or tailing hyphens
			if( !empty( $val ) && $val != '-' ) {
				$classes .= ' ' . esc_attr( 'pmc-wpadmin-screen-' . $attr . '-' . $val );
			}
		}
	}

	return $classes;
}
add_filter( 'admin_body_class', 'pmc_admin_screen_body_class' );

/**
 * Enqueue Patch Styles and scripts
 *
 * @action admin_enqueue_scripts, 11
 *
 * @codeCoverageIgnore This is a temporary patch and these functions don't have unit tests
 */
function enqueue_patch_wpadmin_scripts_styles() {
	//BR-1497  This is a temporary patch for issue with gallery and WP 5.9
	global $wp_version;

	if ( version_compare( $wp_version, '5.9', '==' ) ) {
		$file_extension    = ( \PMC::is_production() ) ? '.min' : '';
		$jquery_ui_version = '1.13.1';
		$in_footer         = true;

		$scripts = [
			[
				'handle' => 'jquery-ui-core',
				'src'    => '/js/jquery/ui/core%s.js',
				'dep'    => [ 'jquery' ],
			],
			[
				'handle' => 'jquery-effects-core',
				'src'    => '/js/jquery/ui/effect%s.js',
				'dep'    => [ 'jquery' ],
			],
			[
				'handle' => 'jquery-effects-blind',
				'src'    => '/js/jquery/ui/effect-blind%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-effects-bounce',
				'src'    => '/js/jquery/ui/effect-bounce%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-effects-clip',
				'src'    => '/js/jquery/ui/effect-clip%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-effects-drop',
				'src'    => '/js/jquery/ui/effect-drop%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-effects-explode',
				'src'    => '/js/jquery/ui/effect-explode%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-effects-fade',
				'src'    => '/js/jquery/ui/effect-fade%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-effects-fold',
				'src'    => '/js/jquery/ui/effect-fold%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-effects-highlight',
				'src'    => '/js/jquery/ui/effect-highlight%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-effects-puff',
				'src'    => '/js/jquery/ui/effect-puff%s.js',
				'dep'    => [ 'jquery-effects-core', 'jquery-effects-scale' ],
			],
			[
				'handle' => 'jquery-effects-pulsate',
				'src'    => '/js/jquery/ui/effect-pulsate%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-effects-scale',
				'src'    => '/js/jquery/ui/effect-scale%s.js',
				'dep'    => [ 'jquery-effects-core', 'jquery-effects-size' ],
			],
			[
				'handle' => 'jquery-effects-shake',
				'src'    => '/js/jquery/ui/effect-shake%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-effects-size',
				'src'    => '/js/jquery/ui/effect-size%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-effects-slide',
				'src'    => '/js/jquery/ui/effect-slide%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-effects-transfer',
				'src'    => '/js/jquery/ui/effect-transfer%s.js',
				'dep'    => [ 'jquery-effects-core' ],
			],
			[
				'handle' => 'jquery-ui-accordion',
				'src'    => '/js/jquery/ui/accordion%s.js',
				'dep'    => [ 'jquery-ui-core' ],
			],
			[
				'handle' => 'jquery-ui-autocomplete',
				'src'    => '/js/jquery/ui/autocomplete%s.js',
				'dep'    => [ 'jquery-ui-menu', 'wp-a11y' ],
			],
			[
				'handle' => 'jquery-ui-button',
				'src'    => '/js/jquery/ui/button%s.js',
				'dep'    => [ 'jquery-ui-core', 'jquery-ui-controlgroup', 'jquery-ui-checkboxradio' ],
			],
			[
				'handle' => 'jquery-ui-datepicker',
				'src'    => '/js/jquery/ui/datepicker%s.js',
				'dep'    => [ 'jquery-ui-core' ],
			],
			[
				'handle' => 'jquery-ui-dialog',
				'src'    => '/js/jquery/ui/dialog%s.js',
				'dep'    => [ 'jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-button' ],
			],
			[
				'handle' => 'jquery-ui-menu',
				'src'    => '/js/jquery/ui/menu%s.js',
				'dep'    => [ 'jquery-ui-core' ],
			],
			[
				'handle' => 'jquery-ui-mouse',
				'src'    => '/js/jquery/ui/mouse%s.js',
				'dep'    => [ 'jquery-ui-core' ],
			],
			[
				'handle' => 'jquery-ui-progressbar',
				'src'    => '/js/jquery/ui/progressbar%s.js',
				'dep'    => [ 'jquery-ui-core' ],
			],
			[
				'handle' => 'jquery-ui-selectmenu',
				'src'    => '/js/jquery/ui/selectmenu%s.js',
				'dep'    => [ 'jquery-ui-menu' ],
			],
			[
				'handle' => 'jquery-ui-slider',
				'src'    => '/js/jquery/ui/slider%s.js',
				'dep'    => [ 'jquery-ui-mouse' ],
			],
			[
				'handle' => 'jquery-ui-spinner',
				'src'    => '/js/jquery/ui/spinner%s.js',
				'dep'    => [ 'jquery-ui-button' ],
			],
			[
				'handle' => 'jquery-ui-tabs',
				'src'    => '/js/jquery/ui/tabs%s.js',
				'dep'    => [ 'jquery-ui-core' ],
			],
			[
				'handle' => 'jquery-ui-tooltip',
				'src'    => '/js/jquery/ui/tooltip%s.js',
				'dep'    => [ 'jquery-ui-core' ],
			],
			[
				'handle' => 'jquery-ui-draggable',
				'src'    => '/js/jquery/ui/draggable%s.js',
				'dep'    => [ 'jquery-ui-mouse' ],
			],
			[
				'handle' => 'jquery-ui-droppable',
				'src'    => '/js/jquery/ui/droppable%s.js',
				'dep'    => [ 'jquery-ui-draggable' ],
			],
			[
				'handle' => 'jquery-ui-resizable',
				'src'    => '/js/jquery/ui/resizable%s.js',
				'dep'    => [ 'jquery-ui-mouse' ],
			],
			[
				'handle' => 'jquery-ui-selectable',
				'src'    => '/js/jquery/ui/selectable%s.js',
				'dep'    => [ 'jquery-ui-mouse' ],
			],
			[
				'handle' => 'jquery-ui-sortable',
				'src'    => '/js/jquery/ui/sortable%s.js',
				'dep'    => [ 'jquery-ui-mouse' ],
			],
			[
				'handle' => 'jquery-ui-position',
				'src'    => false,
				'dep'    => [ 'jquery-ui-core' ],
			],
			[
				'handle' => 'jquery-ui-widget',
				'src'    => false,
				'dep'    => [ 'jquery-ui-core' ],
			],
		];

		foreach ( $scripts as $script ) {
			wp_deregister_script( $script['handle'] );
			wp_register_script( $script['handle'], pmc_global_functions_url( sprintf( $script['src'], $file_extension ) ), $script['dep'], $jquery_ui_version, $in_footer );
		}

		wp_localize_script(
			'jquery-ui-autocomplete',
			'uiAutocompleteL10n',
			[
				/* translators: Number of results found when using jQuery UI Autocomplete. */
				'oneResult'    => __( '1 result found. Use up and down arrow keys to navigate.', 'pmc-global-functions' ),
				/* translators: %d: Number of results found when using jQuery UI Autocomplete. */
				'manyResults'  => __( '%d results found. Use up and down arrow keys to navigate.', 'pmc-global-functions' ),
				'itemSelected' => __( 'Item selected.', 'pmc-global-functions' ),
			]
		);

	}
}
add_action( 'admin_enqueue_scripts', 'enqueue_patch_wpadmin_scripts_styles' );

// EOF
