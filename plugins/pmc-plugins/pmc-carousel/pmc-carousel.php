<?php
/*
Plugin Name: PMC Carousel
Plugin URI: http://www.pmc.com/
Version: 1.1.0
Author: Taylor Lovett, 10up, PMC
License: PMC Proprietary.  All rights reserved.
Text Domain: pmc-carousel
Domain Path: /languages
*/

//life of a carousel item in hours
if (!defined('PMC_CAROUSEL_LIFE')) {
	define('PMC_CAROUSEL_LIFE', 0);	//if carousel item life is not defined for a site then default to immortality
}

wpcom_vip_load_plugin('pmc-global-functions', 'pmc-plugins');
// Required for drag and drop reordering of hierarchical post types
pmc_load_plugin('simple-page-ordering', 'pmc-plugins'); //VIP deprecated plugin now in our pmc-plugins repo

pmc_load_plugin('pmc-store-products', 'pmc-plugins'); // Used for amazon api override preview.

require_once(__DIR__ . '/featured-articles.php');

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Carousel
{

	use Singleton;

	public $option_name = 'pmc_carousel_taxonomies';
	private $option_defaults = array(
		'available_taxes' => array()
	);
	public $default_taxonomy = 'pmc_carousel_modules';
	protected $default_term = 'featured-carousel';

	const modules_taxonomy_name = 'pmc_carousel_modules';
	const cache_group = '_pmc-carousel-modules';

	/**
	 * Initialization function called when object is instantiated. Does nothing by default.
	 *
	 * @uses add_action
	 * @return object
	 */
	protected function __construct()
	{
		add_action('save_post', array($this, 'action_save_post'));
		add_action('restrict_manage_posts', array($this, 'action_restrict_manage_posts'));
		add_action('manage_posts_extra_tablenav', array($this, 'manage_posts_extra_tablenav'));
		add_action('admin_print_footer_scripts', array($this, 'action_print_footer_scripts'));
		add_action('add_meta_boxes', array($this, 'action_add_meta_boxes'));
		add_action('admin_enqueue_scripts', array($this, 'action_admin_enqueue_scripts'));
		add_action('wp_ajax_carousel_cats', array($this, 'action_ajax'));
		add_action('pre_get_posts', array($this, 'action_pre_get_posts'));
		add_action('admin_menu', array($this, 'action_admin_menu'));
		add_action('admin_init', array($this, 'action_admin_init'));
		add_action('init', array($this, 'action_init'));
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
		add_filter('the_title', [$this, 'get_fallback_title'], 10, 2);

		add_action('simple_page_ordering_ordered_posts', array($this, 'action_simple_page_ordering_ordered_posts'), 10, 2);

		$this->default_taxonomy = apply_filters('pmc_carousel_default_taxonomy_name', $this->default_taxonomy);
		$this->default_term = apply_filters('pmc_carousel_default_term_name', $this->default_term);

		add_action('pmc-linkcontent-before-insert-field', array($this, 'action_pmc_linkcontent_before_insert_field'));

		add_action('plugins_loaded', function () {

			load_plugin_textdomain('pmc-outbrain', false, basename(dirname(__FILE__)) . '/languages');
		});
	}

	/**
	 * Add menu item for allowed taxonomies settings page
	 *
	 * @uses add_submenu_page
	 * @return void
	 */
	public function action_admin_menu()
	{
		global $submenu;

		add_submenu_page('edit.php', __('Carousel Taxonomies', 'pmc-carousel'), __('Carousel Taxonomies', 'pmc-carousel'), 'manage_options', 'carousel-taxonomy.php', array($this, 'settings_page'));


		// we need to manually add our carousel modules taxonomy to the Posts menu because it's not associated with the post object
		if (current_user_can('manage_options')) {
			if (isset($submenu['edit.php']) && is_array($submenu['edit.php'])) {
				$submenu['edit.php'][] = array(
					__('Carousel Modules', 'pmc-carousel'),
					'manage_options',
					'edit-tags.php?taxonomy=' . self::modules_taxonomy_name,
				);
			}
		}
	}

	/**
	 * Validate settings from allowed taxonomies page
	 *
	 * @param array $options
	 * @uses get_option, wp_parse_args, sanitize_text_field
	 * @return array
	 */
	public function validate_options($options)
	{

		$current_options = get_option($this->option_name, $this->option_defaults);
		$current_options = wp_parse_args($current_options, $this->option_defaults);

		$new_options = array();

		foreach ($this->option_defaults as $option_key => $option_default_value) {
			$new_options[$option_key] = array_map('sanitize_text_field', $options[$option_key]);
		}

		return $new_options;
	}

	/**
	 * Register setting and validation callback
	 * @uses register_setting
	 * @return void
	 */
	function action_admin_init()
	{
		register_setting($this->option_name, $this->option_name, array($this, 'validate_options'));
		if (($GLOBALS['pagenow'] == 'post.php' || $GLOBALS['pagenow'] == 'post-new.php') && class_exists('PMC_LinkContent')) {
			add_action('init', array('PMC_LinkContent', 'enqueue'));
		}

		add_action('simple_page_ordering_pre_order_posts', array($this, 'action_simple_page_ordering_pre_order_posts'), 10, 2);

		// disable simple page ordering as default
		add_filter('simple_page_ordering_is_sortable', '__return_false', 9);
		// override to enable simple page ordering on pmc_featured post type
		add_filter('simple_page_ordering_is_sortable', array($this, 'filter_simple_page_ordering_is_sortable'), 20, 2);

		add_action('admin_notices', array($this, 'action_admin_notices'));
	}

	/**
	 * TO BE REMOVE: Temporarily debug to capture cache group & cache contents
	 * to troubleshoot issue with carousel not updating after changes are made
	 * only activate when logged in and non debug value is pass in querystring
	 */
	protected function _debug($type, $data)
	{
		if (!is_user_logged_in() || empty($_GET['debug'])) {
			return;
		}

		if (empty($this->debug_data)) {
			$this->debug_data = array();
		}

		$this->debug_data[$type] = $data;
	}

	/**
	 * TO BE REMOVE: Temporarily debug to display cache group
	 * to troubleshoot issue with carousel not updating after changes are made
	 * only activate when logged in, is admin, and non empty debug value is pass in querystring
	 */
	public function action_admin_notices()
	{

		if ('pmc_featured' !== get_post_type() || empty($_GET['debug'])) {
			return;
		}

		// This counter is use for cache group controlling the carousel cache
		// when carousel is updated, a new value is set caushing carousel cache to regenerate.
		// This value should match value display on front end when debug is enable
		$counter = wp_cache_get('PMC_Carousel_Group_Counter', PMC_Carousel::cache_group);

		if (empty($counter)) {
			$counter = pmc_get_option('PMC_Carousel_Group_Counter', PMC_Carousel::cache_group);
			if (empty($counter)) {
				$counter = 0;
			}
		}

		/* translators: %1$s gallery cache counter */
		echo '<div class="updated"><p>' . sprintf(esc_html__('carousel cache group counter value: %1$s', 'pmc-carousel'), esc_html($counter)) . '</p></div>';
	}

	/**
	 * TO BE REMOVE: Temporarily debug to render html comments to show cache
	 * group & cache contents to troubleshoot issue with carousel not updating
	 * after changes are made only activate when user logged in, is admin, and non empty debug
	 * value is passed in querystring
	 */
	public function action_wp_footer()
	{
		if (!is_user_logged_in() || !current_user_can('manage_options') || empty($_GET['debug'])) {
			return;
		}

		// This counter is use for cache group controlling the carousel cache
		// when carousel is updated, a new value is set caushing carousel cache to regenerate.
		// This value should match value display on admin debug is enable
		$counter = wp_cache_get('PMC_Carousel_Group_Counter', PMC_Carousel::cache_group);

		if (empty($counter)) {
			$counter = pmc_get_option('PMC_Carousel_Group_Counter', PMC_Carousel::cache_group);
			if (empty($counter)) {
				$counter = 0;
			}
		}

		echo "<!--// \n";
		/* translators: %1$s gallery cache counter */
		echo sprintf(esc_html__('carousel cache group counter value: %1$s', 'pmc-carousel'), esc_html($counter)) . "\n";
		if (!empty($this->debug_data)) {
			print_r($this->debug_data);
		}
		echo " //-->\n";
	}

	public function filter_simple_page_ordering_is_sortable($sortable, $post_type = '')
	{
		if ('pmc_featured' === $post_type) {
			return true;
		}
		return $sortable;
	}

	public function action_simple_page_ordering_pre_order_posts($post, $start)
	{
		if ('pmc_featured' !== get_post_type($post)) {
			return;
		}
		$this->_pmc_tax = get_post_meta($post->ID, '_pmc_tax', true);
		$this->_pmc_term = get_post_meta($post->ID, '_pmc_term', true);
		add_action('pre_get_posts', array($this, 'action_simple_page_ordering_pre_get_posts'));
	}

	public function action_simple_page_ordering_pre_get_posts($query)
	{
		if (isset($this->_pmc_tax)) {
			$query->set(
				'meta_query',
				array(
					array(
						'key' => '_pmc_tax',
						'value' => sanitize_text_field($this->_pmc_tax)
					),
					array(
						'key' => '_pmc_term',
						'value' => sanitize_text_field($this->_pmc_term)
					)
				)
			);
		}
	}

	/**
	 * Output settings page for choosing allowed taxonomies for carousel
	 * @uses get_option, wp_parse_args, settings_fields, esc_attr, submit_button, esc_html
	 * @return void
	 */
	public function settings_page()
	{

		$taxonomies = get_taxonomies();
		$options = get_option($this->option_name, $this->option_defaults);
		$options = wp_parse_args($options, $this->option_defaults);
?>
		<div class="wrap">
			<h2><?php esc_html_e('Carousel Taxonomies', 'pmc-carousel') ?></h2>

			<?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') : ?>
				<div id="message" class="updated">
					<p><?php esc_html_e('Settings saved.', 'pmc-carousel'); ?></p>
				</div><!-- .updated#message -->
			<?php endif; ?>

			<form action="options.php" method="post">
				<?php settings_fields($this->option_name); ?>
				<p><?php esc_html_e('Check the taxonomies that you want to be available to the carousel.', 'pmc-carousel'); ?></p>
				<table class="form-table">
					<tbody>
						<?php foreach ($taxonomies as $tax) : ?>
							<tr>
								<td style="width: 20px;"><input <?php checked(true, in_array($tax, $options['available_taxes'])); ?> type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[available_taxes][]" value="<?php echo esc_attr($tax); ?>" /></td>
								<td><?php echo esc_html($tax); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
	<?php
	}

	/**
	 * Return taxonomies that are available to carousel
	 * @uses get_option, wp_parse_args
	 * @return array
	 */
	public function get_available_taxonomies()
	{
		$options = get_option($this->option_name, $this->option_defaults);
		$options = wp_parse_args($options, $this->option_defaults);
		if (empty($options['available_taxes'])) $options['available_taxes'] = array();

		return $options['available_taxes'];
	}

	/**
	 * Modify global query for carousel taxonomy filters
	 *
	 * @param object $query
	 * @uses esc_attr, WP_Query::set, get_terms
	 * @return void
	 */
	public function action_pre_get_posts($query)
	{

		if (is_admin() && isset($_GET['post_type']) && 'pmc_featured' == $_GET['post_type']) {

			// clear carousel cache if requested
			if (isset($_GET['clear_carousel_cache']) && $_GET['clear_carousel_cache'] == 'Clear Cache') {
				$this->delete_cache();
			}

			if (isset($_GET['pmc_term']) &&  "show_all"  == $_GET['pmc_term']) {
				return;
			}

			$meta_term = false;
			$meta_tax = false;

			if (!empty($_GET['pmc_taxonomy']) && !empty($_GET['pmc_term'])) {
				$meta_tax = $_GET['pmc_taxonomy'];
				$meta_term = $_GET['pmc_term'];
			} else {
				$meta_term = $this->default_term;
				$meta_tax = $this->default_taxonomy;
			}

			if (!empty($meta_tax) && !empty($meta_term)) {
				$query->set(
					'meta_query',
					array(
						array(
							'key' => '_pmc_tax',
							'value' => sanitize_text_field($meta_tax)
						),
						array(
							'key' => '_pmc_term',
							'value' => sanitize_text_field($meta_term)
						)
					)
				);
			} else {

				$taxes = (array) $this->get_available_taxonomies();

				if (!empty($taxes)) {
					$terms = get_terms(sanitize_text_field($taxes[0]), 'hide_empty=0');

					if (!empty($terms)) {
						$first_term = NULL;
						foreach ($terms as $t) {
							$first_term = $t;
							break;
						}

						$query->set(
							'meta_query',
							array(
								array(
									'key' => '_pmc_tax',
									'value' => $taxes[0]
								),
								array(
									'key' => '_pmc_term',
									'value' => $first_term->slug
								)
							)
						);
					}
				}
			}
		}
	}

	/**
	 * Return options for term dropdown via ajax
	 *
	 * @uses wp_verify_nonce, plugins_url, get_terms, esc_html
	 * @return void
	 */
	public function action_ajax()
	{

		if (!isset($_POST['nonce']) || empty($_POST['taxonomy']))
			exit(0);

		if (!wp_verify_nonce($_POST['nonce'], plugins_url('/js/scripts.js', __FILE__)))
			exit(0);

		$terms = get_terms($_POST['taxonomy'], 'hide_empty=0');
		if (!empty($terms)) {
			foreach ($terms as $term) {
				echo '<option value="' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</option>' . "\n";
			}
		}

		exit();
	}

	/**
	 * Add admin scripts for plugin
	 *
	 * @param string $hook
	 * @uses wp_enqueue_script, wp_localize_script, plugins_url, wp_create_nonce, get_post_type
	 * @return void
	 */
	public function action_admin_enqueue_scripts($hook)
	{
		if ('pmc_featured' == get_post_type() || (isset($_GET['post_type']) && 'pmc_featured' == $_GET['post_type'])) {

			wp_enqueue_script('pmc-carousel-cats', plugins_url('js/scripts.js', __FILE__), array('jquery'), '1.3', true);

			$local_array = array('nonce' => wp_create_nonce(plugins_url('/js/scripts.js', __FILE__)));

			wp_localize_script('pmc-carousel-cats', 'pmcCarouselCats', $local_array);
		}
	}

	/**
	 * Register meta box for plugin
	 *
	 * @uses add_meta_box
	 * @return void
	 */
	public function action_add_meta_boxes()
	{
		add_meta_box('carousel-cat', __('Carousel Taxonomy', 'pmc-carousel'), array($this, 'category_meta_box'), 'pmc_featured', 'side');
	}

	/**
	 *
	 * @codeCoverageIgnore
	 *
	 * Return data for carousel data for given taxonomy and term
	 *
	 * @param string $taxonomy
	 * @param string $term
	 * @param int $num_articles
	 * @param string $size
	 * @param array $opts { // (optional)
	 *   @type bool 'exclude-author' if set, exclude retrieving author information (default <false>).
	 * }
	 *
	 * @uses esc_attr, have_posts, get_the_ID, get_the_title, wp_reset_postdata, get_permalink, has_post_thumbnail
	 *     get_post_thumbnail_id, get_the_category, wp_get_attachment_url, get_category_link, get_the_date, get_the_author
	 *     setup_postdata
	 * @return array|bool|mixed
	 */
	public function render($taxonomy, $term, $num_articles = 5, $size = '', $opts = array())
	{
		$default_opts = array(
			'flush_cache' => false,
			'add_filler'  => true,
			'add_filler_all_posts' => true,
		);
		$opts = wp_parse_args($opts, $default_opts);

		// Never allow the cache to be flushed on the front-end
		if (!is_admin()) {
			$opts['flush_cache'] = false;
		}

		$exclude_author = !empty($opts['exclude-author']);

		/*
		 * Filter to allow short circuit of this method and supply an output controlled under different situations.
		 *
		 * @param array $short_circuit_value Empty array by default, if however any listener sets this value
		 *                                   then the function will return this value and bail out.
		 * @param string $taxonomy           Taxonomy name for which carousel is requested
		 * @param string $term               Term slug for which the carousel is requested
		 * @param int    $num_articles       Number of posts to be returned
		 * @param string $size               Image attachment size name
		 * @param array  $opts               Additional options
		 *
		 * @return array Value returned by this filter has to be a non-empty array for the short circuit to work.
		 */
		$maybe_short_circuit = apply_filters('pmc_carousel_render_short_circuit', [], $taxonomy, $term, $num_articles, $size, $opts);

		if (!empty($maybe_short_circuit) && is_array($maybe_short_circuit)) {
			return $maybe_short_circuit;
		}

		$key = $taxonomy . '-' . $term . '-' . $num_articles . '-' . $size . '-' . $exclude_author;
		$group = $this->get_cache_group();

		// Always load cached data unless the flush_cache option is set
		if (empty($opts['flush_cache'])) {
			$cached_output = wp_cache_get($key, $group);
			$this->_debug('wp_cache_get: ', $cached_output);

			if ($cached_output !== false) {
				return $cached_output;
			}
		}

		$num_articles = absint($num_articles);

		// Enforcing a range of articles 1-50, once available we should use PMC::numeric_range here
		if ($num_articles < 1 || $num_articles > 50)
			return false;

		global $post;
		$old_post = $post;

		$args = array(
			'posts_per_page'  => $num_articles,
			'post_type'       => 'pmc_featured',
			'orderby'         => 'menu_order',
			'order'           => 'ASC',
			'meta_query'      => array(
				array(
					'key'    => '_pmc_tax',
					'value'  => $taxonomy,
				),
				array(
					'key'    => '_pmc_term',
					'value'  => $term,
				),
			),
			//'es' => true,//VIP: This query won't scale and can't be run on MySQL
		);

		$carousel_query = new WP_Query($args);

		$current_time_utc = PMC_TimeMachine::create('UTC')->format_as('U');	//current timestamp per UTC

		$posts = array();
		$section_fronts = array();
		$externals = array();

		if ($carousel_query->have_posts()) {
			while ($carousel_query->have_posts()) {
				$carousel_query->the_post();

				/**
				 * If carousel items are mortal then lets check how old are they
				 * and if its their time to die.
				 *
				 * @since 2014-01-17 Amit Gupta
				 * @see https://penskemediacorp.atlassian.net/browse/PPT-1234
				 */
				if (intval(PMC_CAROUSEL_LIFE) > 0) {
					$item_last_modified = PMC_TimeMachine::create('UTC')->from_time('Y-m-d H:i:s', $post->post_modified_gmt)->format_as('U');
					$hours_since_last_modified = intval((($current_time_utc - $item_last_modified) / 3600));

					if (intval(PMC_CAROUSEL_LIFE) <= $hours_since_last_modified) {
						//delete this item from carousel
						wp_delete_post(get_the_ID());

						//move on to next item
						continue;
					}

					unset($hours_since_last_modified, $item_last_modified);
				}

				$post_id = get_post_meta(get_the_ID(), '_pmc_master_article_id', true);


				if (!is_numeric($post_id)) {
					// then it is from pmc_linkcontent
					$post_id = json_decode(stripslashes($post_id));
					if (is_object($post_id) && isset($post_id->id)) {
						if (isset($post_id->type) && ($post_id->type == 'Section Front')) {
							$section_fronts[get_the_ID()] = $post_id;
							$post_id = 0;
						} elseif (isset($post_id->type) && $post_id->type == 'External') {
							$externals[get_the_ID()] = $post_id;
							$post_id = 0;
						} else {
							$post_id = $post_id->id;
						}
					}
				}

				if (is_home() || is_front_page()) {
					if (has_term('exclude-from-homepage', '_post-options', $post_id)) {
						continue;
					}
				}

				if (is_archive()) {
					if (has_term('exclude-from-section-fronts', '_post-options', $post_id)) {
						continue;
					}
				}

				$posts[get_the_ID()] = $post_id;
			}
		}

		// running these filters give the caller an opportunity to use a different taxonomy and term
		// for the fall back logic.
		$taxonomy = apply_filters('pmc_carousel_items_fallback_taxonomy', $taxonomy);
		$term	  = apply_filters('pmc_carousel_items_fallback_term', $term);

		// if we don't have enough posts, grab the rest from the taxonomy
		if ($opts['add_filler'] === true && count($posts) < $num_articles) {

			// VIP check if there are any terms - just need the count
			$terms_count = (int) get_terms(
				[
					'taxonomy'   => $taxonomy,
					'fields'     => 'count',
					'hide_empty' => 0,
				]
			);

			if (0 < $terms_count) {

				$post__not_in = array_filter(array_unique((array) $posts));
				$count        = (int) $num_articles;
				$count        = $count - count($posts) + count($post__not_in);

				// add 10 to count to ensure we're getting posts that are not exclude-from-homepage
				// or exclude-from-section-front post options
				$count = $count + 10;

				// VIP: ensure that the posts array contains only post IDs, and
				// no stdclass objects
				$posts = array_filter($posts, 'is_int');
				$args  = [
					'post_type'      => 'post',
					'tax_query'      => [ // WPCS: slow query ok.
						[
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $term,
						],
					],
					'posts_per_page' => $count,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
				];

				$args      = apply_filters('pmc_carousel_items_fallback_args', $args);
				$tax_query = new WP_Query($args);

				if ($tax_query->have_posts()) {
					while ($tax_query->have_posts()) {
						$tax_query->the_post();
						$current_post_id = get_the_ID();

						if (in_array($current_post_id, (array) $post__not_in, true)) {
							continue;
						}

						// skip over post-options: exclude-from-homepage and exclude-from-section-fronts
						if (is_home() || is_front_page()) {
							if (has_term('exclude-from-homepage', '_post-options', $current_post_id)) {
								continue;
							}
						}

						if (is_archive()) {
							if (has_term('exclude-from-section-fronts', '_post-options', $current_post_id)) {
								continue;
							}
						}

						$posts[$current_post_id] = $current_post_id;
						if (count($posts) >= $num_articles) {
							break;
						}
					}
				}
			}
		}

		// if we still don't have enough posts, just grab the latest
		if ($opts['add_filler'] === true && $opts['add_filler_all_posts'] === true && count($posts) < $num_articles) {

			$post__not_in = array_filter(array_unique((array) $posts));
			$count        = (int) $num_articles;
			$count        = $count - count($posts) + count($post__not_in);

			// add 10 to count to ensure we're getting posts that are not exclude-from-homepage
			// or exclude-from-section-front post options
			$count = $count + 10;

			$args = [
				'posts_per_page' => $count,
				'post_type'      => 'post',
			];

			$args = apply_filters('pmc_carousel_items_fallback_latest_args', $args);

			$latest_query = new WP_Query($args);

			if ($latest_query->have_posts()) {
				while ($latest_query->have_posts()) {
					$latest_query->the_post();

					$current_post_id = get_the_ID();

					if (in_array($current_post_id, (array) $post__not_in, true)) {
						continue;
					}

					// skip over post-options: exclude-from-homepage and exclude-from-section-fronts
					if (is_home() || is_front_page()) {
						if (has_term('exclude-from-homepage', '_post-options', $current_post_id)) {
							continue;
						}
					}

					if (is_archive()) {
						if (has_term('exclude-from-section-fronts', '_post-options', $current_post_id)) {
							continue;
						}
					}

					$posts[$current_post_id] = $current_post_id;
					if (count($posts) >= $num_articles) {
						break;
					}
				}
			}
		}

		$output = array();

		foreach ($posts as $parent_post_id => $post_id) {
			if ($post_id == 0) {
				if (!empty($section_fronts[$parent_post_id])) {
					$term = get_term($section_fronts[$parent_post_id]->id, $section_fronts[$parent_post_id]->taxonomy);
					$add = array(
						'ID'			=> $section_fronts[$parent_post_id]->id,
						'parent_ID'		=> $parent_post_id,
						'title'			=> $section_fronts[$parent_post_id]->title,
						'url'			=> $section_fronts[$parent_post_id]->url,
						'date'			=> '',
						'author'		=> '',
						'author-url'	=> '',
						'excerpt'		=> "View Articles from {$section_fronts[$parent_post_id]->title}",
						'category'		=> $section_fronts[$parent_post_id]->title,
						'category-url'	=> $section_fronts[$parent_post_id]->url,
						'category-slug'	=> !empty($term->slug) ? $term->slug : '',
					);
					if (has_post_thumbnail($parent_post_id)) {
						$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($parent_post_id), $size, false);
						if (!empty($thumb[0])) {
							$add['image'] = $thumb[0];

							// Image ID so that we can calculate srcset/sizes and fetch for the cached image.
							$add['image_id'] = get_post_thumbnail_id($parent_post_id);
							if (class_exists('PMC')) {
								$add['image_alt'] = PMC::get_attachment_image_alt_text(get_post_thumbnail_id($parent_post_id), $parent_post_id);
							}
						}
					}

					if ($parent_post_id != $post_id) {
						$parent_post = get_post($parent_post_id);

						if (!empty($parent_post->post_excerpt))
							$add['excerpt'] = apply_filters('the_excerpt', $parent_post->post_excerpt);

						if (!empty($parent_post->post_title))
							$add['title'] = apply_filters('the_title', $parent_post->post_title);
					}

					$output[$section_fronts[$parent_post_id]->id] = $add;
				} elseif (!empty($externals[$parent_post_id])) {
					//this is an external link, or a link that was not able to be mapped to a post_id...
					$add = array(
						'ID' => $externals[$parent_post_id]->id,
						'parent_ID' => $parent_post_id,
						'title' => $externals[$parent_post_id]->title,
						'url' => $externals[$parent_post_id]->url,
						'date' => '',
						'author' => '',
						'author-url' => '',
						'excerpt' => '',
						'category' => '',
						'category-url' => '',
						'category-slug' => '',
					);

					if (has_post_thumbnail($parent_post_id)) {
						$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($parent_post_id), $size, false);
						if (!empty($thumb[0])) {
							$add['image'] = $thumb[0];

							// Image ID so that we can calculate srcset/sizes and fetch for the cached image.
							$add['image_id'] = get_post_thumbnail_id($parent_post_id);
							if (class_exists('PMC')) {
								$add['image_alt'] = PMC::get_attachment_image_alt_text(get_post_thumbnail_id($parent_post_id), $parent_post_id);
							}
						}
					}

					$output[$parent_post_id] = $add;
				}
			} else {
				if ($post = get_post($post_id)) {
					setup_postdata($post);

					// lets find the category with the most posts that is attached to the current post in the loop
					$categories = get_the_terms($post_id, 'vertical');
					$heaviest_cat = NULL;
					if (is_array($categories)) {
						foreach ($categories as $category) {
							if (NULL == $heaviest_cat) {
								$heaviest_cat = $category;
							} else {
								if ($category->count > $heaviest_cat->count) {
									$heaviest_cat = $category;
								}
							}
						}
					}

					$add = array(
						'ID'          => get_the_ID(),
						'post_id'     => get_the_ID(),
						'parent_ID'   => $parent_post_id,
						'title'       => get_the_title(),
						'url'         => get_permalink(),
						'date'        => get_the_date('c'),
						'author'      => $exclude_author == 1 ? '' : get_the_author(),
						'author-url'  => $exclude_author == 1 ? '' : get_author_posts_url(get_the_author_meta('ID')),
						'excerpt'     => get_the_excerpt(),
					);

					//if the carousal has a featured image, use that
					if (has_post_thumbnail($parent_post_id)) {
						$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($parent_post_id), $size, false);
						if (!empty($thumb[0])) {
							$add['image'] = $thumb[0];

							// Image ID so that we can calculate srcset/sizes and fetch for the cached image.
							$add['image_id'] = get_post_thumbnail_id($parent_post_id);
							if (class_exists('PMC')) {
								$add['image_alt'] = PMC::get_attachment_image_alt_text(get_post_thumbnail_id($parent_post_id), $parent_post_id);
							}
						}
					}

					//if we don't have featured image yet, use the post's if its there
					if ((!isset($add['image']) || empty($add['image'])) && has_post_thumbnail()) {
						$thumb = wp_get_attachment_image_src(get_post_thumbnail_id(), $size, false);
						if (!empty($thumb[0])) {
							$add['image'] = $thumb[0];

							// Image ID so that we can calculate srcset/sizes and fetch for the cached image.
							$add['image_id'] = get_post_thumbnail_id();
							if (class_exists('PMC')) {
								$add['image_alt'] = PMC::get_attachment_image_alt_text(get_post_thumbnail_id());
							}
						}
					}

					if (NULL != $heaviest_cat) {
						// see if we need to suppress this display
						if (apply_filters('pmc_carousel_category_with_most_posts', TRUE)) {
							$add['category'] = $heaviest_cat->name;
							$add['category-url'] = get_term_link($heaviest_cat);
							$add['category-slug'] = $heaviest_cat->slug;
						}
					}

					if ($parent_post_id != $post_id) {
						$parent_post = get_post($parent_post_id);

						if (!empty($parent_post->post_excerpt))
							$add['excerpt'] = apply_filters('the_excerpt', $parent_post->post_excerpt);

						if (!empty($parent_post->post_title))
							$add['title'] = apply_filters('the_title', $parent_post->post_title);
					}


					if ($exclude_author == 0 && function_exists('variety_fetch_author')) {
						$author = variety_fetch_author(false, array('exclude-thumb' => true));
						if ($author != null) {
							$add['author'] = $author['name'];
							$add['author-url'] = $author['url'];
						}
					}

					$output[get_the_ID()] = $add;
				}
			}
		}

		wp_reset_postdata();
		$post = $old_post;

		//VIP: setting this to 10 minutes because it's a very slow operation
		$time_in_secs = 10 * MINUTE_IN_SECONDS + wp_rand(1, 150);
		$time_in_secs = intval($time_in_secs);
		wp_cache_set($key, $output, $group, $time_in_secs);
		$this->_debug('render: ', $output);

		return $output;
	}

	/**
	 * This function is responsible to return the cache group to use for caching
	 * Since we have no way to flush the cache, we use the cache group to emulate
	 * cache flushing function by changing the cache group to use for caching.
	 */
	private function get_cache_group()
	{

		// get value from wp_cache, this should be a permenant cache
		$counter = wp_cache_get('PMC_Carousel_Group_Counter', PMC_Carousel::cache_group);

		// something wrong with wp_cache?
		if (empty($counter)) {
			// fall back to grab value from pmc option
			$counter = pmc_get_option('PMC_Carousel_Group_Counter', PMC_Carousel::cache_group);
			if (empty($counter)) {
				// we never run into this, except for the first time code is push
				$counter = 0;
			}
		}

		return PMC_Carousel::cache_group . '-' . $counter;
	}

	/**
	 * This function emulate cache flush by setting a new value for cache group.
	 * By doing so, new caching will be operate on the new group.
	 * Hence all existing caches will be left untouch until auto expired.
	 */
	private function delete_cache($post = false)
	{

		// use timestamp for our cache group.
		// This new value will cause existing cache to missed and regenerate
		$counter = time();

		// This gives another option to delete more granular caches (eg, TL's featured carousel cache)
		if ($post && !empty($post->ID)) {
			$meta_tax = get_post_meta($post->ID, '_pmc_tax', true);
			$meta_term = get_post_meta($post->ID, '_pmc_term', true);
			do_action('pmc_carousel_cache_delete', $post, $meta_term, $meta_tax);
		}

		// setup the cache group counter to be use for caching, we want long term caching here
		wp_cache_set('PMC_Carousel_Group_Counter', $counter, PMC_Carousel::cache_group);
		// save in pmc option as a fallback, just in case wp_cache get remove/reset
		pmc_update_option('PMC_Carousel_Group_Counter', $counter, PMC_Carousel::cache_group);
	}

	/**
	 * Output meta box
	 *
	 * @param object $post
	 * @uses wp_nonce_field, get_taxonomies, get_post_meta, selected, esc_html, get_terms
	 * @return void
	 */
	public function category_meta_box($post = '')
	{
		wp_nonce_field(basename(__DIR__), 'carousel_cats_nonce');

		$taxonomies = $this->get_available_taxonomies();

		if (empty($taxonomies)) {
			return;
		}

		if (empty($post)) {
			$meta_tax = (!empty($_GET['pmc_taxonomy'])) ? sanitize_text_field($_GET['pmc_taxonomy']) : $this->default_taxonomy;
			$meta_term = (!empty($_GET['pmc_term'])) ? sanitize_text_field($_GET['pmc_term']) : $this->default_term;
		} else {
			$meta_tax = get_post_meta($post->ID, '_pmc_tax', true);
			$meta_term = get_post_meta($post->ID, '_pmc_term', true);
		}
		//Set Default
		if ('' == $meta_tax) {
			$meta_tax = $this->default_taxonomy; //'pmc_carousel_modules';
		}
		if ('' == $meta_term) {
			$meta_term = $this->default_term; //'featured-carousel';
		}

		$term_list_args = [
			'taxonomy'   => !empty($meta_tax) ? $meta_tax : $taxonomies[0],
			'hide_empty' => false,
		];

		/**
		 * Filters the carousel get terms list arguments.
		 *
		 * @param array $terms An array of get terms arguments.
		 */
		$term_list_args = apply_filters('pmc_carousel_terms_list_args', $term_list_args);

		$terms = get_terms($term_list_args);

	?>
		<select name="pmc_taxonomy">
			<?php foreach ($taxonomies as $tax) : ?>
				<option <?php selected($tax, $meta_tax); ?>><?php echo esc_html($tax); ?></option>
			<?php endforeach; ?>
		</select>

		<?php if (!empty($post)) echo '<br />'; ?>

		<select name="pmc_term" id="carousel-cat-term">
			<?php if (empty($post)) { ?>
				<option <?php selected("show_all", $meta_term); ?> value="show_all"><?php esc_html_e(' All Terms', 'pmc-carousel'); ?></option>
			<?php } ?>
			<?php if (!empty($terms)) : foreach ($terms as $term) : ?>
					<option <?php selected($term->slug, $meta_term); ?> value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
			<?php endforeach;
			endif; ?>
		</select>
		<?php
	}

	/**
	 * Save post meta for plugin
	 *
	 * @param int $post_id
	 * @uses wp_verify_nonce, sanitize_text_field, update_post_meta, delete_post_meta
	 * @return void
	 */
	public function action_save_post($post_id)
	{
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (isset($_POST['carousel_cats_nonce']) && wp_verify_nonce($_POST['carousel_cats_nonce'], basename(__DIR__))) {

			if (!empty($_POST['pmc_taxonomy'])) {
				update_post_meta($post_id, '_pmc_tax', sanitize_text_field($_POST['pmc_taxonomy']));
			} else {
				delete_post_meta($post_id, '_pmc_tax');
			}

			if (!empty($_POST['pmc_term'])) {
				update_post_meta($post_id, '_pmc_term', sanitize_text_field($_POST['pmc_term']));
			} else {
				delete_post_meta($post_id, '_pmc_term');
			}

			/***
			 * Save Linkcontent portion of the post.
			 */
			if (isset($_POST['pmc-carousel-override']) && '1' == $_POST['pmc-carousel-override']) {
				update_post_meta($post_id, '_pmc_carousel_override', true);

				if (isset($_POST['pmc-carousel-override-url']) && '' !== trim($_POST['pmc-carousel-override-url'])) {
					update_post_meta($post_id, '_pmc_carousel_override_url', esc_url_raw($_POST['pmc-carousel-override-url']));
					//get the post id from the provided url
					$linked_post_id = url_to_postid(esc_url_raw($_POST['pmc-carousel-override-url']));
					if (intval($linked_post_id) > 0) {
						//we were able to match this post with something in the database....awesome!
						$post          = get_post($linked_post_id);
						$override_data = array(
							'url'   => esc_url_raw($_POST['pmc-carousel-override-url']),
							'id'    => $linked_post_id,
							'title' => wp_kses_post($post->post_title), // Sanitize title with basic post tags allowed
							'type'  => 'Article',
						);
						// unset the post object used for override data.
						unset($post);
					} else {
						// External Link
						$override_data = array(
							'url'   => esc_url_raw($_POST['pmc-carousel-override-url']),
							'id'    => 0,
							'title' => wp_kses_post($_POST['post_title']), // Sanitize title with basic post tags allowed
							'type'  => 'External'
						);
					}
					update_post_meta($post_id, '_pmc_master_article_id', json_encode($override_data, JSON_UNESCAPED_UNICODE));

					// Price override for Apple News and Today's Top Deal.
					$price_override_input = PMC::filter_input(INPUT_POST, 'pmc-carousel-override-price', FILTER_SANITIZE_STRING);

					if (!empty($price_override_input)) {
						update_post_meta($post_id, '_pmc_carousel_override_price', sanitize_text_field($price_override_input));
					} else {
						delete_post_meta($post_id, '_pmc_carousel_override_price');
					}

					// Tag override for Apple News.
					$tag_override_input = PMC::filter_input(INPUT_POST, 'pmc-carousel-override-tag', FILTER_SANITIZE_STRING);

					if (!empty($tag_override_input)) {
						update_post_meta($post_id, '_pmc_carousel_override_tag', sanitize_text_field($tag_override_input));
					} else {
						delete_post_meta($post_id, '_pmc_carousel_override_tag');
					}

					// Coupon override for Today's Top Deal.
					$coupon_override_input = PMC::filter_input(INPUT_POST, 'pmc-carousel-override-coupon', FILTER_SANITIZE_STRING);

					if (!empty($coupon_override_input)) {
						update_post_meta($post_id, '_pmc_carousel_override_coupon', sanitize_text_field($coupon_override_input));
					} else {
						delete_post_meta($post_id, '_pmc_carousel_override_coupon');
					}
				} else {
					delete_post_meta($post_id, '_pmc_carousel_override_url');
					delete_post_meta($post_id, '_pmc_carousel_override_price');
					delete_post_meta($post_id, '_pmc_carousel_override_tag');
					delete_post_meta($post_id, '_pmc_carousel_override_coupon');
				}
			} else {
				delete_post_meta($post_id, '_pmc_carousel_override');
				delete_post_meta($post_id, '_pmc_carousel_override_url');
				delete_post_meta($post_id, '_pmc_carousel_override_price');
				delete_post_meta($post_id, '_pmc_carousel_override_tag');
				delete_post_meta($post_id, '_pmc_carousel_override_coupon');
			}

			/***
			 * End Save Linkcontent portion of the post.
			 */

			$post = get_post($post_id);


			$this->delete_cache($post);
		}
	}

	/**
	 * Clear cache when item is re-ordered
	 */
	public function action_simple_page_ordering_ordered_posts($post = false, $new_pos = false)
	{

		// check permissions again and make sure we have what we need
		if (!is_admin() || !current_user_can('edit_others_pages') || empty($post)) {
			return;
		}

		// real post?
		if (!$post = get_post($post)) {
			return;
		}

		// carousel?
		if (!isset($post->post_type) || $post->post_type !== 'pmc_featured') {
			return;
		}

		$this->delete_cache($post);
	}

	/**
	 * Output dropdowns for filtering post table
	 */
	public function action_restrict_manage_posts()
	{
		if (isset($_GET['post_type']) && 'pmc_featured' == $_GET['post_type'])
			$this->category_meta_box();
	}

	public function manage_posts_extra_tablenav()
	{

		if (isset($_GET['post_type']) && 'pmc_featured' == $_GET['post_type'] && is_admin()) {
		?>
			<div class="alignleft actions">
				<?php submit_button(esc_html__('Clear Cache', 'pmc-carousel'), 'button', 'clear_carousel_cache', false, array('id' => 'clear-carousel-cache-submit')); ?>
			</div>
		<?php
		}
	}

	/**
	 * Add category meta box to post type
	 *
	 * @uses register_taxonomy_for_object_type
	 * @return void
	 */
	public function action_init()
	{
		register_taxonomy_for_object_type('category', 'pmc_featured');

		// Register the carousel modules taxonomy
		register_taxonomy(self::modules_taxonomy_name, null, array(
			'labels' => array(
				'name'               => __('Carousel Modules', 'pmc-carousel'),
				'singular_name'      => __('Carousel Module', 'pmc-carousel'),
				'add_new'            => __('Add New', 'pmc-carousel'),
				'add_new_item'       => __('Add New Carousel Module', 'pmc-carousel'),
				'edit_item'          => __('Edit Carousel Module', 'pmc-carousel'),
				'new_item'           => __('New Carousel Module', 'pmc-carousel'),
				'view_item'          => __('View Carousel Module', 'pmc-carousel'),
				'search_items'       => __('Search Carousel Modules', 'pmc-carousel'),
				'not_found'          => __('No Carousel Modules found.', 'pmc-carousel'),
				'not_found_in_trash' => __('No Carousel Modules found in Trash.', 'pmc-carousel'),
				'all_items'          => __('Carousel Modules', 'pmc-carousel')
			),
			'public'            => true,
			'show_ui'           => true,
			'show_in_nav_menus' => false,
			'show_in_rest'      => true,
			'show_tagcloud'     => false,
			'hierarchical'      => false,
			'rewrite'           => false,
		));

		add_action('wp_footer', array($this, 'action_wp_footer'));
	}

	/**
	 * Return linked articles title on post list page for pmc_featured post_type if title is not overridden.
	 *
	 * @param     $title
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_fallback_title($title, $post_id = 0): string
	{

		if (!is_admin()) {
			return $title;
		}

		if (!empty($title)) {
			return $title;
		}

		if (empty($post_id)) {
			return $title;
		}

		$current_screen = get_current_screen();

		if (!empty($current_screen->id) && 'edit-pmc_featured' === $current_screen->id) {

			$current_meta      = get_post_meta(
				$post_id,
				'_pmc_master_article_id',
				true
			); // this contains title from linked article
			$current_meta_data = json_decode($current_meta);
			if (!empty($current_meta_data->title)) {
				return $current_meta_data->title;
			}
		}

		return $title;
	}

	/**
	 * Enqueue js script to restrict selecting getty image in the UI.
	 */
	public function enqueue_admin_assets()
	{
		wp_enqueue_style('pmc-carousel-admin-css', plugins_url('css/admin.css', __FILE__), [], '1.0.0');
	}

	/**
	 * Output things (CSS) into the admin head, specifically for this post type
	 */
	public function action_print_footer_scripts()
	{
		?>
		<script type="text/javascript">
			jQuery('#cat').hide();
		</script>
	<?php
	}

	public function action_pmc_linkcontent_before_insert_field()
	{
		if ('pmc_featured' !== get_post_type()) {
			return;
		}

		$post_id          = get_the_ID();
		$override_enabled = get_post_meta($post_id, '_pmc_carousel_override', true);
		$override_url     = get_post_meta($post_id, '_pmc_carousel_override_url', true);
		$override_price   = get_post_meta($post_id, '_pmc_carousel_override_price', true);
		$override_tag     = get_post_meta($post_id, '_pmc_carousel_override_tag', true);
		$override_coupon  = get_post_meta($post_id, '_pmc_carousel_override_coupon', true);

		if (
			!empty($override_url) &&
			class_exists('\PMC\Store_Products\Product') &&
			class_exists('\PMC\Store_Products\Shortcode')
		) {
			$product = apply_filters('pmc_carousel_product', []);

			if (empty($product)) {
				$asin = \PMC\Store_Products\Shortcode::get_asin_from_amazon_url($override_url);
				if (!empty($asin)) {
					$product = (array) \PMC\Store_Products\Product::create_from_asin($asin);
				}
			}

			if (!empty($product)) {
				$amazon_api_data = [
					'title'            => !empty($product['title']) ? $product['title'] : '',
					'price'            => !empty($product['price']) ? $product['price'] : '',
					'original_price'   => !empty($product['original_price']) ? $product['original_price'] : '',
					'discount_amount'  => !empty($product['discount_amount']) ? $product['discount_amount'] : '',
					'discount_percent' => !empty($product['discount_percent']) ? $product['discount_percent'] : '',
					'image_url'        => !empty($product['image_url']) ? $product['image_url'] : '',
				];
			}
		}

		$price_override_details = __('Example: $99.99', 'pmc-carousel');

		if (!empty($amazon_api_data['price'])) {
			$price_override_details = __('Default value if empty: ', 'pmc-carousel') . $amazon_api_data['price'];
		}

		$tag_override_details = __('Example: xyzdealswidget-20', 'pmc-carousel');

		if (class_exists('\PMC\Apple_News\Content_Filter')) {
			$tag_override_details_value = \PMC\Apple_News\Content_Filter::get_instance()->get_amazon_ecommerce_affiliate_code_override();

			if (empty($tag_override_details_value)) {
				$tag_override_details_value = \PMC\Apple_News\Content_Filter::get_instance()->get_default_amazon_ecommerce_affiliate_code();
			}

			$tag_override_details = __('Default value if empty: ', 'pmc-carousel') . $tag_override_details_value;
		}
	?>
		<div class="carousel-overrides" id="pmc-carousel-override-container">
			<input type="checkbox" name="pmc-carousel-override" value="1" id="pmc-carousel-override" <?php checked($override_enabled); ?> />
			<label for="pmc-carousel-override"><?php echo sprintf(esc_html__('Use Manual URL - %sIf checked, the %sURL%s field below will be used to determine what post to link to.%s', 'pmc-carousel'), '<em>', '<strong>', '</strong>', '</em>'); ?></label>
			<br>
			<div class="carousel-override-fields">
				<label for="pmc-carousel-override-url"><?php esc_html_e('URL', 'pmc-carousel'); ?></label>
				<input type="text" name="pmc-carousel-override-url" id="pmc-carousel-override-url" value="<?php echo esc_url($override_url); ?>" />
			</div>
			<div class="carousel-override-fields-apple-news carousel-override-fields-apple-news--price">
				<label for="pmc-carousel-override-price"><?php esc_html_e('Price Override.', 'pmc-carousel'); ?> <?php echo esc_html($price_override_details); ?></label>
				<input type="text" name="pmc-carousel-override-price" id="pmc-carousel-override-price" value="<?php echo esc_attr($override_price); ?>" />
			</div>
			<div class="carousel-override-fields-apple-news carousel-override-fields-apple-news--tag">
				<label for="pmc-carousel-override-tag"><?php esc_html_e('Tag Override.', 'pmc-carousel'); ?> <?php echo esc_html($tag_override_details); ?></label>
				<input type="text" name="pmc-carousel-override-tag" id="pmc-carousel-override-tag" value="<?php echo esc_attr($override_tag); ?>" />
			</div>
			<div class="carousel-override-fields-apple-news carousel-override-fields-apple-news--coupon">
				<label for="pmc-carousel-override-coupon"><?php esc_html_e('Coupon Override.', 'pmc-carousel'); ?></label>
				<input type="text" name="pmc-carousel-override-coupon" id="pmc-carousel-override-coupon" value="<?php echo esc_attr($override_coupon); ?>" />
			</div>
			<div class="carousel-override-fields-apple-news carousel-override-fields-apple-news--preview">
				<?php
				esc_html_e('Amazon API Data (use overrides if data is not as expected): ', 'pmc-carousel');
				if (empty($amazon_api_data)) {
					esc_html_e('N/A (save with new url to try again)', 'pmc-carousel');
				} elseif (is_array($amazon_api_data)) {
					foreach ($amazon_api_data as $key => $data) {
						echo '<br>' . esc_html($key . ': ' . $data);
					}
				}
				?>
			</div>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				var $overrideCheckbox = jQuery('#pmc-carousel-override');
				var $urlInput = jQuery('#pmc-carousel-override-url');
				var currentUrl = $urlInput.val();
				var $searchContainer = jQuery('.pmclinkcontent-search-wrapper');

				var $overrides = jQuery('.carousel-override-fields-apple-news'),
					$tagOverride = jQuery('.carousel-override-fields-apple-news--tag'),
					$couponOverride = jQuery('.carousel-override-fields-apple-news--coupon'),
					$carouselTermSelect = jQuery('#carousel-cat-term'),
					carouselTerm,
					useManualUrlChecked;

				function carouselMaybeDispalayOverrides() {
					carouselTerm = $carouselTermSelect.find(':selected').text(),
						useManualUrlChecked = $overrideCheckbox.is(':checked');
					if (
						useManualUrlChecked &&
						(
							'Apple News Promo Module' === carouselTerm ||
							'Today\'s Top Deal' === carouselTerm ||
							'Today\'s Top Deal (AMP)' === carouselTerm
						)
					) {
						$overrides.show();

						if ('Apple News Promo Module' === carouselTerm) {
							$couponOverride.hide();
						} else if (
							'Today\'s Top Deal' === carouselTerm ||
							'Today\'s Top Deal (AMP)' === carouselTerm
						) {
							$tagOverride.hide();
						}
					} else {
						$overrides.hide();
					}
				}

				carouselMaybeDispalayOverrides();

				$carouselTermSelect.on('change', function() {
					carouselMaybeDispalayOverrides();
				});

				carouselOverrideCheckIfDisplaySearchWrapper();

				$overrideCheckbox.on('change', function(e) {
					if ($overrideCheckbox.is(':checked')) {
						carouselOverrideMaybeRepopulateOriginalUrl();
					} else {
						carouselOverrideClearOldPost();
						carouselOverrideCleanManualUrl();
					}
					carouselOverrideCheckIfDisplaySearchWrapper();
					carouselMaybeDispalayOverrides();
				});

				function carouselOverrideCheckIfDisplaySearchWrapper() {
					if ($overrideCheckbox.is(':checked')) {
						carouselOverrideHideSearchSection();
					} else {
						carouselOverrideShowSearchSection();
					}
				}

				function carouselOverrideClearOldPost() {
					jQuery('.pmclinkcontent-remove').click();
				}

				function carouselOverrideCleanManualUrl() {
					//save old url just in case they accidentally click this, we can easily put it back
					currentUrl = $urlInput.val();
					$urlInput.val('');
				};

				function carouselOverrideMaybeRepopulateOriginalUrl() {
					if ($urlInput.val() == '') {
						carouselOverrideRepopulateOriginalUrl();
					}
				}

				function carouselOverrideRepopulateOriginalUrl() {
					$urlInput.val(currentUrl);
				}

				function carouselOverrideHideSearchSection() {
					$searchContainer.hide();
				}

				function carouselOverrideShowSearchSection() {
					$searchContainer.show();
				}
			});
		</script>
		<hr>
<?php

	}
}

global $pmc_carousel;
$pmc_carousel = PMC_Carousel::get_instance();

// Initialize WP Rest API endpoint.
PMC\Global_Functions\WP_REST_API\Manager::get_instance()->register_endpoint(
	PMC\Carousel\Endpoint::class
);

/**
 * Render carousel
 * @param string $taxonomy
 * @param string $term
 * $param int $num_articles
 * @return array
 */
function pmc_render_carousel($taxonomy, $term, $num_articles = 5, $size = '', $opts = false)
{
	return PMC_Carousel::get_instance()->render($taxonomy, $term, $num_articles, $size, $opts);
}


// EOF
