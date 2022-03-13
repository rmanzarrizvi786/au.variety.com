<?php

namespace PMC\Tags;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Tags
 *
 * @author brandoncamenisch
 * @version 2017-09-08 brandoncamenisch - PMCVIP-2831:
 * - Creating instance with configuration enabled for our general use.
 * tags.
 *
 **/


/**
 * Tags
 *
 * @since 2017-09-08
 *
 * @version 2017-09-08 - brandoncamenisch - PMCVIP-2831:
 * - Writing methods that supplement the general framework for the tags class
 * that can be used in the core theme.
 *
 **/
class Tags {

	use Singleton;

	const TAGS_FILTER                  = 'pmc-tags-filter-tags';
	const TAGS_IDENTIFIER              = 'pmc-tags-';
	const TAGS_FILTER_PREFETCH_DOMAINS = 'pmc-tags-filter-prefetch-domains';

	// Default positions for pmc-tags{top,bottom,footer}
	protected $_positions = [
		'bottom',
		'footer',
		'top',
		'head',
	];

	/**
	 * Example of adding an item to the array.
	 *  'name-of-tag'   => [ // The Name of the tag as a slug type of reference.
	 *   This will be used for creating cheez values and referencing the item as
	 *    the primary key.
	 *    'enabled'     => false, // Whether or not the item is on by enabled.
	 *    'description' => '', // The description is used for cheez values.
	 *    'name'        => 'Comscore', // The name type value used for cheez values
	 *    'positions'   => ['bottom', 'top'], // Common tag positions accepted as an
	 *       array value. These positions will determine if the tag is called for each
	 *       particular tag position i.e. pmc-tags-top, pmc-tags-bottom. Those positions
	 *       are also passed to the templates so template logic can be applied.
	 *    'slug'        => 'comscore',// Used as a slug type reference for cheez
	 *      option names.
	 *    'template'    => '', // A filterable template location that can be changed.
	 *      within the theme or anywhere outside the main plugin when the general
	 *      template and filtering the tags aren't enough.
	 *    'values'      => ['id'], // Default field type values. Each of these is
	 *      turned into a cheez option and passed to the template files so that we
	 *      have a basic way to pass multiple variables into our tag templates.
	 *  ]
	 *
	 * @NOTE: Please keep the vendor tags in alphabetical order.
	 **/
	protected $_options = [
		'amazon'             => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Amazon',
			'positions'   => [ 'top', 'footer' ],
			'slug'        => 'amazon',
			'template'    => '',
			'values'      => [],
		],
		'comscore'           => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Comscore',
			'positions'   => [ 'bottom', 'top' ],
			'slug'        => 'comscore',
			'template'    => '',
			'values'      => [ 'id' => '' ],
		],
		'digioh'             => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Digioh',
			'positions'   => [ 'bottom' ],
			'slug'        => 'digioh',
			'template'    => '',
			'values'      => [ 'id' => '' ],
		],
		'facebook'           => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Facebook',
			'positions'   => [ 'top' ],
			'slug'        => 'facebook',
			'template'    => '',
			'values'      => [ 'id' ],
		],
		'facebook-pixel'     => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Facebook Pixel',
			'positions'   => [ 'top' ],
			'slug'        => 'facebook-pixel',
			'template'    => '',
			'values'      => [ 'id' ],
		],
		'global'             => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Global',
			'positions'   => [ 'bottom' ],
			'slug'        => 'global',
			'template'    => '',
			'values'      => [ 'sitepcode', 'pmcpcode' ],
		],
		'habu'               => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Habu',
			'positions'   => [ 'head' ],
			'slug'        => 'habu',
			'template'    => '',
			'values'      => [ 'id' ],
		],
		'heatmapping'        => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Heat Mapping',
			'positions'   => [ 'bottom' ],
			'slug'        => 'heatmapping',
			'template'    => '',
			'values'      => [],
		],
		'hotjar'             => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'HotJar',
			'positions'   => [ 'bottom' ],
			'slug'        => 'hotjar',
			'template'    => '',
			'values'      => [],
		],
		'instinctive'        => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Instinctive',
			'positions'   => [ 'bottom' ],
			'slug'        => 'instinctive',
			'template'    => '',
			'values'      => [],
		],
		'keywee'             => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Keywee',
			'positions'   => [ 'bottom' ],
			'slug'        => 'keywee',
			'template'    => '',
			'values'      => [ 'id' ],
		],
		'krux'               => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Krux',
			'positions'   => [ 'bottom' ],
			'slug'        => 'krux',
			'template'    => '',
			'values'      => [ 'id' ],
		],
		'memo'               => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Memo',
			'positions'   => [ 'bottom' ],
			'slug'        => 'memo',
			'template'    => '',
			'values'      => [ 'id' ],
		],
		'openx'              => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Openx',
			'positions'   => [ 'top' ],
			'slug'        => 'openx',
			'template'    => '',
			'values'      => [],
		],
		'permutive'          => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'permutive',
			'positions'   => [ 'head' ],
			'slug'        => 'permutive',
			'template'    => '',
			'values'      => [ 'project-id', 'api-key' ],
		],
		'pingdom'            => [
			'priority'    => 99, // we need pingdom tag to be the last output
			'enabled'     => false,
			'description' => '',
			'name'        => 'Pingdom',
			'positions'   => [ 'bottom' ],
			'slug'        => 'pingdom',
			'template'    => '',
			'values'      => [],
		],
		'pinit'              => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Pinit',
			'positions'   => [],
			'slug'        => 'pinit',
			'template'    => '',
			'values'      => [],
		],
		'pinterest'          => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Pinterest',
			'positions'   => [ 'bottom' ],
			'slug'        => 'pinterest',
			'template'    => '',
			'values'      => [],
		],
		'polar'              => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Polar',
			'positions'   => [],
			'slug'        => 'polar',
			'template'    => '',
			'values'      => [],
		],
		'prefetch'           => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Prefetch',
			'positions'   => [ 'top' ],
			'slug'        => 'prefetch',
			'template'    => '',
			'values'      => [],
		],
		'quantcast'          => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Quantcast',
			'positions'   => [ 'bottom' ],
			'slug'        => 'quantcast',
			'template'    => '',
			'values'      => [ 'pmcpcode', 'sitepcode' ],
		],
		'skimlinks'          => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'SkimLinks',
			'positions'   => [ 'bottom' ],
			'slug'        => 'skimlinks',
			'template'    => '',
			'values'      => [],
		],
		'taboola-base-pixel' => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Taboola Base Pixel',
			'positions'   => [ 'head' ],
			'slug'        => 'taboola-base-pixel',
			'template'    => '',
			'values'      => [ 'id' ],
		],
		'trackonomics'       => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Trackonomics',
			'positions'   => [ 'bottom' ],
			'slug'        => 'trackonomics',
			'template'    => '',
			'values'      => [ 'customer_id' ],
		],
		'venatus'            => [
			'enabled'     => false,
			'description' => '',
			'name'        => 'Venatus',
			'positions'   => [ 'head' ],
			'slug'        => 'venatus',
			'template'    => '',
			'values'      => [ 'site_id' ],
		],
	];

	/**
	 * __construct
	 *
	 * Class constructor to initialize the this object
	 *
	 * @since 2017-09-08
	 *
	 * @author brandoncamenisch
	 *
	 * @version 2017-09-08 - PMCVIP-2831:
	 * - Adds actions to our standard pmc template action hooks used for pmc-tags.
	 *
	 * @version 2017-09-15 - PMCVIP-2849:
	 * - Adding a foreach loop that will filter the cheez values into using our
	 * pmc-values plugin. Note: that the pre_update_option_cap priority is set to
	 * 9 because of a VIP production difference in priority level.
	 *
	 **/
	protected function __construct() {
		if ( ! is_admin() ) {
			foreach ( $this->_positions as $position ) {
				add_action( "pmc-tags-{$position}", [ $this, 'get_template' ], 10, 1 );
			}

			foreach ( $this->_positions as $position ) {
				add_action( "pmc_tags_{$position}", [ $this, 'get_template_v2' ], 10, 1 );
			}

		}
	}

	/**
	 * _filter_values
	 *
	 * @since 2017-09-15
	 *
	 * @author brandoncamenisch
	 *
	 * @version 2017-09-15 - PMCVIP-2849:
	 * - Simple getter for this plugin that returns filtered values.
	 *
	 * @return array
	 **/
	protected function _filter_options() {
		if ( empty( $this->_cached_options ) ) {
			$this->_cached_options = apply_filters( self::TAGS_FILTER, $this->_options );
			usort(
				$this->_cached_options,
				function( $a, $b ) {
					$a = empty( $a['priority'] ) ? 10 : intval( $a['priority'] );
					$b = empty( $b['priority'] ) ? 10 : intval( $b['priority'] );
					return ( $a < $b ) ? -1 : 1;
				} 
			);
		}
		return $this->_cached_options;
	}

	/**
	 * get_template
	 *
	 * @since 2017-09-08 - description
	 * @uses PMC::render_template
	 *
	 * @author brandoncamenisch
	 *
	 * @version 2017-09-08 - PMCVIP-2831:
	 * - Adding method which sets up the templates and variables needed to render
	 * the correct tags.
	 *
	 * @return <html> PMC::render_template, <bool> false
	 *
	 **/
	public function get_template() {
		if ( false !== strpos( current_filter(), self::TAGS_IDENTIFIER )
			&& ! is_preview()
			&& ! is_admin()
		) {
			// Get the position based on current filter name
			$position = substr( strrchr( current_filter(), '-' ), 1 );
			if ( in_array( $position, $this->_positions, true ) ) {
				// Loop through the options
				foreach ( $this->_filter_options() as $option ) {
					if ( true === $option['enabled'] && in_array( $position, $option['positions'], true ) ) {
						$template = ! empty( $option['template'] ) ? $option['template'] : PMC_TAGS_ROOT . "/templates/{$option['slug']}.php";
						printf( '<!-- pmc-tags-%s %s -->', esc_html( $position ), esc_html( $option['name'] ) );
						\PMC::render_template(
							$template,
							[
								'option'   => $option,
								'position' => $position,
							],
							true
						);
						printf( '<!-- end pmc-tags-%s %s -->', esc_html( $position ), esc_html( $option['name'] ) );
					}
				}
			}
		} else {
			return false;
		}

	}

	/**
	 * get template v2 for the actions that contain underscore instead of hyphen;
	 *
	 * @return void
	 *
	 */
	public function get_template_v2() : void {

		if ( ! is_preview() && ! is_admin() ) {

			// Get the position based on current filter name
			$position = substr( strrchr( current_filter(), '_' ), 1 );

			if ( ! empty( $position ) && in_array( $position, (array) $this->_positions, true ) ) {
				// Loop through the options
				foreach ( $this->_filter_options() as $option ) {

					if ( true === $option['enabled'] && in_array( $position, (array) $option['positions'], true ) ) {
						$template = ! empty( $option['template'] ) ? $option['template'] : PMC_TAGS_ROOT . "/templates/{$option['slug']}.php";
						printf( '<!-- pmc-tags-%s %s -->', esc_html( $position ), esc_html( $option['name'] ) );
						\PMC::render_template(
							$template,
							[
								'option'   => $option,
								'position' => $position,
							],
							true
						);
						printf( '<!-- end pmc-tags-%s %s -->', esc_html( $position ), esc_html( $option['name'] ) );
					}
				}
			}
		}

	}

}
