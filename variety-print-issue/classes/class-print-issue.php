<?php

/**
 * Variety Print Issue
 *
 * Based heavily on Variety_Print_Issue from
 * the pmc-variety-2014 theme.
 *
 * @package pmc-variety-2017
 * @since 2017.1.0
 */

namespace Variety\Plugins\Variety_Print_Issue;

use \PMC\Global_Functions\Traits\Singleton;

/**
 * Class Print_Issue
 *
 * @package pmc-variety-2017
 */
class Print_Issue
{

	use Singleton;

	/**
	 * The Print Taxonomy slug.
	 */
	const PRINT_TAXONOMY = 'print-issues';

	/**
	 * The Print Info option name.
	 */
	const OPTION = 'print-info';

	/**
	 * The Volume Schedule value
	 *
	 * @var array Array of schedule data.
	 */
	protected $_volume_schedule = array();

	/**
	 * Array of Print info data.
	 *
	 * @var array Print info data.
	 */
	protected $_print_info
	= array(
		'volume'  => 0,
		'issue'   => 0,
		'date'    => 0,
		'changed' => false,
	);

	/**
	 * Cover Image Uploader information.
	 *
	 * @var array Nonce data.
	 */
	protected $_image_nonce = array(
		'name'   => 'print-issues-nonce',
		'action' => 'update-cover-image',
	);

	/**
	 * Class constructor.
	 */
	protected function __construct()
	{

		// Register Taxonomy.
		add_action('init', array($this, 'pmc_register_print_taxonomy'));

		// Upload Cover Image to Term.
		add_action(self::PRINT_TAXONOMY . '_add_form_fields', array($this, 'new_term_uploader'));
		add_action(self::PRINT_TAXONOMY . '_edit_form_fields', array($this, 'update_term_uploader'));
		add_action('created_' . self::PRINT_TAXONOMY, array($this, 'save_term_image'));
		add_action('edited_' . self::PRINT_TAXONOMY, array($this, 'save_term_image'));
		add_action('admin_enqueue_scripts', array($this, 'action_admin_enqueue_scripts'));

		// Other Actions.
		add_action('admin_init', array($this, 'action_admin_init'));
		add_action('pmc_print_issue_run_schedule_on_thursday', array($this, 'run_schedule'));
		add_action('init', array($this, 'add_rewrite_rule'));
		add_action('template_redirect', array($this, 'on_template_redirect'));

		// Filters.
		add_filter('wpcom_is_globalized_taxonomy', array($this, 'set_is_globalized'), 10, 2);
		add_filter('cron_schedules', array($this, 'add_cron_schedules'));
		add_filter('pmc_global_cheezcap_options', array($this, 'filter_pmc_global_cheezcap_options'));
		add_filter('query_vars', array($this, 'add_query_vars'));

		if (!wp_next_scheduled('pmc_print_issue_run_schedule_on_thursday')) {
			wp_schedule_event($this->get_next_schedule_utctime(), 'pmc_weekly', 'pmc_print_issue_run_schedule_on_thursday');
		}
	}

	/**
	 * Set is globalized
	 *
	 * Sets the globalized taxonomy value.
	 *
	 * @filter wpcom_is_globalized_taxonomy
	 *
	 * @param bool   $is_globalized If the taxonomy is globalized.
	 * @param string $taxonomy The taxonomy slug.
	 *
	 * @return bool
	 */
	public function set_is_globalized($is_globalized, $taxonomy)
	{
		if (self::PRINT_TAXONOMY === $taxonomy) {
			$is_globalized = false;
		}

		return $is_globalized;
	}

	/**
	 * Admin Init
	 *
	 * Actions to take on admin_init.
	 *
	 * @action admin_init
	 */
	public function action_admin_init()
	{

		// Instantiate dependency class.
		Print_Issue_Alert::get_instance();

		// Set the print info data.
		$this->_print_info = Print_Issue_Setting::get_instance()->get_option(self::OPTION, $this->_print_info);

		// Maybe initialize print info with empty data.
		if (empty($this->_print_info['date'])) {
			$this->generate();
			$this->update_print_info();
			$marker_issue = $this->get_marker_issue();
			if (empty($marker_issue) || $marker_issue['date'] < $this->_print_info['date']) {
				$this->update_marker();
			}
		}
	}

	/**
	 * Get Next Schedule UTC Time
	 *
	 * Return UTC time for next schedule.
	 *
	 * Maintaining former function name from 2014.
	 *
	 * @return int Time for the next event.
	 */
	public function get_next_schedule_utctime()
	{
		/*
		 * Auto-create a new issue each week after print issue published.
		 * Note: Variety print issue is usually publish on Wednesday.
		 * Set the schedule to run on Monday at 1:00am Los Angeles time.
		 */
		$next_monday_pacific = new \DateTime('Next Thursday 01:00', new \DateTimeZone('America/Los_Angeles'));
		$next_monday_utc     = $next_monday_pacific->setTimezone(new \DateTimeZone('UTC'));

		// Return UTC timestamp.
		return $next_monday_utc->getTimestamp();
	}

	/**
	 * Add Chron Schedules
	 *
	 * @filter add_cron_schedules
	 *
	 * @param array $schedules Schedules array.
	 *
	 * @return array Updated Schedules.
	 */
	public function add_cron_schedules($schedules = array())
	{
		$schedules['pmc_weekly'] = array(
			'interval' => 604800,
			'display'  => __('Weekly'),
		);

		return $schedules;
	}

	/**
	 * Run Schedule
	 *
	 * This function is called by schedule job to update current issue marker.
	 *
	 * @action pmc_print_issue_run_schedule_on_thursday
	 */
	public function run_schedule()
	{
		$this->update_marker();
		$this->clean_volume_schedule();
		$this->write_log(array('issue' => $this->_print_info));
	}

	/**
	 * Function to manually fix an issue due to print schedule does not fall into normal schedule
	 *
	 * Expects data in the following format:
	 * array(
	 *   'date'   => int/string, The date in string that can be convert by strtotime.
	 *   'issue'  => int,        The issue number.
	 *   'volume' => int,        the volume number.
	 * )
	 *
	 * @param array $info The issue information to update.
	 *
	 * @return array (
	 *        'slug'   => string, The issue slug.
	 *        'volume' => int,    The volume number.
	 *        'issue'  => int,    The issue number.
	 *        'date'   => int,    The issue date timestamp.
	 *        'error'  => string, The error message of the data is invalid.
	 *    )
	 */
	public function fix_marker_issue($info)
	{
		if (!is_numeric($info['date'])) {
			$info['date'] = (int) strtotime($info['date']);
		} else {
			$info['date'] = (int) ($info['date']);
		}

		if (empty($info['date'])) {
			$info['error'] = __('Invalid print date, value must be in correct format YYYY-MM-DD', 'pmc-variety');

			return $info;
		}

		if (!is_numeric($info['volume']) || (int) $info['volume'] < 1) {
			$info['error'] = __('Invalid volume, value number must be possitive whole number', 'pmc-variety');

			return $info;
		}

		if (!is_numeric($info['issue']) || (int) $info['issue'] < 1) {
			$info['error'] = __('Invalid issue number, value number must be positive whole number', 'pmc-variety');

			return $info;
		}

		if (!empty($info['term_id'])) {
			$print_term = get_term_by('term_id', (int) $info['term_id'], self::PRINT_TAXONOMY);
		}

		if (empty($print_term) && !empty($info['slug'])) {
			$slug       = sanitize_title_with_dashes($info['slug']);
			$print_term = get_term_by('slug', $slug, self::PRINT_TAXONOMY);
		}

		$print_info = array(
			'slug'   => $this->get_slug($info),
			'volume' => (int) $info['volume'],
			'issue'  => (int) $info['issue'],
			'date'   => (int) $info['date'],
		);

		if (!empty($print_term)) {
			if (empty($info['name'])) {
				$info['name'] = $this->get_auto_display_title($print_info['date']);
			}
			$title     = sanitize_text_field($info['name']);
			$slug      = sanitize_title_with_dashes($print_info['slug']);
			$term_meta = array('seo_title' => $title);
			wp_update_term($print_term->term_id, self::PRINT_TAXONOMY, array(
				'slug' => $slug,
				'name' => $slug,
			));

			if (class_exists('\\PMC_Term_Meta')) {
				\PMC_Term_Meta::get_instance()->save_multiple($print_term->term_id, self::PRINT_TAXONOMY, $term_meta);
			}

			clean_term_cache($print_term->term_id, self::PRINT_TAXONOMY);
		}

		// Store the updated information.
		$setting = Print_Issue_Setting::get_instance();
		$setting->update_option(self::OPTION, $print_info);

		return $print_info;
	}

	/**
	 * Return the issue title from the issue slug
	 *
	 * @param string $slug The issue slug (taxonomy term slug)
	 *
	 * @return false | string False if cannot parse slug, otherwise issue title
	 */
	public function get_display_title_from_slug($slug)
	{
		$info = $this->parse_slug($slug);
		if (!empty($info)) {
			return $this->get_auto_display_title($info['date']);
		}

		return false;
	}

	/**
	 * Auto generate the print title from a given date
	 *
	 * @param int $date The date timestamp
	 *
	 * @return string   The generated print title
	 */
	public function get_auto_display_title($date)
	{
		return sprintf(__('From the %s issue of Variety', 'pmc-variety'), date('F d, Y', $date));
	}

	/**
	 * Get the current issue where the issue-marker taxonomy is pointing to
	 * <issue-marker>::parent => current-issue
	 *
	 * @return false  If there is not print marker found
	 *    array(
	 *       'slug'    => string, issue slug
	 *       'name'    => string, The seo title or name
	 *       'term_id' => int,    The term_id
	 *    )
	 */
	public function get_marker_issue()
	{
		$marker = get_term_by('slug', 'issue-marker', self::PRINT_TAXONOMY);
		if (!empty($marker->parent)) {
			$term = get_term_by('term_id', $marker->parent, self::PRINT_TAXONOMY);
			if (!empty($term)) {
				$result     = $this->parse_slug($term->slug);
				$term_attrs = (class_exists('\\PMC_Term_Meta')) ? \PMC_Term_Meta::get_all($term) : array();

				return array_merge($result, array(
					'slug'    => $term->slug,
					'name'    => !empty($term_attrs['seo_title']) ? $term_attrs['seo_title'] : $term->name,
					'term_id' => $term->term_id,
				));
			}
		}

		return false;
	}

	/**
	 * Update the current issue marker a specific date.  Auto generate issue if needed.
	 *
	 * @param mixed &$date The date to set and/or return.
	 *
	 * @return bool
	 */
	protected function update_marker(&$date = false)
	{
		$marker = get_term_by('slug', 'issue-marker', self::PRINT_TAXONOMY);
		if (!empty($marker)) {
			$term = $this->generate($date);
			if (!empty($term)) {
				wp_update_term($marker->term_id, self::PRINT_TAXONOMY, array('parent' => $term->term_id));
				clean_term_cache($marker->term_id, self::PRINT_TAXONOMY);
				$this->write_log('update_marker: marker => term_id=' . $marker->term_id . ', parent=' . $term->term_id);

				return true;
			}
		} else {
			$this->write_log('update_marker: issue marker not found');
		}
		$this->update_print_info();

		return false;
	}

	/**
	 * Update Print Info
	 *
	 * Update current print issue if change is detected
	 *
	 * @return bool
	 */
	public function update_print_info()
	{
		if (empty($this->_print_info['changed'])) {
			return false;
		}
		unset($this->_print_info['changed']);
		$setting = Print_Issue_Setting::get_instance();
		$setting->update_option(self::OPTION, $this->_print_info);

		return true;
	}

	/**
	 * Generate
	 *
	 * Generate the new issue base on a given date or current date using print schedule.
	 *
	 * @param int|bool &$date Issue date timestamp.
	 *
	 * @return object $term  The issue taxonomy object term.
	 */
	function generate(&$date = false)
	{
		$print_info = $this->get_print_info($date);
		$slug       = $this->get_slug($print_info);
		$term       = get_term_by('slug', $slug, self::PRINT_TAXONOMY);
		if (empty($term)) {
			$term = get_term_by('slug', 'weekly-variety', self::PRINT_TAXONOMY);
			if (empty($term)) {
				$this->write_log('generate: Cannot locate weekly-variety');

				return false;
			}
			$title  = $this->get_auto_display_title($print_info['date']);
			$result = wp_insert_term(
				$slug,
				self::PRINT_TAXONOMY,
				array(
					'slug'        => $slug,
					'parent'      => $term->term_id,
					'description' => '',
				)
			);

			$term_meta = array('seo_title' => $title);

			if (is_array($result)) {
				$term = get_term_by('id', $result['term_id'], self::PRINT_TAXONOMY);

				// Something is wrong.
				if (empty($term) || is_wp_error($term)) {
					$term = get_term($result['term_id'], self::PRINT_TAXONOMY);

					if (empty($term) || is_wp_error($term)) {
						$this->write_log('generate: cannot retrieve new term by id');

						return false;
					}
				}

				// Save the date in the term meta for easy access.
				update_term_meta($term->term_id, 'pub_date', $print_info['date']);
				if (class_exists('\\PMC_Term_Meta')) {
					\PMC_Term_Meta::get_instance()->save_multiple($term->term_id, self::PRINT_TAXONOMY, $term_meta);
				}
			} else {
				$this->write_log('generate: error creating new issue term - ' . $slug);
			}
		} else {
			$this->write_log('generate: term found - id=' . $term->term_id . ', slug=' . $slug);
		}	// End if()

		return $term;
	}

	/**
	 * Get Volume Schedule
	 *
	 * @return array The array of volume schedule information
	 */
	public function get_volume_schedule()
	{
		return Print_Issue_Setting::get_instance()->get_volume_schedule();
	}

	/**
	 * To Print Date
	 *
	 * Converts a given date/time into a print date value.
	 *
	 * @param int|string $date A Date.
	 *
	 * @return int The time.
	 */
	public function to_print_date($date)
	{
		if (is_numeric($date)) {
			$date = (int) $date;
		} else {
			$date = strtotime($date);
		}
		$date = strtotime('Tuesday ', strtotime(date('Y-m-d', $date)));

		return $date;
	}

	/**
	 * Get Volume Info
	 *
	 * Returns the volume information based on a given optional date,
	 * the $data value is adjust accordingly to a print
	 * date base on print schedule
	 *
	 * @param int &$date The issue date
	 *
	 * @return array (
	 *       'volume' => int, The volume number if found
	 *       'issue'  => int, The issue number if found
	 *       'date'   => int, The issue date timestamp if found
	 *    )
	 */
	public function get_volume_info(&$date)
	{
		$found           = false;
		$volume_info     = array(
			'volume' => 0,
			'issue'  => 0,
			'date'   => 0,
		);
		$volume_schedule = $this->get_volume_schedule();

		// Add current issue date to schedule to look up correctly due to an override.
		if (!empty($this->_print_info['date'])) {
			$volume_schedule[$this->_print_info['date']] = $this->_print_info;
		}
		// Adjust date to correct print date.
		$date = $this->to_print_date($date);

		// Sort the schedule keys in order to get get correct issue date.
		$keys = array_keys($volume_schedule);
		sort($keys, SORT_NUMERIC);
		$count = count($keys);

		/*
		 * Loop through the schedule to find the first set of date where it fall within the two scheduled date ranges.
		 * print_schedule_date_1 <= current date < print_schedule_date_2
		 */
		for ($i = 1; $i < $count; $i++) {
			if ($keys[$i - 1] <= $date && $date < $keys[$i]) {
				$found       = $keys[$i - 1];
				$volume_info = $volume_schedule[$found];
				break;
			}
		}

		if (empty($found)) {
			if ($date > $keys[$count - 1]) {
				$volume_info = $volume_schedule[$found];
			}
		}

		return $volume_info;
	}

	/**
	 * Clean Volume Schedule
	 *
	 * Cleans the schedule option regularly by removing the old schedule.
	 * date1 <-- remove
	 * date2 <-- keep this record and beyond
	 * <-- current date
	 * <-- future dates
	 */
	public function clean_volume_schedule()
	{
		$date            = $this->to_print_date(strtotime('Tuesday'));
		$instance        = Print_Issue_Setting::get_instance();
		$volume_schedule = $instance->get_volume_schedule();
		$keys            = array_keys($volume_schedule);
		$count           = count($keys);
		sort($keys, SORT_NUMERIC);

		$old_keys = array();
		for ($i = 1; $i < $count; $i++) {
			if ($keys[$i - 1] <= $date && $date < $keys[$i]) {
				break;
			}
			$old_keys[] = $keys[$i - 1];
		}

		foreach ($old_keys as $key) {
			if (empty($volume_schedule[$key])) {
				continue;
			}
			if (empty($volume_schedule[$key]['locked'])) {
				unset($volume_schedule[$key]);
			}
		}

		// Schedules have been removed and need to be saved.
		if (count($volume_schedule) < $count) {
			$instance->update_volume_schedule($volume_schedule);
		}
	}

	/**
	 * Get Print Info
	 *
	 * Get the print information based on a date or current date.
	 *
	 * @return array (
	 *        'volume'  => int,  The volume number
	 *        'issue'   => int,  The issue number
	 *        'date'    => int,  The issue date timestamp
	 *        'changed' => bool, True if data changed, otherwise False
	 *    )
	 */
	public function get_print_info(&$date = false)
	{
		if (empty($date)) {
			$date = time();
		}
		$volume_info = $this->get_volume_info($date);
		$volume      = $volume_info['volume'];
		if (!empty($this->_print_info['issue']) && $this->_print_info['volume'] >= $volume) {
			$volume_info = $this->_print_info;
		}

		$start_date  = $volume_info['date'];
		$start_issue = $volume_info['issue'];

		$diff  = date_diff(date_create(date('Y-m-d', $start_date)), date_create(date('Y-m-d', $date)));
		$weeks = intval($diff->days / 7);
		$issue = ($diff->invert) ? $start_issue - $weeks : $start_issue + $weeks;

		$this->_print_info['changed'] = ($this->_print_info['volume'] !== $volume || $this->_print_info['issue'] !== $issue || $this->_print_info['date'] !== $date);
		$this->_print_info['volume']  = $volume;
		$this->_print_info['issue']   = $issue;
		$this->_print_info['date']    = $date;

		return $this->_print_info;
	}

	/**
	 * Parse Slug
	 *
	 * Parse the slug for volume, issue, and date.
	 *
	 * @param $slug string   The issue slug
	 *
	 * @return array An array of data.
	 */
	public function parse_slug($slug)
	{
		if (!is_string($slug)) {
			return array();
		};

		$tokens = explode('-', $slug);

		if (is_array($tokens) && 5 === count($tokens)) {
			list($volume, $issue, $month, $day, $year) = $tokens;

			return array(
				'volume' => $volume,
				'issue'  => $issue,
				'date'   => strtotime("$month $day $year"),
			);
		}

		return array();
	}

	/**
	 * Get Slug
	 *
	 * Generate the Issue slug.
	 *
	 * Expects data in the following format:
	 * array (
	 *   'volume'  => int,  The volume number
	 *   'issue'   => int,  The issue number
	 *   'date'    => int,  The issue date timestamp
	 * )
	 *
	 * @param array $data An array of data.
	 *
	 * @return string The issue slug in the form of [volume]-[issue]-[FullMonth-Day-Year]
	 */
	public function get_slug($data = array())
	{
		$volume = !empty($data['volume']) ? (int) $data['volume'] : 0;
		$issue  = !empty($data['issue']) ? (int) $data['issue'] : 0;
		$date   = 0;
		if (!empty($data['date'])) {
			if (is_numeric($data['date'])) {
				$date = (int) $data['date'];
			} else {
				$date = strtotime($data['date']);
			}
		}

		return sanitize_title_with_dashes(sprintf('%d-%d-%s', $volume, $issue, date('F-d-Y', $date)));
	}

	/**
	 * Write Log
	 *
	 * Write log into the PMC Option for troubleshooting.
	 *
	 * @param mixed $data The data to be logged.
	 */
	public function write_log($data)
	{
		$histories = pmc_get_option('run-histories', 'variety-print-issue');
		if (empty($histories)) {
			$histories = [];
		}

		if (is_array($histories) && count($histories) > 20) {
			array_shift($histories);
		}
		$histories[] = array('timestamp' => time(), 'data' => $data);
		pmc_update_option('run-histories', $histories, 'variety-print-issue');
	}

	/**
	 * Register the Print Taxonomy
	 *
	 * @action init
	 */
	public function pmc_register_print_taxonomy()
	{
		$post_types = array(
			'post',
			'pmc-gallery',
			'pmc-content',
			'variety_top_video',
		);
		$args       = array(
			'label'        => __('Print Issue', 'pmc-variety'),
			'labels'       => array(
				'name'               => _x('Print Issues', 'taxonomy general name', 'pmc-variety'),
				'singular_name'      => _x('Print Issue', 'taxonomy singular name', 'pmc-variety'),
				'add_new_item'       => __('Add New Print Issue', 'pmc-variety'),
				'edit_item'          => __('Edit Print Issue', 'pmc-variety'),
				'new_item'           => __('New Print Issue', 'pmc-variety'),
				'view_item'          => __('View Print Issue', 'pmc-variety'),
				'search_items'       => __('Search Print Issues', 'pmc-variety'),
				'not_found'          => __('No Print Issues found.', 'pmc-variety'),
				'not_found_in_trash' => __('No Print Issues found in Trash.', 'pmc-variety'),
				'all_items'          => __('Print Issues', 'pmc-variety'),
			),
			'query_var'    => true,
			'show_ui'      => true,
			'hierarchical' => true,
			'capabilities' => array(
				'manage_terms' => 'manage_categories',
				'edit_terms'   => 'manage_categories',
				'delete_terms' => 'manage_categories',
				'assign_terms' => 'edit_posts',
			),
		);
		register_taxonomy(self::PRINT_TAXONOMY, $post_types, $args);
	}

	/**
	 * New Term Uploader
	 *
	 * Adds a Cover Image uploader to the "Add New Print Issue" section
	 * of the Print Issues taxonomy screen.
	 *
	 * @since 2017.1.0
	 * @action print-issues_add_form_fields
	 */
	public function new_term_uploader()
	{

		$image_nonce_action = $this->_image_nonce['action'];
		$image_nonce_name   = $this->_image_nonce['name'];

		/**
		 * @since 2017-09-01 Milind More CDWE-499
		 */
		echo \PMC::render_template(
			CHILD_THEME_PATH . '/plugins/variety-print-issue/templates/print-issue-term-uploader-new.php',
			array(
				'image_nonce_action' => $image_nonce_action,
				'image_nonce_name'   => $image_nonce_name,
			)
		);
	}

	/**
	 * Update Term Uploader
	 *
	 * Renders the Cover image uploader to the Print Issue term edit
	 * screen.
	 *
	 * @since 2017.1.0
	 * @action print-issues_edit_form_fields
	 *
	 * @param object $term The current \WP_Term object.
	 */
	public function update_term_uploader($term)
	{

		$image_nonce_action = $this->_image_nonce['action'];
		$image_nonce_name   = $this->_image_nonce['name'];

		$image_id = get_term_meta($term->term_id, 'print-issue-image-id', true);
		$image_id = !empty($image_id) ? $image_id : '';
		// Get the attachment source of the id.
		$img_src = wp_get_attachment_image_url($image_id, 'full');

		/**
		 * @since 2017-09-01 Milind More CDWE-499
		 */
		echo \PMC::render_template(
			CHILD_THEME_PATH . '/plugins/variety-print-issue/templates/print-issue-term-uploader-update.php',
			array(
				'image_nonce_action' => $image_nonce_action,
				'image_nonce_name'   => $image_nonce_name,
				'image_id'           => $image_id,
				'img_src'            => $img_src,
			)
		);
	}

	/**
	 * Save Term Image
	 *
	 * Saves the Term cover image when either creating or editing a
	 * Print Issue Term.
	 *
	 * @since 2017.1.0
	 * @action created_print-issues
	 * @action edited_print-issues
	 *
	 * @param int $term_id The present \WP_Term ID.
	 */
	public function save_term_image($term_id)
	{
		if (
			empty($_POST[$this->_image_nonce['name']]) // WPCS: Input var okay.
			|| !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[$this->_image_nonce['name']])), $this->_image_nonce['action']) // WPCS: Input var okay.
		) {
			return;
		}

		if (!empty($_POST['print-issue-image-id'])) { // WPCS: Input var okay.
			$image = sanitize_text_field(wp_unslash($_POST['print-issue-image-id'])); // WPCS: Input var okay.
			update_term_meta($term_id, 'print-issue-image-id', $image);
		} else {
			update_term_meta($term_id, 'print-issue-image-id', '');
		}
	}

	/**
	 * @codeCoverageIgnore
	 * Enqueue Scripts
	 *
	 * @param string $page The current Admin Screen.
	 *
	 * @since  2017.1.0
	 * @action admin_enqueue_scripts
	 *
	 */
	public function action_admin_enqueue_scripts($page)
	{
		if ('term.php' !== $page && 'edit-tags.php' !== $page) {
			return;
		}
		wp_enqueue_media();
		wp_register_script('variety-print-term-js', plugins_url('assets/js/print-term.js', dirname(__FILE__)), array('jquery', 'wp-util'), false, true);

		$exports = array(
			'modalTitle' => __('Select or Upload a Cover', 'pmc-variety'),
			'buttonText' => __('Insert Cover', 'pmc-variety'),
		);

		wp_scripts()->add_data(
			'variety-print-term-js',
			'data',
			sprintf('var _varietyPrintIssueTermExports = %s;', wp_json_encode($exports))
		);
		wp_add_inline_script('variety-print-term-js', 'varietyPrintIssueTerm.init();', 'after');
		wp_enqueue_script('variety-print-term-js');
	}

	/**
	 * Filter PMC Global Cheescap Options
	 *
	 * Adds a field to the global theme options to set a URL
	 * to the Print Subscription page.
	 *
	 * @since 2017.1.0
	 * @action pmc_global_cheezcap_options
	 * @param array $options Cheezcap Options.
	 *
	 * @return array Updated Cheezcap Options.
	 */
	public function filter_pmc_global_cheezcap_options($options = array())
	{

		if (class_exists('\\CheezCapTextOption')) {
			$options[] = new \CheezCapTextOption(
				__('Print Subscription URL', 'pmc-variety'),
				__('Add the URL to the Print Subscription page.', 'pmc-variety'),
				'print-subscription-url',
				'https://variety.com/subscribe-us/'
			);
		}

		return $options;
	}

	/**
	 * To add rewrite rule for print issue cover image.
	 *
	 * @return void
	 */
	public function add_rewrite_rule()
	{

		// Ex. Url : http://variety.com/print-issue-cover-image/weekly/2017-06-06/
		add_rewrite_rule('print-issue-cover-image/weekly/([\d]{4}-[\d]{2}-[\d]{2})/?$', 'index.php?print_issue_date=$matches[1]', 'top');
	}

	/**
	 * To add print_issue_date query variable.
	 *
	 * @param  array $vars Query variables.
	 *
	 * @return array Query variables.
	 */
	public function add_query_vars($vars = array())
	{

		if (empty($vars) || !is_array($vars)) {
			$vars = array();
		}

		// For Print issue.
		$vars[] = 'print_issue_date';

		return $vars;
	}

	/**
	 * To show print issue cover image on custom endpoint.
	 *
	 * @return void
	 */
	public function on_template_redirect()
	{

		$print_issue_date = get_query_var('print_issue_date');

		if (empty($print_issue_date)) {
			return;
		}

		$term = $this->_get_print_issue_term_by_date($print_issue_date);

		if (!empty($term)) {

			$cover_image_id = get_term_meta($term->term_id, 'print-issue-image-id', true);

			if (!empty($cover_image_id)) {

				$mime_type = get_post_mime_type($cover_image_id);
				$cover_image = wp_get_attachment_image_src($cover_image_id, 'full');

				if (!empty($mime_type) && !empty($cover_image)) {
					// Here, we need to show image in custom end point.
					// so, sending custom header according to image type.
					// and image content, And then die (not wp_die()).
					header('Content-Type: ' . $mime_type);
					echo wpcom_vip_file_get_contents($cover_image[0]); // @codingStandardsIgnoreLine
					die;
				}
			}
		}

		// show 404.
		global $wp_query;
		$wp_query->set_404();

		return;
	}

	/**
	 * To get print issue term by date.
	 *
	 * @param  string $date Date in yyyy-mm-dd format.
	 *
	 * @return boolean|\WP_Term
	 */
	protected function _get_print_issue_term_by_date($date)
	{

		if (empty($date)) {
			return false;
		}

		$slug = date('F-d-Y', strtotime($date));
		$slug = strtolower($slug);

		if (empty($slug)) {
			return false;
		}

		$args = array(
			'taxonomy'   => self::PRINT_TAXONOMY,
			'hide_empty' => false,
			'name__like' => $slug,
		);

		$terms = get_terms($args);

		if (empty($terms) || is_wp_error($terms) || !is_array($terms)) {
			return false;
		}

		$print_issue = false;

		foreach ($terms as $term) {
			if (preg_match('/' . preg_quote($slug, '/') . '$/', $term->slug)) {
				$print_issue = $term;
				break;
			}
		}

		if (empty($print_issue)) {
			return false;
		}

		return $print_issue;
	}
}
