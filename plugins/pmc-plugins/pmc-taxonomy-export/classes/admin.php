<?php

namespace PMC\Taxonomy_Export;

use PMC;
use \PMC\Global_Functions\Traits\Singleton;

class Admin {

	use Singleton;

	/**
	 * @var integer Number of seconds for which cache should live
	 */
	const CACHE_LIFE = 600; //10 minutes

	/**
	 * @var cache group for the taxonomy term export
	 */
	const CACHE_GROUP = 'pmc_taxonomy_export';

	/**
	 * @var number of records per CSV file
	 */
	const NUMBER = 10000;

	/**
	 * @var $view_cap capabilities of logged in user to access this plugin
	 */
	var $view_cap = 'manage_options';

	/**
	 * @var $parent_slug slug for the admin menu page
	 */
	var $parent_slug = 'reporting';

	/**
	 * @var $page_slug slug for the admin submenu page
	 */
	var $page_slug = 'taxonomy-export';


	/**
	 * Header for the detailed CSV file
	 *
	 * @since 2015-10-29
	 * @version 2015-10-29 Archana Mandhare PMCVIP-113
	 */
	private $_detailed_headers = array(
		'term_id',
		'name',
		'slug',
		'term_group',
		'term_taxonomy',
		'taxonomy',
		'description',
		'parent',
		'article count',
	);

	/**
	 * Header for the compact CSV file
	 *
	 * @since 2015-11-20
	 * @version 2015-11-20 Archana Mandhare PMCVIP-113
	 */
	private $_compact_headers = array(
		'term_id',
		'name',
		'slug',
		'parent',
		'article count',
	);

	/**
	 * Sets up the class.
	 *
	 * @since 2015-10-29
	 * @version 2015-10-29 Archana Mandhare PMCVIP-113
	 */
	protected function __construct() {

		// Add the top level menu placeholder
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Ajax callbacks
		add_action( 'wp_ajax_export_report', array( $this, 'export_report' ) );
		add_action( 'wp_ajax_get_total_term_count', array( $this, 'get_total_term_count' ) );
	}

	/**
	 * Enqueue scripts in admin
	 *
	 * @since 2015-10-30
	 * @version 2015-10-30 Archana Mandhare PMCVIP-113
	 *
	 * @param $hook
	 */
	public function admin_enqueue_scripts( $hook ) {

		if ( 'reporting_page_taxonomy-export' !== $hook ) {
			return;
		}

		wp_register_script( 'pmc-taxonomy-export-js', plugins_url( 'pmc-taxonomy-export/assets/js/admin-ajax.js', PMC_TAXONOMY_EXPORT_DIR ), array( 'jquery' ), false );
		wp_enqueue_script( 'pmc-taxonomy-export-js' );
		wp_localize_script( 'pmc-taxonomy-export-js', 'pmc_taxonomy_export_admin_options', array(
			'export_nOnce' => wp_create_nonce( 'pmc-taxonomy-export' ),
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
		) );

		wp_enqueue_script( array( 'jquery-ui-progressbar' ) );
		wp_enqueue_style( 'jquery-ui-progressbar-css', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'pmc-taxonomy-export-css', plugins_url( 'pmc-taxonomy-export/assets/css/admin.css', PMC_TAXONOMY_EXPORT_DIR ) );

	}

	/**
	 * Create the top-level admin menu
	 *
	 * @since 2015-10-29
	 * @version 2015-10-29 Archana Mandhare PMCVIP-113
	 */
	public function admin_menu() {

		if ( current_user_can( $this->view_cap ) ) {

			\PMC::maybe_add_menu_page( 'Reporting', 'Reporting', 'manage_options', 'reporting' );

			add_submenu_page( 'reporting', 'Export Taxonomies', 'Export Taxonomies', $this->view_cap, $this->page_slug, array(
				$this,
				'submenu_page_callback'
			) );
		}

	}


	/**
	 * submenu callback function
	 *
	 * @since 2015-10-29
	 * @version 2015-10-29 Archana Mandhare PMCVIP-113
	 */
	public function submenu_page_callback() {

		echo PMC::render_template( sprintf( '%s/templates/admin-ui.php', PMC_TAXONOMY_EXPORT_DIR ) );

	}

	/**
	 * Register settings
	 *
	 * @since 2015-10-29
	 * @version 2015-10-29 Archana Mandhare PMCVIP-113
	 */
	public function settings_init() {

		register_setting( 'taxonomy-export-page', 'export_settings' );

		add_settings_section(
			'settings_section',
			'',
			array( $this, 'settings_section_callback' ),
			'taxonomy-export-page'
		);

	}

	/**
	 * Settings field callback
	 *
	 * @since 2015-10-29
	 * @version 2015-10-29 Archana Mandhare PMCVIP-113
	 */
	public function settings_section_callback() {

		echo PMC::render_template( sprintf( '%s/templates/taxonomy-field-ui.php', PMC_TAXONOMY_EXPORT_DIR ),
			array( 'taxonomies' => get_taxonomies( array( 'public' => true ), 'object' ) ) );

	}

	/**
	 * Ajax function callback
	 *
	 * @since 2015-11-17
	 * @version 2015-11-17 Archana Mandhare PMCVIP-113
	 */
	public function get_total_term_count() {

		check_ajax_referer( 'pmc-taxonomy-export', 'export_nOnce' );

		if ( ! current_user_can( $this->view_cap ) ) {
			wp_die( __( 'You do not have permissions to perform this action.' ) );
			exit();
		}

		$taxonomy    = isset( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : '';
		$total_terms = 0;
		$pages       = 0;

		if ( ! empty( $taxonomy ) ) {
			$total_terms = wp_count_terms( $taxonomy, array() );
			if ( ! empty( $total_terms ) ) {
				$pages = ceil( $total_terms / self::NUMBER );
			}
		}

		wp_send_json( array(
			'has_terms'   => $total_terms > 0,
			'total_terms' => $total_terms,
			'pages'       => $pages,
		) );

		exit();

	}

	/**
	 * Ajax function callback
	 *
	 * @since 2015-11-17
	 * @version 2015-11-17 Archana Mandhare PMCVIP-113
	 */
	public function export_report() {

		check_ajax_referer( 'pmc-taxonomy-export', 'export_nOnce' );

		if ( ! current_user_can( $this->view_cap ) ) {
			wp_die( __( 'You do not have permissions to perform this action.' ) );
			exit();
		}

		$taxonomy    = isset( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : '';
		$page      = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 1;
		$report_type = isset( $_POST['report_type'] ) ? intval( $_POST['report_type'] ) : 0;

		if ( empty( $taxonomy ) ) {
			wp_send_json( array(
				'success' => false,
				'files'   => false,
				'message' => 'No taxonomy specified for the export',
			) );

			exit();
		}

		$csv_files = $this->process_csv( $taxonomy, $page, $report_type );

		$success = ! empty( $csv_files ) ? true : false;

		$message = ! $success ? 'No terms found for this taxonomy' : 'File ' . $page . ' downloaded';

		wp_send_json( array(
			'success' => $success,
			'files'   => $csv_files,
			'message' => $message,
		) );
		exit();


	}


	/**
	 * Fetch taxonomy terms, store it in an array and return the array containing base64encoded string of CSV
	 *
	 * @since 2015-11-19
	 * @version 2015-11-19 Archana Mandhare PMCVIP-113
	 *
	 * @param $taxonomy string
	 * @param $page int
	 * @param $report_type int
	 *
	 * @return array
	 */
	public function process_csv( $taxonomy, $page = 1, $report_type = 0 ) {

		if ( empty( $taxonomy ) ) {
			return;
		}

		if ( class_exists( 'PMC_Cache' ) ) {

			// Since PMC_Cache is applying md5, so we want raw data for key
			$cache_key = 'pmc_taxonomy_export_' . serialize( array(
					'taxonomy'    => $taxonomy,
					'page'      => $page,
					'report_type' => $report_type,
				) );

			$pmc_cache = new \PMC_Cache( $cache_key, self::CACHE_GROUP );

			$csv_terms = $pmc_cache->expires_in( self::CACHE_LIFE )
			                       ->updates_with( array( $this, 'get_terms_from_db' ), array(
				                       $taxonomy,
				                       $page,
				                       $report_type,
			                       ) )
			                       ->get();

			return $csv_terms;
		} else {
			return $this->get_terms_from_db( $taxonomy, $page, $report_type );
		}
	}

	/**
	 * Fetch taxonomy terms, store it in an array and return the array containing base64encoded string of CSV
	 *
	 * @since 2015-11-19
	 * @version 2015-11-19 Archana Mandhare PMCVIP-113
	 *
	 * @param $taxonomy string
	 * @param $page int
	 * @param $report_type int
	 *
	 * @return array
	 */
	public function get_terms_from_db( $taxonomy, $page, $report_type = 0 ) {

		$csv_terms  = array();
		$term_files = array();

		$number = self::NUMBER;

		$offset = ( $page - 1 ) * $number;

		// if $report_type = 0 we show detailed report and $report_type = 1 we show compact report
		$headers = ( 0 === $report_type ) ? $this->_detailed_headers : $this->_compact_headers;

		$args = array(
			'orderby'      => 'count',
			'order'        => 'DESC',
			'hide_empty'   => false,
			'childless'    => false,
			'fields'       => 'all',
			'cache_domain' => 'pmc_taxonomy_export',
			'number'       => $number,
			'offset'       => $offset,
		);

		$terms = get_terms( $taxonomy, $args );

		if ( ! empty( $terms ) ) {

			foreach ( $terms as $term ) {

				$csv_term = get_object_vars( $term );

				if ( 1 === $report_type ) {
					unset( $csv_term['term_group'] );
					unset( $csv_term['term_taxonomy_id'] );
					unset( $csv_term['taxonomy'] );
					unset( $csv_term['description'] );
				}
				// unset the values we do not require for the report
				unset( $csv_term['filter'] );

				$csv_terms[] = $csv_term;
			}

			$lob      = get_bloginfo( 'name' );
			$date     = date( 'Y-m-d' );
			$filename = $lob . '-' . $taxonomy . '-' . $date . '-file-' . $page . '.csv';

			$term_files[ $filename ] = $this->array_to_csv( $csv_terms );

			unset( $csv_terms );

		}

		return $term_files;

	}

	/**
	 * Array to CSV converter
	 *
	 * @since 2016-01-6
	 * @version 2016-01-6 Archana Mandhare PMCVIP-113
	 *
	 * @param $array array
	 *
	 * @return string
	 */
	public function array_to_csv( $array ) {

		// Grab the first element to build the header
		$arr = array_shift( $array );

		$temp = array();

		foreach ( $arr as $key => $data ) {
			$temp[] = $key;
		}

		$csv = implode( ',', $temp ) . "\n";

		// Add the data from the first element
		$csv .= $this->to_csv_line( $arr );

		// Add the data for the rest
		foreach ( $array as $arr ) {
			$csv .= $this->to_csv_line( $arr );
		}

		return $csv;
	}

	/**
	 * Create a CSV on each line
	 *
	 * @since 2016-01-6
	 * @version 2016-01-6 Archana Mandhare PMCVIP-113
	 *
	 * @param $array array
	 *
	 * @return string
	 */
	public function to_csv_line( $array ) {

		$temp = array();
		foreach ( $array as $elt ) {
			$elt = str_replace('"', "", $elt);
			$temp[] = '"' . addslashes( $elt ) . '"';
		}
		$string = implode( ',', $temp ) . "\n";

		return $string;

	}

}
