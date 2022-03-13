<?php

/**
 * Expansion of Co-Authors Guest Author functionality
 *
 * Added functionality to Guest Authors. Add more fields and support Multipost thumbnail.
 */

require_once(__DIR__ . '/dependencies.php');

// Instantiate
PMC\Guest_Authors\SEO::get_instance();
PMC\Guest_Authors\Shortcode::get_instance();

if (defined('WP_CLI') && WP_CLI) {
	\WP_CLI::add_command('pmc-guest-authors', \PMC\Guest_Authors\CLI::class);
}

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Guest_Authors
{

	use Singleton;

	const post_type = 'guest-author';
	const CACHE_GROUP  = 'pmc-guest-author-cache';
	const CACHE_EXPIRE = 600; // 10 minutes

	protected function __construct()
	{

		add_action('init', array($this, 'init'));
	}

	public function init()
	{
		global $post, $typenow, $current_screen;
		add_filter('coauthors_guest_author_fields', array($this, 'add_coauthors_guest_author_fields'), 10, 2);

		// quick workaround to prevent co-author disable linked users dropdown
		// there is some bug in co-author plug where imported guest author might have dirty data
		// causing guest author profile can't be linked to wp user.
		add_action('admin_head', function () {
			global $current_screen;

			if (!empty($current_screen) && 'guest-author' === $current_screen->post_type) {
				add_filter('wp_dropdown_users', function ($output) {
					return str_replace('<select disabled="disabled" ', '<select ', $output);
				}, 11);
			}
		});

		/**
		 * Add filter to run before CoAuthors Plus filter to override default CoAuthors Plus behavior
		 * @see https://github.com/Automattic/Co-Authors-Plus/blob/master/php/class-coauthors-guest-authors.php#L57
		 */
		add_filter('wp_insert_post_data', array($this, 'manage_guest_author_filter_post_data'), 9, 2);
	}

	/**
	 * Override default CoAuthors Plus behavior and allow mapping of existing WP Accounts to existing Guest Author accounts.
	 * Override condition: When existing guest author has no linked account and WP Account is selected
	 *
	 * @see https://github.com/Automattic/Co-Authors-Plus/blob/master/php/class-coauthors-guest-authors.php#L668
	 * @see https://wordpressvip.zendesk.com/requests/36553
	 * @version 2015-01-06 Hau Vong Initial Version
	 *
	 */
	function manage_guest_author_filter_post_data($post_data, $original_args)
	{

		// Do nothing if post type is not guest author or no WP Account is selected
		if ($post_data['post_type'] != self::post_type || empty($original_args['cap-linked_account'])) {
			return $post_data;
		}

		$slug = sanitize_title(get_post_meta($original_args['ID'], 'cap-user_login', true));

		if (!empty($slug)) {
			// determine if wpcom user already exist with same slug
			$user_nicename = str_replace('cap-', '', $slug);
			$user = get_user_by('slug', $user_nicename);

			if (!empty($user) && is_user_member_of_blog($user->ID, get_current_blog_id())) {

				$linked_account = get_post_meta($original_args['ID'], 'cap-linked_account', true);

				// only update post meta if existing linked account is empty
				if (empty($linked_account)) {
					$linked_user = get_user_by('id', $original_args['cap-linked_account']);

					// make sure the linked account matched up with guest author slug account
					if (!empty($linked_user) && $linked_user->user_login == $user->user_login) {
						update_post_meta($original_args['ID'], 'cap-linked_account', $linked_user->user_login);
					}
				}
			}
		}

		return $post_data;
	}

	/**
	 * Function to add our custom contact/bio infor for authors.
	 *
	 * @param $fields_to_return
	 * @param $groups
	 *
	 * @return array
	 */
	public function add_coauthors_guest_author_fields($fields_to_return, $groups)
	{

		$new_fields = array(

			// Title
			array(
				'key'      => '_pmc_title',
				'label'    => 'Title',
				'group'    => 'name',
				'required' => (empty($_GET['action']) || $_GET['action'] !== 'cap-create-guest-author') ? true : false,
			),

			// Extra Contact info
			array(
				'key'   => '_pmc_user_twitter',
				'label' => 'Twitter Handle',
				'group' => 'contact-info',
			),

			array(
				'key'   => '_pmc_user_facebook',
				'label' => 'Facebook Profile Link',
				'group' => 'contact-info',
			),
			array(
				'key'   => '_pmc_user_google_plus',
				'label' => 'Google + Profile Link',
				'group' => 'contact-info',
			),
			array(
				'key'   => '_pmc_user_instagram',
				'label' => 'Instagram Profile Link',
				'group' => 'contact-info',
			),
			array(
				'key'   => '_pmc_user_youtube',
				'label' => 'Youtube Profile Link',
				'group' => 'contact-info',
			),
			array(
				'key'   => '_pmc_user_linkedin',
				'label' => 'Linkedin Profile Link',
				'group' => 'contact-info',
			),

			//Biographical info
			array(
				'key'               => '_pmc_excerpt',
				'label'             => 'Biographical Excerpt',
				'group'             => 'about',
				'sanitize_function' => 'wp_filter_post_kses',
			),

		);

		$is_all_group = (isset($groups[0]) && 'all' === $groups[0]);

		foreach ($new_fields as $single_field) {
			if ('hidden' !== $single_field['group']) {
				if ($is_all_group || in_array($single_field['group'], (array) $groups, true)) {
					$fields_to_return[] = $single_field;
				}
			}
		}

		return $fields_to_return;
	}

	/**
	 * Get Author Details for post
	 *
	 * @param int  $post_id
	 * @param bool $single
	 * @param array $opts { // (optional)
	 *   @type bool 'exclude-thumb' if set, exclude retrieving author image (default <false>).
	 * }
	 *
	 * @return array
	 */
	function get_post_authors_data($post_id = 0, $single = false, $opts = false)
	{

		$exclude_thumb = (is_array($opts) && !empty($opts['exclude-thumb']));
		$post = get_post($post_id);
		$cache_key = md5('guest-author-' . $post->ID . '-' . $single . '-' . $exclude_thumb);
		if (false !== ($retval = wp_cache_get($cache_key, self::CACHE_GROUP))) {
			return $retval;
		}

		$co_authors = get_coauthors($post_id);

		if (empty($co_authors)) {
			return;
		}

		$guest_author_fields = $GLOBALS['coauthors_plus']->guest_authors->get_guest_author_fields();
		$guest_author_fields = wp_list_pluck($guest_author_fields, 'key');

		$author_fields = array_merge(array('ID', 'type', 'user_nicename'), $guest_author_fields);

		$return_user = array();

		foreach ($co_authors as $co_author) {
			$usr = array();

			foreach ($author_fields as $key) {
				$new_key = str_replace('_pmc_', '', $key);

				if (isset($co_author->$key)) {
					$usr[$new_key] = $co_author->$key;
				} else {
					$usr[$new_key] = '';
				}
			}

			if ($exclude_thumb !== true) {
				$img_id = get_post_thumbnail_id($co_author->ID);
				if (!empty($img_id)) {
					$usr['image'] = wp_get_attachment_url($img_id);
					$usr['image_id'] = $img_id;
				} else {
					$usr['image'] = '';
					$usr['image_id'] = '';
				}
			}

			$usr['url'] = get_author_posts_url($co_author->ID, $co_author->user_login);

			$return_user[] = $usr;

			if ($single) {
				break;
			}
		}

		wp_cache_set($cache_key, (array)$return_user, self::CACHE_GROUP, self::CACHE_EXPIRE);

		return $return_user;
	}

	/**
	 * Get guest author data by id.
	 *
	 * @param $author_id
	 *
	 * @return array
	 */
	function get_guest_author_data($author_id)
	{

		if ($author_id < 1) {
			return array();
		}

		$user = $GLOBALS['coauthors_plus']->guest_authors->get_guest_author_by('ID', $author_id);

		if (empty($user)) {
			return array();
		}

		$ret_user = array();

		foreach ($user as $key => $value) {
			$new_key            = str_replace('_pmc_', '', $key);
			$ret_user[$new_key] = $value;
		}

		return $ret_user;
	}

	/**
	 * this function grabs author data using any meta-key allowed by
	 * co-authors plus and returns an array with our custom key names sanitized
	 *
	 * @since 2013-08-22 Amit Gupta
	 */
	public function get_author_data_by($key, $value)
	{
		if (empty($key) || empty($value)) {
			return false;
		}

		if (!is_string($key) || (!is_string($value) && !is_numeric($value))) {
			return false;
		}

		$author = $GLOBALS['coauthors_plus']->get_coauthor_by($key, $value);



		if (empty($author)) {
			return false;
		}

		$author = (array) $author;

		if (!empty($author['data']->type) && $author['data']->type == 'wpuser') {
			$author = (array) $author['data'];
		}

		$data = array();

		$author_fields = array_merge(array('ID', 'type', 'user_nicename'), wp_list_pluck($GLOBALS['coauthors_plus']->guest_authors->get_guest_author_fields(), 'key'));

		foreach ($author_fields as $meta_key) {
			$meta_key_new = str_replace('_pmc_', '', $meta_key);

			if (empty($meta_key_new)) {
				continue;
			}

			if (isset($author[$meta_key])) {
				$data[$meta_key_new] = $author[$meta_key];
			} else {
				$data[$meta_key_new] = '';
			}

			unset($meta_key_new);
		}

		unset($author_fields, $author);

		return $data;
	}
}

$GLOBALS['pmc_guest_authors'] = PMC_Guest_Authors::get_instance();

/**
 * Wrapper function to fetch co_authors data for post.
 * @param int  $post_id
 * @param bool $single
 *
 * @return mixed
 */
function pmc_get_post_authors_data($post_id = 0, $single = false, $opts = false)
{
	return $GLOBALS['pmc_guest_authors']->get_post_authors_data($post_id, $single, $opts);
}

/**
 * wrapper to grab author data using any meta-key allowed by
 * co-authors plus
 *
 * @since 2013-08-22 Amit Gupta
 */
function pmc_get_coauthor_data_by($key, $value)
{
	return $GLOBALS['pmc_guest_authors']->get_author_data_by($key, $value);
}

/**
 * Generates HTML with social links for the author.
 * Its same as pmc_author_social_links() of PMC-Authors plugin
 *
 * @since 2013-08-22 Amit Gupta
 * @version 2013-09-10 Amit Gupta
 */
function pmc_coauthor_social_links($author_id, $echo = 'yes')
{
	$author_id = intval($author_id);

	if ($author_id < 1) {
		return false;
	}

	$echo = ($echo == 'yes') ? 'yes' : 'no';

	$author = $GLOBALS['pmc_guest_authors']->get_author_data_by('id', $author_id);

	if (empty($author) || !is_array($author)) {
		return false;
	}

	if (
		empty($author['user_twitter']) && empty($author['user_facebook']) && empty($author['user_google_plus'])
		&& empty($author['user_youtube']) && empty($author['user_linkedin'])
	) {
		return false;
	}

	ob_start();
?>
	<h4><?php echo esc_html(apply_filters('pmc_coauthor_social_links_title', 'Connect:', $author)); ?></h4>
	<ul id="author-social-links">
		<?php if (!empty($author['user_twitter'])) { ?>
			<li class="twitter icon">
				<a rel="me" href="<?php echo esc_url('http://twitter.com/' . $author['user_twitter']); ?>" title="Follow on Twitter">Follow @<?php echo esc_html($author['user_twitter']); ?></a>
			</li>
		<?php } ?>
		<?php if (!empty($author['user_facebook'])) { ?>
			<li class="facebook icon">
				<a rel="me" href="<?php echo esc_url($author['user_facebook']); ?>" title="Friend on Facebook">Facebook <?php echo esc_html($author['display_name']); ?></a>
			</li>
		<?php } ?>
		<?php if (!empty($author['user_google_plus'])) { ?>
			<li class="google-plus icon">
				<a rel="me" href="<?php echo esc_url($author['user_google_plus']); ?>" title="Add to Google+">Google+ <?php echo esc_html($author['display_name']); ?></a>
			</li>
		<?php } ?>
		<?php if (!empty($author['user_linkedin'])) { ?>
			<li class="linkedin icon">
				<a rel="me" href="<?php echo esc_url($author['user_linkedin']); ?>" title="Link up on LinkedIn">Linkedin <?php echo esc_html($author['display_name']); ?></a>
			</li>
		<?php } ?>
		<?php if (!empty($author['user_youtube'])) { ?>
			<li class="youtube icon">
				<a rel="me" href="<?php echo esc_url($author['user_youtube']); ?>" title="YouTube channel">YouTube <?php echo esc_html($author['display_name']); ?></a>
			</li>
		<?php } ?>
	</ul>
<?php
	$html = ob_get_clean();

	unset($author);

	if ($echo == 'no') {
		return $html;
	}

	echo $html;

	unset($html);
}

/**
 * Redirects from old author URLs to new PMC Guest Author URLs
 * Co-Authors Plus doesn't seem to be doing this on its own!
 *
 * @since 2013-08-26 Amit Gupta
 * @version 2013-09-10 Amit Gupta
 */
function pmc_handle_coauthor_request($query_vars)
{
	if (empty($query_vars['author_name'])) {
		return $query_vars;
	}

	$author_name = strtolower($query_vars['author_name']);

	$cache_config = array(
		'key' => 'vip_redirect_' . $author_name,
		'group' => 'pmc_guest_authors',
		'expiry' => 604800,		//7 days cache
	);

	$vip_redirects = wp_cache_get($cache_config['key'], $cache_config['group'], false);

	if ($vip_redirects === false) {
		$vip_redirects = array();

		$author = pmc_get_coauthor_data_by('user_nicename', $author_name);

		if (
			empty($author)
			|| (!empty($author['type']) && $author['type'] == 'wpuser')
			|| empty($author['linked_account'])
		) {
			return $query_vars;
		}

		//dont need redirect loop, redirect only if old URL
		if (strtolower($author['linked_account']) == $author_name && strtolower($author['user_nicename']) !== $author_name) {
			$current_url = '/author/' . $author_name . '/';
			$new_url = home_url('/author/' . $author['user_nicename'] . '/');

			if (home_url($current_url) !== $new_url) {
				$vip_redirects[$current_url] = $new_url;
				unset($current_url, $new_url);

				wp_cache_set($cache_config['key'], $vip_redirects, $cache_config['group'], $cache_config['expiry']);
			}
		} else {
			$query_vars['author_name'] = $author['linked_account'];
			return $query_vars;
		}
	}

	if (!empty($vip_redirects) && is_array($vip_redirects)) {
		vip_redirects($vip_redirects);
		exit();
	}

	return $query_vars;
}

//add_filter( 'request', 'pmc_handle_coauthor_request' );


//EOF
