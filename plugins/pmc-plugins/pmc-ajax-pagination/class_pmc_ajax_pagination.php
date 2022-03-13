<?php
/**
 * Main Class for the Ajax Pagination Plugin
 *
 * Extends 2 static function
 * 1. To Add a "Key" => "Pagination Function" Pair
 * 2. To show the data and Pagination with desired parameters
 *
 * The plugin needs only the above 2 things to plugin anywhere
 *
 * Handles one pagination per page but can be modified to handle multiple paginations per page
 *
 * @author Vicky Biswas
 * @since 20131010
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Ajax_Pagination {

	use Singleton;

	private $_collection = array();

	protected function __construct() {

		//Handling Ajax
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'template_include', array( $this, 'get_data' ) );
		add_filter( 'query_vars', array( $this, 'ajax_query_vars' ) );

	}

	/**
	 * Add Pagination Query vars to WP
	 *
	 * @author Vicky Biswas
	 * @since 20131010
	 *
	 * @param array $vars
	 * @return array
	 */
	public function ajax_query_vars( $vars ) {

		$vars[] = "pg-current";
		$vars[] = "pg-key";
		$vars[] = "pg-term_id";

		return $vars;

	}

	/**
	 * Function used to generate JSON for AJAX
	 *
	 * This is called on template_include
	 * so that AJAX calls are handles upwards in the chain
	 *
	 * @author Vicky Biswas
	 * @since 20131010
	 *
	 * @param array $template
	 */
	function get_data($template){

		$params = get_query_var( "pg" );

		if ( '' != $params  && preg_match("/^([0-9]+)\/([^\/]+)\/([0-9]+)/", $params, $matches) && isset( $_GET['json'] ) ){
			$current = $matches[1];
			$key = $matches[2];
			$term_id = $matches[3];

			$instance = self::get_instance();

			//Only work if relevant data passed
			if ( isset( $instance->_collection[$key] ) && ( intval( $current ) >= 0 ) && ( intval( $term_id ) >= 0 ) ) {

				//Fetch data
				$data = call_user_func($instance->_collection[$key], intval( $term_id ), intval( $current ) );
				$stories = $data['html'];

				//Create response
				$template = json_encode( array( 'html' => $stories ) );

				// clean up any warning or other message before returning ajax json data
				ob_clean();

				//output response
				header( "Content-Type: application/json" );
				echo $template;
				die();

			}


		}

		return $template;

	}

	/**
	 * Called on init action creates AJAX endpoint
	 *
	 * This creates the AJAX endpoint for batcache-friendly AJAX calls
	 *
	 * @author Vicky Biswas
	 * @since 20131016
	 */
	public function init () {
		add_rewrite_endpoint( 'pg', EP_PERMALINK );
		//add_rewrite_rule( '^pg/([0-9]+)/([a-z-]+)/([0-9]+)?$', 'index.php?pg-key=$matches[2]&pg-term_id=$matches[3]&pg-current=$matches[1]', 'top' );
	}

	/**
	 * Function used to add the functions which will be used for pagination along with their keys
	 *
	 * This should be used on or before the init action
	 * so that AJAX calls are handles seemlessly
	 *
	 * @author Vicky Biswas
	 * @since 20131010
	 *
	 * @param aray $args Contains an array of key value pairs
	 * Where key is the pagination Key
	 * Value is the reference to the function which returns html and noofpages
	 * as array('html'=>'what to show', 'pages'=>'no of pages')
	 * @param boolean $append Flag to append or overwrite list of paginations
	 * Default is Overwrite
	 * @return boolean true if added false if not
	 */
	public static function add( $args, $append = false ) {

		//No go if data is bad
		if ( !(is_array($args) && is_bool($append)) ) {
			return false;
		}

		//fetch or create instance if doesnt exist
		$instance = self::get_instance();

		//Clear collection if overwriting
		if (!$append){
			$instance->_collection = array();
		}

		//merge data
		$instance->_collection = array_merge($instance->_collection, $args);

		return true;
	}

	/**
	 * Function used to show html and pagination for a specific key
	 *
	 * This can be used anywhere on the theme
	 * to ouput data and pagination
	 * Parameters can be passes to modify the pagination
	 *
	 * @author Vicky Biswas
	 * @since 20131010
	 *
	 * @version 2017-05-20 Added $print_output argument to return output
	 *
	 * @param bool $print_output Echo output if true else return. Default is true.
	 * @param array $args Contains an array of key value pairs
			'key' => key whose function to use as added by the "add" function
			'nav_type' => array having one or multiple of prev, numeric, next
			'current' => number if some different page needs to be shown
			'class' => a name you want to use for cosmetic purposes
			'term_id' => id associated used to group data
			'top_adjust' => no of pixels to adjust the scroll to top with
	 *
	 * @return string|WP_Error
	 */
	public static function html( $args, $print_output = true ) {

		$defaults = 	array(
			'key' => '',
			'nav_type' => array('numeric'),
			'current' => 1,
			'pages' => 1,
			'class' => '',
			'term_id' => get_the_ID(),
			'top_adjust' => 0
		);

		$args = array_merge( $defaults, $args );

		$key = $args['key'];
		$class = $args['class'];
		$term_id = $args['term_id'];
		$current = $args['current'];
		$nav_type = $args['nav_type'];
		$top_adjust = $args['top_adjust'];
		$pages = $args['pages'];

		$instance = self::get_instance();
		if ( !isset($instance->_collection[$key]) ){
			return  new WP_Error( 'Pagination Error', 'Key for AJAX Pagination Doesnt Exist' );
		}

		$class = trim( $class );
		$classdata = '';
		$classnav = '';

		$params = get_query_var( "pg" );

		if ( '' != $params  && preg_match("/^([0-9]+)\/([^\/]+)\/([0-9]+)/", $params, $matches)){
			$current = $matches[1];
			$key = $matches[2];
			$term_id = $matches[3];
		}

		if ( '' != $class ) {
			$classdata = ' ' . $class . '-data';
			$classnav = ' ' . $class . '-nav';
		}

		$data = call_user_func( $instance->_collection[ $key ], $term_id, $current );

		if ( $print_output !== true && empty( $data['html'] ) ) {
			return '';
		}

		$pages = intval( $data['pages'] );
		$current = intval( $current );
		if ( $current < 1 || $current > $pages ) {
			$current = 1;
		}
		$term_id = intval( $term_id );
		$top_adjust = intval( $top_adjust );

		if ( !is_array( $nav_type ) || count( $nav_type ) == 0 ){
			return  new WP_Error( 'Pagination Error', 'Incorrect AJAX Pagination Type' );
		}

		$args = compact('key', 'nav_type', 'current', 'pages', 'class', 'term_id');

		//Enqueue Javascripts
		wp_enqueue_script( 'history', plugins_url( 'js/jquery.history.js', __FILE__  ), array( 'jquery' ), '1.8b2', true);
		wp_enqueue_script( 'ajax-pagination', plugins_url( 'js/ajax-pagination.js', __FILE__  ), array( 'jquery' ), '1', true);

		//Add Localize Data
		wp_reset_query();
		wp_localize_script('ajax-pagination', 'pmc_ajax_pagination', array(
			'url' => get_permalink(),
			'current' => $current,
			'total' => $pages,
			'key' => $key,
			'term_id' => $term_id,
			'dataclass' => trim($classdata),
			'navclass' => trim($classnav),
			'topadjust' => intval($top_adjust) )
		);

		// Generate HTML
		$html = '';
		$html .= '<div class="'. esc_attr( "pmc-ajax-pagination-{$key}-data{$classdata}" ) . '"	>';
		$html .=  $data['html'];
		$html .=  '</div>';

		// only show pagination if there are more than 2 pages
		if ( $pages > 1 ) {
			$html .=  '<ul class="'. esc_attr( "pagination pmc-ajax-pagination-{$key}-nav{$classnav}" ) . '" >';
			$html .=  self::pagination( $args );
			$html .=  '</ul>';
		}

		$allowed = wp_kses_allowed_html( 'post' );
		$allowed['a']['data-pagination'] = true;
		$allowed['img']['data-original'] = true;
		$allowed['script']['type'] = true;
		$allowed['script']['src'] = true;

		$html = wp_kses( $html, $allowed );

		if ( $print_output !== true ) {

			return $html;

		}

		echo $html;

	}

	/**
	 * Function used to generate pagination and its urls
	 *
	 * Used Internally
	 *
	 * @author Vicky Biswas
	 * @since 20131010
	 *
	 * @param aray $args Contains an array of key value pairs
			'key' => key whose function to use as added by the "add" function
			'nav_type' => array having one or multiple of prev, numeric, next
			'current' => number if some different page needs to be shown
			'class' => a name you want to use for cosmetic purposes
			'term_id' => id associated used to group data
			'pages' => total number of Pages
	 */
	private function pagination( $args ){
		$markup = '';

		//Sanitizing Values
		$key = esc_attr($args['key']);
		$term_id = intval($args['term_id']);

		wp_reset_query();

		//Generate buttons
		foreach ( $args['nav_type'] as $type ) {

			switch ( $type ) {
				case 'prev':
					//Previous button
					$class = '';
					$pos = $args['current'] - 1;
					if ($pos <= 0){
						$pos = 1;
						$class = ' current';
					}

					$markup .= '<li><a class="' . esc_attr( "page-numbers prev prev{$class}" ) . '" href="' . esc_url( get_permalink() . "pg/{$pos}/{$key}/{$term_id}" ) . '" data-pagination="' . esc_attr( json_encode( compact( 'pos' ) ) ) . '" ><i class="icon-caret-left"></i></a></li>';

					break;

				case 'numeric':
					//generate the sequence
					for ($pos=1; $pos <= $args['pages']; $pos++){

						$class = "page-numbers nav{$pos}";

						if( $args['current'] == $pos ){
							$class .= ' current';
						}

						$markup .= '<li><a href="' . esc_url( get_permalink() . "pg/{$pos}/{$key}/{$term_id}" ) . '" class="' . esc_attr( $class) . '" data-pagination="' . esc_attr( json_encode( compact( 'pos' ) ) ) . '" >' . esc_html( $pos ) . '</a></li>';
					}
					break;

				case 'next':
					//Next Button
					$class = '';
					$pos = $args['current'] + 1;
					if ($pos > $args['pages']){
						$pos = $args['pages'];
						$class = ' current';
					}
					$markup .= '<li><a class="' . esc_attr( "next page-numbers next{$class}" ) . '" href="' . esc_url( get_permalink() . "pg/{$pos}/{$key}/{$term_id}" ) . '" data-pagination="' . esc_attr( json_encode( compact( 'pos' ) ) ) . '" ><i class="icon-caret-right"></i></a></li>';

					break;

			}
		}

		return $markup;

	}

}

?>
