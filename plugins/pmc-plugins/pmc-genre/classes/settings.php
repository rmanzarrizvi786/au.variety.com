<?php
/*
 * This class handles the settings page of PMC Genre plugin in wp-admin
 *
 * @author Amit Gupta <agupta@pmc.com>
 */

namespace PMC\Genre;

use \PMC;


class Settings extends Base {

	/**
	 * @var string Option key name under which genre->category and genre->vertical mappings are stored
	 */
	const OPTION_NAME = 'pmc-genre-mappings';

	/**
	 * @var PMC\Genre\Taxonomy Object of the PMC\Genre\Taxonomy class
	 */
	protected $_taxonomy;

	/**
	 * @var array Contains admin notices to be shown
	 */
	protected $_notices = array();

	/**
	 * @var array Contains the genre->category and genre->vertical mappings
	 */
	protected $_terms_map = null;

	/**
	 * @var array Contains the genre terms which are already mapped and no longer available for mapping
	 */
	protected $_mapped_genres = null;

	/**
	 * @var array Contains the genre terms which are not mapped and are available for mapping
	 */
	protected $_unmapped_genres = null;

	/**
	 * @var array Contains the nonce action and name
	 */
	protected $_nonce = array(
		'action' => '',
		'name'   => '',
	);


	/**
	 * Initialization function called by parent::get_instance() when
	 * object of this class is created
	 */
	protected function __construct() {
		$this->_taxonomy = Taxonomy::get_instance();
		$this->_nonce = array(
			'action' => self::PLUGIN_ID . '-action',
			'name'   => self::PLUGIN_ID . '-nonce',
		);

		$this->_setup_hooks();
	}

	/**
	 * Function to setup all action/filter hooks needed for the objective
	 * of current class
	 */
	protected function _setup_hooks() {
		//call function to add options menu item
		add_action( 'admin_menu', array( $this, 'add_menu' ) );

		//our form handler
		add_action( 'admin_init', array( $this, 'handle_settings_form' ) );

		//notice/messages handler
		add_action( 'admin_notices', array( $this, 'out_notices' ) );

		//setup our script enqueuing for wp-admin
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_stuff' ) );
	}

	/**
	 * This function checks whether current page is our plugin page in wp-admin
	 * or not. If it is our page then it returns TRUE else FALSE.
	 * This function is needed as we need to check for our page on 'admin_init'
	 * or earlier and WP_Screen object is created after 'admin_init'.
	 */
	protected function _is_our_page() {
		if ( ! is_admin() ) {
			//not in wp-admin so definitely not our page
			return false;
		}

		if ( isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ) {
			//if $_GET['page'] is set then should not go in ELSE irrespective of value
			if ( sanitize_title( $_GET['page'] ) == self::PLUGIN_ID . '-page' ) {
				//our plugin page
				return true;
			}
		} elseif ( strpos( $_SERVER['REQUEST_URI'], '?' ) !== false && strpos( $_SERVER['REQUEST_URI'], 'page=' . self::PLUGIN_ID . '-page' ) !== false ) {
			//our plugin page
			return true;
		}

		//not our page
		return false;
	}

	/**
	 * This function weeds out all the padding added by javascript
	 * when converting our array to string and also sanitizes the data.
	 */
	protected function _clean_payload( array $map = array() ) {
		if ( empty( $map ) ) {
			return $map;
		}

		$clean_map = array();

		/*
		 * Ideally this would have one or two iterations at max
		 */
		foreach ( $map as $type => $collection ) {
			//weed out all the padding
			$collection = array_map( 'array_filter', array_filter( $collection ) );

			//sanitize data
			foreach ( $collection as $key => $items ) {
				$collection[ $key ] = array_map( 'sanitize_text_field', $items );
			}

			$clean_map[ $type ] = $collection;
		}

		return $clean_map;
	}

	/**
	 * This function accepts a message which is to be displayed on plugin page
	 * in wp-admin
	 */
	protected function _add_admin_notice( $message, $type = 'error' ) {
		if ( empty( $message ) || ! is_string( $message ) ) {
			return;
		}

		$key = md5( $message );

		if ( array_key_exists( $key, $this->_notices ) ) {
			return;
		}

		$type = ( $type !== 'success' ) ? 'error' : 'updated';

		$this->_notices[ $key ] = array(
			'type' => $type,
			'message' => $message
		);

		return true;
	}

	/**
	 * This function is called on admin_notices and it displays all notices/messages
	 * for the current plugin
	 */
	public function out_notices() {
		if ( ! $this->_is_our_page() || empty( $this->_notices ) || ! is_array( $this->_notices ) ) {
			return false;
		}

		foreach ( $this->_notices as $notice ) {
			printf( '<div class="%s"><p>%s</p></div>', esc_attr( $notice['type'] ), esc_html( $notice['message'] ) );
		}
	}

	/**
	 * This function returns an array containing genres which have
	 * been mapped to any category/vertical.
	 *
	 * @return array
	 */
	public function get_mapped_terms( $return_raw = false ) {
		/*
		 * If we already have the data in the class var then return that
		 * instead of processing the data again.
		 */
		if ( $return_raw === false && is_array( $this->_mapped_genres ) ) {
			return $this->_mapped_genres;
		} elseif ( $return_raw === true && is_array($this->_terms_map ) ) {
			return $this->_terms_map;
		}

		$this->_terms_map = get_option( self::OPTION_NAME, false );

		if ( empty( $this->_terms_map ) ) {
			$this->_terms_map = array();
		}

		if ( $return_raw === true ) {
			return $this->_terms_map;
		}

		$terms = array();

		if ( ! empty( $this->_terms_map['categories'] ) ) {
			$terms = array_merge( $terms, $this->_terms_map['categories'] );
		}

		if ( ! empty( $this->_terms_map['verticals'] ) ) {
			$terms = array_merge( $terms, $this->_terms_map['verticals'] );
		}

		$this->_mapped_genres = array();

		if ( empty( $terms ) ) {
			return $this->_mapped_genres;
		}

		/*
		 * Can't use array_merge() here because keys are numeric and
		 * array_merge() eats them up while we must preserve them
		 */
		for ( $i = 0; $i < count( $terms ); $i++ ) {
			foreach ( $terms[ $i ] as $key => $value ) {
				$this->_mapped_genres[ $key ] = $value;
			}
		}

		unset( $terms );

		return $this->_mapped_genres;
	}

	/**
	 * This function returns an array containing genres which have not
	 * been mapped to any category/vertical.
	 *
	 * @return array
	 */
	public function get_unmapped_terms() {
		/*
		 * If we already have the data in the class var then return that
		 * instead of processing the data again.
		 */
		if ( is_array( $this->_unmapped_genres ) ) {
			return $this->_unmapped_genres;
		}

		$terms = $this->_taxonomy->get_terms_array();
		$mapped_terms = $this->get_mapped_terms( false );

		/*
		 * Remove mapped terms from the terms array to get the
		 * unmapped terms.
		 */
		$unmapped_terms = array_diff_assoc( $terms, $mapped_terms );

		$this->_unmapped_genres = array();

		if ( ! empty( $unmapped_terms ) && is_array( $unmapped_terms ) ) {
			$this->_unmapped_genres = $unmapped_terms;
		}

		unset( $unmapped_terms, $mapped_terms, $terms );

		return $this->_unmapped_genres;
	}

	/**
	 * Utility function to return name for a UI field after sanitizing
	 * it for use in an HTML element.
	 *
	 * @return string
	 */
	public function get_field_name( $name = '' ) {
		return sanitize_key( sprintf( '%s-%s', self::PLUGIN_ID, $name ) );
	}

	/**
	 * This function adds plugin's admin page in the Settings menu
	 */
	public function add_menu() {
		add_submenu_page( 'options-general.php', self::PLUGIN_NAME, self::PLUGIN_NAME, $this->_capability, self::PLUGIN_ID . '-page', array( $this, 'render_admin_page' ) );
	}

	/**
	 * Enqueue the scripts etc
	 * Not meant to be called directly.
	 *
	 * @return void
	 */
	public function enqueue_stuff( $hook ) {
		if ( $hook !== sprintf( 'settings_page_%s-page', self::PLUGIN_ID ) ) {
			return;
		}

		$mapped_genres = $this->get_mapped_terms( true );

		if ( empty( $mapped_genres ) || ! is_array( $mapped_genres ) ) {
			$mapped_genres = array(
				'categories' => array(),
				'verticals' => array(),
			);
		}

		wp_enqueue_script( 'jquery' );

		//load our script & stylesheet
		wp_enqueue_style( self::PLUGIN_ID . '-admin-css', Helper::get_asset_url( 'css/settings.css' ) );
		wp_enqueue_script( self::PLUGIN_ID . '-admin-js', Helper::get_asset_url( 'js/settings.js' ), array( 'jquery' ) );

		wp_localize_script( self::PLUGIN_ID . '-admin-js', 'pmc_genre_vars', array(
			'plugin_id'     => self::PLUGIN_ID,
			'mapped_genres' => $mapped_genres,
		) );
	}

	/**
	 * This function renders the UI for the plugin admin page
	 * Not meant to be called directly.
	 *
	 * @return void
	 */
	public function render_admin_page() {
		/*
		 * We are not using WP Settings API for rendering the UI as our settings page
		 * UI is not simple and creating it with Settings API would make it overly
		 * complicated. Also we need to store data in a specific format (serialized
		 * array containing relationships between cats/verticals and genres).
		 */
		echo PMC::render_template( sprintf( '%s/templates/settings.php', PMC_GENRE_ROOT ), array(
			'settings'        => $this,
			'plugin_id'       => self::PLUGIN_ID,
			'plugin_name'     => self::PLUGIN_NAME,
			'nonce'           => $this->_nonce,
			'categories'      => Helper::get_terms_array( 'category' ),
			'verticals'       => Helper::get_terms_array( 'vertical' ),
			'unmapped_genres' => $this->get_unmapped_terms(),
		) );
	}

	/**
	 * Capture settings page form input and store relationship array in WP options
	 * Not meant to be called directly.
	 *
	 * @return void
	 */
	public function handle_settings_form() {
		if ( ! $this->_is_our_page() || ! current_user_can( $this->_capability ) ) {
			return;
		}

		/*
		 * The empty() check for $_POST must be before check_admin_referer() here
		 * otherwise this plugin's page would stop working because this function is called on 'admin_init'
		 * and will be called on every page load even when form is not submitted by browser
		 */
		if ( empty( $_POST[ $this->get_field_name( 'save-btn' ) ] ) ) {
			return;
		}

		if ( empty( $_POST[ $this->get_field_name( 'mappings-hdn' ) ] ) || ! check_admin_referer( $this->_nonce['action'], $this->_nonce['name'] ) ) {
			return;
		}

		$genre_map = json_decode( stripslashes( $_POST[ $this->get_field_name( 'mappings-hdn' ) ] ), true );

		if ( ! is_array( $genre_map ) || empty( $genre_map ) ) {
			return;
		}

		/*
		 * Weed out extra padding added by javascript and also
		 * sanitize data
		 */
		$genre_map = $this->_clean_payload( $genre_map );

		if ( ! is_array( $genre_map ) || empty( $genre_map ) ) {
			return;
		}

		update_option( self::OPTION_NAME, $genre_map );

		$this->_add_admin_notice( 'Genre mappings saved', 'success' );
	}

}	//end of class


//EOF
