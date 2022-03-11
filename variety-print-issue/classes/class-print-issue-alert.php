<?php
/**
 * Print Issue Alert.
 *
 * This class implements a notification alert to editors to review
 * the auto print issue creation to make sure the print volume, issue, and date
 * are correct.
 *
 * The editors are presented with a form to approve the issue or make
 * corrections to the print issue information.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Plugins\Variety_Print_Issue;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Print_Issue_Alert
 *
 * @package pmc-variety-2017
 */
class Print_Issue_Alert {

	use Singleton;

	/**
	 * The Option Key.
	 */
	const OPT_KEY = 'variety_print_issue';

	/**
	 * If nonce is active.
	 *
	 * @var int
	 */
	protected $_notice_active = 0;

	/**
	 * The issue marker.
	 *
	 * @var bool
	 */
	protected $_issue_marker = false;

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		add_action( 'admin_notices', array( $this, 'action_admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue Scripts
	 *
	 * @action admin_enqueue_scripts.
	 */
	public function action_admin_enqueue_scripts() {
		if ( $this->notice_active() ) {
			wp_register_style( 'variety-print-alert-css', plugins_url( 'assets/css/admin-alert.css', dirname( __FILE__ ) ), array(), false, false );
			wp_enqueue_style( 'variety-print-alert-css' );

			wp_register_script( 'variety-print-alert-js', plugins_url( 'assets/js/admin-alert.js', dirname( __FILE__ ) ), array(), false, true );

			$exports = array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'ajaxAction' => 'print-issue-setting',
				'l10n'       => array(
					'invalidDate'       => __( 'Invalid date value', 'pmc-variety' ),
					'invalidDateFormat' => __( 'Invalid date format. Expecting YYYY-MM-DD.', 'pmc-variety' ),
					'invalidVolume'     => __( 'Invalid volume value', 'pmc-variety' ),
					'invalidIssue'      => __( 'Invalid issue number value', 'pmc-variety' ),
				),
			);

			wp_scripts()->add_data(
				'variety-print-alert-js',
				'data',
				sprintf( 'var _varietyPrintIssueAlertExports = %s;', wp_json_encode( $exports ) )
			);

			wp_add_inline_script( 'variety-print-alert-js', 'varietyPrintIssueAlert.init();', 'after' );
			wp_enqueue_script( 'variety-print-alert-js' );
		}
	}

	/**
	 * Nonce Active
	 *
	 * Determine if user needs to be notified.
	 *
	 * @return true | false
	 */
	public function notice_active() {
		global $current_user;
		if ( 0 === $this->_notice_active ) {
			$this->_notice_active = false;

			// Get the list of users from setting.
			$user_list = Print_Issue_Setting::get_instance()->get_option( 'notify-user-list', array() );

			// is current user in the list?
			if ( in_array( strtolower( $current_user->data->user_login ), $user_list, true ) ) {

				// have user responded to the current print issue?
				$print_slug          = get_user_attribute( $current_user->ID, 'print-issue-alert' );
				$this->_issue_marker = Print_Issue::get_instance()->get_marker_issue();

				if ( ! empty( $this->_issue_marker ) && ( empty( $print_slug ) || $print_slug !== $this->_issue_marker['slug'] ) ) {
					$this->_notice_active = true;
				}
			}
		}

		return $this->_notice_active;
	}

	/**
	 * Admin Notices
	 *
	 * Display the admin notice and ask user if current issue is correct or not.
	 *
	 * @action admin_notices
	 */
	public function action_admin_notices() {
		if ( ! $this->notice_active() || empty( $this->_issue_marker ) ) {
			return;
		}

		$info              = $this->_issue_marker;
		$datestr           = date( 'l, F d, Y', $info['date'] );
		$syndicate_datestr = date( 'l d F', strtotime( 'this Wednesday', $info['date'] ) );
		$esc_print_slug    = esc_attr( $info['slug'] );
		$esc_datestr       = esc_attr( date( 'Y-m-d', $info['date'] ) );
		$esc_name          = esc_attr( $info['name'] );
		$esc_name_html     = esc_html( $info['name'] );
		$esc_volume        = (int) ( $info['volume'] );
		$esc_issue         = (int) ( $info['issue'] );
		$esc_term_id       = (int) $info['term_id'];

		/**
		 * @since 2017-09-01 Milind More CDWE-499
		 */
		echo \PMC::render_template( CHILD_THEME_PATH . '/plugins/variety-print-issue/templates/print-issue-alert.php',
			array(
				'esc_print_slug'    => $esc_print_slug,
				'esc_term_id'       => $esc_term_id,
				'syndicate_datestr' => $syndicate_datestr,
				'esc_volume'        => $esc_volume,
				'esc_issue'         => $esc_issue,
				'datestr'           => $datestr,
				'esc_name_html'     => $esc_name_html,
				'esc_datestr'       => $esc_datestr,
				'esc_name'          => $esc_name,
			)
		);

	}
}
