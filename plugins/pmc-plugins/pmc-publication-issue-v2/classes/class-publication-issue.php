<?php

/**
 * Publication Issue
 *
 * @package pmc-publication-issue
 *
 * @since 2018-05-25
 */

namespace PMC\Publication_Issue_V2;

use \PMC\Global_Functions\Traits\Singleton;

class Publication_Issue
{

	use Singleton;

	// use pmc prefix so that this plugin can be move to pmc-plugins
	const PUB_TAXONOMY        = 'pmc-publication';  // use hidden taxonomy to represent publication for issue filtering
	const POST_TYPE           = 'pmc-pub-issue';    // max length of post_type is 20 chars
	const POST_TYPE_SLUG      = 'issue'; // the issue slug name use for custom permalink
	const CONNECTION_NAME     = 'pmc-pub-issue-posts'; // a relation name to represent object to object relationship
	const MAX_POSTS_PER_ISSUE = 200; // limit number of posts can be assigned to an issue
	const MAX_PUB_TERMS       = 50; // limit number of publication taxonomy to represent a publication
	const CACHE_GROUP         = 'pmc-pub-issue';
	const CACHE_DURATION      = 60;

	public $issue = false;

	function _init()
	{
		add_action('init', array($this, 'action_init'));
		add_action('pre_get_posts', array($this, 'action_pre_get_posts'));
		add_action('admin_enqueue_scripts', array($this, 'action_admin_enqueue_scripts'));
	}

	public function action_admin_enqueue_scripts()
	{
		wp_enqueue_style(self::POST_TYPE . 'wp-admin-css', plugins_url('assets/css/wp-admin.css', __FILE__));
	}

	/**
	 * Modifies the query object so it requests posts linked to the requested issue.
	 */
	public function action_pre_get_posts($query)
	{
		if (is_admin() || !$query->is_main_query()) {
			return;
		}

		$post_type = $query->get('post_type');

		if (self::POST_TYPE !== $post_type) {
			return;
		}

		$this->issue = $query->get('p');

		if (!apply_filters('pmc_publication_issue_as_archive', true)) {
			return;
		}

		$connection_terms = get_the_terms($this->issue, 'o2o_' . self::CONNECTION_NAME);

		if (is_wp_error($connection_terms) || !is_array($connection_terms)) {
			return;
		}

		$connection_terms = wp_list_pluck($connection_terms, 'term_id');

		if (empty($connection_terms)) {
			return;
		}

		// Clear vars used to load the single post.
		$query->set('p', null);
		$query->set('year', null);
		$query->set('issue', null);
		$query->set('name', null);

		// Request posts with one of the connection terms for this issue saved as a meta value.
		$query->set('meta_query', [
			[
				'key'     => 'o2o_term_id_o2o_' . self::CONNECTION_NAME,
				'value'   => $connection_terms,
				'compare' => 'IN',
			],
		]);

		// These may affect templating and styles.
		$query->is_singular = 0;
		$query->is_archive  = 1;

		// Make sure the archive template is loaded.
		add_filter('template_include', function () {
			return locate_template('archive.php');
		});
	} // function action wp

	/**
	 * Locating templates to use: single-pmc-pub-issue.php -> pmc-pub-issue.php
	 */
	public function filter_template_include($template)
	{

		// is current post type a publication issue?
		if (!empty($this->issue->post_type) && self::POST_TYPE === $this->issue->post_type) {
			$new_template = locate_template(
				array(
					'single-' . self::POST_TYPE . '.php',
					self::POST_TYPE . '.php',
				)
			);
			if (!empty($new_template)) {
				return $new_template;
			}
		}

		return $template;
	} // function filter template include

	public function action_init()
	{

		add_filter(
			'template_include',
			array(
				$this,
				'filter_template_include',
			),
			99
		);

		// use filter to allow theme override supporting features
		$this->supports = apply_filters(
			'pmc_publication_issue_supports',
			array('pdf-attachment', 'sub-title', 'lead-article', 'issue-date', 'issue-number')
		);

		// register our custom post type
		register_post_type(
			self::POST_TYPE,
			array(
				'description'         => __('Publication Issues'),
				'labels'              => array(
					'name'               => _x('Publication Issues', 'Post Type General Name'),
					'singular_name'      => _x('Publication Issue', 'Post Type Singular Name'),
					'menu_name'          => __('Publication Issue'),
					'all_items'          => __('All Issues'),
					'add_new'            => __('Add New'),
					'add_new_item'       => __('Add New Issue'),
					'edit_item'          => __('Edit Issue'),
					'view_item'          => __('View Issue'),
					'search_items'       => __('Search Issues'),
					'not_found'          => __('Not found'),
					'not_found_in_trash' => __('Not found in Trash'),
					'parent_item_colon'  => __('Parent Issue:'),
				),
				'public'              => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_nav_menus'   => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => array('title', 'thumbnail'),
				// we only support 2 built in features for now
				'taxonomies'          => array(self::PUB_TAXONOMY),
				// we'll assign it later
				'has_archive'         => true,
				// we do want archive in the future
				'rewrite'             => false,
				// we don't want wp default rewrite rules
				'query_var'           => self::POST_TYPE_SLUG,
				// important that we have a unique slug for this custom post type
				'can_export'          => true,
			)
		);

		// create publications to posts relationship
		\O2O::Register_Connection(
			self::CONNECTION_NAME,
			self::POST_TYPE,
			'post',
			array(
				'reciprocal' => true,
				'rewrite'    => false,
				'from'       => array(
					'limit'  => 1,
					// a post can only be assigned to 1 publication
					'labels' => array(
						'name'          => 'Publication Issue',
						'singular_name' => 'Publication Issue',
					),
				), // from
				'to'         => array(
					'limit'    => self::MAX_POSTS_PER_ISSUE,
					// limit number of post can assign to a single publication
					'sortable' => true,
					// allow custom sorting
					'labels'   => array(
						'name'          => 'Posts',
						'singular_name' => 'Post',
					),
				), // to
				'metabox'    => array(
					'context' => 'normal',
				),
			)
		);

		// Enable pdf file attachment
		if (in_array('pdf-attachment', (array) $this->supports, true)) {
			new \MultiplePostAttachments(
				array(
					'post_type' => self::POST_TYPE,
					'label'     => 'PDF File',
					'id'        => 'pdf_attachment',
					'type'      => 'application',
				)
			);
		}

		// supporting publication taxonomy?
		if (in_array('publication', (array) $this->supports, true)) {
			// the taxonomy will be a hidden, we're using a custom metabox drop down to assign
			register_taxonomy(
				self::PUB_TAXONOMY,
				self::POST_TYPE,
				array(
					'labels'            => array(
						'name'                  => __('Publications', 'pmc-publication-issue'),
						'singular_name'         => __('Publication', 'pmc-publication-issue'),
						'search_items'          => __('Search Publications', 'pmc-publication-issue'),
						'popular_items'         => __('Popular Publications', 'pmc-publication-issue'),
						'all_items'             => __('All Publications', 'pmc-publication-issue'),
						'parent_item'           => __('Parent Publication', 'pmc-publication-issue'),
						'parent_item_colon'     => __('Parent Publication', 'pmc-publication-issue'),
						'edit_item'             => __('Edit Publication', 'pmc-publication-issue'),
						'update_item'           => __('Update Publication', 'pmc-publication-issue'),
						'add_new_item'          => __('Add New Publication', 'pmc-publication-issue'),
						'new_item_name'         => __('New Publication Name', 'pmc-publication-issue'),
						'add_or_remove_items'   => __('Add or remove Publications', 'pmc-publication-issue'),
						'choose_from_most_used' => __('Choose from most used Publications', 'pmc-publication-issue'),
						'menu_name'             => __('Publications', 'pmc-publication-issue'),
					),
					'show_in_nav_menus' => false,
					'sort'              => true,
					'show_admin_column' => true,
					'rewrite'           => false,
				)
			);
		}

		$this->register_permastruct();

		add_filter('post_link', array($this, 'filter_post_type_link'), 10, 3);
		add_filter('post_type_link', array($this, 'filter_post_type_link'), 10, 3);
		add_action('generate_rewrite_rules', array($this, 'register_permastruct'));

		// meta boxes
		add_action('custom_metadata_manager_init_metadata', array($this, 'action_custom_metadata_manager_init_metadata'));
		add_action('add_meta_boxes', array($this, 'action_add_meta_boxes'));

		if (in_array('lead-article', (array) $this->supports, true)) {
			add_action('save_post', array($this, 'action_save_post'));
		}

		add_filter('wp_insert_post_data', array($this, 'filter_wp_insert_post_data'), 10, 2);
	} // function action init

	// auto issue title
	public function filter_wp_insert_post_data($data, $postarr)
	{

		// only do auto title if support and only no title is assigned
		if (!in_array('auto-title', (array) $this->supports, true) || self::POST_TYPE !== $data['post_type'] || !empty($data['post_title'])) {
			return $data;
		}

		// data coming from edit screen
		if (!empty($_POST['publication'])) { // WPCS: Input var ok. CSRF ok.
			$publication = sanitize_text_field(wp_unslash($_POST['publication'])); // WPCS: Input var ok. CSRF ok.
		} else {
			$publication = $this->get_publication($postarr['ID']);
		}

		// allow filter to override the title from theme
		$data['post_title'] = trim(apply_filters('publication_issue_title', $publication . ' ' . mysql2date('Y-m-d', $data['post_date']), $data, $postarr));

		return $data;
	}

	/**
	 * Intercept save post action to extract additional meta data that need to be add
	 * Since we're inside save post action, do we still need to implement nonce?
	 */
	public function action_save_post($post_id)
	{
		if (
			(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
			(defined('DOING_AJAX') && DOING_AJAX) ||
			self::POST_TYPE !== get_post_type($post_id) || // not a publication issue
			!current_user_can('edit_post', $post_id) || // make sure user has edit permission
			empty($_POST['pmclinkcontent-post-value-lead_article']) // WPCS: Input var ok. CSRF ok. Post data should contains the value we need,
		) {
			// abort if any of the condition above is true
			return;
		}

		// pmc link content plugin encode the data as json, we need to decode it
		$data = json_decode(trim(sanitize_text_field(wp_unslash($_POST['pmclinkcontent-post-value-lead_article'])))); // Input var ok. CSRF ok.
		if (!empty($data) && isset($data->id)) {
			$lead_article_id = $data->id;
		}

		if (!empty($lead_article_id)) {
			update_post_meta($post_id, 'lead_article_id', $lead_article_id);
		} else {
			delete_post_meta($post_id, 'lead_article_id');
		}

		// do we supports publication taxonomy?
		if (in_array('publication', (array) $this->supports, true) && taxonomy_exists(self::PUB_TAXONOMY)) {
			// at this point, the metadata should already been saved by wp post save
			$publication = $this->get_publication($post_id);
			// only re-assign taxonomy if we have a valid publication
			if (!empty($publication)) {
				// check to make sure the taxonomy exist fisrt, create otherwise
				if (!term_exists($publication, self::PUB_TAXONOMY)) {
					wp_insert_term($publication, self::PUB_TAXONOMY);
				}
				wp_set_post_terms($post_id, $publication, self::PUB_TAXONOMY, false);
			}
		}
	} // function

	/**
	 * @param int $issue_id The post id of the custom post type
	 *
	 * @return post object
	 */
	public function get_lead_article($issue_id = 0)
	{
		$post_id = get_post_meta($this->issue_id($issue_id), 'lead_article_id', true);
		if (!empty($post_id)) {
			return get_post($post_id);
		}

		return false;
	}

	public function action_add_meta_boxes()
	{
		// supporting lead article?
		if (in_array('lead-article', (array) $this->supports, true)) {
			// add meta box for entering lead article
			add_meta_box(
				'pmc-publication-issue-lead-article',
				'Lead Article',
				array(
					$this,
					'meta_box_lead_article',
				),
				self::POST_TYPE,
				'normal',
				'core'
			);
		}
	}

	/**
	 * @param int $issue_id The post id of the publication issue custom post type
	 *
	 * @return string The publication name
	 */
	public function get_publication($issue_id = 0)
	{
		return get_post_meta($this->issue_id($issue_id), 'publication', true);
	}

	/**
	 * Customize permalink like for the custom post type
	 *
	 * @see filter post_type_link
	 */
	public function filter_post_type_link($permalink, $post_id, $leavename)
	{
		$post = get_post($post_id);

		if ('' !== $permalink && self::POST_TYPE === $post->post_type) {
			$time      = strtotime($post->post_date);
			$codes     = array(
				'%publication%',
				'%post_id%',
				'%year%',
				'%monthnum%',
				'%day%',
				'%postname%',
				'%issue%',
			);
			$replaces  = array(
				$this->get_publication($post->ID),
				$post->ID,
				date('Y', $time),
				date('m', $time),
				date('d', $time),
				$leavename ? '%postname%' : $post->post_name,
				$leavename ? '%postname%' : $post->post_name,
			);
			$permalink = str_replace($codes, $replaces, $permalink);
		}

		return $permalink;
	}

	/**
	 * Registering perma structure for publication issue permalink
	 */
	public function register_permastruct()
	{
		global $wp_rewrite;

		// if auto title contains the date, so no need to have a year reference on permalink
		if (in_array('auto-title', (array) $this->supports, true)) {
			$permastruct = '/' . self::POST_TYPE_SLUG . '/%issue%-%post_id%';
		} else {
			$permastruct = '/' . self::POST_TYPE_SLUG . '/%year%/%issue%-%post_id%';
		}

		$perma_options = array(
			'permastruct'  => $permastruct,
			'permaoptions' => array(
				'with_front'  => false,
				'ep_mask'     => EP_NONE,
				'paged'       => true,
				'feed'        => false,
				'forcomments' => false,
				'walk_dirs'   => false,
				'endpoints'   => true,
			),
		);

		// filter to allow theme to override the permastruct options
		$perma_options = apply_filters('pmc_publication_issue_permastruct', $perma_options);
		$wp_rewrite->add_rewrite_tag('%' . self::POST_TYPE_SLUG . '%', '([^/]+)', self::POST_TYPE_SLUG . '=');
		$wp_rewrite->add_permastruct(self::POST_TYPE, $perma_options['permastruct'], $perma_options['permaoptions']);
	} // function

	/**
	 * Add custom post meta
	 */
	public function action_custom_metadata_manager_init_metadata()
	{

		// add a new meta group
		x_add_metadata_group(self::POST_TYPE . '-grp', array(self::POST_TYPE), array('label' => 'Details'));

		// do we support publication taxonomy?
		if (taxonomy_exists(self::PUB_TAXONOMY)) {

			// get a list of taxonomy terms for the metabox drop down
			$list = get_terms(
				self::PUB_TAXONOMY,
				array(
					'fields'     => 'names',
					'hide_empty' => false,
					'number'     => self::MAX_PUB_TERMS,
					// IMPORTANT: to restrict to a safe number
				)
			);

			if (is_wp_error($list)) {
				$list = array();
			}

			// allow theme to override the list
			$list = apply_filters('pmc_publication_list', $list);

			if (!empty($list)) {
				$values = array();
				// convert into associated array, do not allow custom taxonomy slug
				foreach (array_unique((array) $list) as $value) {
					$values[$value] = $value;
				}
				x_add_metadata_field(
					'publication',
					array(self::POST_TYPE),
					array(
						'group'      => self::POST_TYPE . '-grp',
						'label'      => 'Publication',
						'field_type' => 'select',
						'values'     => $values,
						// values must be an associated array( key => value )
					)
				);
			}
		}

		// supporting sub title?
		if (in_array('sub-title', (array) $this->supports, true)) {
			x_add_metadata_field(
				'sub_title',
				array(self::POST_TYPE),
				array(
					'group'       => self::POST_TYPE . '-grp',
					'label'       => 'Sub Headline',
					'description' => 'Sub Headline of issue',
				)
			);
		}

		// Do we support issue date?
		if (in_array('issue-date', (array) $this->supports, true)) {
			x_add_metadata_field(
				'issue_date',
				array(self::POST_TYPE),
				array(
					'group'       => self::POST_TYPE . '-grp',
					'label'       => __('Issue Date', 'pmc-publication-issue'),
					'description' => __('The publication date of the issue', 'pmc-publication-issue'),
				)
			);
		}

		// Do we support issue number?
		if (in_array('issue-number', (array) $this->supports, true)) {
			x_add_metadata_field(
				'issue_number',
				array(self::POST_TYPE),
				array(
					'group'       => self::POST_TYPE . '-grp',
					'label'       => __('Issue Number', 'pmc-publication-issue'),
					'description' => __('The publication number of the issue', 'pmc-publication-issue'),
				)
			);
		}
	}

	/**
	 * Render the meta box for lead article input
	 */
	public function meta_box_lead_article($post)
	{
		$data = new \stdClass();
		$post = get_post($post);

		if (!empty($post)) {
			// get the lead article if available
			$post = $this->get_lead_article($post->ID);
			if (!empty($post)) {
				$data->url   = get_permalink($post->ID);
				$data->id    = $post->ID;
				$data->title = $post->post_title;
			}
		}

		// PMC Link Content plugin to create the meta box
		\PMC_LinkContent::insert_field(wp_json_encode($data), 'Article', 'lead_article');
	} // function

	/**
	 * @param int $issue_id The id of post of type pmc-pub-issue
	 *
	 * @return int | false  The id of the pdf attachment
	 */
	public function get_pdf_attachment_id($issue_id = 0)
	{
		$issue_id = $this->issue_id($issue_id);

		if (self::POST_TYPE !== get_post_type($issue_id)) {
			return false;
		}

		return \MultiplePostAttachments::get_post_attachment_id(self::POST_TYPE, 'pdf_attachment', $issue_id);
	} // function get pdf attachment id

	/**
	 * @param int $issue_id The id of post of type pmc-pub-issue
	 *
	 * @return string The pdf url
	 */
	public function get_pdf_attachment_url($issue_id = 0, $default = false)
	{
		$url = wp_get_attachment_url($this->get_pdf_attachment_id($issue_id));
		if (empty($url)) {
			return $default;
		}

		return $url;
	}

	/**
	 * @param int $issue_id The id of the issue
	 *
	 * @return string The sub title of the given issue
	 */
	public function get_sub_title($issue_id = 0)
	{
		$issue_id = $this->issue_id($issue_id);

		return get_post_meta($issue_id, 'sub_title', true);
	}

	/**
	 * Return issue id or default to current issue
	 *
	 * @param int $issue_id The id of issue, if not given, will default to current issue
	 *
	 * @return int The id of the issue
	 */
	public function issue_id($issue_id = 0)
	{
		if (!empty($issue_id)) {
			return $issue_id;
		}

		if (is_object($this->issue) && isset($this->issue->ID)) {
			return $this->issue->ID;
		}

		if (!empty($this->issue) && is_numeric($this->issue)) {
			return $this->issue;
		}

		return get_the_ID();
	}

	/**
	 * Build the wp query parameters
	 *
	 * @param array $args The query array, where
	 *            int 'issue_id' The id of post of type pmc-pub-issue
	 *
	 * @see get_posts
	 * @return array The wp query parameters
	 */
	public function query_args($args = array())
	{
		if (!empty($args['issue_id'])) {
			$issue_id = $args['issue_id'];
			unset($args['issue_id']);
		} else {
			$issue_id = $this->issue_id();
		}

		// preventing querying un-support post type
		if (self::POST_TYPE !== get_post_type($issue_id)) {
			// hack to make query return empty result
			return array('post__in' => array(0));
		}

		$defaults = array(
			'o2o_query'   => array(
				'connection' => self::CONNECTION_NAME,
				'direction'  => 'to', // we want post: issue (from) -> post (to)
				'id'         => $issue_id,
			),
			'o2o_orderby' => self::CONNECTION_NAME,
		);

		return wp_parse_args($args, $defaults);
	}

	/**
	 * @param array $args The query array, where
	 *            int 'issue_id' The id of post of type pmc-pub-issue
	 *
	 * @see get_posts
	 * @return object WP_Query
	 */
	public function new_query($args = array())
	{
		return new \WP_Query($this->query_args($args));
	} // function

	/**
	 * Return articles associated to an issue, @see Publication_Issue::query_args()
	 *
	 * @param int $issue_id The id of post of type pmc-pub-issue
	 *
	 * @return array The list of post object
	 */
	public function get_posts($args = array())
	{
		$cache_key = md5('get-posts' . wp_json_encode($args));
		$posts     = wp_cache_get($cache_key, self::CACHE_GROUP);

		if (!$posts) {
			$this->query = $this->new_query($args);
			$posts       = $this->query->get_posts();
			wp_cache_set($cache_key, $posts, self::CACHE_GROUP, self::CACHE_DURATION);
		}

		return $posts;
	} // function get posts

	/**
	 * @see get_posts
	 * @return list of wp post object of pmc-pub-issue type
	 */
	public function get_issues($args = array())
	{
		$default = array(
			'posts_per_page'   => 1,
			'suppress_filters' => false,
			// need this set for un-cached get_posts
		);

		$args = wp_parse_args($args, $default);

		// make sure we query our custom post type, there is no overriding this value.
		$args['post_type'] = self::POST_TYPE;
		$cache_key         = md5('get-issues' . wp_json_encode($args));
		$posts             = wp_cache_get($cache_key, self::CACHE_GROUP);

		if (!$posts) {
			$query = new \WP_Query($args);
			$posts = $query->posts;
			wp_cache_set($cache_key, $posts, self::CACHE_GROUP, self::CACHE_DURATION);
		}

		return $posts;
	}

	/**
	 * return the custom post publication issue if the given post is assign an issue
	 *
	 * @param int|wp object $post The id or wp post object
	 *
	 * @return wp post object of type pmc-pub-issue type
	 */
	public function post_to_issue($post)
	{
		$post = get_post($post);

		if (empty($post)) {
			return false;
		}

		// objects 2 objects query
		$args  = array(
			'o2o_query'      => array(
				'connection' => self::CONNECTION_NAME,
				'direction'  => 'from',
				// we want issue => issue (from ) -> post(to)
				'id'         => $post->ID,
			),
			'o2o_orderby'    => self::CONNECTION_NAME,
			'posts_per_page' => 1,
		);
		$query = new \WP_Query($args);
		$posts = $query->get_posts();
		if (empty($post) || is_wp_error($posts)) {
			return false;
		}

		return reset($posts);
	}
}

// EOF
