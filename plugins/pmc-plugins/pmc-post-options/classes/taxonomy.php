<?php

/**
 * The Post Options taxonomy class which registers the taxonomy
 *
 * @author Amit Gupta <agupta@pmc.com>
 */

namespace PMC\Post_Options;

class Taxonomy extends Base
{

	const PARENT_TERM       = 'global-options';
	const PARENT_TERM_LABEL = 'Global Options';

	/**
	 * PMC option key to save added term's hash.
	 *
	 * @var string
	 */
	const PARENT_TERM_SAVE_OPTION_KEY = 'pmc-post-options-tax-terms-hashes';

	/**
	 * Number of hashes can be stored in PMC option.
	 *
	 * @var int
	 */
	const TERMS_HASH_CACHE_COUNT = 20;

	/**
	 * @var array An array of post types on which Post Options taxonomy is enabled
	 */
	protected $_post_types = array('post');

	/**
	 * @var array Default list of post options which must be available on all sites.
	 *
	 * The first index in this array is for global options and should remain as is.
	 * More options can be added like existing option(s) in its 'children' array.
	 *
	 * Options for specific post types should be added as the global options have been
	 * added. The format should be:
	 *
	 * $_default_terms[ <custom-post-type> ] = array(
	 *		'label'    => <Label to be given to this term>,
	 *		'children' => array(
	 *			<option-slug> => array(
	 *				'label' => <Label for Option is mandatory>,
	 *			),
	 *			<option-slug> => array(
	 *				'label'       => <Label for Option is mandatory>,
	 *				'description' => <Description for Option is NOT mandatory>,
	 *			),
	 *		),
	 * );
	 *
	 */
	protected $_default_terms = [
		self::PARENT_TERM => [
			'label'    => self::PARENT_TERM_LABEL,
			'children' => [

				// NOTE: Child terms MUST, at the very least; include a label.
				//       Though, a description is also preferred.

				// Option to flag content as inappropriate for syndication.
				'inappropriate-for-syndication' => [
					'label'       => 'Inappropriate For Syndication',
					'description' => 'Posts with this term will not be syndicated.',
				],
				// Option to exclude content from landing pages.
				'exclude-from-google-news'      => [
					'label'       => 'Exclude from Google News',
					'description' => 'Posts with this term will be excluded from Googlebot News.',
				],
				// Option to exclude content from homepage.
				'exclude-from-homepage'         => [
					'label'       => 'Exclude from Homepage',
					'description' => 'Posts with this term will be excluded from Homepage.',
				],
				// Option to exclude content from section fronts.
				'exclude-from-section-fronts'   => [
					'label'       => 'Exclude from Section Fronts',
					'description' => 'Posts with this term will be excluded from Section Fronts.',
				],
				// Option to exclude content from homepage river only.
				'exclude-from-homepage-river'   => [
					'label'       => 'Exclude from Homepage River',
					'description' => 'Posts with this term will be excluded from Homepage River Only.',
				],
				// Option to exclude content from yahoo feed.
				'exclude-from-yahoo'            => [
					'label'       => 'Exclude from Yahoo',
					'description' => 'Posts with this term will be excluded from Yahoo Feed.',
				],

			],
		],
	];

	/**
	 * Class initialization routine
	 *
	 * @return void
	 */
	protected function __construct()
	{
		$this->_setup_hooks();
	}

	/**
	 * Setup listeners on action and filter hooks
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	protected function _setup_hooks()
	{
		/**
		 * Actions
		 */
		add_action('init', array($this, 'register_taxonomy'), 9); // Most plugins are using init to add additional default terms, so let's ensure taxonomy is registered earlier.
		add_action('admin_init', array($this, 'add_default_terms'), 11);
		add_action('admin_enqueue_scripts', [$this, 'action_admin_enqueue_scripts']);
		add_filter('block_editor_preload_paths', [$this, 'block_editor_preload_paths']);
	}

	/**
	 * This function registers the Post Options taxonomy. Its not meant to be called directly.
	 *
	 * @return void
	 */
	public function register_taxonomy()
	{

		$args = array(
			'label'         => 'Post Option',
			'labels' => array(
				'name'               => __('Post Options', 'pmc-plugins'),
				'singular_name'      => __('Post Option', 'pmc-plugins'),
				'add_new_item'       => __('Add New Post Option', 'pmc-plugins'),
				'edit_item'          => __('Edit Post Option', 'pmc-plugins'),
				'new_item'           => __('New Post Option', 'pmc-plugins'),
				'view_item'          => __('View Post Option', 'pmc-plugins'),
				'search_items'       => __('Search Post Options', 'pmc-plugins'),
				'not_found'          => __('No Post Options found.', 'pmc-plugins'),
				'not_found_in_trash' => __('No Post Options found in Trash.', 'pmc-plugins'),
				'all_items'          => __('Post Options', 'pmc-plugins'),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_rest'  => true,
			'hierarchical'  => true,
			// admins only
			'capabilities'  => array(
				'manage_terms'  => $this->_capability, // admin+
				'edit_terms'    => $this->_capability, // admin+
				'delete_terms'  => $this->_capability, // admin+
				'assign_terms'  => 'edit_posts', // contributor+
			),
		);

		$cli_args = [
			'publicly_queryable' => true,
		];

		$tax_args = (defined('WP_CLI') && WP_CLI) ? array_merge($args, $cli_args) : $args;

		register_taxonomy(parent::NAME, $this->get_post_types(), $tax_args);
	}

	/**
	 * Prelaod data in Gutenberg
	 *
	 * @codeCoverageIgnore
	 */
	public function block_editor_preload_paths($paths)
	{
		$paths[] = '/wp/v2/_post-options';
		return $paths;
	}

	/**
	 * This function returns the post types on which the Post Options taxonomy
	 * should be enabled.
	 *
	 * @return array
	 */
	public function get_post_types()
	{
		//get all public custom post types
		$custom_types = get_post_types(array(
			'public' => true,
			'_builtin' => false,
		), 'names');

		//allow override on post types which get post options
		$post_types = apply_filters('pmc-post-options-allowed-types', array_filter(array_unique(array_merge($this->_post_types, (array) $custom_types))));

		if (is_array($post_types)) {
			$post_types = array_filter(array_unique(array_values($post_types)));
		}

		if (!is_array($post_types) || empty($post_types)) {
			$post_types = $this->_post_types;
		}

		return $post_types;
	}

	/**
	 * Called on 'admin_init' action, this function makes sure default Post Options are
	 * present in the DB. This function is not meant to be called directly.
	 *
	 * @return void
	 */
	public function add_default_terms()
	{
		/*
		 * Add default terms only if current user is an admin
		 */
		if (!current_user_can($this->_capability)) {
			return;
		}

		if (empty($this->_default_terms) || !is_array($this->_default_terms)) {
			return;
		}

		$this->maybe_add_terms($this->_default_terms);
	}

	/**
	 * This function accepts an array of terms and adds them if they do not
	 * exist in DB already.
	 *
	 * @param array $terms An array of terms which must be added
	 * @return void
	 */
	public function maybe_add_terms(array $terms = array())
	{
		if (empty($terms)) {
			return;
		}

		// Get terms's hash.
		$terms_hash = $this->_get_terms_hash($terms);

		// Check if it is good to add or already exists.
		if (!$this->_should_add_terms($terms_hash)) {
			return;
		}

		foreach ($terms as $term => $sub_terms) {

			//if term has no children then handle differently
			if (!is_array($sub_terms)) {
				$term_id = term_exists($sub_terms, parent::NAME, 0);

				if (empty($term_id)) {
					//top level term doesn't exist, lets create it
					wp_insert_term($sub_terms, parent::NAME, array(
						'parent' => 0,
						'slug'   => $term,
					));
				}

				continue;
			} elseif (empty($sub_terms['children']) || !is_array($sub_terms['children'])) {
				throw new \ErrorException('Array associated with parent term key must have "children" index which should contain array of child terms.');
			}

			//term has children, handle accordingly
			//get parent term's ID
			$term_id = term_exists($term, parent::NAME, 0);
			$term_label = (!empty($sub_terms['label'])) ? $sub_terms['label'] : $term;

			if (empty($term_id)) {
				//top level term doesn't exist, lets create it
				$term_array = wp_insert_term($term_label, parent::NAME, array(
					'parent' => 0,
					'slug'   => $term,
				));

				if (is_wp_error($term_array) || empty($term_array['term_id'])) {
					//we have a problem
					continue;
				}

				$term_id = intval($term_array['term_id']);
			} else {
				$term_id = (is_array($term_id) && !empty($term_id['term_id'])) ? intval($term_id['term_id']) : intval($term_id);
			}

			if (empty($term_id) || intval($term_id) < 1) {
				//something got messed up
				continue;
			}

			foreach ($sub_terms['children'] as $sub_term_slug => $sub_term) {

				// Only proceed if this term has the appropriate details
				if (empty($sub_term['label'])) {
					continue;
				}

				$sub_term_exists = term_exists($sub_term['label'], parent::NAME, $term_id);

				if (!empty($sub_term_exists)) {
					//child term exists for parent term, lets move on
					continue;
				}

				// Define the sub term arguments
				$sub_term_arguments = array(
					'parent' => $term_id,
					'slug'   => $sub_term_slug,
				);

				// Add the description to the arguments if it exists
				if (!empty($sub_term['description'])) {
					$sub_term_arguments['description'] = $sub_term['description'];
				}

				//child term doesn't exist for parent, lets create it
				$sub_term_array = wp_insert_term($sub_term['label'], parent::NAME, $sub_term_arguments);
			}	//end loop child terms

		}	//end loop parent terms

		// If terms is added than mark is as added.
		$this->_mark_terms_as_added($terms_hash);
	}	//end maybe_add_terms()

	/**
	 * Enqueues the main(.min).js file in admin side.
	 *
	 * @param string $hook Hook suffix for the current admin page.
	 *
	 * @since 2018-06-22 Kelin Chauhan <kelin.chauhan@rtcamp.com> READS-1288
	 */
	public function action_admin_enqueue_scripts($hook)
	{

		// Don't load the script unless its Add new post page or post edit page.
		if ('post-new.php' !== $hook && 'post.php' !== $hook) {
			return;
		}

		$js_dir = 'build';

		if (!\PMC::is_production() && \PMC::filter_input(INPUT_GET, 'debug', FILTER_VALIDATE_BOOLEAN) === true) {

			$js_dir = 'src';
		}

		wp_enqueue_script(
			'pmc-post-options-main-js',
			sprintf('%s/assets/%s/js/main.js', untrailingslashit(PMC_POST_OPTIONS_URL), $js_dir),
			['jquery']
		);
	}

	/**
	 * Conditional function to check if Post Options for the
	 * post type exist or not.
	 *
	 * @param string $post_type
	 * @return boolean Returns TRUE if Post Options for the post type are registered else FALSE
	 */
	public function does_post_type_has_option($post_type)
	{

		if (!empty($post_type)) {
			$term = term_exists($post_type, parent::NAME, 0);

			if (!empty($term)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Conditional function to check if Post Options for the
	 * post type (to which a post belongs) exist or not.
	 *
	 * @param int|WP_Post $post Post ID or object
	 * @return boolean Returns TRUE if Post Options for the post type are registered else FALSE
	 */
	public function is_using_post_type_options($post)
	{

		if (empty($post)) {
			return false;
		}

		$post_type = get_post_type($post);

		if (!empty($post_type)) {
			return $this->does_post_type_has_option($post_type);
		}

		return false;
	}

	/**
	 * Return parent term id
	 */
	public function get_parent_id($post = '')
	{

		if (!empty($post) && $this->is_using_post_type_options($post)) {

			$term = term_exists(get_post_type($post), parent::NAME, 0);

			if (!empty($term['term_id'])) {
				return intval($term['term_id']);
			}
		} else {

			$term = term_exists(self::PARENT_TERM, parent::NAME, 0);

			if (!empty($term['term_id'])) {
				return intval($term['term_id']);
			}
		}

		return 0;
	}

	/**
	 * Return parent term id
	 */
	public function get_parent_id_for_post_type($post_type)
	{

		if (!empty($post_type) && $this->does_post_type_has_option($post_type)) {

			$term = term_exists($post_type, parent::NAME, 0);

			if (!empty($term['term_id'])) {
				return intval($term['term_id']);
			}
		} else {

			$term = term_exists(self::PARENT_TERM, parent::NAME, 0);

			if (!empty($term['term_id'])) {
				return intval($term['term_id']);
			}
		}

		return 0;
	}

	public function get_term_id($term, $post)
	{
		if (empty($term) || empty($post)) {
			return 0;
		}

		if (!is_string($term)) {
			throw new \ErrorException(__CLASS__ . '::' . __FUNCTION__ . '() expects $term to contain a term name');
		}

		$parent_id = $this->get_parent_id($post);

		$term_array = term_exists($term, parent::NAME, $parent_id);

		if (!empty($term_array['term_id'])) {
			return intval($term_array['term_id']);
		}

		unset($term_array, $parent_id);

		//likely looked up term in custom post type children & it was not found
		//so lets look it up under global term now
		$parent_term = term_exists(self::PARENT_TERM, parent::NAME, 0);

		if (empty($parent_term['term_id'])) {
			return 0;
		} else {
			$parent_id = intval($parent_term['term_id']);
		}

		$term_array = term_exists($term, parent::NAME, $parent_id);

		if (!empty($term_array['term_id'])) {
			return intval($term_array['term_id']);
		}

		return 0;
	}

	/**
	 * Method to fetch post option ID for a specific post type which would be
	 * parent term for the option term in question. If parent term for post type
	 * is not found then the option term is looked under global options.
	 *
	 * @param string $term Option name
	 * @param string $post_type Post type under which option is to be looked up
	 * @return integer Term ID of option or zero if nothing is found
	 */
	public function get_term_id_by_post_type($term, $post_type)
	{

		if (empty($term) || empty($post_type)) {
			return 0;
		}

		if (!is_string($term)) {
			throw new \ErrorException(__CLASS__ . '::' . __FUNCTION__ . '() expects $term to contain a term name');
		}

		$parent_id = $this->get_parent_id_for_post_type($post_type);

		$term_array = term_exists($term, parent::NAME, $parent_id);

		if (!empty($term_array['term_id'])) {
			return intval($term_array['term_id']);
		}

		unset($term_array, $parent_id);

		//likely looked up term in custom post type children & it was not found
		//so lets look it up under global term now
		$parent_term = term_exists(self::PARENT_TERM, parent::NAME, 0);

		if (empty($parent_term['term_id'])) {
			//something is wrong, this should exist
			return 0;
		} else {
			$parent_id = intval($parent_term['term_id']);
		}

		$term_array = term_exists($term, parent::NAME, $parent_id);

		if (!empty($term_array['term_id'])) {
			return intval($term_array['term_id']);
		}

		return 0;
	}

	public function post_has_term($post, $term)
	{
		if (empty($term) || empty($post)) {
			return false;
		}

		return has_term($term, parent::NAME, $post);
	}

	/**
	 * Generate hash for terms.
	 *
	 * @param array $terms Array of terms for which hash has to be generated.
	 *
	 * @return string Hash.
	 */
	protected function _get_terms_hash(array $terms = array())
	{

		ksort($terms);

		return md5(serialize($terms));
	}


	/**
	 * Function will identify that requested terms should be add or
	 * It's already exists.
	 *
	 * @param $hash string terms's hash.
	 *
	 * @return bool TRUE If terms is not exists and can to be add, FALSE if it's already exists.
	 */
	protected function _should_add_terms($hash = '')
	{

		if (empty($hash)) {
			return false;
		}

		$existing_term_hashes = pmc_get_option(self::PARENT_TERM_SAVE_OPTION_KEY);

		if (empty($existing_term_hashes)) {
			return true;
		}

		/**
		 * Check if request term's hash is added.
		 * If it is Than return false.
		 */
		if (in_array($hash, $existing_term_hashes, true)) {
			return false;
		}

		return true;
	}

	/**
	 * Mark terms as added.
	 *
	 * @param $hash string terms's hash.
	 *
	 * @return bool TRUE on success, Otherwise FALSE.
	 */
	protected function _mark_terms_as_added($hash = '')
	{

		if (empty($hash)) {
			return false;
		}

		$existing_term_hashes = pmc_get_option(self::PARENT_TERM_SAVE_OPTION_KEY);

		if (empty($existing_term_hashes) || !is_array($existing_term_hashes)) {
			$existing_term_hashes = array();
		}

		if (in_array($hash, $existing_term_hashes, true)) {
			return false;
		}

		$existing_term_hashes[] = $hash;

		if (count($existing_term_hashes) >= self::TERMS_HASH_CACHE_COUNT) {
			array_shift($existing_term_hashes);
		}

		return pmc_update_option(self::PARENT_TERM_SAVE_OPTION_KEY, $existing_term_hashes);
	}
}	// end class


//EOF
