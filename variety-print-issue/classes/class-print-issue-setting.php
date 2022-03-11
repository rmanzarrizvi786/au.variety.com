<?php
/**
 * Print Issue Setting
 *
 * schedule is defined as array of (
 *    'issue'  => int, The starting issue number for the listed publication date
 *    'volume' => int, The starting volume number for the listed publication date
 *    'date'   => int, The publication date of first printing for the issue & volume #
 * )
 *
 * This class implement the UI to allow administrator to manage the list of
 * print issue schedule for auto increment of issue and volume number.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Plugins\Variety_Print_Issue;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Print_Issue_Setting
 *
 * @package pmc-variety-2017
 */
class Print_Issue_Setting {

	use Singleton;

	/**
	 * Nonce Name.
	 */
	const NONCE_NAME = 'variety-print-info-nonce';

	/**
	 * Nonce Action
	 */
	const NONCE_ACTION = 'variety-print-info-nonce-action';

	/**
	 * Option Key.
	 */
	const OPT_KEY = 'variety_print_issue';

	/**
	 * The options.
	 *
	 * @var bool
	 */
	protected $_options = false;

	/**
	 * The volume schedule.
	 *
	 * @var bool
	 */
	protected $_volume_schedule = false;

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		add_action('admin_menu', array($this, 'action_admin_menu'));
		add_action('admin_enqueue_scripts', array($this, 'action_enqueue_scripts'));
		add_action('wp_ajax_print-issue-setting', array($this, 'do_ajax_action'));
	}

	/**
	 * Enqueue Scripts
	 *
	 * @action admin_enqueue_scripts
	 *
	 * @param string $page The current Page.
	 */
	public function action_enqueue_scripts( $page ) {

		$current_page = filter_input( INPUT_GET, 'page' );

		if ( 'settings_page_print-issue-settings' === $page && ! empty( $current_page ) && 'print-issue-settings' === sanitize_text_field( wp_unslash( $current_page ) ) ) {

			wp_enqueue_style('jquery-ui-datepicker-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
			wp_register_style('variety-print-setting-css', plugins_url('assets/css/admin-setting.css', dirname(__FILE__)), array('jquery-ui-datepicker-style'), false, false);
			wp_enqueue_style('variety-print-setting-css');

			wp_register_script('variety-print-setting-js', plugins_url('assets/js/admin-setting.js', dirname(__FILE__)), array('jquery', 'jquery-ui-datepicker'), false, true);

			$exports = array(
				'ajaxUrl'    => admin_url('admin-ajax.php'),
				'ajaxAction' => 'print-issue-setting',
				'l10n'       => array(
					'invalidDate'        => __('Invalid date value', 'pmc-variety'),
					'invalidDateFormat'  => __('Invalid date format. Expecting YYYY-MM-DD.', 'pmc-variety'),
					'invalidVolume'      => __('Invalid volume value', 'pmc-variety'),
					'invalidIssue'       => __('Invalid issue number value', 'pmc-variety'),
					'msgScheduleError'   => __('Error saving schedule, or schedule cannot be replaced.', 'pmc-variety'),
					'msgScheduleUpdated' => __('Volume schedule updated.', 'pmc-variety'),
					'msgScheduleRemoved' => __('Volume schedule removed.', 'pmc-variety'),
					'msgUserListSaved'   => __('User list saved.', 'pmc-variety'),
					'msgEdit'            => __('Edit', 'pmc-variety'),
					'msgRemove'          => __('Remove', 'pmc-variety'),
					'msgToDo'            => __('TODO: Edit', 'pmc-variety'),
				),
			);

			wp_scripts()->add_data(
				'variety-print-setting-js',
				'data',
				sprintf('var _varietyPrintIssueSettingExports = %s;', wp_json_encode($exports))
			);
			wp_add_inline_script('variety-print-setting-js', 'varietyPrintIssueSetting.init();', 'after');
			wp_enqueue_script('variety-print-setting-js');
		}
	}

	/**
	 * Get Input
	 *
	 * @param string $name    The input key.
	 * @param bool   $default A default value.
	 *
	 * @return bool|string A value.
	 */
	public static function get_input($name, $default = false) {

		$post_data = filter_input( INPUT_POST, $name );

		if ( ! empty( $post_data ) ) {
			return sanitize_text_field( wp_unslash( $post_data ) );
		}

		$get_data = filter_input( INPUT_GET, $name );

		if ( ! empty( $get_data ) ) {
			return sanitize_text_field( wp_unslash( $get_data ) );
		}

		return $default;
	}

	/**
	 * Do Ajax Action
	 *
	 * @action wp_ajax_print-issue-setting
	 * @global $current_user
	 */
	public function do_ajax_action() {
		global $current_user;
		if (!current_user_can('edit_posts')) {
			wp_die();
		}
		$result = false;
		$cmd = self::get_input('cmd');

		// Bypass nonce check for certain ajax calls for troubleshooting.
		if (!in_array($cmd, array('reconfirm-marker-issue', 'run-histories'), true)) {
			if (!self::verify_nonce()) {
				wp_die();
			}
		}

		switch ($cmd) {
			case 'run-histories':
				// Get the run log history.
				$result = pmc_get_option('run-histories', 'variety-print-issue');
				break;
			case 'get-all':
				// Return all data.
				$info = $this->get_option('print-info');
				$info['date_str'] = date('Y-m-d', $info['date']);
				$info['locked'] = 'current active marker - auto updated';

				$schedule = $this->get_volume_schedule();
				array_walk($schedule, function (&$value) {
					$value['date_str'] = date('Y-m-d', $value['date']);
				});

				$result = array(
					'print-info'      => $info,
					'volume-schedule' => $schedule,
					'user-list'       => implode(', ', $this->get_option('notify-user-list', array())),
				);
				break;
			case 'update-print-info':
				// Update the current print information if editor needs to make adjustments to the current issue info.
				$info = array(
					'issue'  => (int)self::get_input('issue', 0),
					'volume' => (int)self::get_input('volume', 0),
					'date'   => (int)strtotime(self::get_input('date_str'), 0),
				);

				if ($info['issue'] > 0 && $info['volume'] > 0 && $info['date'] > 0) {
					$this->update_option('print-info', $info);
				}

				break;
			case 'add-print-volume-schedule':
				// Add a new print schedule to the list.
				$schedule = $this->get_volume_schedule();
				$info = array(
					'issue'  => (int)self::get_input('issue', 0),
					'volume' => (int)self::get_input('volume', 0),
					'date'   => (int)strtotime(self::get_input('date_str'), 0),
				);

				// Validate.
				if (isset($schedule[ $info['date'] ]) && !empty($schedule[ $info['date'] ]['locked'])) {
					$result = false;
					break;
				}

				$schedule[ $info['date'] ] = $info;
				if ($this->update_volume_schedule($schedule)) {
					$result = $info;
				}

				break;
			case 'remove-print-volume-schedule':
				// Remove a print schedule from the list.
				$result = false;
				$date = (int)self::get_input('date');

				if (!empty($date)) {
					$schedule = $this->get_volume_schedule();
					if (isset($schedule[ $date ])) {
						unset($schedule[ $date ]);
						if ($this->update_volume_schedule($schedule)) {
							$result = $date;
						}
					}
				}
				break;
			case 'update-notify-user-list':
				// Update the list of users to get notified when new issue is created.
				$list = preg_split('/[,\n\r ]/', sanitize_text_field(self::get_input('list')), -1, PREG_SPLIT_NO_EMPTY);
				if (is_array($list)) {
					array_walk($list, function (&$item, $key) {
						$item = strtolower($item);
					});

					$this->update_option('notify-user-list', array_unique($list));
					$result = implode(', ', $this->get_option('notify-user-list', array()));
				} else {
					$result = false;
				}
				break;
			case 'reconfirm-marker-issue':
				// Use for troubleshooting, reset the admin alert to re-prompt.
				// Can't add nonce here, we need a way to manually force and reset the user alert in some rare occasions.
				$result = delete_user_attribute($current_user->ID, 'print-issue-alert');
				break;
			case 'confirm-marker-issue':
				// Record the action from editor that issue is correct.
				$print_slug = sanitize_title_with_dashes(self::get_input('slug'));
				$result = update_user_attribute($current_user->ID, 'print-issue-alert', $print_slug);
				break;
			case 'fix-marker-issue':
				// Need to fix the issue with information provided.
				$info = array(
					'volume'  => (int)self::get_input('volume'),
					'issue'   => (int)self::get_input('issue'),
					'name'    => sanitize_text_field(self::get_input('name')),
					'slug'    => sanitize_title_with_dashes(self::get_input('slug')),
					'date'    => (int)strtotime(self::get_input('date')),
					'term_id' => (int)self::get_input('term_id'),
				);
				$instance = Print_Issue::get_instance();
				$info = $instance->fix_marker_issue($info);
				if (!isset($info['error'])) {
					update_user_attribute($current_user->ID, 'print-issue-alert', $info['slug']);
					$result = true;
				} else {
					$result = array('error' => $info['error']);
				}
				break;
		}	// End switch()
		ob_clean();
		header('Content-Type: application/json');
		echo wp_json_encode($result);
		wp_die();
	}

	/**
	 * Verify Nonce
	 *
	 * @param string $name The nonce name.
	 *
	 * @return false|int
	 */
	public static function verify_nonce($name = 'nonce') {
		$nonce = self::get_input($name);

		return wp_verify_nonce($nonce, self::NONCE_ACTION);
	}

	/**
	 * Nonce Field
	 *
	 * Renders the nonce field.
	 */
	public static function nonce_field() {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
	}

	/**
	 * Get Option
	 *
	 * @param string $name    Name of the option retrieve.
	 * @param string $default Optional. A default value.
	 *
	 * @return mixed The value of option name, or return array list of options.
	 */
	public function get_option($name, $default = '') {
		if (false === $this->_options) {
			$this->_options = get_option(self::OPT_KEY, array());
		}

		if (!empty($name)) {
			if (isset($this->_options[ $name ])) {
				return $this->_options[ $name ];
			}

			return $default;
		}

		return $this->_options;
	}

	/**
	 * Update Option
	 *
	 * @param string $name  The option name to update.
	 * @param bool   $value The value of the option to update.
	 *
	 * @return bool If the option was updated.
	 */
	public function update_option($name, $value = false) {
		if (empty($name)) {
			return false;
		}
		$this->_options[ $name ] = $this->get_option( $name );
		$this->_options[ $name ] = $value;

		return update_option(self::OPT_KEY, $this->_options);
	}

	/**
	 * Get Volume Schedule
	 *
	 * The schedule cron job only needs to check once a week, therefore it is best to
	 * have it separate from common options.
	 *
	 * @return bool|mixed The Print Volume Schedule.
	 */
	public function get_volume_schedule() {
		if (false === $this->_volume_schedule) {
			$this->_volume_schedule = pmc_get_option('volume-schedule', self::OPT_KEY);
			if ( empty( $this->_volume_schedule ) ) {
				$this->_volume_schedule = [];
			}
			$last_print_timestamp = strtotime('2013-07-23');

			if (empty($this->_volume_schedule[ $last_print_timestamp ])) {
				if (!empty($this->_volume_schedule)) {
					array_walk($this->_volume_schedule, function (&$value) {
						unset($value['locked']);
					});
				}
				// Set last known real print date/volume/issue.
				$this->_volume_schedule[ $last_print_timestamp ] = array(
					'date'   => $last_print_timestamp,
					'volume' => 320,
					'issue'  => 15,
					'locked' => true,
				);
			}
		}

		return $this->_volume_schedule;
	}

	/**
	 * Update Volume Schedule
	 *
	 * Save the list of print volume schedules, valid schedule before saving
	 *
	 * @param array $schedule The array list of schedules.
	 *
	 * @return bool If the schedule was updated.
	 */
	public function update_volume_schedule($schedule) {
		if (is_array($schedule)) {
			foreach ($schedule as $key => $value) {
				if (empty($value['date']) || empty($value['volume']) || empty($value['issue']) || $value['date'] !== $key) {
					return false;
				}
			}
			if (empty($schedule)) {
				pmc_delete_option('volume-schedule', self::OPT_KEY);
				$this->_volume_schedule = false;
			} else {
				ksort($schedule);
				$this->_volume_schedule = $schedule;
				pmc_update_option('volume-schedule', $this->_volume_schedule, self::OPT_KEY);
			}

			return true;
		}

		return false;
	}

	/**
	 * Admin Menu
	 *
	 * @action admin_menu
	 */
	public function action_admin_menu() {
		add_options_page(__('Print Syndication', 'pmc-variety'), __('Print Syndication', 'pmc-variety'), 'manage_options', 'print-issue-settings', array(
			$this,
			'admin_options_page',
		));
	}

	/**
	 * Admin Options Page
	 *
	 * Renders the Print Issue Settings form.
	 *
	 * Ajax is used to add/remove print schedule entries.
	 */
	public function admin_options_page() {

		/**
		 * @since 2017-09-01 Milind More CDWE-499
		 */
		echo \PMC::render_template( CHILD_THEME_PATH . '/plugins/variety-print-issue/templates/print-issue-settings.php' );

	}

}

//EOF
