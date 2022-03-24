<?php

namespace PMC\Gallery;

/**
 * Lists functionality for PMC properties. Largely migrated from the old pmc-lists plugin originally from Rolling Stone.
 *
 * TODO: Refactor appropriate parts to work with multiple list membership
 *
 * @since 2019-09-09
 */

use CheezCapDropdownOption;
use CheezCapTextOption;
use PMC\Global_Functions\Traits\Singleton;
use PMC_Cheezcap;

class Lists_Settings
{

	use Singleton;

	/**
	 * Post type for the list.
	 */
	const LIST_POST_TYPE = 'pmc_list';

	/**
	 * Post type of the list items.
	 */
	const LIST_ITEM_POST_TYPE = 'pmc_list_item';

	/**
	 * Used for relating lists to list items. Querying postmeta can be slow. This will also allow
	 * us to determine which list items are used in which lists (which is currently a problem with galleries).
	 * Will also allow for list items to be used in multiple lists. RS does not need this currently, but others might.
	 */
	const LIST_RELATION_TAXONOMY = 'pmc_list_relation';


	/**
	 * The meta key for the list numbering direction.
	 */
	const NUMBERING_OPT_KEY = 'pmc_list_numbering';

	/**
	 * The meta key for the list item template type.
	 */
	const TEMPLATE_OPT_KEY = 'pmc_list_template';

	const ORDERED_LIST_ITEMS = 'pmc_list_order';

	const PMC_LISTS_URL = PMC_GALLERY_PLUGIN_URL;

	/**
	 * List configuration.
	 *
	 * @var array
	 */
	protected static $_list_config = array();

	/**
	 * List items per page
	 */
	protected static $_list_items_per_page = null;

	/**
	 * Lists constructor.
	 */
	protected function __construct()
	{
		add_filter('pmc_cheezcap_groups', array($this, 'filter_add_list_cheezcap'));
		add_filter('pmc_adm_locations', array($this, 'add_list_ad_locations'));
		add_action('init', array($this, 'init'));
		add_filter('redirect_canonical', array($this, 'prevent_canonical_redirect'), 100);

		// Add PMC Featured
		add_filter('pmc_feature_video_post_types', [$this, 'enable_videos_for_list_and_items']);
	}

	/**
	 * Cheezcap to enable the new list plugin
	 *
	 * @param array $cheezcap_groups List of cheezcap options.
	 *
	 * @return array $cheezcap_groups
	 */
	public function filter_add_list_cheezcap(array $cheezcap_groups = array())
	{
		if (empty($cheezcap_groups) || !is_array($cheezcap_groups)) {
			$cheezcap_groups = array();
		}

		// Needed for compatibility with BGR_CheezCap
		// @codeCoverageIgnoreStart
		if (class_exists('BGR_CheezCapGroup')) {
			$cheezcap_group_class = 'BGR_CheezCapGroup';
		} else {
			$cheezcap_group_class = 'CheezCapGroup';
		}
		// @codeCoverageIgnoreEnd

		$list_cheez_opts = array(
			// Enable/disable
			new CheezCapDropdownOption(
				esc_html__('Enable v4 Lists', 'pmc-gallery-v4'),
				esc_html__('This option will enable the new list plugin', 'pmc-gallery-v4'),
				'pmc_gallery_list_enabled',
				array('no', 'yes'),
				0,
				array('No', 'Yes')
			),

			new CheezCapDropdownOption(
				esc_html__('List Ad Frequency', 'pmc-gallery-v4'),
				esc_html__('This number will determine the number of list items between ads. 1 is default', 'pmc-gallery-v4'),
				'pmc_list_ad_frequency',
				array(1, 2, 3, 4, 5),
				0, // First option => Disabled
				array(1, 2, 3, 4, 5)
			),
			new CheezCapTextOption(
				esc_html__("Don't show ads on these lists", 'pmc-gallery-v4'),
				esc_html__('Comma delimited post IDs, e.g.: 123,456,789', 'pmc-gallery-v4'),
				'pmc_list_no_ads',
				null
			),
		);

		$cheezcap_groups[] = new $cheezcap_group_class('New List Plugin', 'pmc-gallery-list', $list_cheez_opts);

		return $cheezcap_groups;
	}

	/**
	 * Are the new lists enabled? Used so we can develop and test while RS has the old list plugin working.
	 *
	 * @return boolean
	 */
	public function is_gallery_list_enabled()
	{
		$list_opt = PMC_Cheezcap::get_instance()->get_option('pmc_gallery_list_enabled');

		return 'yes' === $list_opt;
	}

	/**
	 * Check cheezcap before activating everything
	 */
	public function init()
	{
		if (!$this->is_gallery_list_enabled()) {
			return;
		}
		$this->register_list_post_types();
		$this->_add_rewrite_rules();

		add_filter('manage_pmc_list_posts_columns', [$this, 'list_add_custom_column']);
		add_action('manage_pmc_list_posts_custom_column', [$this, 'list_manage_custom_column'], 10, 2);
		add_filter('manage_pmc_list_item_posts_columns', [$this, 'list_item_add_custom_column'], 10, 2);
		add_action('manage_pmc_list_item_posts_custom_column', [$this, 'list_item_manage_custom_column'], 10, 2);
		add_action('restrict_manage_posts', [$this, 'action_restrict_manage_posts']);
		add_filter('views_edit-' . self::LIST_ITEM_POST_TYPE, [$this, 'show_current_list']);
		add_filter('pmc_sitemaps_post_type_whitelist', [$this, 'whitelist_post_type_for_sitemaps']);
		add_action('add_meta_boxes', array($this, 'list_manager_meta_box'));
		// Run this late so that themes have a change to register their own default	sizes
		add_action('init', array($this, 'register_list_image_sizes'), 99);

		// Create view templates used by the admin area
		add_action('print_media_templates', array($this, 'print_item_template'));

		// Add addition list item metabox
		add_action('fm_post_' . self::LIST_ITEM_POST_TYPE, [$this, 'add_additional_list_item_meta_boxes']);
	}

	/**
	 * Register the list and list item post types, and add URL query var.
	 */
	public function register_list_post_types()
	{
		register_post_type(
			self::LIST_POST_TYPE,
			[
				'labels'               => [
					'name'               => wp_strip_all_tags(__('Lists', 'pmc-gallery-v4')),
					'singular_name'      => wp_strip_all_tags(__('List', 'pmc-gallery-v4')),
					'add_new'            => wp_strip_all_tags(_x('Add New', 'List', 'pmc-gallery-v4')),
					'add_new_item'       => wp_strip_all_tags(__('Add New List', 'pmc-gallery-v4')),
					'edit_item'          => wp_strip_all_tags(__('Edit List', 'pmc-gallery-v4')),
					'new_item'           => wp_strip_all_tags(__('New List', 'pmc-gallery-v4')),
					'view_item'          => wp_strip_all_tags(__('View List', 'pmc-gallery-v4')),
					'search_items'       => wp_strip_all_tags(__('Search Lists', 'pmc-gallery-v4')),
					'not_found'          => wp_strip_all_tags(__('No Lists found.', 'pmc-gallery-v4')),
					'not_found_in_trash' => wp_strip_all_tags(__('No Lists found in Trash.', 'pmc-gallery-v4')),
					'all_items'          => wp_strip_all_tags(__('Lists', 'pmc-gallery-v4')),
				],
				'public'               => true,
				'show_in_rest'         => true,
				'supports'             => ['title', 'author', 'comments', 'thumbnail', 'excerpt', 'editor'],
				'has_archive'          => 'lists',
				'rewrite'              => [
					'slug' => apply_filters('pmc_gallery_lists_standalone_slug', 'lists'),
				],
				'register_meta_box_cb' => [$this, 'list_meta_boxes'],
				'taxonomies'           => ['category', 'post_tag'],
				'menu_icon'            => 'dashicons-list-view',
			]
		);

		register_post_type(
			self::LIST_ITEM_POST_TYPE,
			[
				'labels'               => [
					'name'               => wp_strip_all_tags(__('List Item', 'pmc-gallery-v4')),
					'singular_name'      => wp_strip_all_tags(__('List Item', 'pmc-gallery-v4')),
					'add_new'            => wp_strip_all_tags(_x('Add New', 'List Item', 'pmc-gallery-v4')),
					'add_new_item'       => wp_strip_all_tags(__('Add New List Item', 'pmc-gallery-v4')),
					'edit_item'          => wp_strip_all_tags(__('Edit List Item', 'pmc-gallery-v4')),
					'new_item'           => wp_strip_all_tags(__('New List Item', 'pmc-gallery-v4')),
					'view_item'          => wp_strip_all_tags(__('View List Item', 'pmc-gallery-v4')),
					'search_items'       => wp_strip_all_tags(__('Search List Items', 'pmc-gallery-v4')),
					'not_found'          => wp_strip_all_tags(__('No Lists found.', 'pmc-gallery-v4')),
					'not_found_in_trash' => wp_strip_all_tags(__('No Lists found in Trash.', 'pmc-gallery-v4')),
					'all_items'          => wp_strip_all_tags(__('List Items', 'pmc-gallery-v4')),
				],
				'public'               => true,
				'supports'             => ['title', 'author', 'comments', 'editor', 'thumbnail', 'excerpt'],
				'register_meta_box_cb' => [$this, 'list_item_meta_boxes'],
				'taxonomies'           => [self::LIST_RELATION_TAXONOMY],
				'has_archive'          => false,
				'show_ui'              => true,
				'show_in_menu'         => 'edit.php?post_type=' . self::LIST_POST_TYPE,
				'hierarchical'         => false,
				'rewrite'              => [
					'slug' => 'list_item',
				],
			]
		);

		register_taxonomy(
			self::LIST_RELATION_TAXONOMY,
			self::LIST_ITEM_POST_TYPE,
			[
				'labels'            => [
					'name' => __('List Relation', 'pmc-gallery-v4'),
				],
				'public'            => false,
				'rewrite'           => false,
				'show_ui'           => false,
				'show_in_nav_menus' => false,
				'show_admin_column' => false,
			]
		);
	}

	/**
	 * Defines a rewrite rule for the single image page from list.
	 */
	protected function _add_rewrite_rules()
	{
		if (apply_filters('pmc_gallery_v4_lists_has_custom_rewrite_rule', false)) {
			return;
		}

		$slug = preg_quote(sanitize_title_with_dashes(apply_filters('pmc_gallery_lists_standalone_slug', 'lists')));

		$rewrite_rules = [
			$slug . '/([^/]+)/([^/]+)/?$'            => 'index.php?pmc_list=$matches[1]&pmc_list_item_slug=$matches[2]',
			$slug . '/([^/]+)/([^/]+)/feed/(feed|rdf|rss|rss2|atom)?/?$' => 'index.php?pmc_list=$matches[1]&pmc_list_item_slug=$matches[2]&feed=$matches[3]',
			$slug . '/([^/]+)/([^/]+)/(feed|rdf|rss|rss2|atom)/?$' => 'index.php?pmc_list=$matches[1]&pmc_list_item_slug=$matches[2]&feed=$matches[3]',
			$slug . '/([^/]+)/([^/]+)/embed/?$'      => 'index.php?pmc_list=$matches[1]&pmc_list_item_slug=$matches[2]&embed=true',
			$slug . '/([^/]+)/([^/]+)/trackback/?$'  => 'index.php?pmc_list=$matches[1]&pmc_list_item_slug=$matches[2]&tb=true',
			$slug . '/([^/]+)/([^/]+)/fbid/(.*)?/?$' => 'index.php?pmc_list=$matches[1]&pmc_list_item_slug=$matches[2]&fbid=$matches[3]',
			$slug . '/([^/]+)/([^/]+)/amp/(.*)?/?$'  => 'index.php?pmc_list=$matches[1]&pmc_list_item_slug=$matches[2]&amp=$matches[3]',
		];

		foreach ($rewrite_rules as $regex => $query) {
			add_rewrite_rule($regex, $query, 'top');
		}

		add_rewrite_tag('%pmc_list_item_slug%', '([^/])+');
	}

	/**
	 * Metabox for List post type.
	 *
	 */
	public function list_meta_boxes()
	{
		// List options meta box.
		add_meta_box(
			'pmc-list-options',
			esc_html__('List Options', 'pmc-gallery-v4'),
			[$this, 'list_options_meta_box'],
			self::LIST_POST_TYPE,
			'side',
			'high'
		);
	}

	/**
	 * Metabox for List Items post type.
	 *
	 */
	public function list_item_meta_boxes()
	{
		// Meta box to select a list.
		add_meta_box(
			'pmc-list-select',
			esc_html__('Choose List', 'pmc-gallery-v4'),
			[$this, 'list_select_meta_box'],
			self::LIST_ITEM_POST_TYPE,
			'side',
			'high'
		);
	}

	/**
	 * Metabox for List Items post type (additional fields).
	 *
	 * @return object|null \Fieldmanager_Context_Post
	 */
	public function add_additional_list_item_meta_boxes(): ?object
	{
		$additional_fields = [];

		if (true === apply_filters('pmc_gallery_v4_lists_enable_list_item_subtitle', false)) {
			array_push(
				$additional_fields,
				new \Fieldmanager_TextArea(
					[
						'name'     => sprintf('%s_subtitle', self::LIST_ITEM_POST_TYPE),
						'label'    => __('Subtitle', 'pmc-gallery'),
						'sanitize' => 'wp_kses_post',
					]
				)
			);
		}

		if (true === apply_filters('pmc_gallery_v4_lists_enable_list_item_apple_music_player', false)) {
			array_push(
				$additional_fields,
				new \Fieldmanager_Textfield(
					[
						'name'  => sprintf('%s_apple_song_id', self::LIST_ITEM_POST_TYPE),
						'label' => __('Apple Song ID', 'pmc-gallery-v4'),
					]
				)
			);
		}

		if (0 < count($additional_fields)) {

			$fm_additional = new \Fieldmanager_Group(
				array(
					'name'           => 'pmc-list-item-additional-data',
					'serialize_data' => false,
					'add_to_prefix'  => false,
					'children'       => $additional_fields,
				)
			);

			return $fm_additional->add_meta_box(
				__('Additional List Item Data', 'pmc-gallery-v4'),
				self::LIST_ITEM_POST_TYPE,
				'normal',
				'high'
			);
		}
		return null;
	}

	/**
	 * List select meta box render.
	 *
	 * @param \WP_Post $post The WP Post object.
	 *
	 * @throws \Exception
	 */
	public function list_select_meta_box($post)
	{
		$list_id    = '';
		$list_terms = get_the_terms($post->ID, self::LIST_RELATION_TAXONOMY);

		if (is_array($list_terms) && is_a(reset($list_terms), 'WP_Term') && !empty(reset($list_terms)->name)) {
			$list_id = reset($list_terms)->name;
		}
		$recent_query = new \WP_Query(
			[
				'numberposts' => 10,
				'post_status' => 'any',
				'post_type'   => Lists_Settings::LIST_POST_TYPE,
				'orderby'     => 'post_modified',
			]
		);
		$recent_lists = $recent_query->have_posts() ? $recent_query->posts : [];
		\PMC::render_template(PMC_GALLERY_PLUGIN_DIR . '/template-parts/admin/list-select.php', compact('list_id', 'recent_lists'), true);
	}

	/**
	 * List options meta box render.
	 *
	 * @param \WP_Post $post The WP Post object.
	 *
	 * @throws \Exception
	 */
	public function list_options_meta_box($post)
	{

		\PMC::render_template(PMC_GALLERY_PLUGIN_DIR . '/template-parts/admin/list-options.php', compact('post'), true);
	}

	/**
	 * Whitelist post type for sitemap.
	 *
	 * @param array $post_types List of post type for site map.
	 *
	 * @return array List of post type for site map.
	 */
	public function whitelist_post_type_for_sitemaps($post_types)
	{

		$post_types = (!empty($post_types) && is_array($post_types)) ? $post_types : [];

		if (!in_array(self::LIST_POST_TYPE, (array) $post_types, true)) {
			$post_types[] = self::LIST_POST_TYPE;
		}

		return $post_types;
	}

	/**
	 * Add custom list item column in list.
	 *
	 * @param array $columns list of default columns.
	 *
	 * @return array $columns.
	 */
	public function list_item_add_custom_column($columns)
	{

		return array_merge($columns, array('list' => __('List', 'pmc-gallery-v4')));
	}

	/**
	 * Manage custom list item column for list post type.
	 *
	 * @param string  $column_name  current column name.
	 * @param integer $list_item_id current list id.
	 */
	public function list_item_manage_custom_column($column_name, $list_item_id)
	{
		if (!empty($column_name) && !empty($list_item_id) && 'list' === $column_name) {
			$list_relation = get_the_terms($list_item_id, self::LIST_RELATION_TAXONOMY);
			if (!empty($list_relation)) {
				$list_relations = [];
				foreach ($list_relation as $relation) {
					$list_relations[] = sprintf('<a href="%s" target="_blank">%s</a>', get_edit_post_link($relation->slug), get_the_title($relation->slug));
				}
				$list_relations = implode(', ', $list_relations);
				echo wp_kses_post($list_relations);
			}
		}
	}


	/**
	 * Add custom list item column in list.
	 *
	 * @param array $columns list of default columns.
	 *
	 * @return array $columns.
	 */
	public function list_add_custom_column($columns)
	{

		return array_merge($columns, array('list-items' => __('List Items', 'pmc-gallery-v4')));
	}

	/**
	 * Manage custom list item column for list post type.
	 *
	 * @param string  $column_name current column name.
	 * @param integer $list_id     current list id.
	 */
	public function list_manage_custom_column($column_name, $list_id)
	{
		if (!empty($column_name) && !empty($list_id) && 'list-items' === $column_name) {
			$list_relation = get_term_by('slug', $list_id, self::LIST_RELATION_TAXONOMY);
			if (!empty($list_relation->count)) {
				echo sprintf('<a href="%s">%s</a>', esc_url(add_query_arg(self::LIST_RELATION_TAXONOMY, $list_id, admin_url('/edit.php?post_type=' . self::LIST_ITEM_POST_TYPE))), esc_html($list_relation->count));
			}
		}
	}

	/**
	 * Adds the meta box container
	 */
	public function list_manager_meta_box()
	{
		add_meta_box(
			self::LIST_ITEM_POST_TYPE . '_meta_box',
			esc_html__('List Items', 'pmc-gallery-v4'),
			array($this, 'render_meta_box_content'),
			self::LIST_POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Adds the ability to filter list items based on a list. Uses an
	 * autocomplete text input.
	 *
	 * @return void|false False if the list inputs template is not rendered.
	 * @throws \Exception
	 */
	public function action_restrict_manage_posts(): void
	{

		if (self::LIST_ITEM_POST_TYPE !== get_post_type()) {
			return;
		}

		$list_id        = get_query_var(self::LIST_RELATION_TAXONOMY);
		$list_title     = get_the_title($list_id);
		$list_permalink = get_the_permalink($list_id);

		\PMC::render_template(
			PMC_GALLERY_PLUGIN_DIR . '/template-parts/admin/list-inputs.php',
			[
				'list_id'        => $list_id,
				'taxonomy'       => self::LIST_RELATION_TAXONOMY,
				'list_title'     => $list_title,
				'list_permalink' => $list_permalink,
			],
			true
		);
	}

	/**
	 * Displays the current list and an edit link when viewing list items that
	 * are filtered based on a list.
	 *
	 * @return void|false Returns false if the list view template is not rendered.
	 */
	public function show_current_list($views)
	{
		if (self::LIST_ITEM_POST_TYPE !== get_post_type()) {
			return $views;
		}

		$list_id = get_query_var(self::LIST_RELATION_TAXONOMY);

		if (empty($list_id)) {
			return $views;
		}
		$list_title             = get_the_title($list_id);
		$list_permalink         = get_the_permalink($list_id);
		$edit_link              = get_edit_post_link($list_id);
		$views['currently_f']   = sprintf('Currently Viewing: <a href="%s"><strong>%s</strong></a>', $list_permalink, $list_title);
		$views['currentedit_f'] = sprintf('<a href="%s"><strong>Edit Current List</strong></a>', $edit_link);

		return $views;
	}

	/**
	 * Render Meta Box content
	 */
	public function render_meta_box_content()
	{
		echo '<ul id="pmc-list-items"></ul>'; // Give the list manager a home
	}

	/**
	 * Emit template for media playlist item
	 *
	 * @action print_media_templates
	 */
	function print_item_template()
	{
?>
		<script type="text/html" id="tmpl-list-item-template">
			<div class="pmc-list-item-table">

				<div class="item-fields">
					<a class="close media-modal-icon remove-list-item" href="javascript:" title="Remove"></a>
					<input type="hidden" class="pmcListItem-id" name="pmclistitems[]" value="{{ data.ID }}" />
					<input type="text" class="sort-order" value="{{ data.index + 1 }}" />
					<div class="image-field" id="image-field-{{ data.index }}">
						{{{ data.imageSrc ? data.imageSrc : data.type == 'image' ? data.src : '<div class="item-image-placeholder"><span>No</span><span>Image</span><span>Set</span></div>' }}}
					</div>
					<div class="title-edit-container">
						<p class="pmcListItem-namefield">
							{{{ data.title }}}
						</p>
						<p class="pmcListItem-status">
							<span class="{{{ data.status }}}">{{{ data.status }}}</span>
						</p>
						<a class="pmcListItem-edit" role="button" href="{{{ data.editLink }}}" target="_blank">
							<span aria-hidden="true">Edit</span>
						</a>
					</div>
				</div>
			</div>
		</script>
<?php
	}

	/**
	 * @return string template used for the list
	 */
	public static function get_current_list_template()
	{
		$template = get_post_meta(get_the_ID(), self::TEMPLATE_OPT_KEY, true);
		if (!$template) {
			$template = 'item-featured-image';
		}

		return $template;
	}

	/**
	 * @return string ordering scheme used for the list
	 */
	public static function get_current_list_order()
	{
		return get_post_meta(get_the_ID(), self::NUMBERING_OPT_KEY, true);
	}

	/**
	 * Check if the current post is on a "no ads" blocklist
	 *
	 * @return boolean
	 * @see PMC_Ads::no_ads_on_this_post()
	 *
	 */
	public function no_ads_on_this_post()
	{
		$no_ads_string = PMC_Cheezcap::get_instance()->get_option('pmc_list_no_ads');
		$no_ads_array  = explode(',', $no_ads_string);
		$no_ads_array  = array_map('intval', $no_ads_array);

		return in_array(get_queried_object_id(), (array) $no_ads_array, true);
	}

	/**
	 * Adding ad locations required for lists
	 *
	 * @param array $locations Ad locations.
	 *
	 * @return array Ad locations.
	 */
	public function add_list_ad_locations($locations = [])
	{
		$locations['in-list-1'] = [
			'title'     => 'Gallery v4: In List Top Ad',
			'providers' => ['boomerang', 'google-publisher'],
		];

		$locations['in-list-x'] = [
			'title'     => 'Gallery v4: In List X',
			'providers' => ['boomerang', 'google-publisher'],
		];

		/**
		 * Deprecated locations below
		 */

		// pmc-rollingstone-2018
		$locations['lists-top-river-ad'] = [
			'title'     => 'Lists Top River Ad',
			'providers' => ['boomerang', 'google-publisher'],
		];
		$locations['lists-river-ad']     = [
			'title'     => 'Lists River Ad',
			'providers' => ['boomerang', 'google-publisher'],
		];

		return $locations;
	}

	/**
	 * Register image sizes for galleries.
	 */
	public function register_list_image_sizes()
	{

		/**
		 * Image size configuration.
		 *
		 * 4:3 aspect ratio is being used for landscape and 2:3 for all portrait images.
		 */
		$sizes = array(
			'pmc-list-thumbnail-portrait' => array(
				'width'  => 150,
				'height' => 225,
			),
			'pmc-list-s'                  => array(
				'width'  => 320,
				'height' => 240,
			),
			'pmc-list-s-portrait'         => array(
				'width'  => 320,
				'height' => 480,
			),
			'pmc-list-m'                  => array(
				'width'  => 640,
				'height' => 480,
			),
			'pmc-list-m-portrait'         => array(
				'width'  => 640,
				'height' => 960,
			),
			'pmc-list-l'                  => array(
				'width'  => 800,
				'height' => 600,
			),
			'pmc-list-l-portrait'         => array(
				'width'  => 800,
				'height' => 1200,
			),
			'pmc-list-xl'                 => array(
				'width'  => 1024,
				'height' => 768,
			),
			'pmc-list-xl-portrait'        => array(
				'width'  => 1024,
				'height' => 1536,
			),
			'pmc-list-xxl'                => array(
				'width'  => 1280,
				'height' => 1024,
			),
			'pmc-list-xxl-portrait'       => array(
				'width'  => 1280,
				'height' => 1920,
			),
		);

		$registered_sizes = \PMC\Image\get_intermediate_image_sizes();

		foreach ($sizes as $name => $size) {
			if (!in_array($name, (array) $registered_sizes, true)) {
				add_image_size($name, $size['width'], $size['height']);
			}
		}
	}

	/**
	 * Get maximum list per page config
	 */
	public function get_list_items_per_page()
	{
		if (!empty(self::$_list_items_per_page)) {
			return self::$_list_items_per_page;
		}

		self::$_list_items_per_page = apply_filters('pmc_gallery_list_items_per_page', 100);

		return self::$_list_items_per_page;
	}

	/**
	 * Get list configuration for display.
	 *
	 * @return array
	 */
	public function get_list_config()
	{
		if (!empty(self::$_list_config)) {
			return self::$_list_config;
		}
		$list_id = get_the_ID();

		// Options.

		$options       = Settings::get_instance()->get_common_options();
		$ad_frequency  = PMC_Cheezcap::get_instance()->get_option('pmc_list_ad_frequency');
		$list_ordering = self::get_current_list_order();
		$list_template = self::get_current_list_template();

		$current_page_items             = Lists::fetch_list($list_id, $list_template);

		$current_page_items_first_index = array_column((array) $current_page_items, 'position')[0];

		$all_list_item_ids    = array_column(Lists::$all_list_items_by_list_id[$list_id], 'ID');
		$all_list_items_count = count($all_list_item_ids);

		$previous_page_slug = Lists::$all_list_items_by_list_id[$list_id][$current_page_items_first_index - self::get_list_items_per_page()]->slug ?? null;
		$next_page_slug     = Lists::$all_list_items_by_list_id[$list_id][$current_page_items_first_index + self::get_list_items_per_page()]->slug ?? null;

		self::$_list_config = apply_filters(
			'pmc_list_v4_config',
			[
				'gallery'                     => $current_page_items,
				'galleryCount'                => $all_list_items_count,
				'galleryID'                   => $list_id,
				'previousPageLink'            => $previous_page_slug ? trailingslashit(get_permalink()) . $previous_page_slug : '',
				'nextPageLink'                => $next_page_slug ? trailingslashit(get_permalink()) . $next_page_slug : '',
				'template'                    => $list_template,
				'ordering'                    => $list_ordering,
				'galleryTitle'                => get_the_title($list_id),
				'isList'                      => true,
				'logo'                        => [],
				'i10n'                        => [
					'backToArticle'      => esc_html__('Back to Article', 'pmc-gallery-v4'),
					'backToAllGalleries' => esc_html__('Back to All Galleries', 'pmc-gallery-v4'),
					'backToReview'       => esc_html__('Back to Review', 'pmc-gallery-v4'),
					'backToAllReviews'   => esc_html__('Back to All Reviews', 'pmc-gallery-v4'),
					'thumbnail'          => esc_html__('Thumbnails', 'pmc-gallery-v4'),
					'nextSlide'          => esc_html__('Next Slide', 'pmc-gallery-v4'),
					'prevSlide'          => esc_html__('Previous Slide', 'pmc-gallery-v4'),
					'skipAd'             => esc_html__('Skip Ad', 'pmc-gallery-v4'),
					'skipIn'             => esc_html__('Skip In', 'pmc-gallery-v4'),
					'of'                 => esc_html__('of', 'pmc-gallery-v4'),
					'missingSomething'   => __('You\'re missing something!', 'pmc-gallery-v4'),
					'subscribeNow'       => esc_html__('Subscribe Now', 'pmc-gallery-v4'),
					'next'               => esc_html__('Next', 'pmc-gallery-v4'),
					'nextGallery'        => esc_html__('Next Gallery', 'pmc-gallery-v4'),
					'closeThisMessage'   => esc_html__('Close this message', 'pmc-gallery-v4'),
					'closeModal'         => esc_html__('Close Modal', 'pmc-gallery-v4'),
					'closeGallery'       => esc_html__('Close Gallery', 'pmc-gallery-v4'),
					'startSlideShow'     => esc_html__('Start Slideshow', 'pmc-gallery-v4'),
					'lightBox'           => esc_html__('Lightbox', 'pmc-gallery-v4'),
					'scrollUp'           => esc_html__('Scroll Up', 'pmc-gallery-v4'),
					'scrollDown'         => esc_html__('Scroll Down', 'pmc-gallery-v4'),
					'look'               => esc_html__('Look', 'pmc-gallery-v4'),
					'readMore'           => esc_html__('Read More', 'pmc-gallery-v4'),
					'showLess'           => esc_html__('Show Less', 'pmc-gallery-v4'),
					'vertical'           => [
						'photo' => esc_html__('Photo', 'pmc-gallery-v4'),
					],
				],

				'ads'                         => [],
				'adsProvider'                 => 'boomerang',
				'railBottomAdRefreshInterval' => $options['rail_bottom_ad_refresh_clicks'],
				'adhesionAdRefreshInterval'   => $options['adhesion_ad_refresh_clicks'],
				'adAfter'                     => null,
				'listAdFrequency'             => $ad_frequency,

				'socialIconsUseMenu'          => true,
				'socialIcons'                 => [
					'facebook'  => [],
					'twitter'   => [],
					'pinterest' => [],
					'tumblr'    => [],
				],
				'twitterUserName'             => defined('PMC_TWITTER_SITE_USERNAME') ? PMC_TWITTER_SITE_USERNAME : '',
				'timestamp'                   => [
					'date'     => get_the_date('F d, Y, g:ia'),
					'datetime' => get_the_date('Y-m-d\TH:i:sP'),
				],
				'showThumbnails'              => false,
				'siteTitle'                   => sanitize_text_field(get_bloginfo('name')),
				'siteUrl'                     => trailingslashit(wp_make_link_relative(get_site_url())),
				'pagePermalink'               => trailingslashit(get_permalink()),
				'enableInterstitial'          => null,
				'interstitialAdAfter'         => null,
				'zoom'                        => false,
				'pinit'                       => null,
				'sponsored'                   => '',
				'sponsoredStyle'              => [],
				'runwayMenu'                  => [],
				'mobileCloseButton'           => '', // PMCP-1298: Added specially for HL.
				'galleryFetchUrl'             => null,
				'styles'                      => [
					'header-height'                      => '79px',
					'theme-color'                        => '#d32531',
					'vertical-album-image-margin-top'    => '0',
					'vertical-album-image-margin-right'  => '1.25rem',
					'vertical-album-image-margin-bottom' => '2.5rem',
					'vertical-album-image-margin-left'   => '0',
					'vertical-headline-font-weight'      => 700,
					'vertical-caption-font-weight'       => 300,
					'vertical-subtitle-font-weight'      => 500,
					'vertical-player-font-weight'        => 500,
					'vertical-headline-font-family'      => 'inherit',
					'vertical-caption-font-family'       => 'inherit',
					'vertical-subtitle-font-family'      => 'inherit',
					'vertical-player-font-family'        => 'inherit',
					'vertical-max-image-width'           => 'inherit',
					'horizontal-intro-card-font-family'  => 'inherit',
				],
				'forceSameEnding'             => false,
				'subscriptionsLink'           => '',
				'isMobile'                    => \PMC::is_mobile(),
				'listItemsPerPage'            => self::get_list_items_per_page(),
				'listItemStyles'              => [
					'rankNumberStyle'           => [
						'backgroundColor' => '#000000',
						'color'           => '#ffffff',
					],
					'videoPlayButtonStyle'      => [
						'fill'        => 'rgba(0, 0, 0, 0.7)',
						'stroke'      => '#000000',
						'strokeWidth' => '4px',
					],
					'videoPlayButtonHoverStyle' => [
						'stroke'      => '#ffffff',
						'strokeWidth' => '4px',
					],
					'videoPlayButtonIconStyle'  => [
						'fill' => '#ffffff',
					],
					'shareButtonStyle'          => [
						'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
						'borderWidth'     => '1px',
						'borderColor'     => '#444444',
					],
					'shareButtonIconStyle'      => [
						'fill' => '#ffffff',
					],
					'paginationButtonStyle'     => [
						'color'      => 'inherit',
						'border'     => 'solid 1px #000',
						'padding'    => '10px 30px',
						'fontWeight' => '700',
					],
				],
				'listNavBar'                  => [
					// Minimum number of list items before list nav bar will be displayed.
					'minimumNumberOfItems'         => 20,

					// Control amount of ranges depending on viewport width.
					// Keys are viewport width in pixels (used in CSS @media query).
					// Values are the number of ranges to display.
					'numberOfRangesAtMinWidth'     => [
						'0'   => 5,
						'530' => 7,
						'800' => 10,
					],

					// Arguments for "document.querySelector" which should select the nav bar's parent element.
					// The List Nav Bar's container element will be appended to this parent element (i.e. `listNavBarParentElement.appendChild( listNavbarContainerElement );`)
					'parentElementQuerySelector'   => '#pmc-gallery-list-nav-bar-parent',

					/**
					 * The remaining options are direct pass-throughs to React code:
					 * - key ending in "ElementStyle" means it's a direct pass-through to the DOM element's
					 *   style attribute.
					 * - key ending in "ElementAttributes" means it's a direct pass-throough to the root of the DOM
					 *   element - so you can add your own "style" but also "className"
					 */

					'parentElementStyle'           => [
						'transition'   => 'margin 250ms ease-in',
						'marginBottom' => '50px',
						'zIndex'       => '9999',
						'overflow'     => 'visible',
					],
					// The element appended to Parent Element (selected via parentElementQuerySelector)
					'containerElementAttributes'   => [
						'id'        => 'pmc-gallery-list-nav-bar-container',
						'className' => '',
						'style'     => [
							'transition' => 'opacity 500ms ease-in',
							'position'   => 'absolute',
							'top'        => '100%',
							'right'      => '0',
							'left'       => '0',
							'width'      => '100%',
							'height'     => '50px',
						],
					],
					// Attributes for the <ListNavBar> React component, rendered within the Container Element.
					'renderElementAttributes'      => [
						'id'        => 'pmc-gallery-list-nav-bar-render',
						'className' => '',
						'style'     => [
							'backgroundColor' => '#ffffff',
							'color'           => 'black',
							'height'          => '100%',
							'fontWeight'      => '500',
							'display'         => 'flex',
							'alignItems'      => 'center',
							'justifyContent'  => 'space-evenly',
							'textAlign'       => 'center',
						],
					],
					// Attributes for the range element (It's an <a> element)
					'rangeElementAttributes'       => [
						'style' => [
							'color'   => 'gray',
							'padding' => '4px 8px',
						],
					],
					// Same as above but for currently selected range.
					'activeRangeElementAttributes' => [
						'style' => [
							'color' => 'black',
						],
					],
					// Progress bar
					'progressBarElementAttributes' => [
						'style' => [
							'position'        => 'absolute',
							'bottom'          => '0',
							'height'          => '3px',
							'width'           => '100%',
							'backgroundColor' => 'black',
							'transform'       => 'scaleX(0)',
							'transformOrigin' => 'left center',
							'transition'      => 'transform 240ms cubic-bezier( 0.215, 0.610, 0.355, 1.000 )',
							'willChange'      => 'transform',
						],
					],
				],
			]
		);

		/**
		 * Setup listNavBar range data (needed for paginated lists)
		 */

		$generated_ranges = [];

		foreach ((array) self::$_list_config['listNavBar']['numberOfRangesAtMinWidth'] as $min_width => $max_number_of_ranges) {
			$max_chunk_size = ceil(count($all_list_item_ids) / $max_number_of_ranges);
			$all_chunks     = array_chunk($all_list_item_ids, $max_chunk_size, true);

			foreach ((array) $all_chunks as $chunk) {
				$chunk_first_index = array_keys((array) $chunk)[0];
				$chunk_last_index  = array_keys((array) $chunk)[count($chunk) - 1];
				$chunk_first_slug  = Lists::$all_list_items_by_list_id[$list_id][$chunk_first_index]->slug;

				$generated_ranges[$min_width][] = [
					'indexStart'           => $chunk_first_index,
					'indexEnd'             => $chunk_last_index,
					'positionDisplayStart' => 'desc' === $list_ordering ? $all_list_items_count - $chunk_first_index : $chunk_first_index + 1,
					'positionDisplayEnd'   => 'desc' === $list_ordering ? $all_list_items_count - $chunk_last_index : $chunk_last_index + 1,
					'link'                 => trailingslashit(get_permalink()) . $chunk_first_slug,
				];
			}
		}

		self::$_list_config['listNavBar']['generatedRanges'] = $generated_ranges;
		self::$_list_config['closeButtonLink']               = self::$_list_config['siteUrl'];

		$ads = Plugin::get_instance()->get_ads('right-rail-gallery');

		if (!empty($ads)) {

			// Override and add some default
			array_walk(
				$ads['data'],
				function (&$item) {
					$item['displayType'] = 'flexrec';
					if (empty($item['targeting'])) {
						$item['targeting'] = [];
					}
					$item['targeting'][] = [
						'key'   => 'pos',
						'value' => 'top',
					];
				}
			);

			self::$_list_config['ads']['rightRailGallery'] = $ads;
		}

		return self::$_list_config;
	}

	/**
	 * Get image sizes.
	 *
	 * @param int $attachment_id Attachment id.
	 *
	 * @param     $list_template
	 *
	 * @return array
	 */
	public static function get_image_sizes($attachment_id, $list_template = '')
	{
		$image_sizes = array();
		// TODO: Add filters for image size
		$sizes_config = array(
			'pmc-gallery-s'   => array(
				'landscape' => 'pmc-list-s',
				'portrait'  => 'pmc-list-s-portrait',
			),
			'pmc-gallery-m'   => array(
				'landscape' => 'pmc-list-m',
				'portrait'  => 'pmc-list-m-portrait',
			),
			'pmc-gallery-l'   => array(
				'landscape' => 'pmc-list-l',
				'portrait'  => 'pmc-list-l-portrait',
			),
			'pmc-gallery-xl'  => array(
				'landscape' => 'pmc-list-xl',
				'portrait'  => 'pmc-list-xl-portrait',
			),
			'pmc-gallery-xxl' => array(
				'landscape' => 'pmc-list-xxl',
				'portrait'  => 'pmc-list-xxl-portrait',
			),
		);

		foreach ($sizes_config as $size_name => $size_config) {
			$image_sizes[$size_name] = self::get_image_size($attachment_id, $size_config, $list_template);
		}

		return $image_sizes;
	}

	/**
	 * Get single image size.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param array  $size_config   Image size config.
	 *
	 * @param string $list_template
	 *
	 * @return array
	 */
	public static function get_image_size($attachment_id, $size_config, $list_template = '')
	{
		$image_meta = wp_get_attachment_metadata($attachment_id);

		if (empty($image_meta)) {
			return array();
		}

		if ('item-album' === $list_template) {
			$size = $size_config['portrait'];
		} else {
			$size = $size_config['landscape'];
		}

		$image = wp_get_attachment_image_src($attachment_id, $size);

		return array(
			'src'    => (is_array($image) && !empty($image[0])) ? $image[0] : '',
			'width'  => (is_array($image) && !empty($image[1])) ? $image[1] : '',
			'height' => (is_array($image) && !empty($image[2])) ? $image[2] : '',
		);
	}

	public static function get_image_sizes_external($image_url)
	{
		$sizes_config = array(
			'pmc-gallery-s'   => array(
				'landscape' => 'pmc-list-s',
				'portrait'  => 'pmc-list-s-portrait',
			),
			'pmc-gallery-m'   => array(
				'landscape' => 'pmc-list-m',
				'portrait'  => 'pmc-list-m-portrait',
			),
			'pmc-gallery-l'   => array(
				'landscape' => 'pmc-list-l',
				'portrait'  => 'pmc-list-l-portrait',
			),
			'pmc-gallery-xl'  => array(
				'landscape' => 'pmc-list-xl',
				'portrait'  => 'pmc-list-xl-portrait',
			),
			'pmc-gallery-xxl' => array(
				'landscape' => 'pmc-list-xxl',
				'portrait'  => 'pmc-list-xxl-portrait',
			),
		);

		global $_wp_additional_image_sizes;

		foreach ($sizes_config as $size_name => $size_config) {
			$sizes = ($_wp_additional_image_sizes[$size_name]);
			$image_sizes[$size_name] = [
				'src' => $image_url,
				'width' => $sizes['width'],
				'height' => $sizes['height'],
			];
		}

		return $image_sizes;
	}

	/**
	 * Adds the pmc_list and pmc_list_item post types to an array of post types.
	 *
	 * @param array $post_types
	 * @return array
	 */
	public function enable_videos_for_list_and_items($post_types)
	{
		return array_unique(
			array_merge(
				(array) $post_types,
				[
					'pmc_list',
					'pmc_list_item',
				]
			)
		);
	}

	/**
	 * Prevent canonical redirect for pmc gallery post type.
	 *
	 * @ticket PMCP-1140
	 * @since 2019-01-17 - Sayed Taqui
	 *
	 * Some themes redirect single posts to post permalink if permalink path is not equal to url path.
	 * PMC Gallery v3 used hashbang urls and therefore bailing out for canonical redirect for pmc-gallery post type was not required
	 * however now that we support the single slide slug in pmc-gallery-v4, without this filter it would redirect the single slide slug
	 * back to the gallery permalink if the theme/parent-theme using this pmc-gallery-v4 plugin does the redirect.
	 *
	 * @return string
	 */
	public function prevent_canonical_redirect($redirect_url)
	{
		return (!is_singular(self::LIST_POST_TYPE)) ? $redirect_url : '';
	}
}

// Ad placements:
//    'article-header-logo'
//
// first river: 'lists-top-river-ad'
//  the rest:  'lists-river-ad';
