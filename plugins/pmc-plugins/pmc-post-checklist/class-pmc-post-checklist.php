<?php

/*
@see http://docs.pmc.com/2014/12/01/pmc-post-checklist/
*/

use \PMC\Global_Functions\Traits\Singleton;

class PMC_Post_Checklist {

	use Singleton;

	protected $_list = array();
	protected $_post_types = array( 'post' );

	protected function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
		$this->register( array(
			'mt_seo_title' => array(
					'title'    => 'Add an SEO Title',
					'validate' => 'textinput',
			),
			'mt_seo_description' => array(
					'title'    => 'Add an SEO Description',
					'validate' => 'textinput',
			),
			'post_name' => array(
					'title'    => 'Edit URL',
					'validate' => 'urlslug',
			),
			'featured_image' => array(
					'title'    => 'Add a Featured Image',
					'validate' => 'featured_image',
			),
			'post_tag' => array(
					'title'    => 'Add Tag(s)',
					'validate' => 'taxinput',
			),
			'vertical' => array(
					'title'    => 'Add a Vertical',
					'validate' => 'checklist',
				),
			'category' => array(
					'title' => 'Add a Category',
					'validate' => 'checklist',
				),
		) );
	}

	public function action_init() {
		add_action( 'add_meta_boxes', array( $this, 'action_add_meta_boxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
	}

	public function action_admin_enqueue_scripts( $hook ) {

		if ( !in_array( get_post_type(), $this->_post_types ) || !in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) {
			return;
		}

		wp_enqueue_style( 'pmc-post-checklist', plugins_url( 'css/pmc-post-checklist.css', __FILE__ ) );

		wp_register_script( 'pmc-post-checklist-js', plugins_url( 'js/pmc-post-checklist.js', __FILE__ ) , array( 'jquery', 'pmc-hooks' ) );

		wp_localize_script( 'pmc-post-checklist-js', 'pmc_post_checklist_options', array(
				'list' => $this->_get_list(),
			) );
		wp_enqueue_script( 'pmc-hooks' );
		wp_enqueue_script( 'pmc-post-checklist-js' );

	}

	/**
	 * Register the post type(s) that support checklist
	 * @param array|string @post_types The post type
	 */
	public function register_post_type( $post_types ) {
		if ( empty( $post_types ) ) {
			return $this;
		}
		$this->_post_types = array_merge( $this->_post_types, (array)$post_types );
		return $this;
	}

	/**
	 * Register checklist
	 * @param array $args (
	 *		'key'           // the key/slug/id of the checklist
	 *			=> array(
	 *					'title'     => string,       // The checklist title
	 *					'validate'  => string,       // The checklist function for validation: textinput, taxinput, attachement, featured_image
	 *					'post_type' => string|array  // Default post, optional post type to enabled the checklist on
	 *				),
	 * )
	 * @return object $this
	 */
	public function register( $args ) {
		if ( empty( $args) ) {
			return $this;
		}

		foreach ( $args as $key => $value ) {
			if ( empty( $value['title'] ) || empty( $value['validate'] ) ) {
				continue;
			}

			if ( is_numeric( $key ) ) {
				$key = $value['title'];
			}

			$key = sanitize_title_with_dashes( $key );
			$value['slug'] = $key;

			// if no post type define, support post by default
			if ( empty( $value['post_type'] ) ) {
				$value['post_type'] = array( 'post' );
			} else {
				$value['post_type'] = (array)$value['post_type'];
			}

			$this->_list[ $key ] = $value;
		}

		return $this;
	}

	/**
	 * Action hook to add meta boxes
	 * @param string $post_type The post type
	 */
	public function action_add_meta_boxes( $post_type ) {
		// Only display our meta box while editing supported post types
		if ( !in_array( $post_type, $this->_post_types ) ) {
			return;
		}

		add_meta_box(
			'pmc-post-checklist-meta-box',
			'Article Checklist',
			array( $this, 'render_meta_box' ),
			$post_type, // post type
			'side', // side column
			'high'  // on top of list
		);
	}//add_meta_boxes

	/**
	 * Callback function to render the metabox
	 */
	public function render_meta_box() {

		echo '<ul>';

		foreach ( $this->_get_list() as $value ) {
			$title = ! empty( $value['title'] ) ? $value['title'] : ucfirst( $value['slug'] );
			printf('<li id="pmc-post-checklist-%1$s">%2$s</li>', esc_attr( $value['slug'] ), esc_html( $title ) );
		}

		echo '</ul>';

	} // function render_meta_box

	/**
	 * Return a list of support checklist for the current post type
	 * @return array The list of checklist
	 */
	protected function _get_list() {
		$list = array();

		foreach ( $this->_list as $key => $value ) {
			if ( empty( $value['post_type'] ) || !in_array( get_post_type(), $value['post_type'] ) ) {
				continue;
			}
			$list[ $key ] = $value;
		}

		return array_values( apply_filters( 'pmc-post-checklist', $list ) );
	}

}

PMC_Post_Checklist::get_instance();

// EOF
