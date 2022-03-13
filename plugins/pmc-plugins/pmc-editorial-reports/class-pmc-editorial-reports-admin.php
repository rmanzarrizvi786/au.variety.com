<?php

/**
 * class PMC_Editorial_Reports_Admin which handles all admin functionality for the PMC Editorial Reports plugin
 *
 * @author Amit Gupta
 * @since 2013-06-07
 *
 * @version 2013-06-11
 * @version 2013-06-13
 * @version 2013-06-14
 * @version 2013-06-17
 * @version 2013-07-03 Taylor Lovett
 * @version 2013-07-09
 * @version 2014-03-03
 */

use PMC\Global_Functions\Traits\Singleton;

class PMC_Editorial_Reports_Admin {

	use Singleton;

	/**
	 * @var const Class constant containing unique plugin ID
	 */
	const plugin_id = "pmc-editorial-reports";

	/**
	 * @var const Class constant containing plugin name for display
	 */
	const plugin_name = "PMC Editorial Reports";

	/**
	 * @var const Class constant containing max range allowed for reports
	 */
	const report_max_range = 8985600;	//92 days + 12 days leeway (to account for snapping start day to Sunday and end day to Saturday)

	/**
	 * @var array Contains some basic internal plugin options
	 */
	protected $_options = array(
		'report_prefix' => self::plugin_id,		//prefix for report file name - can be changed per site via filter
	);

	/**
	 * @var array Contains admin notices to be shown
	 */
	protected $_notices = array();

	/**
	 * @var object Contains the object of PMC_Editorial_Reports initialized in _init()
	 */
	protected $reports;

	/**
	 * @var string Contains the capability a user must have to generate and view reports
	 */
	protected $_capability = 'manage_options';

	/**
	 * Initialization function called by parent::get_instance() when
	 * object of this class is created
	 *
	 * @since 2013-06-07 Amit Gupta
	 * @version 2013-06-07 Amit Gupta
	 * @version 2013-06-11 Amit Gupta
	 * @version 2013-07-03 Taylor Lovett
	 */
	protected function __construct() {

		// Only load the plugin if it's admin side.
		if ( ! is_admin() ) {
			return;
		}

		//init reports class
		$this->reports = PMC_Editorial_Reports::get_instance();

		//allow user capability override
		$this->_capability = apply_filters( 'pmc_editorial_reports_capability_override', $this->_capability );

		//allow class options to be overridden
		add_action( 'init', array( $this, 'allow_override_on_options' ) );

		//setup our style/script enqueuing for wp-admin
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_stuff' ) );

		//call function to add options menu item
		add_action( 'admin_menu', array( $this, 'add_menu' ) );

		//our form handler
		add_action( 'admin_init', array( $this, 'admin_form_handler' ) );

		add_action( 'admin_init', array( $this, 'admin_form_handler_exec' ) );

		//notice/messages handler
		add_action( 'admin_notices', array( $this, 'out_notices' ) );

		//setup word count updates whenever a post is published or published post is updated. Need to hook later so it can capture all data properly.
		add_action( 'save_post', array( $this, 'action_save_post' ), 99 );
	}

	/**
	 * Save wordcount, image count and post categorization in post meta
	 *
	 * @param int $post_id
	 * @uses get_post_type, current_user_can, get_post, get_post_meta, get_the_ID, have_posts,
	 *		 update_post_meta, wp_reset_postdata, wp_is_post_revision, get_post_status
	 * @return void
	 *
	 * @since 2013-07-03 Taylor Lovett
	 * @version 2013-07-03 Taylor Lovett
	 * @version 2020-02-28 | SADE-438 | Kelin Chauhan <kelin.chauhan@rtcamp.com>
	 */
	public function action_save_post( $post_id ) {

		$post = get_post( $post_id );

		if ( ! $post ) {
			return false;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post->ID ) || wp_is_post_revision( $post->ID ) || 'publish' !== get_post_status( $post->ID ) ) {
			return false;
		}

		// Get word count.
		$word_count = $this->reports->get_post_word_count( $post->ID );
		$meta_value = get_post_meta( $post->ID, '_pmc_word_count', true );
		if ( (int) $word_count !== (int) $meta_value ) {
			update_post_meta( $post->ID, '_pmc_word_count', (int) $word_count );
		}

		// Get image count.
		$image_count = $this->reports->get_image_count( $post->ID );
		$meta_value  = get_post_meta( $post->ID, '_pmc_image_count', true );
		if ( (int) $image_count !== (int) $meta_value ) {
			update_post_meta( $post->ID, '_pmc_image_count', (int) $image_count );
		}

		// Get taxonomy categorization.
		$categories = implode( ', ', $this->reports->get_post_taxonomy_categorization( $post, 'category' ) );
		$verticals  = implode( ', ', $this->reports->get_post_taxonomy_categorization( $post, 'vertical' ) );
		$meta_value = get_post_meta( $post->ID, '_pmc_post_categorization', true );
		$json_value = wp_json_encode(
			[
				'category' => $categories,
				'vertical' => $verticals,
			]
		);

		if ( $meta_value !== $json_value ) {
			update_post_meta( $post->ID, '_pmc_post_categorization', $json_value );
		}

	}

	/**
	 * This function checks whether current page is our plugin page in wp-admin
	 * or not. If it is our page then it returns TRUE else FALSE
	 *
	 * @since 2013-06-13
	 * @version 2013-06-17
	 */
	protected function _is_our_page() {
		if( ! is_admin() ) {
			//not in wp-admin so definitely not our page
			return false;
		}

		if( isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ) {
			//if $_GET['page'] is set then should not go in ELSE irrespective of value
			if( sanitize_title( $_GET['page'] ) == self::plugin_id . '-page' ) {
				//our plugin page
				return true;
			}
		} elseif( strpos( $_SERVER['REQUEST_URI'], '?' ) !== false && strpos( $_SERVER['REQUEST_URI'], 'page=' . self::plugin_id . '-page' ) !== false ) {
			//our plugin page
			return true;
		}

		//not our page
		return false;
	}

	/**
	 * This function gets the GMT offset from options & converts the passed
	 * timestamp into GMT
	 *
	 * @since 2013-06-07
	 * @version 2013-06-07
	 */
	protected function _get_gmt_timestamp( $timestamp = 0 ) {
		if( empty( $timestamp ) || ! is_numeric( $timestamp ) || $timestamp < 0 ) {
			$timestamp = time();
		}

		return ( $timestamp - ( get_option('gmt_offset') * 3600 ) );
	}

	/**
	 * This function accepts CSV content and file name and pushes it to browser
	 * to force download as file.
	 *
	 * @since 2013-06-07
	 * @version 2013-06-07
	 * @version 2013-06-11
	 */
	protected function _push_download( $content, $file_name = '' ) {
		header( 'Content-Type: application/csv' );
		header( 'Content-length: ' . strlen( $content ) );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
		echo $content;
		exit();
	}

	/**
	 * This function accepts a message which is to be displayed on plugin page
	 * in wp-admin
	 *
	 * @since 2013-06-13
	 * @version 2013-06-13
	 */
	protected function _add_admin_notice( $message, $type = 'error' ) {
		if( empty( $message ) || ! is_string( $message ) ) {
			return;
		}

		$key = md5( $message );

		if( array_key_exists( $key, $this->_notices ) ) {
			return;
		}

		$type = ( $type !== 'success' ) ? 'error' : 'updated';

		$this->_notices[$key] = array(
			'type' => $type,
			'message' => $message
		);

		return true;
	}

	/**
	 * This function is called on admin_notices and it displays all notices/messages
	 * for the current plugin
	 *
	 * @since 2013-06-13
	 * @version 2013-06-13
	 */
	public function out_notices() {
		if( ! $this->_is_our_page() || empty( $this->_notices ) || ! is_array( $this->_notices ) ) {
			return false;
		}

		foreach( $this->_notices as $notice ) {
			echo '<div class="' . esc_attr( $notice['type'] ) . '"><p>' . $notice['message'] . '</p></div>';
		}
	}

	/**
	 * This function allows overriding of default options of the plugin like report name prefixes.
	 * Binding it on 'init' allows late execution of filter yet still before anything is actually done,
	 * so that the filter can be added in a site's functions.php after plugin is loaded.
	 *
	 * @since 2013-06-07
	 * @version 2013-06-07
	 */
	public function allow_override_on_options() {
		$new_options = apply_filters( 'pmc_editorial_reports_options_override', $this->_options );
		$this->_options = wp_parse_args( $new_options, $this->_options );

		unset( $new_options );
	}

	/**
	 * This function loads up scripts/styles etc
	 *
	 * @since 2013-06-07
	 * @version 2013-06-07
	 * @version 2013-06-17
	 */
	public function enqueue_stuff( $hook ) {
		if( $hook !== 'tools_page_' . self::plugin_id . '-page' ) {
			return;
		}

		//load scripts bundled in WordPress
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		//load jquery-ui css, WordPress doesn't seem to have it for some weird reason
		wp_enqueue_style( self::plugin_id . '-jquery-ui-theme-smoothness', plugins_url( 'css/jquery-ui/smoothness/jquery-ui-1.10.3.custom.css', __FILE__ ), array() );

		//load our css
		wp_enqueue_style( self::plugin_id . '-admin', plugins_url( 'css/admin.css', __FILE__ ), array() );

		//load our script
		wp_enqueue_script( self::plugin_id . '-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery-ui-datepicker' ) );
	}

	/**
	 * This function adds plugin's admin page in the Settings menu
	 *
	 * @since 2013-06-07
	 * @version 2013-06-07
	 */
	public function add_menu() {
		add_submenu_page( 'tools.php', self::plugin_name, self::plugin_name, $this->_capability, self::plugin_id . '-page', array($this, 'admin_page') );
	}

	/**
	 * This function constructs the UI for the plugin admin page
	 *
	 * @since 2013-06-07
	 * @version 2013-06-07
	 * @version 2013-06-17
	 */
	public function admin_page() {
?>
		<div class="wrap">
			<h2><?php echo '<strong>' . self::plugin_name . '</strong>'; ?></h2>
			<p>&nbsp;</p>
			<h3>Reports always start on Sunday and end on Saturday.</h3>
			<p>&nbsp;</p>
			<form action="<?php menu_page_url( self::plugin_id . '-page', true ); ?>" method="post" id="pmc_er_form" name="pmc_er_form">
			<table id="pmc-er-admin-ui" width="85%" border="0">
				<tr>
					<td width="15%" align="center">
						<label for="pmc_er_start_date_cal">Start date</label>
					</td>
					<td width="35%">
						<div id="pmc_er_start_date_cal"></div>
					</td>
					<td width="15%" align="center">
						<label for="pmc_er_end_date_cal">End date</label>
					</td>
					<td width="35%">
						<div id="pmc_er_end_date_cal"></div>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<input name="pmc_er_start_date" id="pmc_er_start_date" class="pmc-er-option" value="" />
					</td>
					<td>&nbsp;</td>
					<td>
						<input name="pmc_er_end_date" id="pmc_er_end_date" class="pmc-er-option" value="" />
					</td>
				</tr>
				<tr>
					<td colspan="4">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<?php wp_nonce_field( self::plugin_id, '_wpnonce' ); ?>
						<input type="hidden" id="hid_pmc_er_download" name="hid_pmc_er_download" value="yes" />
						<input type="submit" id="btn_pmc_er_download" name="btn_pmc_er_download" class="button button-primary" value="Download Report" />
					</td>
					<td colspan="2">&nbsp</td>
				</tr>
			</table>

			<h2>Executive Profile Report</h2>
			<input type="submit" id="btn_pmc_er_download_exec" name="btn_pmc_er_download_exec" class="button button-primary" value="Download Hollywood Executive Low Tag Usage Report" />
			</form>
		</div>
<?php
	}

	/**
	 * Download CSV for pmc hollywood exec profiles report
	 */
	public function admin_form_handler_exec() {
		if ( ! is_admin() || ! current_user_can( $this->_capability ) || ! $this->_is_our_page() ) {
			return false;
		}

		if ( empty( $_POST['btn_pmc_er_download_exec'] ) || ! check_admin_referer( self::plugin_id, '_wpnonce' ) ) {
			return false;
		}

		$report_name = $this->_options['report_prefix'] . '_hollywood_execs.csv';

		$report_csv = $this->reports->get_hollywood_execs_report();

		if( empty( $report_csv ) ) {
			$this->_add_admin_notice( 'No data found for selected date range', 'error' );
			return false;
		}

		$this->_push_download( $report_csv, $report_name );
	}

	/**
	 * This function handles the form input from the plugin page and generates report
	 * to push back to the browser. This unconventional approach is used as this
	 * plugin page does not save any options or settings etc for which the usual
	 * settings API etc are made. The report data need to be sent to browser before
	 * any other headers are sent.
	 *
	 * @since 2013-06-07
	 * @version 2013-06-07
	 * @version 2013-06-11
	 * @version 2013-06-17
	 * @version 2013-07-09
	 */
	public function admin_form_handler() {
		if( ! is_admin() || ! current_user_can( $this->_capability ) || ! $this->_is_our_page() ) {
			return false;
		}

		if ( empty( $_POST['btn_pmc_er_download'] ) ) {
			return false;
		}

		/*
		 * The empty() check for $_POST must be before check_admin_referer() here
		 * otherwise this plugin's page would stop working because this function is called on 'admin_init'
		 * and will be called on every page load even when form is not submitted by browser
		 */
		if( empty( $_POST['hid_pmc_er_download'] ) || $_POST['hid_pmc_er_download'] !== 'yes' || ! check_admin_referer( self::plugin_id, '_wpnonce' ) ) {
			return false;
		}

		if( empty( $_POST['pmc_er_start_date'] ) || empty( $_POST['pmc_er_end_date'] ) ) {
			//no dates sent by form, bail out
			$this->_add_admin_notice( 'Both Start and End dates need to be selected to generate report', 'error' );
			return false;
		}

		$pattern = '/^(\d{1,2})\/(\d{1,2})\/((?:\d{2}){1,2})$/';

		if( preg_match( $pattern, $_POST['pmc_er_start_date'] ) == 0 || preg_match( $pattern, $_POST['pmc_er_end_date'] ) == 0 ) {
			//invalid date sent by form, bail out
			$this->_add_admin_notice( 'Invalid date selected', 'error' );
			return false;
		}

		exit('hhh');

		unset( $pattern );

		$start_date = array_map( 'intval', explode( '/', $_POST['pmc_er_start_date'] ) );
		$end_date = array_map( 'intval', explode( '/', $_POST['pmc_er_end_date'] ) );

		$start_date = mktime( 0, 0, 0, $start_date[0], $start_date[1], $start_date[2] );
		$end_date = mktime( 0, 0, 0, $end_date[0], $end_date[1], $end_date[2] );

		//if date range selected is more than the allowed range then bail out
		if( ( $end_date - $start_date ) > self::report_max_range ) {
			//max date range violated, abort
			$this->_add_admin_notice( 'Report can only be generated for a maximum of 3 months', 'error' );
			return false;
		}

		$start_date_day = intval( date( 'w', $start_date ) );
		$end_date_day = intval( date( 'w', $end_date ) );

		//if start date selected is not of Sunday then get Sunday
		if( $start_date_day > 0 ) {
			$start_date = strtotime( '-' . $start_date_day . ' days', $start_date );
		}

		//if end date selected is not of Saturday then get Saturday
		if( $end_date_day !== 6 ) {
			$end_date = strtotime( '+' . ( 6 - $end_date_day ) . ' days', $end_date );
		}

		unset( $end_date_day, $start_date_day );

		$report_name = $this->_options['report_prefix'] . '_' . date( 'Y-m-d', $start_date ) . '_to_' . date( 'Y-m-d', $end_date ) . '.csv';

		$report_csv = $this->reports->get_weekly_numbers_report( $this->_get_gmt_timestamp( $start_date ), $this->_get_gmt_timestamp( $end_date ) );

		if( empty( $report_csv ) ) {
			$this->_add_admin_notice( 'No data found for selected date range', 'error' );
			return false;
		}

		$this->_push_download( $report_csv, $report_name );
	}

//end of class
}


//EOF
