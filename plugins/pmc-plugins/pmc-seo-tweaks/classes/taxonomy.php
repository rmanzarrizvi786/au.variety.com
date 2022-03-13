<?php

namespace PMC\SEO_Tweaks;

use \PMC;
use \PMC\Global_Functions\Traits\Singleton;

/*
 * Modifies SEO title, description, and keywords for categories and tags.
 *
 * Before WP 4.2 the PMC SEO was shared between terms of different taxonomy with same term_id
 * Post WP 4.2 the new term will get PMC SEO option data migrated from the old term only once in the 'split_shared_term' action hook.
 *
 */
class Taxonomy {

	use Singleton;

	const FILTER_ALLOW_POST_TYPE = 'pmc_seo_tweaks_post_type_allowlist';

	private $_taxonomies = array( 'category', 'post_tag' );

	private $_quick_edit_nonce_check = false;

	const option_name = 'pmc_seo_tweaks_custom_field';

	/**
	 * Return the supported allowed taxonomies
	 */
	public function get_taxonomies() {
		return $this->_taxonomies;
	}

	/**
	 * Initialization function called when object is instantiated. Does nothing by default.
	 */
	protected function __construct() {
		// Make sure the init method is called after tax/tag/category flags have been set in WP_Query, or it won't work correctly.
		add_action( 'wp' , array( $this, 'setup' ) );
		add_action( 'admin_init' , array( $this, 'admin_setup' ) );
	}

	public function setup() {
		// Make sure this method is called after tax/tag/category flags have been set in WP_Query, or it won't work correctly.
		if ( ! is_tax() && ! is_tag() && ! is_category() ) {
			return;
		}

		// @TODO: SADE-517 to be removed
		$this->_taxonomies = apply_filters('pmc_seo_tweaks_post_type_whitelist', $this->_taxonomies );
		$this->_taxonomies = apply_filters( self::FILTER_ALLOW_POST_TYPE, $this->_taxonomies );

		/* amt_metatags passes the full tag HTML, so we can't easily manipulate the values of existing tags. We're going to clobber the defaults and use our own. */
		add_filter( 'amt_metatags', '__return_null', 1, 99 );
		add_action( 'wp_head', array( $this, 'seo_add_meta_tags' ), 0 );
		add_filter( 'wp_title', array( $this, 'seo_rewrite_title' ), 20, 3);
		add_filter( 'pre_get_document_title', array( $this, 'seo_rewrite_title' ) );
	}

	public function admin_setup( ) {
		// @TODO: SADE-517 to be removed
		$this->_taxonomies = apply_filters('pmc_seo_tweaks_post_type_whitelist', $this->_taxonomies );
		$this->_taxonomies = apply_filters( self::FILTER_ALLOW_POST_TYPE, $this->_taxonomies );

		if ( ! empty( $this->_taxonomies ) && is_array( $this->_taxonomies ) ) {
			foreach ( $this->_taxonomies as $taxonomy ) {
				if ( empty( $taxonomy ) ) {
					continue;
				}
				add_action( "{$taxonomy}_edit_form_fields", array( $this, 'add_custom_field' ), 10, 2);
				add_action( "edited_{$taxonomy}" , array( $this, 'save_custom_field' ) , 10 , 2 );
				add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'filter_seo_columns' ) );
				add_filter( "manage_{$taxonomy}_custom_column" , array( $this, 'filter_seo_columns_output' ), 10, 3 );
			}
		}

		add_action( 'quick_edit_custom_box', array( $this, 'action_quick_edit_options' ), 10, 3 );

		add_action( 'admin_enqueue_scripts', array( $this, 'action_quick_editor_js' ) );
		add_action( 'admin_print_styles-edit-tags.php', array( $this, 'action_print_quick_editor_css' ), 10, 1 );
	}

	/**
	 * Output custom column info to tax editor
	 *
	 * @param string $value
	 * @param string $column
	 * @param int $term_id
	 * @uses esc_html
	 * @return void
	 */
	public function filter_seo_columns_output( $value, $column, $term_id ) {
		switch ( $column ) {
			case 'pmc_seo_tweaks_title':
				$taxonomy_term = pmc_get_option( self::option_name . $term_id );
				if ( is_array( $taxonomy_term ) ) {
					if ( ! empty( $taxonomy_term['title'] ) ) {
						echo esc_html( $taxonomy_term['title'] );
					}
				}
				break;
			case 'pmc_seo_tweaks_description':
				$taxonomy_term = pmc_get_option( self::option_name . $term_id );
				if( is_array( $taxonomy_term ) ) {
					if( isset( $taxonomy_term['description'] ) ) {
						echo esc_html( $taxonomy_term['description'] );
					}
				}
				break;
		}
	}

	/**
	 * Hide custom column info in tax term table
	 *
	 * @return void
	 */
	public function action_print_quick_editor_css() {
		?>
		<style type="text/css">
			.column-pmc_seo_tweaks_title, .column-pmc_seo_tweaks_description {
				display: none;
			}
		</style>
		<?php
	}

	/**
	 * Print quick editor JS modifications to footer.
	 *
	 * @param $hook
	 * @uses plugins_url
	 *
	 * @return void
	 */
	function action_quick_editor_js( $hook ) {
		if ( 'edit-tags.php' === $hook ) {
			wp_enqueue_script( 'pmc-seo-tweaks-admin-edit-tags', plugin_dir_url( __FILE__ ) . '../js/admin-edit-tags.js', array('jquery'), '2.0', true );
		}
	}

	/**
	 * Add custom SEO columns to tax table
	 *
	 * @param array $columns
	 * @return array
	 */
	public function filter_seo_columns( $columns ) {
		$columns['pmc_seo_tweaks_title'] = '';
		$columns['pmc_seo_tweaks_description'] = '';
		return $columns;
	}

	/**
	 * Output quick editor HTML for seo fields
	 *
	 * @param string $column_name
	 * @param string $post_type
	 * @uses wp_nonce_field, esc_attr
	 * @return void
	 */
	public function action_quick_edit_options( $column_name, $post_type ) {
		if ( 'edit-tags' !== $post_type ) {
			return;
		}

		$label = '';

		// Include the nonce only once.
		if ( ! $this->_quick_edit_nonce_check ) {
			wp_nonce_field( __FILE__, 'pmc_seo_tweaks_custom_field' );
			$this->_quick_edit_nonce_check = true;
		}

		switch ( $column_name ) {
			case 'pmc_seo_tweaks_title':
				$label = 'SEO Title';
				break;
			case 'pmc_seo_tweaks_description':
				$label = 'SEO Description';
				break;
		}
		echo PMC::render_template( PMC_SEO_TWEAKS_ROOT . '/templates/taxonomy-seo-fields-quick-edit.php', array(
			'column_name'  => $column_name,
			'column_label' => $label,
		) );
	}

	/**
	 * Adds SEO custom fields to term edit.
	 *
	 * @param $tag
	 *
	 * @return void
	 */
	public function add_custom_field( $tag ) {
		$taxonomy_term = pmc_get_option( self::option_name . $tag->term_id );

		$title = ( ! empty( $taxonomy_term['title'] ) ) ? $taxonomy_term['title'] : '';
		$description = ( ! empty( $taxonomy_term['description'] ) ) ? $taxonomy_term['description'] : '';
		$keywords = ( ! empty( $taxonomy_term['keywords'] ) ) ? $taxonomy_term['keywords'] : '';

		wp_nonce_field( __FILE__, 'pmc_seo_tweaks_custom_field' );

		echo PMC::render_template( PMC_SEO_TWEAKS_ROOT . '/templates/taxonomy-seo-fields.php', array(
			'title'       => $title,
			'description' => $description,
			'keywords'    => $keywords,
		) );
	}

	/**
	 * Saves SEO custom fields.
	 *
	 * @param $term_id
	 *
	 * @return void
	 */
	function save_custom_field( $term_id ) {
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['pmc_seo_tweaks_custom_field'], __FILE__ ) ) {
			return;
		}

		if ( ! intval( $term_id ) ) {
			return;
		}

		$option_name = self::option_name . intval( $term_id );

		$taxonomy_term = pmc_get_option( $option_name );

		if( ! is_array( $taxonomy_term ) ) {
			$taxonomy_term = array();
		}

		if( isset( $_POST['pmc_seo_tweaks_title'] ) ) {
			$taxonomy_term['title'] = sanitize_text_field( $_POST['pmc_seo_tweaks_title'] );
		}

		if( isset( $_POST['pmc_seo_tweaks_description'] ) ) {
			$taxonomy_term['description'] = sanitize_text_field( $_POST['pmc_seo_tweaks_description'] );
		}

		if( isset( $_POST['pmc_seo_tweaks_keywords'] ) ) {
			$taxonomy_term['keywords'] = sanitize_text_field( $_POST['pmc_seo_tweaks_keywords'] );
		}

		pmc_update_option( $option_name, $taxonomy_term );
	}

	/**
	 * Adds meta tags to term page.
	 */
	public function seo_add_meta_tags() {
		$taxonomy_term = pmc_get_option( self::option_name . get_queried_object_id() );

		// We want to render an empty description and/or keywords tag if an SEO description hasn't been defined. e.g., we specifically want to render content="" if there's no custom text.

		$default_description = ( ! empty( $taxonomy_term['description'] ) ) ? sanitize_text_field( $taxonomy_term['description'] ) : '';

		$default_keywords = ( ! empty( $taxonomy_term['keywords'] ) ) ? sanitize_text_field( $taxonomy_term['keywords'] ) : '';

		$taxonomy_term = get_queried_object();
		$taxonomy = $taxonomy_term->taxonomy;

		if ( 'post_tag' === $taxonomy ) {
			$taxonomy = 'tag';
		}

		$tags = apply_filters( 'pmc_seo_add_meta_tags', array(
			'description' => $default_description,
			'keywords' => $default_keywords,
		) );

		if ( empty( $tags ) || ! is_array( $tags ) ) {
			return;
		}

		$token_name = $taxonomy;
		$replacement_value = single_term_title( '', false );

		foreach ( $tags as $key => $value ) {
			$value = str_replace('{' . $token_name . '}', $replacement_value, $value);

			echo '<meta name="' . esc_attr( $key ) .'" content="' . esc_attr( $value ) .'" />' . PHP_EOL;
		}

	}

	/**
	 * Rewrites SEO title.
	 *
	 * @param        $title
	 * @param string $sep
	 * @param string $seplocation
	 *
	 * @return string|void
	 */
	public function seo_rewrite_title( $title, $sep = '' , $seplocation = '' ) {
		// On custom taxonomy archive pages, WordPress will add the taxonomy type to the term name (e.g. "Business Verticals" for the Vertical term "Business"). That's ugly, so let's undo it.
		$seo_title = single_term_title( '', false );

		$taxonomy_term = pmc_get_option( self::option_name . get_queried_object_id() );

		if ( isset( $taxonomy_term['title'] ) ) {
			$seo_title = esc_html( sanitize_text_field( $taxonomy_term['title'] ) );
		}

		return $seo_title;
	}
}

// EOF
