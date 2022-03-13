<?php

namespace PMC\Content_Publishing;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

/**
 *
 * Display a Post Checklist meta box within the edit post view
 * It's purpose is to aid authors in completing all required content
 *
 */
class Checklist {

	use Singleton;
	/**
	 * Plugin ID.
	 */
	const PLUGIN_ID = 'pmc-content-publishing-checklist';

	/**
	 * List of registered post type
	 *
	 * @var array
	 */
	private $_registered_post_types = [ 'post', 'pmc-gallery' ];

	/**
	 * Default post type for task list.
	 *
	 * @var array
	 */
	private $_task_post_types = [ 'post', 'pmc-gallery' ];

	/**
	 * Task list.
	 *
	 * @var array
	 */
	private $_tasks = [];

	/**
	 * Run any hooks we'll need to tap into
	 *
	 * @since 2015-10-20
	 * @version 2015-10-20 Archana Mandhare PMCVIP-339
	 *
	 * @return null
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'publishing_checklist_init', array( $this, 'action_register_tasks' ) );
		add_filter( 'pmc_global_cheezcap_options', array( $this, 'filter_pmc_global_cheezcap_options' ), 10, 1 );

		// Check is the cheezcap checklist enforcement option has been enabled.
		if ( $this->is_checklist_enforced() ) {
			add_action( 'admin_print_scripts', array( $this, 'action_admin_print_scripts' ) );
		}
	}

	/**
	 * Check if the checklist is being enforced with a popup prompt
	 *
	 * This setting is enabled/disabled in the theme's global cheezcap settings
	 *
	 * @param null
	 *
	 * @return bool
	 */
	public function is_checklist_enforced() {

		// wtf is the point of cheezcap_get_option?
		// cheezcap_get_option() is only available after init 10
		// get_option works just as well and is available super early
		if ( 'enabled' === get_option( 'cap_pmc_publishing_checklist_enforcement' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Filter the 'Global Theme Options' cheezcap group
	 *
	 * @param array $cheezcap_options The cheezcap options displayed in this group
	 *
	 * @return array The *possibly* modified cheezcap group of options
	 */
	public function filter_pmc_global_cheezcap_options( $cheezcap_options = array() ) {

		// Add an option for admins to toggle the display
		// of a checklist popup when the user saves, updates,
		// publishes, or schedules a post who's checklist
		// is not 100% complete.
		$cheezcap_options[] = new \CheezCapDropdownOption(
			'Enforce Publishing Checklist',
			'Display a popup prompting users to complete their checklist before saving, updating, publishing, or scheduling a post.',
			'pmc_publishing_checklist_enforcement',
			array(
				'disabled',
				'enabled',
			),
			0,
			array( 'Disabled', 'Enabled', )
		);

		// Grab the core post statuses for the post status cheezcap field
		$post_statuses = get_post_statuses();

		// If EF is available and has custom statuses also merge them in.
		// NOTE, this will only work if your theme's init hook which
		// calls PMC_Cheezcap::get_instance()->register(); is on priority 11
		// That's because EF is created on init w priority 10
		if ( class_exists( '\EF_Custom_Status' ) ) {

			// Using \EditFlow()->custom_status instead of
			// \EF_Custom_Status because calling `new \EF_Custom_Status`
			// foobars all the Edit Flow custom status usage on the site.
			$edit_flow_post_status_terms = \EditFlow()->custom_status->get_post_statuses();

			if ( ! empty( $edit_flow_post_status_terms ) && is_array( $edit_flow_post_status_terms ) ) {
				foreach ( $edit_flow_post_status_terms as $status_term ) {
					if ( is_a( $status_term, 'WP_Term' ) ) {
						$post_statuses[ $status_term->slug ] = $status_term->name;
					}
				}
			}
		}

		if ( ! empty( $post_statuses ) && is_array( $post_statuses ) ) {

			// Create a checkbox field of post statuses for the user to select
			$cheezcap_options[] = new \CheezCapMultipleCheckboxesOption(
				'Enforce Publishing Checklist - Post Statuses',
				'Only display the enforcement popup when a post is advancing to one of these selected post statuses.',
				'pmc_publishing_checklist_enforcement_statuses',
				array_keys( $post_statuses ),
				array_values( $post_statuses ),
				'', // No default-selection checkboxes, pls
				array( 'PMC_Cheezcap', 'sanitize_cheezcap_checkboxes' )
			);
		}

		return $cheezcap_options;
	}

	/**
	 * Do stuff the admin_print_scripts action is fired in the admin header
	 *
	 * @param null
	 *
	 * @return null
	 */
	public function action_admin_print_scripts () {
		?>

		<!--
			Content Publishing Checklist Enforcement Popup

			Hidden by default. When enabled, this popup is shown
			when a user clicks save, update, schedule, or publish.
		-->
		<div id="content-publishing-checklist-popup" style="display: none;">

			<h4><?php echo esc_html_e( 'Please complete the following items before changing this post\'s status:', 'pmc-checklist' ); ?></h4>

			<!--
				This UL will be dynamically populated with
				JavaScript/underscore incomplete checklist items.
			-->
			<ul></ul>

			<div class="buttons">
				<button
					id="checklist-popup-continue-editing"
					class="button button-primary button-large">
					<?php echo esc_html_e( 'Continue Editing', 'pmc-checklist' ); ?>
				</button>
				<button
					id="checklist-popup-continue-anyway"
					class="button button-secondary button-large">
					<?php echo esc_html_e( 'Proceed Anyway', 'pmc-checklist' ); ?>
				</button>
			</div>
		</div>

		<!--
			Content Publishing Checklist Enforcement Popup <li> Template

			underscore template for the checklist popup <li> elements
			which are dynamically created on-the-fly based on the post's
			incomplete checklist items, and essentially copied from
			the post checklist <li> items themselves.
		-->
		<script type="text/template" id="content-publishing-checklist-popup-li">
			<li class="<%- li_class %>"><span class="<%- span_class %>"></span><%- li_text %></li>
		</script>

		<?php
	}

	/**
	 * Enqueue our Javascript and CSS
	 *
	 * @since 2015-10-20
	 * @version 2015-10-20 Archana Mandhare PMCVIP-339
	 *
	 * @return null
	 */
	public function admin_enqueue_scripts() {
		global $pagenow, $post;

		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		// Only fire for post type of registered post types
		if ( empty( $post ) || empty( $post->post_type ) || ! in_array( $post->post_type, $this->_registered_post_types ) ) {
			return;
		}

		wp_register_script( self::PLUGIN_ID . '-js', plugins_url( 'pmc-content-publishing/assets/js/checklist.js', PMC_CONTENT_PUBLISHING_ROOT ), array(
			'jquery',
			'pmc-hooks',
			'underscore',
		), PMC_CONTENT_PUBLISHING_VERSION );

		// JavaScript only need a couple of the task properties
		$task_list = array();

		foreach( $this->get_task_list() as $task_name => $task_data ) {
			$clean_task_list[ $task_name ] = array(
				'validate' => sanitize_text_field( $task_data['validate'] ),
				'explanation' => sanitize_text_field( $task_data['explanation'] ),
				'force_check' => ( isset( $task_data['force_check'] ) ) ? sanitize_text_field( $task_data['force_check'] ) : false,
			);
		}

		// wtf is the point of cheezcap_get_option?
		$post_statuses = get_option( 'cap_pmc_publishing_checklist_enforcement_statuses' );

		if ( empty( $post_statuses ) || ! is_array( $post_statuses ) ) {
			$post_statuses = array();
		}

		wp_localize_script( self::PLUGIN_ID . '-js', 'pmc_content_checklist_options', array(

			// The values within this $task_list array have already been cleaned
			'list' => $clean_task_list,

			'enforce_checklist_popup' => sanitize_text_field( $this->is_checklist_enforced() ),
			'enforce_checklist_popup_statuses' => array_map( 'sanitize_text_field', $post_statuses ),
		) );

		wp_enqueue_script( 'pmc-hooks' );

		//Load the plugin's javascript
		wp_enqueue_script( self::PLUGIN_ID . '-js' );

		//Load the plugin's css
		wp_enqueue_style( self::PLUGIN_ID . '-css', plugins_url( 'pmc-content-publishing/assets/css/checklist.css', PMC_CONTENT_PUBLISHING_ROOT ), array(), 1, 'screen', PMC_CONTENT_PUBLISHING_VERSION );
	}


	/**
	 * Return a list of support checklist for the current post type
	 *
	 * @since 2015-10-20
	 * @version 2015-10-20 Archana Mandhare PMCVIP-339
	 *
	 * @return array The list of checklist items
	 */
	public function get_task_list() {
		$list = array();

		foreach ( $this->_tasks as $key => $value ) {
			if ( empty( $value['post_type'] ) || ! in_array( get_post_type(), $value['post_type'] ) ) {
				continue;
			}

			$list[ $key ] = $value;
		}

		return array_values( $list );
	}

	/**
	 * Register the checklist items as tasks for the editors to complete.
	 * use this function to add more items to the list
	 *
	 * @since 2015-10-20
	 * @version 2015-10-20 Archana Mandhare PMCVIP-339
	 *
	 * @return $this Object
	 */
	public function register_tasks( array $tasks ) {

		if ( PMC::is_associative_array( $tasks ) ) {
			$this->_tasks = array_merge( $this->_tasks, $tasks );
		}

		return $this;

	}


	/**
	 * Return the registered tasks
	 *
	 * @since 2015-10-20
	 * @version 2015-10-20 Archana Mandhare PMCVIP-339
	 *
	 * @return array
	 */
	public function get_registered_tasks() {
		return $this->_tasks;
	}


	/**
	 * Register the post types that we want to enable plugin
	 *
	 * @since 2015-10-20
	 * @version 2015-10-20 Archana Mandhare PMCVIP-339
	 *
	 * @param array $post_types The array of post type to activate the plugin on
	 *
	 * @return $this Object
	 */
	public function register_post_type( $post_types ) {
		if ( ! empty( $post_types ) ) {
			if ( ! is_array( $post_types ) ) {
				$post_types = array( $post_types );
			}
			$this->_registered_post_types = array_unique( array_merge( $this->_registered_post_types, array_values( $post_types ) ), SORT_REGULAR );
		}

		return $this;
	}


	/**
	 * Get the post types that we want to enable the plugin
	 *
	 * @since 2015-10-20
	 * @version 2015-10-20 Archana Mandhare PMCVIP-339
	 *
	 * @return array $this->_registered_post_types
	 */
	public function get_plugin_post_type() {
		return $this->_registered_post_types;
	}


	/**
	 * Get the registered the post types that we want to enable the checklist items
	 *
	 * @since 2015-10-20
	 * @version 2015-10-20 Archana Mandhare PMCVIP-339
	 *
	 * @return array $this->_task_post_types
	 */
	public function get_tasks_post_type() {
		return $this->_task_post_types;
	}


	/**
	 * Register checklist tasks
	 *
	 * @since 2015-10-05
	 * @version 2015-10-05 - Javier Martinez - PMCVIP-123 - Use publishing checklist VIP plugin
	 * @version 2015-10-19 - Archana Mandhare - PMCVIP-339 - Changed label and added Vertical
	 */
	public function action_register_tasks() {

		$tasks = array(
			// Post Title
			'title'              => array(
				'label'       => __( 'Add a Headline', 'pmc-content-publishing' ),
				'callback'    => array( $this, 'check_task_status_post_title' ),
				'explanation' => __( 'title', 'pmc-content-publishing' ),
				'post_type'   => $this->_task_post_types,
				'validate'    => 'textinput',
				'force_check' => true,
			),

			// Post Title
			'gallery_alt_text'   => array(
				'label'       => __( 'Add Alt Text to EVERY Image', 'pmc-content-publishing' ),
				'callback'    => array( $this, 'check_task_status_gallery_alt_text' ),
				'explanation' => __( 'gallery_alt_text', 'pmc-content-publishing' ),
				'post_type'   => [ 'pmc-gallery' ], // This check list only related to gallery.
				'validate'    => 'gallery_alt_text',
				'force_check' => false,
			),

			// SEO Title
			'mt_seo_title'       => array(
				'label'       => __( 'Add an SEO Title', 'pmc-content-publishing' ),
				'callback'    => array( $this, 'check_task_status_title' ),
				'explanation' => __( 'mt_seo_title', 'pmc-content-publishing' ),
				'post_type'   => $this->_task_post_types,
				'validate'    => 'textinput',
				'force_check' => true,
			),
			// SEO Description
			'mt_seo_description' => array(
				'label'       => __( 'Add an SEO Description', 'pmc-content-publishing' ),
				'callback'    => array( $this, 'check_task_status_description' ),
				'explanation' => __( 'mt_seo_description', 'pmc-content-publishing' ),
				'post_type'   => $this->_task_post_types,
				'validate'    => 'textinput',
				'force_check' => true,
			),
			// Edit URL
			'post_name'          => array(
				'label'       => __( 'Edit URL', 'pmc-content-publishing' ),
				'callback'    => array( $this, 'check_task_status_edit_url' ),
				'explanation' => __( 'post_name', 'pmc-content-publishing' ),
				'post_type'   => $this->_task_post_types,
				'validate'    => 'urlslug',
				'force_check' => false,
			),
			// Featured Image
			'featured_image'     => array(
				'label'       => __( 'Add a Featured Image', 'pmc-content-publishing' ),
				'callback'    => array( $this, 'check_task_status_featured_image' ),
				'explanation' => __( 'featured_image', 'pmc-content-publishing' ),
				'post_type'   => $this->_task_post_types,
				'validate'    => 'featured_image',
				'force_check' => false,
			),
			// Featured Image Alt Text
			'featured_image_alt_text' => array(
				'label'       => __( 'Add a Featured Image Alt Text', 'pmc-content-publishing' ),
				'callback'    => array( $this, 'check_task_status_featured_image_alt_text' ),
				'explanation' => __( 'featured_image_alt_text', 'pmc-content-publishing' ),
				'post_type'   => $this->_task_post_types,
				'validate'    => 'featured_image_alt_text',
				'force_check' => false,
			),
			// Tags
			'post_tag'           => array(
				'label'       => __( 'Add Tag(s)', 'pmc-content-publishing' ),
				'callback'    => array( $this, 'check_task_status_tag' ),
				'explanation' => __( 'post_tag', 'pmc-content-publishing' ),
				'post_type'   => $this->_task_post_types,
				'validate'    => 'taxinput',
				'force_check' => false,
			),
			// Vertical
			'vertical'           => array(
				'label'       => __( 'Add a Vertical', 'pmc-content-publishing' ),
				'callback'    => array( $this, 'check_task_status_vertical' ),
				'explanation' => __( 'vertical', 'pmc-content-publishing' ),
				'post_type'   => $this->_task_post_types,
				'validate'    => 'checklist',
				'force_check' => false,
			),
			// Category
			'category'           => array(
				'label'       => __( 'Add a Category', 'pmc-content-publishing' ),
				'callback'    => array( $this, 'check_task_status_category' ),
				'explanation' => __( 'category', 'pmc-content-publishing' ),
				'post_type'   => $this->_task_post_types,
				'validate'    => 'checklist',
				'force_check' => false,
			),
			// Sub Category
			'sub_category'       => array(
				'label'       => __( 'Add a Sub Category', 'pmc-content-publishing' ),
				'callback'    => array( $this, 'check_has_child_term' ),
				'explanation' => __( 'sub_category', 'pmc-content-publishing' ),
				'post_type'   => $this->_task_post_types,
				'validate'    => 'child_term_checklist',
				'force_check' => false,
			),
		);

		$updated_tasks = apply_filters( 'pmc_content_publishing_checklist_default_tasks', $tasks );

		if ( PMC::is_associative_array( $updated_tasks ) ) {
			if ( ! empty( $this->_tasks ) && is_array( $this->_tasks ) ) {
				$this->_tasks = array_merge( $updated_tasks, $this->_tasks );
			} else {
				$this->_tasks = $updated_tasks;
			}
		}

		if ( ! empty( $this->_tasks ) ) {
			foreach ( $this->_tasks as $task_id => $args ) {
				Publishing_Checklist()->register_task( $task_id, $args );
			}
		}

	}

	/**
	 * To check if post have excerpt or not.
	 *
	 * @param  int    $post_id Post ID.
	 * @param  string $task_id Task ID.
	 *
	 * @return bool TRUE on success otherwise FALSE
	 */
	public function check_task_status_post_title( $post_id, $task_id ) {

		if ( empty( $post_id ) || empty( $task_id ) ) {
			return false;
		}

		$post = get_post( $post_id );

		if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) ) {
			return false;
		}

		return ( ! empty( $post->post_title ) ) ? true : false;

	}

	/**
	 * To check if all slide of gallery post have alt text or not.
	 *
	 * @param  int    $post_id Post ID.
	 * @param  string $task_id Task ID.
	 *
	 * @return bool True on success False otherwise.
	 */
	public function check_task_status_gallery_alt_text( $post_id, $task_id ) {

		if ( empty( $post_id ) || empty( $task_id ) ) {
			return false;
		}

		$post = get_post( $post_id );

		if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) || 'pmc-gallery' !== $post->post_type ) {
			return false;
		}

		$gallery_meta = get_post_meta( $post_id, \PMC_Gallery_Defaults::name, true );
		$gallery_meta = ( ! empty( $gallery_meta ) && is_array( $gallery_meta ) ) ? $gallery_meta : array();
		$gallery_meta = array_map( 'intval', (array) $gallery_meta );

		/**
		 * This is legacy code to support backward compatibility to pmc-gallery-v3.
		 */
		// @codeCoverageIgnoreStart
		if ( ! class_exists( 'PMC\Gallery\Attachment\Detail' ) ) {

			foreach ( $gallery_meta as $attachment_id ) {

				$alt_text = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

				if ( empty( $alt_text ) ) {
					return false;
				}
			}

			return true;
		}
		// @codeCoverageIgnoreEnd

		$gallery_attachment_detail = \PMC\Gallery\Attachment\Detail::get_instance();

		foreach ( $gallery_meta as $variant_id => $attachment_id ) {

			$attachment_meta = $gallery_attachment_detail->get_variant_meta( $variant_id );

			if ( empty( trim( $attachment_meta['alt'] ) ) ) {
				return false;
			}

		}

		return true;
	}

	/**
	 * Check SEO Title field value for checklist
	 *
	 * @since 2015-10-05
	 * @version 2015-10-05- Javier Martinez - PMCVIP-123 - Use publishing checklist VIP plugin
	 *
	 * @param $post_id
	 * @param $task_id
	 *
	 * @return bool
	 *
	 */
	public function check_task_status_title( $post_id, $task_id ) {
		$mt_seo_title = (string) get_post_meta( $post_id, 'mt_seo_title', true );

		return empty( trim( $mt_seo_title ) ) ? false : true;
	}


	/**
	 *
	 * Check SEO Description field value for checklist
	 * @since 2015-10-05
	 * @version 2015-10-05- Javier Martinez - PMCVIP-123 - Use publishing checklist VIP plugin
	 *
	 * @param $post_id
	 * @param $task_id
	 *
	 * @return bool
	 *
	 */
	public function check_task_status_description( $post_id, $task_id ) {
		$mt_seo_description = (string) get_post_meta( $post_id, 'mt_seo_description', true );

		return empty( trim( $mt_seo_description ) ) ? false : true;
	}


	/**
	 *
	 * Check URL is edited after post saved for checklist
	 *
	 * @since 2015-10-05
	 * @version 2015-10-05 - Javier Martinez - PMCVIP-123 - Use publishing checklist VIP plugin
	 *
	 * @param $post_id
	 * @param $task_id
	 *
	 * @return bool
	 *
	 */
	public function check_task_status_edit_url( $post_id, $task_id ) {
		$title           = get_the_title( $post_id );
		$title           = str_replace( '&nbsp;', ' ', $title );
		$sanitized_title = strtolower( sanitize_title( $title ) );
		$permalink       = strtolower( basename( get_permalink( $post_id ) ) );

		if ( trim( $sanitized_title ) == "auto-draft" ) {
			return false;
		}

		return ( $sanitized_title != $permalink && $sanitized_title.'-'.$post_id != $permalink ) ? true : false;
	}


	/**
	 * Check post has vertical
	 *
	 * @since 2015-10-19
	 * @version 2015-10-19 - Archana Mandhare - PMCVIP-339 - Use publishing checklist VIP plugin
	 *
	 * @param $post_id
	 * @param $task_id
	 *
	 * @return mixed
	 */
	public function check_task_status_vertical( $post_id, $task_id ) {
		return has_term( '', 'vertical', $post_id );
	}

	/**
	 * Check if post have any terms of given taxonomy.

	 * @param int    $post_id Post ID.
	 * @param string $task_id Task ID, Or taxonomy.
	 *
	 * @return bool TRUE on success FALSE on fail.
	 */
	public function check_has_term( $post_id, $task_id ) {

		if ( empty( $post_id ) || empty( $task_id ) || ! taxonomy_exists( $task_id ) ) {
			return false;
		}

		return has_term( '', $task_id, $post_id );
	}

	/**
	 * To Check if post have child term of given taxonomy.
	 *
	 * @param  int    $post_id Post ID.
	 * @param  string $task_id Task ID.
	 *
	 * @return bool True on success False otherwise.
	 */
	public function check_has_child_term( $post_id, $task_id ) {

		if ( empty( $post_id ) || empty( $task_id ) ) {
			return false;
		}

		$taxonomy = substr( $task_id, 4 );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		$terms = get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return false;
		}

		foreach ( $terms as $term ) {

			if ( ! empty( $term ) && is_a( $term, 'WP_Term' ) && ! empty( $term->parent ) ) {
				return true;
			}

		}

		return false;
	}

	/**
	 * Check post has category and is not marked in default Uncategorized category
	 *
	 * @since 2015-10-05
	 * @version 2015-10-05 - Javier Martinez - PMCVIP-123 - Use publishing checklist VIP plugin
	 *
	 * @param $post_id
	 * @param $task_id
	 *
	 * @return mixed
	 */
	public function check_task_status_category( $post_id, $task_id ) {
		if ( has_category( 'uncategorized', $post_id ) ) {
			$categories = get_the_terms( $post_id, 'category' );

			return ( 1 < count( $categories ) );
		} else {
			return has_category( '', $post_id );
		}
	}


	/**
	 * Check post has tag
	 * @since 2015-10-05
	 * @version 2015-10-05 - Javier Martinez - PMCVIP-123 - Use publishing checklist VIP plugin
	 *
	 * @param $post_id
	 * @param $task_id
	 *
	 * @return mixed
	 */
	public function check_task_status_tag( $post_id, $task_id ) {
		return has_tag( '', $post_id );
	}


	/**
	 * Check post has featured image
	 *
	 * @since 2015-10-05
	 * @version 2015-10-05 - Javier Martinez - PMCVIP-123 - Use publishing checklist VIP plugin
	 *
	 * @param $post_id
	 * @param $task_id
	 *
	 * @return mixed
	 */
	public function check_task_status_featured_image( $post_id, $task_id ) {
		return has_post_thumbnail( $post_id );
	}

	/**
	 * Check post has featured image alt text
	 *
	 * @since 2019-03-19
	 * @version 2019-03-19 - MJ Zorick - SADE-132 - Add Featured Image Alt Text
	 *
	 * @param $post_id
	 * @param $task_id
	 *
	 * @return mixed
	 */
	public function check_task_status_featured_image_alt_text( $post_id, $task_id ) {
		$featured_img = get_post_thumbnail_id( $post_id );
		$alt_text     = get_post_meta( $featured_img, '_wp_attachment_image_alt', true );
		return ( ! empty( $alt_text ) );
	}

}

//Checklist
