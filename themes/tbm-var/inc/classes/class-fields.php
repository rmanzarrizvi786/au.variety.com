<?php
/**
 * Fields
 *
 * Hooks for registering Field Manager Fields.
 *
 * @package pmc-variety-2017
 * @since 2018.04.17
 */

namespace Variety\Inc;

use \PMC\Global_Functions\Traits\Singleton;
use Variety\Inc\Widgets\Attend;

/**
 * Class Fields
 *
 * @since 2018.04.17
 * @see \PMC\Global_Functions\Traits\Singleton
 */
class Fields {

	use Singleton;

	protected $_post_types = [ 'post' ];

	/**
	 * Class constructor.
	 *
	 * Initializes the theme assets.
	 *
	 * @since 2018.04.17
	 */
	protected function __construct() {
		$this->_setup_hooks();
	}

	protected function _setup_hooks() {
		/**
		 * Filters
		 */
		add_filter( 'pmc_core_global_curation_modules', [ $this, 'global_curation_modules' ] );

		/**
		 * Actions
		 */
		add_action( 'fm_post_pmc_featured', [ $this, 'watch_link_field' ], 20 );
		add_action( 'fm_post_pmc_featured', [ $this, 'hear_details_field' ], 20 );

		foreach ( $this->_post_types as $post_type ) {
			add_action( "fm_post_{$post_type}", [ $this, 'add_meta' ] );
		}
	}

	/**
	 * Fetch the fields for Global Curation.
	 *
	 * @codeCoverageIgnore // Testing covered by FieldManager Plugin. Similar methods do not have unit tests.
	 *
	 * @param array $modules
	 *
	 * @return array Array
	 */
	public function global_curation_modules( $modules ) {
		return array_merge(
			[
				'variety_business_events' => [
					'label'    => esc_html__( 'VarietyBusiness Event', 'pmc-variety' ),
					'children' => Attend::get_fields(),
				],
			],
			[
				'variety_what_to_watch_sponsor' => [
					'label'    => esc_html__( 'Variety "What To Watch" Sponsor Details', 'pmc-variety' ),
					'children' => [
						'variety_heading_text'      => new \Fieldmanager_TextField(
							[
								'label' => __( 'Heading Text', 'pmc-variety' ),
							]
						),
						'variety_logline_text'      => new \Fieldmanager_TextField(
							[
								'label' => __( 'Logline Text', 'pmc-variety' ),
							]
						),
						'variety_sponsored_by_text' => new \Fieldmanager_TextField(
							[
								'label' => __( '"Sponsored By" Text', 'pmc-variety' ),
							]
						),
						'variety_sponsor_link'      => new \Fieldmanager_Link(
							[
								'label' => __( 'Sponsor Link', 'pmc-variety' ),
							]
						),
					],
				],
			],
			[
				'variety_documentaries' => [
					'label'    => esc_html__( 'Variety Documentaries', 'pmc-variety' ),
					'children' => [
						'variety_heading_text'          => new \Fieldmanager_TextField(
							[
								'label' => __( 'Heading Text', 'pmc-variety' ),
							]
						),
						'variety_sponsored_by_text'     => new \Fieldmanager_TextField(
							[
								'label' => __( '"Powered By" Text', 'pmc-variety' ),
							]
						),
						'variety_sponsor_link'          => new \Fieldmanager_Link(
							[
								'label' => __( '"Powered By" Link', 'pmc-variety' ),
							]
						),
						'variety_classics_heading_text' => new \Fieldmanager_TextField(
							[
								'label' => __( 'Classics Heading Text', 'pmc-variety' ),
							]
						),
						'variety_classics_logline_text' => new \Fieldmanager_TextField(
							[
								'label' => __( 'Classics Logline Text', 'pmc-variety' ),
							]
						),
						'variety_video_btn_txt'         => new \Fieldmanager_TextField(
							[
								'label' => __( 'More Videos Button Text', 'pmc-variety' ),
							]
						),
						'variety_video_btn_link'        => new \Fieldmanager_Link(
							[
								'label' => __( 'More Videos Link', 'pmc-variety' ),
							]
						),
					],
				],
			],
			[
				'variety_what_to_hear' => [
					'label'    => esc_html__( 'Variety What To Hear', 'pmc-variety' ),
					'children' => [
						'variety_sponsored_by_text'        => new \Fieldmanager_TextField(
							[
								'label' => __( '"Powered By" Text', 'pmc-variety' ),
							]
						),
						'variety_sponsor_link'             => new \Fieldmanager_Link(
							[
								'label' => __( '"Powered By" Link', 'pmc-variety' ),
							]
						),
						'variety_vy_recommends_header_copy' => new \Fieldmanager_TextField(
							[
								'label' => __( 'Variety Recommends Module Header Text', 'pmc-variety' ),
							]
						),
						'variety_vy_recommends_logline_copy' => new \Fieldmanager_TextField(
							[
								'label' => __( 'Variety Recommends Module Logline Text', 'pmc-variety' ),
							]
						),
						'variety_vy_podcasts_header_copy'  => new \Fieldmanager_TextField(
							[
								'label'       => __( 'Variety Produced Podcasts Module Header Text', 'pmc-variety' ),
								'description' => __( 'Located on What To Hear page.', 'pmc-variety' ),
							]
						),
						'variety_vy_podcasts_logline_copy' => new \Fieldmanager_TextField(
							[
								'label' => __( 'Variety Produced Podcasts Module Logline Text', 'pmc-variety' ),
							]
						),
						'variety_album_header_copy'        => new \Fieldmanager_TextField(
							[
								'label' => __( 'Album Reviews Module Header Text', 'pmc-variety' ),
							]
						),
						'variety_album_logline_copy'       => new \Fieldmanager_TextField(
							[
								'label' => __( 'Album Reviews Module Logline Text', 'pmc-variety' ),
							]
						),
					],
				],
			],
			[
				'variety_trending_tv' => [
					'label'    => esc_html__( 'Variety Trending TV', 'pmc-variety' ),
					'children' => [
						'variety_heading_text'         => new \Fieldmanager_TextField(
							[
								'label'         => __( 'Heading Text', 'pmc-variety' ),
								'default_value' => 'Trending TV',
							]
						),
						'variety_sponsored_by_text'    => new \Fieldmanager_TextField(
							[
								'label'         => __( '"Powered By" Text', 'pmc-variety' ),
								'default_value' => 'Powered By',
							]
						),
						'variety_sponsor_link'         => new \Fieldmanager_Link(
							[
								'label' => __( '"Powered By" Link', 'pmc-variety' ),
							]
						),
						'variety_sponsor_widget'       => new \Fieldmanager_TextField(
							[
								'label'         => __( 'Sponsor Widget Header', 'pmc-variety' ),
								'default_value' => 'DirectTV Trending',
							]
						),
						'variety_trending_social'      => new \Fieldmanager_Media(
							__( 'Social Image', 'pmc-variety' ),
							[
								'mime_type'    => 'image',
								'button_label' => 'Upload Image',
							]
						),
						'variety_trending_shows'       => new \Fieldmanager_TextField(
							[
								'label'         => __( 'Top Ten Trending Shows Header', 'pmc-variety' ),
								'default_value' => 'Top 10 Trending Shows',
							]
						),
						'variety_engagement'           => new \Fieldmanager_TextField(
							[
								'label'         => __( 'Top Three Engagement Header', 'pmc-variety' ),
								'default_value' => 'Engagement of Top 3 Shows',
							]
						),
						'variety_continental'          => new \Fieldmanager_TextField(
							[
								'label'         => __( 'Top Continental Shows Header', 'pmc-variety' ),
								'default_value' => 'Top 2 Shows in the Continental U.S.',
							]
						),
						'variety_trending_methodology' => new \Fieldmanager_RichTextArea(
							[
								'label' => __( 'Trending TV Methodology', 'pmc-variety' ),
							]
						),
					],
				],
			],
			$modules
		);
	}

	/**
	 * * Add Fieldmanager Meta Box for if article is free or not.
	 *
	 * @codeCoverageIgnore // Testing covered by FieldManager Plugin. Similar methods do not have unit tests.
	 * @throws \FM_Developer_Exception
	 */
	public function add_meta() {

		$fm = new \Fieldmanager_Group(
			[
				'name'           => 'variety_article_vip',
				'serialize_data' => false,
				'add_to_prefix'  => false,
				'children'       => [
					'variety_post_vip' => new \Fieldmanager_Checkbox(
						[
							'label'           => __( 'Is VIP Variety Article', 'pmc-variety' ),
							'default_value'   => 'N',
							'checked_value'   => 'Y',
							'unchecked_value' => 'N',
						]
					),
				],
			]
		);

		$fm->add_meta_box( __( 'Article Type', 'pmc-variety' ), $this->_post_types );
	}

	/**
	 * Add Fieldmanager Meta Box to manage 'Watch' links on Editorial Hub.
	 *
	 * @codeCoverageIgnore // Testing covered by FieldManager Plugin. Similar methods do not have unit tests.
	 */
	public function watch_link_field() {
		$fm = new \Fieldmanager_Group(
			[
				'name'     => 'variety_watch_link',
				'children' => [
					'variety_watch_url' => new \Fieldmanager_Link(
						[
							'label'       => __( 'Watch Link', 'pmc-variety' ),
							'description' => __( 'Link to watch featured media in What To Watch (Editorial) Hub.', 'pmc-variety' ),
						]
					),
					'variety_streamer'  => new \Fieldmanager_TextField(
						[
							'label'       => __( 'Streamer name', 'pmc-variety' ),
							'description' => __( 'Add name of the streamer for featured media in What To Watch (Editorial) Hub.', 'pmc-variety' ),
						]
					),
					'stream_date'       => new \Fieldmanager_Datepicker(
						[
							'label'       => __( 'Stream Date', 'pmc-variety' ),
							'description' => __( 'Add stream date for the featured media', 'pmc-variety' ),
						]
					),
					'new_arrival'       => new \Fieldmanager_Checkbox(
						[
							'label'       => __( 'New arrival', 'pmc-variety' ),
							'description' => __( 'Check to mark featured media as new arrival in What To Watch (Editorial) Hub.', 'pmc-variety' ),
						]
					),
				],
			]
		);

		$fm->add_meta_box( __( 'Watch Details', 'pmc-variety' ), 'pmc_featured' );
	}

	/**
	 * Add Fieldmanager Meta Box to manage additional options for What To Hear page.
	 *
	 * @codeCoverageIgnore // Testing covered by FieldManager Plugin. Similar methods do not have unit tests.
	 */
	public function hear_details_field() {
		$fm = new \Fieldmanager_Group(
			[
				'name'     => 'variety_hear_details',
				'children' => [
					'variety_podcast_genre' => new \Fieldmanager_TextField(
						[
							'label'       => __( 'Podcast Genre or Album Title', 'pmc-variety' ),
							'description' => __( 'Add podcast genre/album title on What To Hear page.', 'pmc-variety' ),
						]
					),
					'variety_ab_author'     => new \Fieldmanager_TextField(
						[
							'label'       => __( 'Audiobook Author', 'pmc-variety' ),
							'description' => __( 'Black text for Audible/Variety Recommends module on What To Hear page.', 'pmc-variety' ),
						]
					),
					'variety_ab_narrator'   => new \Fieldmanager_TextField(
						[
							'label'       => __( 'Audiobook Narrator', 'pmc-variety' ),
							'description' => __( 'Grey text for Audible/Variety Recommends module on What To Hear page.', 'pmc-variety' ),
						]
					),
					'variety_watch_url'     => new \Fieldmanager_Link(
						[
							'label'       => __( 'Listen/Subscribe Link', 'pmc-variety' ),
							'description' => __( 'Set Listen/Subscribe link on What To Hear page.', 'pmc-variety' ),
						]
					),
				],
			]
		);

		$fm->add_meta_box( __( 'Hear Details', 'pmc-variety' ), 'pmc_featured' );
	}

}
