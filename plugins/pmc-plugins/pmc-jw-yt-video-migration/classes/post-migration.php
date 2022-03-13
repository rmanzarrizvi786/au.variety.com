<?php

namespace PMC\JW_YT_Video_Migration;

use PMC\Google_Amp\Single_Post;

/**
* Post_Migration | post-migration.php
*
* @author brandoncamenisch
* @version 2017-04-10 brandoncamenisch - feature/PMCBA-363:
* - Overrides the JW shortcodes and replaces it with a YouTube shorcode.
*
**/


/**
* Post_Migration
*
* @since 2017-04-10
*
* @version 2017-04-10 - brandoncamenisch - feature/PMCBA-363:
* - Overrides the JW shortcodes and replaces it with a YouTube shorcode.
*
**/

use \PMC\Global_Functions\Traits\Singleton;

class Post_Migration {

	use Singleton;

	protected $_map = false;

	protected function __construct() {
		$this->get_option( \PMC\JW_YT_Video_Migration\Cheez_Options::get_option() );
		add_action( 'wp_loaded', array( $this, 'replace_shortcode' ) );
	}


	/**
	* get_option | post-migration.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Gets the mapped option and sets it
	*
	* @param map array
	* @return this->_map array
	**/
	public function get_option( $map ) {
		if ( is_array( $map ) ) {
			$this->_map = $map;
		}
		return $this->_map;
	}


	/**
	* key_exists | post-migration.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Checks the shortcode attributes against our mapping array for matches
	*
	* @param atts array of shorcode attributes
	* @return this->_key string is the key we want for our array mapping
	**/
	public function key_exists( $atts ) {
		$key = false;

		if ( ! isset( $atts[0] ) ) {
			return $key;
		}
		$atts[0] = trim( $atts[0] );
		// Explode by `-` jw keys are something like [jwplatform Sd07vlvI] [jwplatform 8IjY8sML-5CUXIEFs]
		$arr = explode( '-', $atts[0] );

		// If we have a key match greater than because there could be and edge case of two in a single array
		if ( count( array_intersect_key( array_flip( $arr ), $this->_map ) ) >= 1 ) {
			$key = trim( $arr[0] );
		}

		return $key;
	}


	/**
	* validates | post-migration.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Validates a number of checks on the url and if the key exists that we need
	* for our video mapping to find the correct reference for that video.
	*
	* @param atts array of shortcode attributes
	* @return bool true/false
	**/
	public function validates( $atts ) {
		// Check if map is usable
		if ( ! $this->_map || ! is_array( $this->_map ) ) {
			$validates = false;
		}

		// Check if key exists in map array
		$key = $this->key_exists( $atts );

		if ( ! $key ) {
			$validates= false;
		} elseif ( $key ) {
			$url = "https://www.youtube.com/watch?v={$this->_map[ $key ]}";
			if ( false !== filter_var( $url, FILTER_VALIDATE_URL ) ) {
				$validates = array( 'valid' => true, 'url' => (string) $url );
			}
		}

		return $validates;
	}


	/**
	* replace_shortcode | post-migration.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Removes and replaces the jwplayer and jwplatform shortcodes.
	* @version 2017-04-14 - feature/PMCBA-363:
	* - Adding a check for pmc-dev group
	**/
	public function replace_shortcode() {
		remove_shortcode( 'jwplatform' );
		remove_shortcode( 'jwplayer' );
		add_shortcode( 'jwplatform', array( $this, 'output_shortcode' ) );
		add_shortcode( 'jwplayer', array( $this, 'output_shortcode' ) );
	}


	/**
	* output_shortcode | post-migration.php
	*
	* @since 2017-04-10
	*
	* @author brandoncamenisch
	* @version 2017-04-10 - feature/PMCBA-363:
	* - Outputs the shortcode override using a YouTube URL of the video
	*
	* @param atts array of shortcode attributes
	* @return mixed shortcode/false on failure
	**/
	public function output_shortcode( $atts ) {

		$valid = $this->validates( $atts );

		if ( is_array( $valid ) && true === $valid['valid'] && isset( $valid['url'] ) ) {
			return do_shortcode( "[youtube={$valid['url']}]" );
		} elseif ( function_exists( 'jwplayer_shortcode_handle' ) ) {

			if ( \PMC::is_amp() ) {
				return Single_Post::get_amp_jwplayer( $atts );
			}

			return jwplayer_shortcode_handle( $atts );
		} else {
			return false;
		}
	}

}

//EOF
