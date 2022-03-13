<?php

/**
 * Main plugin class.
 */

namespace PMC\Safe_Redirect_Manager;

use PMC\Global_Functions\Traits\Singleton;

/**
 * Main Plugin class.
 */
class Plugin
{

	use Singleton;

	const BULK_ACTION    = 'pmc_make_redirections_permanent';
	const ROW_ACTION     = 'pmc_make_redirections_permanent_row';
	const POST_TYPE_SLUG = 'redirect_rule';
	const NONCE_ACTION   = 'pmc_srm_mark_permanent';

	/**
	 * Class initializatio routine.
	 */
	protected function __construct()
	{
		// Hook before SRM sets the redirect limit.
		add_action('init', [$this, 'setup_hooks_early'], 9);

		add_action('admin_init', array($this, 'setup_hooks'));
	}

	/**
	 * Add hooks early enough to override SRM defaults.
	 */
	public function setup_hooks_early(): void
	{
		add_filter('srm_max_redirects', [$this, 'max_redirects']);
	}

	/**
	 * Function responsible for registering all actions and filters.
	 */
	public function setup_hooks()
	{
		if (!class_exists('\WPCOM_Legacy_Redirector')) {
			// We will never reach this code, added just in case plugin wasn't loadedd on self host envrionment.
			return;  // @codeCoverageIgnore
		}

		// Filters.
		add_filter('bulk_actions-edit-' . self::POST_TYPE_SLUG, array($this, 'filter_bulk_actions'));
		add_filter('handle_bulk_actions-edit-' . self::POST_TYPE_SLUG, array($this, 'handle_bulk_action'), 10, 3);
		add_filter('post_row_actions', array($this, 'filter_post_row_actions'), 10, 2);

		// Actions.
		add_action('admin_notices', array($this, 'action_admin_notices'));
		add_action('admin_enqueue_scripts', [$this, 'action_admin_enqueue_scripts']);
		add_action('wp_ajax_' . self::ROW_ACTION, [$this, 'handle_post_row_action']);
	}

	/**
	 * Enqueue admin scripts
	 */
	public function action_admin_enqueue_scripts($hook)
	{

		if (in_array($hook, ['edit.php'], true) && self::POST_TYPE_SLUG === get_post_type()) {

			wp_enqueue_script(
				'pmc-srm',
				PMC_SAFE_REDIRECT_MANAGER_URI . 'assets/js/pmc-srm.js',
				['jquery'],
				'1.1',
				true
			);

			$wpnonce = wp_create_nonce(self::NONCE_ACTION);

			wp_localize_script(
				'pmc-srm',
				'pmcSRM',
				[
					'_nonce'     => $wpnonce,
					'row_action' => self::ROW_ACTION,
				]
			);
		}
	}

	/**
	 * Add bulk action option for making redirects permanent.
	 *
	 * @param array $bulk_actions Array containing all the Bulk Actions.
	 *
	 * @return array
	 */
	public function filter_bulk_actions($bulk_actions)
	{
		$bulk_actions[self::BULK_ACTION] = __('Move To Legacy Redirect', 'pmc-safe-redirect-manager');
		return $bulk_actions;
	}

	/**
	 * Handler for when bulk action to make redirections permanent is selected.
	 *
	 * @param string $redirect_to URL where the request will be redirected to after processing.
	 * @param string $action      The name of the bulk action being executed.
	 * @param array  $post_ids    Array of Post IDs being processed as bulk action.
	 *
	 * @return string
	 */
	public function handle_bulk_action($redirect_to, $action, $post_ids)
	{

		// Bail out if it's not our custom action.
		if (self::BULK_ACTION !== $action || empty($post_ids)) {
			return $redirect_to;
		}

		$missed = [];
		$moved  = 0;

		// Loop through each selected post to make it permanent redirect.
		foreach ($post_ids as $post_id) {

			try {
				// Mark the redirection permanent.
				$this->move_to_legacy_redirect($post_id);
			} catch (\Exception $e) {

				$missed[] = $post_id;
				continue;
			}

			$moved++;
		}

		$redirect_to = remove_query_arg(wp_removable_query_args(), $redirect_to);

		$redirect_to = remove_query_arg('pmc_srm_msg', $redirect_to);

		$redirect_to = add_query_arg('pmc_srm_moved', $moved, $redirect_to);

		if (!empty($missed)) {
			$redirect_to = add_query_arg('pmc_srm_missed', implode(',', $missed), $redirect_to);
		}

		return $redirect_to;
	}

	/**
	 * Add Post action option for making redirects permanent.
	 *
	 * @param array $actions Array containing all the Post Actions.
	 *
	 * @return array
	 */
	public function filter_post_row_actions($actions, $post)
	{

		// Bail out if not our post type.
		if (!is_object($post) || self::POST_TYPE_SLUG !== $post->post_type) {
			return $actions;
		}

		// Bail out if post is trashed or current user don't have enough permission.
		if ('trash' === $post->post_status || !current_user_can('edit_post', $post->ID)) {
			return $actions;
		}

		$action_url = sprintf(
			'<a href="#" data-id="%d" class="pmc-srm-row-mark-permanent" aria-label="%s">%s</a>',
			$post->ID,
			esc_html__('Move Entry To Permanent Legacy Redirect', 'pmc-safe-redirect-manager'),
			esc_html__('Move To Legacy Redirect', 'pmc-safe-redirect-manager')
		);

		$actions[self::ROW_ACTION] = $action_url;

		return $actions;
	}

	/**
	 * Handles post row action for making redirect permanent.
	 */
	public function handle_post_row_action()
	{

		$nonce = \PMC::filter_input(INPUT_POST, 'security', FILTER_SANITIZE_STRING);

		if (empty($nonce) || !wp_verify_nonce($nonce, self::NONCE_ACTION)) {
			wp_send_json_error('Nonce verification failed.');
		}

		$post_id = \PMC::filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_STRING);

		try {

			if (empty($post_id)) { // WPCS: Input var okay. CSRF okay.
				throw new \Exception('No Post ID specified');
			}

			$post_id = (int) $post_id; // WPCS: Input var okay. CSRF okay.

			if ($post_id <= 0) {
				throw new \Exception('Invalid Post ID');
			}

			$post = get_post($post_id);

			if (!$post || self::POST_TYPE_SLUG !== $post->post_type) {
				throw new \Exception('Invalid Post');
			}

			$args = array(
				'pmc_srm_moved'  => 1,
				'pmc_srm_missed' => 0,
			);

			$this->move_to_legacy_redirect($post_id);
		} catch (\Exception $e) {

			$message = $e->getMessage();

			wp_send_json_error($message);
		}

		return wp_send_json_success(true);
	}

	/**
	 * Helper function to move redirections to wpcom vip legacy redirect.
	 *
	 * @param string $post_id Post ID.
	 *
	 * @throws \Exception Throws base exception containing message.
	 *
	 * @return bool
	 */
	private function move_to_legacy_redirect($post_id)
	{

		// Return if post_id is empty.
		if (empty($post_id)) {
			throw new \Exception('Post ID not found.');
		}

		$post = get_post($post_id);

		if (empty($post)) {
			throw new \Exception('Post not found.');
		}

		if (!current_user_can('edit_post', $post->ID)) {
			throw new \Exception('Not authorized to perform this action.');
		}

		// Retrieve redirection info.
		$redirect_from_url = get_post_meta($post->ID, '_redirect_rule_from', true);
		$redirect_to_url   = get_post_meta($post->ID, '_redirect_rule_to', true);
		$regex_enabled     = get_post_meta($post->ID, '_redirect_rule_from_regex', true);

		if (!empty($regex_enabled)) {
			throw new \Exception('Regex redirections are not supported for moving to legacy redirect.');
		}

		// Bail out if no proper data is found.
		if (empty($redirect_from_url) || empty($redirect_to_url)) {
			throw new \Exception('Invalid Data.');
		}

		// Check if the current url is a valid WordPress post and is accessible.
		$from_post_id = url_to_postid(home_url($redirect_from_url));
		$from_post    = get_post($from_post_id);

		if (!empty($from_post) && 'publish' === $from_post->post_status) {
			throw new \Exception('From URL must be 404 for moving to legacy redirect.');
		}

		// Append forward slash at the end of the from url unless it has file extension or query string.
		$redirect_from_url = $this->trailingslashit($redirect_from_url);

		// Remove the host part if it matches the site domain.
		$redirect_to_host = wp_parse_url($redirect_to_url, PHP_URL_HOST);

		if ($redirect_to_host === $_SERVER['HTTP_HOST']) { // phpcs:ignore
			$redirect_to_url = $this->trailingslashit(explode($redirect_to_host, $redirect_to_url)[1]);
		}

		// Insert permanent redirect.
		$inserted = \WPCOM_Legacy_Redirector::insert_legacy_redirect($redirect_from_url, $redirect_to_url);

		if (is_wp_error($inserted)) {
			throw new \Exception($inserted->get_error_message());
		}

		// Trash the Safe Redirect Manager entry.
		wp_trash_post($post->ID);

		return true;
	}

	/**
	 * Admin Notices handler, handles notifying user about bulk action results.
	 */
	public function action_admin_notices()
	{

		if (!get_current_screen() || self::POST_TYPE_SLUG !== get_current_screen()->post_type) {
			return;
		}

		echo '<div id="message" class="notice notice-info is-dismissible"><p><strong>Note: Make sure "From" URL is 404 before marking it permanent.</strong></p></div>';

		$moved   = isset($_GET['pmc_srm_moved']) ? \PMC::filter_input(INPUT_GET, 'pmc_srm_moved', FILTER_VALIDATE_INT) : false; // WPCS: Input var okay. CSRF okay.
		$missed  = !empty($_GET['pmc_srm_missed']) ? \PMC::filter_input(INPUT_GET, 'pmc_srm_missed', FILTER_SANITIZE_STRING) : false; // WPCS: Input var okay. CSRF okay.
		$message = !empty($_GET['pmc_srm_msg']) ? \PMC::filter_input(INPUT_GET, 'pmc_srm_msg', FILTER_SANITIZE_STRING) : false; // WPCS: Input var okay. CSRF okay.

		if (empty($moved) && empty($missed) && empty($message)) {
			return;
		}

		echo '<div id="message" class="updated notice is-dismissible"><p>';

		if (false !== $moved) {
			printf('<strong>%s</strong> redirections moved to permanent legacy redirect. <br>', esc_html($moved));
		}

		if (!empty($missed)) {

			$missed_ids = explode(',', $missed);

			printf('Failed to move <strong>%s</strong> redirections to permanent legacy redirect ( Post IDs : ', esc_html(count($missed_ids)));

			foreach ($missed_ids as $post_id) {

				$post_edit_url = get_edit_post_link($post_id);
				printf((!empty($post_edit_url)) ? '<a href="%s">%s</a> ' : '', esc_url($post_edit_url), esc_html($post_id));
			}

			echo ' ). <br>';
		}

		if (!empty($message)) {
			printf('<strong>%s</strong>', esc_html($message));
		}

		echo '</p></div>';
	}

	/**
	 * Appends forward slash at the end of the url unless it has file extension or query string.
	 *
	 * @param string $url URL to which the trailing slash is to be appended.
	 *
	 * @return string
	 */
	private function trailingslashit($url)
	{

		$path = wp_parse_url($url, PHP_URL_PATH);

		if (empty(wp_parse_url($url, PHP_URL_QUERY)) && empty(pathinfo($path, PATHINFO_EXTENSION))) {
			$url = trailingslashit($url);
		}

		return $url;
	}

	/**
	 * Sets the max number of redirects to 250.
	 *
	 * @param int $value
	 *
	 * @return int
	 */
	public function max_redirects($value): int
	{
		return 250;
	}
}
