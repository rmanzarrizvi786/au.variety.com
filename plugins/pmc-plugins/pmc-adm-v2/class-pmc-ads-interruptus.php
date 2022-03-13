<?php

/**
 * This class handles setting up endpoints and rendering pages for the endpoints
 * for intrusive ads like Prestitial & Interstitial
 *
 * @author Amit Gupta <agupta@pmc.com>
 * @since 2014-03-19
 * @version 2014-07-28 Amit Gupta - added body_class() hooked on to 'body_class' filter
 */

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Ads_Interruptus {

	use Singleton;

	/**
	 * @var integer Number of seconds for which cache should live
	 */
	const cache_life = 300;	//5 minutes

	/**
	 * @var array Ad Page Endpoints
	 */
	protected $_ad_types = array(
		'is_home' => array( 'prestitial' ),
		'is_single' => array( 'interstitial' ),
	);


	/**
	 * @var array URL endpoints for all interrupting ads
	 */
	protected $_endpoints = array();

	/**
	 * @var string Current Endpoint
	 */
	public $current_endpoint = '';

	// we need to track if scripts has been render do we don't output duplicate code
	protected $_enqueue_stuff_rendered = false;

	/**
	 * This function fires off when the object of this class is created.
	 * Hook up stuff in here
	 *
	 * @return void
	 */
	protected function __construct() {

		add_action( 'init', array( $this, 'action_init' ), 20);

	}

	public function action_init() {

		add_action( 'admin_head', array( $this, 'enqueue_admin_stuff' ), 2 );
		add_action( 'pmc-tags-top', array( $this, 'action_interruptus') );

	}

	/**
	 * Common function to return cookiename
	 *
	 * @param $name
	 *
	 * @return string
	 */
	protected function make_cookie_name( $name  ) {
		//use string pmc-adi- to make sure we know its our adi cookie and also add same string as salt for md5
		return 'pmc-adi-' . md5( "pmc-adi-" . $name );
	}

	/**
	 * This function accepts a multi-dimensional array and returns a single
	 * array containing values from all dimensions
	 *
	 * @param array $arr Array from which values are to be returned
	 * @return array
	 */
	public function get_array_values( $arr ) {
		$values = array_values( $arr );

		if ( empty( $values ) ) {
			return array();
		}

		$all_values = array();

		$count = count( $values );

		for ( $i = 0; $i < $count; $i++ ) {
			$all_values = array_merge( $all_values, (array) $values[ $i ] );
		}

		unset( $count, $values );

		return $all_values;
	}

	/**
	 * Extract the endpoints from ad types and return them
	 *
	 * @return array
	 */
	protected function _get_endpoints() {
		if ( ! empty( $this->_endpoints ) ) {
			return $this->_endpoints;
		}

		$this->_endpoints = array_filter( array_unique( $this->get_array_values( $this->_ad_types ) ) );

		sort( $this->_endpoints );

		return $this->_endpoints;
	}



	/**
	 * This function provides quick conditional methods to check the
	 * type of current endpoint.
	 * Example: $this->is_prestitial()
	 *
	 * @return boolean
	 */
	public function __call( $name, $args = array() ) {
		$name_parts = explode( '_', $name );

		if ( $name_parts[0] !== 'is' ) {
			throw new LogicException( 'Method ' . __CLASS__ . '::' . $name . '() does not exist' );
		}

		array_shift( $name_parts );	//bump off 'is'

		return $this->is_ad_interruptus( implode( '_', $name_parts ) );
	}

	/**
	 * This function is a conditional method which checks whether the current
	 * endpoint is same as the one passed to it or not. Returns TRUE if endpoint
	 * passed matches current endpoint else FALSE.
	 *
	 * @param string $name Endpoint name to check
	 * @return boolean
	 */
	public function is_ad_interruptus( $name = '' ) {
		if ( empty( $name ) || ! in_array( $name, $this->_get_endpoints() ) ) {
			return false;
		}

		if ( array_key_exists( $name, $GLOBALS['wp_query']->query_vars ) ) {
			return true;
		}

		return false;
	}

	/**
	 * This function returns the current endpoint if there is any else an empty string
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		if ( empty( $this->_ad_types ) ) {
			return '';
		}

		$endpoints = $this->_get_endpoints();

		if ( ! empty( $this->current_endpoint ) && in_array( $this->current_endpoint, $endpoints ) ) {
			return $this->current_endpoint;
		}

		$count = count( $endpoints );

		for ( $i = 0; $i < $count; $i++ ) {
			if ( $this->is_ad_interruptus( $endpoints[ $i ] ) ) {
				$this->current_endpoint = $endpoints[ $i ];
				return $endpoints[ $i ];
			}
		}

		unset( $count, $endpoints );

		return '';
	}


	/**
	 * This function enqueues javascripts in admin head
	 *
	 * @return void
	 */
	public function enqueue_admin_stuff() {
		if ( empty( $_GET['page'] ) || $_GET['page'] !== 'ad-manager' ) {
			return;
		}

		//load endpoint js
		return $this->_load_admin_endpoint_stuff();
	}

	/**
	 * This function enqueues javascripts in page head
	 *
	 * @return void
	 */
	public function enqueue_stuff() {
		// do not interrupt page in preview mode
		if ( is_preview() ) {
			return;
		}

		// prevent script from rendering multiple time
		if ( !empty( $this->_enqueue_stuff_rendered ) ) {
			return;
		}

		$this->_enqueue_stuff_rendered = true;

		$current_endpoint = $this->get_current_endpoint();

		if ( empty( $current_endpoint ) ) {
			//load non endpoint js
			return $this->_load_non_endpoint_stuff();
		}

		//load endpoint js
		return $this->_load_endpoint_stuff( $current_endpoint );
	}
	/**
	 * This function outputs Javascript for the endpoint name passed to it.
	 * If no endpoint name is passed to it then it outputs Javascript for current endpoint.
	 *
	 * @param string $endpoint Endpoint name whose Javascript output is desired
	 * @return void
	 */
	protected function _load_endpoint_stuff( $endpoint = '' ) {
		$endpoint = ( empty( $endpoint ) ) ? $this->get_current_endpoint() : $endpoint;

		if ( empty( $endpoint ) ) {
			return;
		}

		$ad = PMC_Ads::get_instance()->get_ads_to_render( $endpoint );

		if ( ! empty( $ad ) && is_array( $ad ) ) {
			$ad = array_shift( $ad );
		}

		if ( empty( $ad ) || ! is_array( $ad ) ) {
			// there is no ad, but ad end point is call to render
			// let's force a default so script can execute properly
			$ad_duration = 1; // since we have no ad, let's not delay the page, value need to be non-zero
			$ad_time_gap = 5;
		} else {
			$ad_duration = $ad['duration'];
			$ad_time_gap = $ad['timegap'];
		}

		$template_path = PMC_ADM_DIR . '/templates/ads/interruptus/template-js-' . sanitize_file_name( $endpoint ) . '.php';

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		//load script for endpoint page and echo it
		$html = PMC::render_template( $template_path, array(
			'home_url' => untrailingslashit( get_home_url() ),
			'duration' => $ad_duration,
			'time_gap' => apply_filters( 'pmc_adm_ads_interruptus_time_gap', ( intval( $ad_time_gap ) * 3600 ), $endpoint ),	//timegap has to be in seconds
		) );

		if ( ! empty( $html ) ) {
			echo $html;
		}

		unset( $html, $template_path, $ad );
	}

	/**
	 * This function outputs Javascript for non-endpoint pages.
	 *
	 * @return void
	 */
	protected function _load_non_endpoint_stuff() {
		//load script for rest of the site
		foreach ( $this->_ad_types as $key => $endpoints ) {
			$endpoints = $this->get_endpoints_with_active_ads( $endpoints );

			if ( empty( $endpoints ) ) {
				continue;
			}

			$function_name = apply_filters( 'pmc_adm_ads_interruptus_' . $key, $key );

			if ( empty( $function_name ) || ! function_exists( $function_name ) || ! $function_name() ) {
				continue;
			}

			$template_path = PMC_ADM_DIR . '/templates/ads/interruptus/template-js-' . sanitize_file_name( $key ) . '.php';

			if ( file_exists( $template_path ) ) {
				//load script for endpoint page and echo it
				$html = PMC::render_template( $template_path, array(
					'home_url' => untrailingslashit( get_home_url() ),
					'endpoints' => $endpoints,
				) );

				if ( ! empty( $html ) ) {
					echo $html;
				}

				unset( $html );
			}

			unset( $template_path, $function_name );
		}
	}

	/**
	 * This function builds a list of endpoints which have active ad campaigns
	 * assigned to them. It accepts an array of endpoints to check, if none are passed
	 * then it will check against all endpoints.
	 *
	 * @param array $endpoints Array containing endpoints to check for active ad campaigns
	 * @return array
	 */
	public function build_endpoints_with_active_ads( array $endpoints = array() ) {
		if ( empty( $endpoints ) ) {
			$endpoints = $this->_get_endpoints();

			if ( empty( $endpoints ) ) {
				return array();
			}
		}

		$endpoints = array_filter( array_unique( $endpoints ) );

		$count = count( $endpoints );

		$endpoints_with_active_ads = array();

		for ( $i = 0; $i < $count; $i++ ) {
			$html = PMC_Ads::get_instance()->get_ads_to_render( $endpoints[ $i ] );

			if ( ! empty( $html ) ) {
				$endpoints_with_active_ads[] = $endpoints[ $i ];
			}

			unset( $html );
		}

		return array_unique( $endpoints_with_active_ads );
	}

	/**
	 * This function returns a list of endpoints which have active ad campaigns
	 * assigned to them. It accepts an array of endpoints to check, if none are passed
	 * then it will check against all endpoints.
	 *
	 * @param array $endpoints Array containing endpoints to check for active ad campaigns
	 * @return array
	 */
	public function get_endpoints_with_active_ads( array $endpoints = array() ) {
		if ( empty( $endpoints ) ) {
			$endpoints = $this->_get_endpoints();

			if ( empty( $endpoints ) ) {
				return array();
			}
		}

		$endpoints = array_filter( array_unique( $endpoints ) );

		// NOTE: Need to take into consideration of devices for cache key since
		// get_ads_to_render return active ads base on current device via get_current_applicable_device
		$applicable_devices = array();
		$applicable_devices[] = PMC_Ads::get_instance()->get_current_applicable_device( array( 'Desktop' ) );
		$applicable_devices[] = PMC_Ads::get_instance()->get_current_applicable_device( array( 'Mobile' ) );
		$applicable_devices[] = PMC_Ads::get_instance()->get_current_applicable_device( array( 'Tablet' ) );

		// Since PMC_Cache is applying md5, so we want raw data for key
		$cache_key = 'ads_interruptus_endpoints_with_active_ads_' . serialize( $endpoints ) . implode('-', $applicable_devices );

		if ( ! class_exists( 'PMC_Cache' ) ) {
			throw new Exception( 'PMC_Cache does not exist' );
		}

		$pmc_cache = new PMC_Cache( $cache_key );

		$endpoints_with_active_ads = $pmc_cache->expires_in( self::cache_life )
												->updates_with( array( $this, 'build_endpoints_with_active_ads' ), array( $endpoints ) )
												->get();

		return $endpoints_with_active_ads;
	}

	/**
	 * This function runs on 'template_include' hook and returns path of the
	 * appropriate template for the current endpoint
	 *
	 * @return string Template path
	 */
	public function load_template( $template = ''  , $current_endpoint = '' ) {
		if ( empty( $this->_ad_types ) ) {
			return $template;
		}

		$ad_page_template = '';


		if ( ! empty( $current_endpoint ) ) {
			//allow override on template name by theme
			$endpoint_template = apply_filters( 'pmc_adm_ads_interruptus_template', strtolower( 'template-overlay-' . $current_endpoint ) );

			//load the template for current endpoint if it exists in current theme
			$ad_page_template = sprintf( '%s/plugins/templates/pmc-adm/%s.php', untrailingslashit( STYLESHEETPATH ), sanitize_file_name( trim( $endpoint_template, '/' ) ) );

			if ( ! file_exists( $ad_page_template ) ) {
				$ad_page_template = '';		//free up var

				//check in parent theme, if any
				$ad_page_template = sprintf( '%s/plugins/templates/pmc-adm/%s.php', untrailingslashit( TEMPLATEPATH ), sanitize_file_name( trim( $endpoint_template, '/' ) ) );

				if ( ! file_exists( $ad_page_template ) ) {
					$ad_page_template = '';		//free up var and let it cascade down
				}
			}

			if ( empty( $ad_page_template ) ) {
				//no theme specific template, lets see if we have a generic one in plugin templates
				$ad_page_template = PMC_ADM_DIR . '/templates/ads/interruptus/template-overlay-' . sanitize_file_name( $current_endpoint ) . '.php';

				if ( ! file_exists( $ad_page_template ) ) {
					//bugger, no template, someone messed up!
					$ad_page_template = '';
				}
			}
		}

		if ( ! empty( $ad_page_template ) ) {
			$ad = PMC_Ads::get_instance()->get_ads_to_render( $current_endpoint );

			if ( ! empty( $ad ) && is_array( $ad ) ) {
				$ad = array_shift( $ad );
			}

			$ad_duration = empty( $ad['duration'] )? 8 : intval( $ad['duration'] );

			$template = PMC::render_template( $ad_page_template , array(
				'duration' => $ad_duration,
				'css_class' => $ad['css-class'],
			));
		}
		echo $template;
	}


	/**
	 * This function outputs Javascript on the plugin's admin page.
	 *
	 * @return void
	 */
	protected function _load_admin_endpoint_stuff() {
		$template_path = PMC_ADM_DIR . '/templates/ads/interruptus/template-js-admin.php';

		if ( ! file_exists( $template_path ) ) {
			return;
		}

		//load common script for endpoint page and echo it
		$html = PMC::render_template( $template_path, array(
			'locations' => $this->_get_endpoints(),
		) );

		if ( ! empty( $html ) ) {
			echo $html;
		}

		unset( $html, $template_path );
	}



	/**
	 * @return bool
	 * returns true or cals if the current page we are on is a page that
	 * can be interrupted with a prestitial or interstitial ad.
	 * an example of a page that can't be interrupted is a feed. An example of a
	 * page that can be interrupted is an article page.
	 */
	public function can_interrupt() {

		if ( is_home()  || is_single()) {
			return true;
		}

		if ( is_admin() || is_404() || is_feed() ) {
			return false;
		}

		return true;
	}
	/**
	 * returns the show interrupt template name.
	 * the plugin has a show-interrupt.php template
	 * but each LOB can decide to override it.
	 * the reason being that each LOB has a different ID for it's content area
	 * since we are hiding and showing content areas for the ads, the IDs will differ
	 * the shared plugin was implemented with the variety theme content ID.
	 */
	public function get_show_interrupt_template(){

		$default_show_interrupt_template = apply_filters( 'pmc_adm_show_interrupt_template_name', PMC_ADM_DIR . '/templates/show-interrupts.php');

		//first we check in the current theme
		$show_interrupt_template = sprintf( '%s/plugins/templates/pmc-adm/show-interrupts.php', untrailingslashit( STYLESHEETPATH ) );

		if( file_exists( $show_interrupt_template) ){
			//yes! the theme has defined it, we should return the file name defined by the theme.
			return $show_interrupt_template;
		}else{
			$show_interrupt_template = '';
		}
		//if we got here then we should check the theme parent if there is such a thing
		$show_interrupt_template = sprintf( '%s/plugins/templates/pmc-adm/show-interrupts.php', untrailingslashit( TEMPLATEPATH ) );

		if( file_exists( $show_interrupt_template) ){
			//yes! the  parebttheme has defined it, we should return the file name defined by the theme.
			return $show_interrupt_template;
		}else{
			$show_interrupt_template = '';
		}

		//if we got here then there was no template in the theme or the parent theme so we are left with
		// one option. the plugin directory.
		return $default_show_interrupt_template;

	}

	/**
	 * This function lookup current ads endpoints and shows the thickbox if an endpoint is set for the page.
	 *
	 * @return void
	 */
	public function action_interruptus() {

		// do not interrupt page in preview mode
		if ( is_preview() || ! $this->can_interrupt() ) {
			return;
		}
		$current_endpoint = $this->get_current_endpoint();

		// we are at ad endpoint? no need to continue
		if ( !empty( $current_endpoint ) ) {
			return;
		}


		//load script for rest of the site
		foreach ( $this->_ad_types as $key => $endpoints ) {
			$endpoints = $this->get_endpoints_with_active_ads( $endpoints );

			if ( empty( $endpoints ) ) {
				continue;
			}

			$function_name = apply_filters( 'pmc_adm_ads_interruptus_' . $key, $key );

			if ( empty( $function_name ) || ! function_exists( $function_name ) || ! $function_name() ) {
				continue;
			}


			foreach ( $endpoints as $endpoint ) {

				//Check if cookie is set or not
				$cookie_name = $this->make_cookie_name( $key . $endpoint );

				$this->load_template( '' , $endpoint );

				// we need to be able to override this template from LOBs
				$show_interrupt_template =  $this->get_show_interrupt_template();

				if( file_exists( $show_interrupt_template ) ){

					$ad = PMC_Ads::get_instance()->get_ads_to_render( $endpoint );

					if ( ! empty( $ad ) && is_array( $ad ) ) {
						$ad = array_shift( $ad );
					}

					$time_gap = empty( $ad['timegap'] ) ? 0 : intval( $ad['timegap'] ) *3600;

					PMC::render_template( $show_interrupt_template , array(
						'cookie_name' => $cookie_name,
						'endpoint' 	=> $endpoint,
						'time_gap' => apply_filters( 'pmc_adm_ads_interruptus_time_gap', $time_gap , $endpoint ),	//timegap has to be in seconds

					), true );
				}

			}
		}
	} // function


//end of class
}


//EOF
