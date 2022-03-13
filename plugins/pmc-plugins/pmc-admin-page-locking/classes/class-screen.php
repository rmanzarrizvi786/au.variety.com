<?php

namespace PMC\Admin_Page_Locking;

use \PMC;
/**
 * Admin Page Locking main class.
 */
class Screen {

	/**
	 * The page slug for the current screen.
	 *
	 * @var string
	 */
	protected $_page;

	/**
	 * Number of seconds a lock is valid.
	 *
	 * @var integer
	 */
	protected $_lock_period = 30;

	/**
	 * Max number of seconds for all locks in a session.
	 *
	 * @var integer
	 */
	protected $_max_lock_period = 600;

	/**
	 * Messages displayed to the user.
	 *
	 * @var array
	 */
	protected $_messages;

	public function __construct( $page ) {
		$this->_page            = $page;
		$this->_lock_period     = apply_filters( 'pmc_admin_page_locking_lock_period', $this->_lock_period );
		$this->_max_lock_period = apply_filters( 'pmc_admin_page_locking_max_lock_period', $this->_max_lock_period );

		$this->_messages = apply_filters(
			'pmc_admin_page_locking_messages',
			[
				/* translators: %s : User Name. */
				'error-lock'     => __( 'Sorry, this screen is in use by %s and is currently locked. Please try again later.', 'pmc-admin-page-locking' ),
				'error-lock-max' => __( 'Sorry, you have reached the maximum idle limit and will now be redirected to the Dashboard.', 'pmc-admin-page-locking' ),
				'nonce'          => __( 'It looks like you may have been on this page for too long. Please refresh your browser.', 'pmc-admin-page-locking' ),
				'more-time'      => __( 'You are approaching the maximum time limit for this screen. Would you like more time? Answering "No" will return you to the dashboard and any unsaved changes will be lost.', 'pmc-admin-page-locking' ),
			],
			$this->_page
		);

		add_action( 'admin_print_scripts-' . $this->_page, [ $this, 'admin_enqueue_scripts_page' ] );
		add_action( 'wp_ajax_admin-page-locking-update-' . $this->_page, [ $this, 'ajax_update_lock' ] );
		add_action( 'wp_ajax_admin-page-locking-release-' . $this->_page, [ $this, 'ajax_release_lock' ] );
		add_action( 'admin_notices', [ $this, 'maybe_add_alert' ] );
	}

	/**
	 * Enqueue scripts on the current admin page screen.
	 * The hook is 'admin_print_scripts-' . $page where $page is the current page that we have this class initialized for
	 * Hence this function will be called only on $page.
	 *
	 */
	public function admin_enqueue_scripts_page() {
		add_thickbox();
		wp_enqueue_script( 'admin-page-locking-js', sprintf( '%s/assets/js/admin-page-locking.min.js', PMC_ADMIN_PAGE_LOCKING_URL ), [
			'jquery',
			'thickbox',
		], '0.1.0', true );

		wp_localize_script(
			'admin-page-locking-js', 'adminPageLockingData', [
				'adminUrl'          => esc_url_raw( admin_url() ),
				'ajaxUrl'           => esc_url_raw( wp_nonce_url( admin_url( 'admin-ajax.php' ), 'apl_lock_nonce' ) ),
				'actionUpdateLock'  => 'admin-page-locking-update-' . $this->_page,
				'actionReleaseLock' => 'admin-page-locking-release-' . $this->_page,
				'errorLock'         => sprintf( $this->_messages['error-lock'], __( 'another user', 'pmc-admin-page-locking' ) ),
				'errorLockMax'      => $this->_messages['error-lock-max'],
				'moreTime'          => $this->_messages['more-time'],
				'lockPeriod'        => $this->_lock_period,
				'lockPeriodMax'     => $this->_max_lock_period,
			]
		);
	}

	/**
	 * Get the lock key as strings
	 * @return string
	 */
	protected function get_lock_key() {
		return sprintf( 'apl-%s', md5( $this->_page ) );
	}

	/**
	 * Update the screen lock in ajaxs
	 */
	public function ajax_update_lock() {
		if ( ! check_ajax_referer( 'apl_lock_nonce' ) ) {
			wp_send_json_error( [ 'message' => esc_html( $this->_messages['nonce'] ) ] );
		}

		$locked = $this->is_locked();
		if ( ! empty( $locked ) ) {
			$locking_user = get_userdata( $locked );
			wp_send_json_error( [ 'message' => sprintf( $this->_messages['error-lock'], $locking_user->display_name ) ] );
		}

		$this->lock();
		wp_send_json_success();
	}

	/**
	 * Release screen lock via ajax
	 */
	public function ajax_release_lock() {
		if ( ! check_ajax_referer( 'apl_lock_nonce' ) ) {
			wp_send_json_error( [ 'message' => esc_html( $this->_messages['nonce'] ) ] );
		}

		$this->unlock();
		wp_send_json_success();
	}

	/**
	 * Acquire current screen lock for the current logged in user in wp-admin.
	 * @param int $user_id
	 */
	public function lock( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user = wp_get_current_user();
		}

		// Add 3 seconds to avoid most race condition issues between lock expiry and ajax call
		$expiry = $this->_lock_period + 3;
		set_transient( $this->get_lock_key(), $user->ID, $expiry );
	}

	/**
	 * Unlock the screen
	 */
	public function unlock() {
		delete_transient( $this->get_lock_key() );
	}

	/**
	 * Check if the current screen is locked by any other user
	 * @return bool|int
	 */
	public function is_locked() {
		$user = wp_get_current_user();

		$lock = get_transient( $this->get_lock_key() );

		// If lock doesn't exist, or check if current user same as lock user
		if ( ! $lock || absint( $lock ) === absint( $user->ID ) ) {
			return false;
		} else {
			// return user_id of locking user
			return absint( $lock );
		}
	}

	/**
	 * If the screen is locked by some other user show alert message to the current user.
	 */
	public function maybe_add_alert() {
		global $hook_suffix;

		if ( $this->_page === $hook_suffix ) {
			$locked = $this->is_locked();
			if ( $locked ) {
				$locking_user = get_userdata( $locked );

				PMC::render_template(
					sprintf( '%s/templates/admin-notice.php', untrailingslashit( PMC_ADMIN_PAGE_LOCKING_DIR ) ),
					[ 'admin_notice' => sprintf( $this->_messages['error-lock'], $locking_user->display_name ) ],
					true
				);

			}
		}
	}
}
