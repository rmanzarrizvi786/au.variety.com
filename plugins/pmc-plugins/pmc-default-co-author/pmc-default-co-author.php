<?php
/*
Plugin Name: PMC Default Co-Author
Plugin URI: http://www.pmc.com
Description: Adds a default co-author to various post types. This plugin relies on co-authors plus so make sure thats loaded first. You can instantiate this plugin like any PMC plugin using `pmc_load_plugin( 'pmc-default-co-author', 'pmc-plugins' );`
Author: PMC
License: PMC Proprietary. All rights reserved.
*/

wpcom_vip_load_plugin( 'pmc-global-functions', 'pmc-plugins' );

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Default_Co_Author {

	use Singleton;

	const DEFAULT_USER = 'PMC';
	const FILTER_POST_TYPES = 'pmc_default_co_author_post_types';
	const OPTION_NAME = 'pmc_default_co_author';

	public $add_default = false;
	public $author;
	public $post_types = array( 'page' );


	/**
	*
	* Setup our actions and filters
	* @return void
	*
	*/
	protected function __construct() {
		add_action( 'admin_init', array( $this, 'init' ), 9 );
		add_action( 'admin_init', array( $this, 'get_author' ) );
		add_action( 'admin_head', array( $this, 'add_default' ) );

		add_filter( 'coauthors_default_author', array( $this, 'filter_author' ) );
		add_filter( 'pmc_global_cheezcap_options', array( $this, 'add_option_to_cheez' ) );
	}

	/**
	*
	* Instantiate class variables and setup data
	* @return void
	*
	*/
	public function init()
	{
		$this->post_types = apply_filters( self::FILTER_POST_TYPES, ( array ) $this->post_types );
		$this->author = PMC_Cheezcap::get_instance()->get_option( self::OPTION_NAME, false );
	}


	/**
	*
	* Check if the current screen is in our whitelisted post types to accept a default author.
	* @return bool
	*
	*/
	public function add_default()
	{
		$screen = get_current_screen();
		if ( is_admin() && function_exists( 'get_current_screen' ) && in_array( $screen->id, $this->post_types ) ) {
				return $this->add_default = true;
		} else {
			return $this->add_default = false;
		}
	}


	/**
	* get_author
	* Gets the coauthor global object
	* Checks if its a standard user slug and if not grabs the user_nicename from co-authors plus
	* @return WP_User object or co-authors user object
	*/
	public function get_author()
	{
		global $coauthors_plus;

		if ( $this->author ) {
			$author = get_user_by( 'slug', $this->author );
		}

		if ( ! $author && $this->author && is_a( $coauthors_plus, 'coauthors_plus' ) ) {
			$author = $coauthors_plus->get_coauthor_by( 'user_nicename', $this->author );
		}

		return $this->author = $author;
	}


	/**
	*
	* Filter the author by author slug from the value in our global PMC options page.
	* @note If by chance the author comes back 0 then co-authors will default to the current author
	* @return object WP_User
	*
	*/
	public function filter_author()
	{
		if ( is_object( $this->author ) && $this->add_default ) {
			return $this->author;
		} else {
			return wp_get_current_user();
		}
	}


	/**
	*
	* Add an option with a default value to our global PMC options Cheez page.
	* @param array $cheezcap_options
	* @return array $cheezcap_options
	*
	*/
	public function add_option_to_cheez( $cheezcap_options = array() )
	{
		$cheezcap_options[] = new CheezCapTextOption(
			'Enter a username to use as a default for this site',
			'Username must be the users slug',
			self::OPTION_NAME,
			self::DEFAULT_USER
		);
		return $cheezcap_options;
	}


}

PMC_Default_Co_Author::get_instance();
