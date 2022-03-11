<?php
namespace PMC\Core\Inc\Widgets;

/**
 * Trending Now widget
 */
class Trending_Now extends \FM_Widget {

	use Traits\Templatize;

	/**
	 * Trending_Now_Widget constructor.
	 */
	public function __construct() {
		parent::__construct( 'trending_now', __( 'Trending Now', 'pmc-core' ), [
			'classname'   => 'trending-now-widget',
			'description' => __( 'A list of trending posts.', 'pmc-core' ),
		] );
	}

	/**
	 * Define the fields that should appear in the widget.
	 *
	 * @return array Fieldmanager fields.
	 */
	protected function fieldmanager_children() {

		$image_sizes = \PMC\Image\get_intermediate_image_sizes();

		return [
			'title'      => new \Fieldmanager_TextField( __( 'Title', 'pmc-core' ) ),
			'type'       => new \Fieldmanager_Select(
				[
					'label'   => __( 'Type', 'pmc-core' ),
					'options' => [
						'most_commented' => __( 'Most Commented', 'pmc-core' ),
						'most_viewed'    => __( 'Most Viewed', 'pmc-core' ),
					],
				]
			),
			'count'      => new \Fieldmanager_Select(
				[
					'label'   => __( 'Count', 'pmc-core' ),
					'options' => array_combine( range( 1, 10 ), range( 1, 10 ) ),
				]
			),
			'period'     => new \Fieldmanager_Select(
				[
					'label'         => __( 'Period', 'pmc-core' ),
					'options'       => [
						'1'   => __( 'Last 1 day', 'pmc-core' ),
						'7'   => __( 'Last 7 days', 'pmc-core' ),
						'15'  => __( 'Last 15 days', 'pmc-core' ),
						'30'  => __( 'Last 30 days', 'pmc-core' ),
						'60'  => __( 'Last 60 days', 'pmc-core' ),
						'90'  => __( 'Last 90 days', 'pmc-core' ),
						'180' => __( 'Last 6 months', 'pmc-core' ),
						'365' => __( 'Last 1 year', 'pmc-core' ),
					],
					'default_value' => '30',
				]
			),
			'image_size' => new \Fieldmanager_Select(
				[
					'label'   => __( 'Image Size', 'pmc-core' ),
					'options' => array_combine( $image_sizes, $image_sizes ),
				]
			),
		];
	}
}
