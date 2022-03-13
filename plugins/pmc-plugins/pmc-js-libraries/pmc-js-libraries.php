<?php
/**
 * Javascript Script Registration & Enqueueing
 *
 * Using these functions over wp_register_script and wp_enqueue_script
 * provide the benefit to register a script AND allow overrides to be made
 * when that script is being enqueued to the dom (wp_enqueue_script does not allow this)
 *
 * ADDING A SCRIPT
 * Scripts are added to the /vendor/ directory
 * 1) Create a *lowercase* folder for the new script e.g. /vendor/cycle/2.0/jquery.cycle.min.js
 * 2) Include a README.md with usage instructions for the script e.g. /vendor/cycle/README.md
 * 2) Add the script and it's arguments to the $scripts array below (See examples there)
 *
 *
 * USING A SCRIPT
 * 1) Enqueue the script in the theme/plugin, ex:
 *
 * function my_theme_enqueue_scripts () {
 *     pmc_js_libraries_enqueue_script( 'pmc-jquery-cycle' ) ;
 * }
 * add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_scripts', 0 );
 *
 *
 * OVERRIDING A REGISTERED SCRIPT DURING ENQUEUE
 * function my_theme_enqueue_scripts () {
 *     pmc_js_libraries_enqueue_script(
 *     		'pmc-jquery-cycle',				#reference the registered script handle
 *     		'', 							#use registered path
 *     		array( 'new-dependency' ),		#add additional dependency
 *     		'', 							#use registered version
 *     		false 							#override registered $in_footer setting
 *     ) ;
 * }
 * add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_scripts', 0 );
 */

/**
 * Register front-end scripts
 *
 * @param  boolean $script_handle [description]
 * @return array || false Receive the requested script array. If no script handle is given return an array of all scripts. If the script handle is present, but it has no registered arguments--return false.
 */
function pmc_js_libraries_get_registered_scripts( $script_handle = false, $version = null ) {
	static $scripts = false;

	// Define default script arguments
	$default_args = array(
		'src'           => '',
		'deps'          => array(),
		'ver'           => '',
		'in_footer'     => true,
		'pmc_css_deps'  => array()
	);

	if( empty( $scripts ) ) {
		$scripts = array(

			/* Example:
			 *
			 * 'pmc-my-script-handle' => array(
			 * 	'latest' => 'latest-version-here',
			 * 	'version' => array(
			 * 		'version-number' => array(
			 * 			'src'       => '',
			 * 			'deps'      => array(),
			 * 			'ver'       => '',
			 * 			'in_footer' => true,
			 * 			'pmc_css_deps'  => array( // optional CSS dependencies
			 * 				'pmc-my-script-handle' = array(
			 * 					'src'   => plugins_url( 'vendor/chosen/1.2.0/chosen.min.css', __FILE__ ), // path to CSS file. Required.
			 * 					'deps'  => array(), // optional
			 * 					'ver'   => false, // optional
			 * 					'media' => 'all', // optional
			 * 				),
			 * 			),
			 * 		),
			 * 	),
			 * );
			 */

			//See vendor/jquery.dotdotdot/README.md
			'pmc-jquery-dotdotdot' => array(
				'latest'  => '1.6.14',
				'version' => array(
					'1.6.14' => array(
						'src'       => plugins_url( 'vendor/dotdotdot/1.6.14/jquery.dotdotdot.min.js', __FILE__ ),
						'deps'      => array( 'jquery' ),
						'ver'       => '1.6.14',
						'in_footer' => true,
					),
				),
			),

			'pmc-jquery-deparam' => array(
				'latest'  => '1.0-2010',
				'version' => array(
					'1.0-2010' => array(
						'src'       => plugins_url( 'vendor/jquery.deparam/1.0-2010/jquery.deparam.min.js', __FILE__ ),
						'deps'      => array( 'jquery' ),
						'ver'       => '1.0-2010',
						'in_footer' => true,
					),
				),
			),

			// https://github.com/harvesthq/chosen/releases/
			'pmc-chosen' => array(
				'latest'  => '1.2.0',
				'version' => array(
					'1.2.0' => array(
						'src'           => plugins_url( 'vendor/chosen/1.2.0/chosen.jquery.min.js', __FILE__ ),
						'deps'          => array( 'jquery' ),
						'ver'           => '1.2.0',
						'in_footer'     => true,
						'pmc_css_deps'  => array(
												'pmc-chosen' => array(
													'src'  => plugins_url( 'vendor/chosen/1.2.0/chosen.min.css', __FILE__ ),
												),
											),
					),
				),
			),

			// https://github.com/garand/sticky/releases/tag/1.0.1
			'pmc-stickyjs' => array(
				'latest'  => '1.0.1',
				'version' => array(
					'1.0.1' => array(
						'src'           => plugins_url( 'vendor/stickyjs/1.0.1/jquery.sticky.min.js', __FILE__ ),
						'deps'          => array( 'jquery' ),
						'ver'           => '1.0.1',
						'in_footer'     => true,
					),
				),
			),

			'pmc-pinit-overlay' => array(
				'latest'  => '1.0',
				'version' => array(
					'1.0' => array(
						'src' => plugins_url( 'vendor/pmc-pinit-overlay/1.0/pmc-pinit-overlay.js', __FILE__ ),
						'deps' => array( 'jquery' ),
						'ver' => '1.0',
						'in_footer' => true,
						'pmc_css_deps' => array(
							'pmc-pinit-overlay' => array(
								'src' => plugins_url( 'vendor/pmc-pinit-overlay/1.0/pmc-pinit-overlay.css', __FILE__ ),
							),
						),
					),
				),
			),

			// https://github.com/thebird/Swipe/
			'pmc-swipe' => array(
				'latest'  => '2.0',
				'version' => array(
					'2.0' => array(
						'src'       => plugins_url( 'vendor/swipe/2.0/swipe.js', __FILE__ ),
						'deps'      => array( 'jquery' ),
						'ver'       => '2.0',
						'in_footer' => false,
					),
				),
			),

			// https://github.com/d3/d3
			'pmc-d3' => array(
				'latest'  => '3.5.17',
				'version' => array(
					'3.5.17' => array(
						'src'       => plugins_url( 'vendor/d3/3.5.17/d3.v3.min.js', __FILE__ ),
						'deps'      => array(),
						'ver'       => '3.5.17',
						'in_footer' => true,
					),
				),
			),

			// PMC Custom jQuery extensions (selectors/functions)
			'pmc-jquery-extensions' => array(
				'latest'  => '1.0',
				'version' => array(
					'1.0' => array(
						'src'       => plugins_url( 'vendor/pmc-jquery-extensions/1.0/pmc-jquery-extensions.js', __FILE__ ),
						'min_src'   => plugins_url( 'vendor/pmc-jquery-extensions/1.0/pmc-jquery-extensions.min.js', __FILE__ ),
						'deps'      => array( 'jquery' ),
						'ver'       => '1.0',
						'in_footer' => true,
					),
				),
			),

			// PMC Custom jQuery extensions (selectors/functions)
			'jquery-inview' => array(
				'latest'  => '1.0',
				'version' => array(
					'1.0' => array(
						'src'       => plugins_url( 'vendor/jquery-inview/1.0/jquery-inview.js', __FILE__ ),
						'min_src'   => plugins_url( 'vendor/jquery-inview/1.0/jquery-inview.min.js', __FILE__ ),
						'deps'      => array( 'jquery' ),
						'ver'       => '1.0',
						'in_footer' => true,
					),
				),
			),

			'pmc-slick' => array(
				'latest'  => '1.0',
				'version' => array(
					'1.0' => array(
						'src' => plugins_url( 'vendor/slick/slick.js', __FILE__ ),
						'min_src' => plugins_url( 'vendor/slick/slick.min.js', __FILE__ ),
						'deps' => array( 'jquery' ),
						'ver' => '1.6.0',
						'in_footer' => true,
						'pmc_css_deps' => array(
							'pmc-slick-css' => array(
								'src' => plugins_url( 'vendor/slick/slick.css', __FILE__ ),
							),
						),
					),
				),
			),

			'pmc-hover3d' => array(
				'latest'  => '1.0',
				'version' => array(
					'1.0' => array(
						'src' => plugins_url( 'vendor/hover3d/hover3d.js', __FILE__ ),
						'min_src' => plugins_url( 'vendor/hover3d/hover3d.min.js', __FILE__ ),
						'deps' => array( 'jquery' ),
						'ver' => '1.1.0',
						'in_footer' => false,
					),
				),
			),

			'pmc-scrolltofixed' => array(
				'latest'  => '1.0',
				'version' => array(
					'1.0' => array(
						'src' => plugins_url( 'vendor/scrolltofixed/1.0/jquery-scrolltofixed.js', __FILE__ ),
						'min_src' => plugins_url( 'vendor/scrolltofixed/1.0/jquery-scrolltofixed.min.js', __FILE__ ),
						'deps' => array( 'jquery' ),
						'ver' => '1.0',
						'in_footer' => true,
						'pmc_css_deps' => array( ),
					),
				),
			),

			'pmc-jquery-mousewheel' => array(
				'latest'  => '3.1.13',
				'version' => array(
					'3.1.13' => array(
						'src'          => plugins_url( 'vendor/jquery-mousewheel/3.1.13/jquery.mousewheel.js', __FILE__ ),
						'min_src'      => plugins_url( 'vendor/jquery-mousewheel/3.1.13/jquery.mousewheel.min.js', __FILE__ ),
						'deps'         => array( 'jquery' ),
						'ver'          => '3.1.13',
						'in_footer'    => true,
						'pmc_css_deps' => array( ),
					),
				),
			),

		); // array $scripts

		// Applicable to Desktop only
		if ( !( PMC::is_mobile() || PMC::is_tablet() ) ) {

			$scripts['pmc-pinit-hover'] = array(
				'latest'  => '1.0',
				'version' => array(
					'1.0' => array(
						'src'       => plugins_url( 'vendor/pmc-pinit-hover/1.0/pmc-pinit-hover.js', __FILE__ ),
						'deps'      => array( 'jquery' ),
						'ver'       => '1.0',
						'in_footer' => true,
						'pmc_css_deps'  => array(
							'pmc-pinit-hover' => array(
								'src'  => plugins_url( 'vendor/pmc-pinit-hover/1.0/pmc-pinit-hover.css', __FILE__ ),
							),
						),
					),
				),
			); // array[pmc-pinit-hover]

		} // if not ( mobile or tablet )

	} // if $scripts is empty

 	// 1) Return arguments for the requested script
 	//      If present, but there are no registered arguments--return false
 	//      this allows for logic like:
 	//      if ( $script = pmc_js_libraries_get_registered_scripts('pmc-jquery-cycle') ) { //do stuff }
 	//
 	// 2) Return an array of all the scripts if script handle is not provided
	if( $script_handle ) {
		if ( ! empty( $version ) && isset( $scripts[ $script_handle ]['version'][ $version ] ) ) {
			$script = $scripts[ $script_handle ]['version'][ $version ];
		} elseif ( ! empty( $scripts[ $script_handle ]['latest'] ) ) {
			$latest_script_version = $scripts[ $script_handle ]['latest'];

			if ( isset( $scripts[ $script_handle ]['version'][ $latest_script_version ] ) ) {
				$script = $scripts[ $script_handle ]['version'][ $latest_script_version ];
			}

			unset( $latest_script_version );
		}

		if ( ! empty( $script ) ) {
			return wp_parse_args( $script, $default_args );
		}

		return false;
	} else {
		return $scripts;
	}
}// pmc_js_libraries_get_registered_scripts()

/**
 * Enqueue a registered script
 *
 * @param  string $handle    The script to be registered
 * @param  string $src       Override the registered script's source
 * @param  array  $deps      Add additional script dependencies
 * @param  string $ver       Override the registered script's version
 * @param  string $in_footer Override the registered script's in_footer setting
 * @param  bool   $enqueue   Enqueue now or just register script
 * @param  bool   $dev_mode  When true, the unminified source is used (if one is specificed for the script)
 * @return null
 */
function pmc_js_libraries_enqueue_script( $handle, $src = '', $deps = array(), $ver = '', $in_footer = '', $enqueue = true, $dev_mode = false ) {
	// See if we already have a script with that handle registered
	// If so, we'll be given the previously-registered script's arguments
	$args = pmc_js_libraries_get_registered_scripts( $handle, $ver );

	// Unknown or invalid script handle
	if( empty( $args ) ) {
		if ( function_exists( '_doing_it_wrong' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'Invalid script handle' ), '3.9' );
		}

		return false;
	}

	// Allow registered script overrides when passing arugments to this function

	// Override the register script source
	if ( empty( $src ) ) {

		// If dev mode had been enabled, use the script's unminified source
		if ( $dev_mode ) {
			$src = $args['src'];
		} else {
			// However, when not in dev mode
			// Attempt to use a minified source if it exists
			if ( ! empty( $args['min_src'] ) ) {
				$src = $args['min_src'];
			} else if ( ! empty( $args['src'] ) ) {
				$src = $args['src'];
			}
		}
	}

	if ( empty( $src ) ) {
		return false;
	}

 	// Add additional dependencies to the registered script
	if( ! empty( $deps ) && is_array( $deps ) ) {
		$deps = wp_parse_args( $deps, $args['deps'] );
	}

 	// Override the registered script's version
	if( $ver === '' ) {
		$ver = $args['ver'];
	}

	// Override the registered script's in_footer setting
	if( $in_footer === '' ) {
		$in_footer = $args['in_footer'];
	}

	// Lastly utilize WordPress' enqueue script function
	if ( $enqueue ) {
		wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
	} else {
		wp_register_script( $handle, $src, $deps, $ver, $in_footer );
	}

	// Enqueue any CSS dependencies
	if( is_array( $args['pmc_css_deps'] ) && !empty( $args['pmc_css_deps'] ) ) {
		foreach( $args['pmc_css_deps'] as $handle => $opts ) {
			$default_opts = array(
				'src'        => false,
				'deps'       => array(),
				'ver'        => $ver,
				'in_footer'  => false,
			);
			$opts =  wp_parse_args( $opts, $default_opts );
			if( !empty( $opts['src'] ) ) {
				wp_enqueue_style( $handle, $opts['src'], $opts['deps'], $opts['ver'], $opts['in_footer'] );
			}
		}
	}

} // pmc_js_libraries_enqueue_script()

// EOF
