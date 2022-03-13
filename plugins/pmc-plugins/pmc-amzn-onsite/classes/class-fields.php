<?php

namespace PMC\Amzn_Onsite;

use \PMC;
use PMC\Global_Functions\Traits\Singleton;
use \Fieldmanager_Group;
use \Fieldmanager_Autocomplete;
use \Fieldmanager_Datasource_Term;
use \Fieldmanager_Select;
use \Fieldmanager_Textfield;
use \Fieldmanager_Checkbox;
use \Fieldmanager_RichTextarea;
use \Fieldmanager_Link;

/**
 * Fieldmanager fields class for PMC Amazon Onsite
 *
 * @since 2019-07-11 - Keanan Koppenhaver
 */

class Fields {

	use Singleton;

	/**
	 * Fields constructor.
	 */
	protected function __construct() {

		$this->_setup_hooks();

	}

	/**
	 * Setup hooks.
	 */
	protected function _setup_hooks() : void {

		add_action( 'init', [ $this, 'after_init_hooks' ] );

	}

	/**
	 * Hooks that need to execute after init.
	 */
	public function after_init_hooks() : void {

		$post_types = apply_filters( 'amzn_onsite_post_types', [ 'post' ] );

		foreach ( $post_types as $post_type ) {
			if ( post_type_exists( $post_type ) ) {
				$fm_action = sprintf( 'fm_post_%s', $post_type );

				add_action( $fm_action, [ $this, 'add_amazon_onsite_info_meta_box' ] );
				add_action( $fm_action, [ $this, 'add_amazon_products_meta_box' ] );
			}
		}

	}

	public function add_amazon_onsite_info_meta_box() {
		$fm = new Fieldmanager_Group(
			[
				'name'           => 'amazon-onsite-info',
				'serialize_data' => false,
				'add_to_prefix'  => false,
				'children'       => [
					'_alternative-amazon-heading' => new Fieldmanager_TextField(
						[
							'label' => __( 'Amazon Alternative Headline', 'pmc-amzn-onsite' ),
							'name'  => '_alternative-amazon-heading',
						]
					),
					'amazon_date'                 => new \Fieldmanager_Datepicker(
						[
							'label'    => __( 'Amazon Publish Date', 'pmc-amzn-onsite' ),
							'name'     => 'amazon_date',
							'use_time' => true,
						]
					),
					'hide_products_from_post'     => new Fieldmanager_Checkbox(
						[
							'label' => __( 'Hide Products from bottom of post?', 'pmc-amzn-onsite' ),
							'name'  => 'hide_products_from_post',
						]
					),
					'amazon_intro_text'           => new Fieldmanager_RichTextarea(
						[
							'label' => __( 'Intro Text', 'pmc-amzn-onsite' ),
							'name'  => 'amazon_intro_text',
						]
					),
					'amazon_post_content'         => new Fieldmanager_RichTextarea(
						[
							'label' => __( 'Amazon Alternate Post Content', 'pmc-amzn-onsite' ),
							'name'  => 'amazon_post_content',
						]
					),
				],
			]
		);
		return $fm->add_meta_box( __( 'Amazon Onsite Information', 'pmc-amzn-onsite' ), apply_filters( 'amzn_onsite_post_types', [ 'post' ] ) );
	}

	/**
	 * Register products meta box
	 */
	public function add_amazon_products_meta_box() {
		$fm = new Fieldmanager_Group(
			[
				'name'           => '_amzn_product_information',
				'limit'          => 0,
				'label'          => __( 'Add Product', 'pmc-amzn-onsite' ),
				'label_macro'    => [ 'Product: %s', 'title' ],
				'add_more_label' => __( 'Add another Product', 'pmc-amzn-onsite' ),
				'sortable'       => true,
				'collapsible'    => true,
				'collapsed'      => false,
				'extra_elements' => 0,
				'children'       => [
					'title'             => new Fieldmanager_Textfield(
						[
							'label'            => __( 'Title', 'pmc-amzn-onsite' ),
							'name'             => 'title',
							'validation_rules' => [
								'required' => true,
							],
						]
					),
					'alternative_title' => new Fieldmanager_Textfield(
						[
							'label' => __( 'Amazon Alternative Title', 'pmc-amzn-onsite' ),
							'name'  => 'alternative_title',
						]
					),
					'summary'           => new Fieldmanager_RichTextarea(
						[
							'label' => __( 'Product Summary', 'pmc-amzn-onsite' ),
							'name'  => 'summary',
						]
					),
					'description'       => new Fieldmanager_RichTextarea(
						[
							'label' => __( 'Product Description', 'pmc-amzn-onsite' ),
							'name'  => 'description',
						]
					),
					'product_link'      => new Fieldmanager_Link(
						[
							'label' => __( 'Product Link[Amazon only]', 'pmc-amzn-onsite' ),
							'name'  => 'product_link',
						]
					),
					'product_id'        => new Fieldmanager_Textfield(
						[
							'label'            => __( 'Product ID/ASIN[Amazon only]', 'pmc-amzn-onsite' ),
							'name'             => 'product_id',
							'validation_rules' => [
								'required' => true,
							],
						]
					),
					'product_awards'    => new Fieldmanager_Textfield(
						[
							'label' => __( 'Award[Amazon only]', 'pmc-amzn-onsite' ),
							'name'  => 'product_awards',
						]
					),
				],
			]
		);

		$fm = apply_filters( 'amazon_onsite_available_product_fields', $fm );

		return $fm->add_meta_box( __( 'Product Information', 'pmc-amzn-onsite' ), apply_filters( 'amzn_onsite_post_types', [ 'post' ] ) );
	}
}
