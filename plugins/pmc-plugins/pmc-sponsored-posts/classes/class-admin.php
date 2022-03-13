<?php
namespace PMC\Sponsored_Posts;

use PMC\Global_Functions\Traits\Singleton;
use PMC\Post_Options\API;
use PMC\Post_Options\Taxonomy;

/**
 * Class Admin
 */
class Admin {

	use Singleton;

	/**
	 * @var \PMC\Post_Options\API
	 */
	protected $_post_options;

	/**
	 * Admin constructor.
	 */
	protected function __construct() {
		$this->_post_options = API::get_instance();

		$this->_setup_hooks();
	}

	/**
	 * Setup hooks.
	 *
	 * @return void
	 */
	protected function _setup_hooks() : void {
		// Priority is 9 for UI placement.
		add_action( 'pmc_core_global_curation_modules', [ $this, 'global_curation_modules' ], 9 );
		add_action( 'widgets_init', [ $this, 'register_widget' ] );
		add_action( 'init', [ $this, 'register_post_option' ] );
	}

	/**
	 * Register post option term to filter sponsored posts.
	 *
	 * @return void
	 */
	public function register_post_option() : void {
		$term = Utility::get_instance()->get_post_option();

		$this->_post_options->register_global_options(
			[
				sanitize_key( $term['slug'] ) => [
					'label' => sanitize_text_field( $term['name'] ),
				],
			]
		);
	}

	/**
	 * Add Sponsored Posts to Global Curation menu.
	 *
	 * @param array $modules
	 *
	 * @return array
	 */
	public function global_curation_modules( array $modules ) : array {
		return array_merge(
			[
				'pmc_sponsored_posts' => [
					'label'    => __( 'Sponsored Posts', 'pmc-sponsored-posts' ),
					'name'     => 'tab_pmc_sponsored_posts',
					'children' => $this->_sponsored_posts_module(),
				],
			],
			$modules
		);
	}

	/**
	 * Checks if a main group is truly empty (no posts) and can be deleted in UI.
	 *
	 * @param $values
	 *
	 * @return bool
	 */
	public function is_main_group_empty( array $values ) : bool {
		if ( empty( $values['post_data'] ) || ! is_iterable( $values['post_data'] ) ) {
			return true;
		}

		$empty = true;

		foreach ( $values['post_data'] as $value ) {
			if ( is_array( $value['sponsored_post'] ) && 0 !== intval( $value['sponsored_post'][0] ) ) {
				$empty = false;
				break;
			}
		}

		return $empty;
	}

	/**
	 * Checks if a post group is truly empty and can be deleted in UI.
	 *
	 * @param $values
	 *
	 * @return bool
	 */
	public function is_post_group_empty( array $values ) : bool {
		if ( empty( $values['sponsored_post'] ) ) {
			return true;
		}

		if (
			is_array( $values['sponsored_post'] )
			&& 0 !== intval( $values['sponsored_post'][0] )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Set fields for Sponsored Posts.
	 *
	 * @return \Fieldmanager_Group[]
	 */
	protected function _sponsored_posts_module() : array {
		return [
			'pmc_sponsored_posts' => new \Fieldmanager_Group(
				[
					'label'          => __( 'Date Range', 'pmc-sponsored-posts' ),
					'name'           => 'pmc_sponsored_posts',
					'limit'          => 0,
					'sortable'       => true,
					'group_is_empty' => [ $this, 'is_main_group_empty' ],
					'extra_elements' => 0,
					'add_more_label' => __( 'Add another date range', 'pmc-sponsored-posts' ),
					'children'       => [
						'start_date' => new \Fieldmanager_Datepicker(
							[
								'label'            => __( 'Start Date', 'pmc-sponsored-posts' ),
								'name'             => 'start_date',
								'store_local_time' => true,
								'field_class'      => 'throwaway alignleft',
								'default_value'    => time(),
							]
						),
						'end_date'   => new \Fieldmanager_Datepicker(
							[
								'label'            => __( 'End Date', 'pmc-sponsored-posts' ),
								'name'             => 'end_date',
								'store_local_time' => true,
								'default_value'    => time(),
							]
						),
						'post_data' => new \Fieldmanager_Group(
							[
								'label'          => __( 'Post Data', 'pmc-sponsored-posts' ),
								'name'           => 'post_data',
								'limit'          => 0,
								'sortable'       => true,
								'group_is_empty' => [ $this, 'is_post_group_empty' ],
								'extra_elements' => 0,
								'add_more_label' => __( 'Add another post', 'pmc-sponsored-posts' ),
								'children'       => [
									'sponsored_post' => new \Fieldmanager_Zone_Field(
										[
											'accept_from_other_zones' => true,
											'name'         => 'sponsored_post',
											'post_limit'   => 1,
											'placeholders' => 1,
											'query_args'   => [
												'post_type' => apply_filters(
													'pmc_sponsored_posts_post_types',
													[ 'post' ]
												),
												'tax_query' => [ // phpcs:ignore
													[
														'taxonomy' => Taxonomy::NAME,
														'field'    => 'slug',
														'terms'    => sanitize_key(
															Utility::get_instance()->get_post_option()['slug']
														),
													],
												],
											],
										]
									),
									'sponsored_by' => new \Fieldmanager_TextField(
										[
											'label' => __( 'Sponsored By (optional)', 'pmc-sponsored-posts' ),
											'name'  => 'sponsored_by',
										]
									),
									'sponsor_logo' => new \Fieldmanager_Media(
										[
											'label'        => __( 'Sponsor Logo (optional)', 'pmc-sponsored-posts' ),
											'name'         => 'sponsor_logo',
											'button_label' => __( 'Add Logo', 'pmc-sponsored-posts' ),
											'modal_title'  => __( 'Select Logo', 'pmc-sponsored-posts' ),
											'modal_button_label' => __( 'Add Logo', 'pmc-sponsored-posts' ),
											'preview_size' => 'medium',
										]
									),
								],
							]
						),
					],
				]
			),
		];
	}

	/**
	 * Registers the PMC Sponsored Posts widget.
	 *
	 * @return void
	 */
	public function register_widget() : void {
		register_widget( Widget::class );
	}

}
