<?php

	/**
	 * Class containing PMC Stocks Global Index widget.
	 *
	 * @since 2016-07-18 - Mike Auteri - PPT-6906
	 * @version 2016-07-18 - Mike Auteri - PPT-6906
	 *
	 */
	namespace PMC\Stocks;

	use \PMC;

	if ( class_exists( '\FM_Widget' ) ) {

		class Widget_Global_Index extends \FM_Widget {

			const widget_id = 'pmc_stocks_global_index_widget';

			/*
			 * Defines the widget name
			 */
			function __construct() {
				// Instantiate the parent object
				parent::__construct( self::widget_id, __( 'PMC Stocks Global Index', 'pmc-plugins' ), array (
					'description' => 'Render PMC Stocks Global Index widget',
				) );
			} // __construct

			public function widget( $args, $instance ) {
				$api = Api::get_instance();

				$data = $api->stock_index_data( '1 month ago' );

				//$instance['graph_days'] = intval( $instance['graph_days'] ) ?: 7;
				$instance[ 'graph_days' ] = 30;

				$summary = $api->stock_summary_data();

				$stocks = $api->stock_data();

				$gainers = array ();
				$decliners = array ();

				foreach ( $stocks as $stock ) {
					$sample = floatval( $stock[ 'usd_adj_1m_pct_change' ] );
					if ( $sample > 0 ) {
						$gainers[] = array (
							'symbol' => $stock[ 'symbol' ],
							'name' => $stock[ 'name' ],
							'usd_adj_1m_pct_change' => $stock[ 'usd_adj_1m_pct_change' ],
						);
					} else if ( $sample < 0 ) {
						$decliners[] = array (
							'symbol' => $stock[ 'symbol' ],
							'name' => $stock[ 'name' ],
							'usd_adj_1m_pct_change' => $stock[ 'usd_adj_1m_pct_change' ],
						);
					}
				}

				usort( $gainers, function( $a, $b ) {
					$a_value = floatval( $a[ 'usd_adj_1m_pct_change' ] );
					$b_value = floatval( $b[ 'usd_adj_1m_pct_change' ] );
					if ( $a_value < $b_value ) {
						return 1;
					} else if ( $a_value === $b_value ) {
						return 0;
					} else {
						return - 1;
					}
				} );

				usort( $decliners, function( $a, $b ) {
					$a_value = floatval( $a[ 'usd_adj_1m_pct_change' ] );
					$b_value = floatval( $b[ 'usd_adj_1m_pct_change' ] );
					if ( $a_value > $b_value ) {
						return 1;
					} else if ( $a_value === $b_value ) {
						return 0;
					} else {
						return - 1;
					}
				} );

				$instance[ 'gainers' ] = intval( $instance[ 'gainers' ] ) ? : 3;
				$gainers = array_slice( $gainers, 0, $instance[ 'gainers' ] );

				$instance[ 'decliners' ] = intval( $instance[ 'decliners' ] ) ? : 3;
				$decliners = array_slice( $decliners, 0, $instance[ 'decliners' ] );

				$logo_id = ( !empty( $instance[ 'logo_id' ] ) ) ? $instance[ 'logo_id' ] : 0;
				$logo = ( $logo_id > 0 ) ? wp_get_attachment_image( $logo_id ) : '';

				$widget_data = array (
					'title' => $instance[ 'title' ],
					'url' => $instance[ 'url' ],
					'logo' => $logo,
					'logo_url' => $instance[ 'logo_url' ],
				);

				Plugin::get_instance()->enqueue();

				echo wp_kses_post( $args[ 'before_widget' ] );
				echo PMC::render_template( PMC_STOCKS_ROOT . '/assets/templates/global-index.php', array (
					'widget_data' => $widget_data,
					'data' => $data,
					'summary' => $summary,
					'gainers' => $gainers,
					'decliners' => $decliners,
				) );
				echo wp_kses_post( $args[ 'after_widget' ] );
			}

			/**
			 * Define the fields that should appear in the widget.
			 *
			 * @return array Fieldmanager fields.
			 */
			protected function fieldmanager_children() {
				return [
					'title' => new \Fieldmanager_Textfield( [
						'label' => __( 'Title:', 'pmc-plugins' ),
						'default_value' => 'Global Stock Index',
					] ),
					'url' => new \Fieldmanager_Link( [
						'label' => __( 'Business Page URL:', 'pmc-plugins' ),
					] ),
					'gainers' => new \Fieldmanager_Textfield( [
						'label' => __( 'Number of Gainers to show:', 'pmc-plugins' ),
						'default_value' => 3,
						'attributes' => [
							'size' => 2,
						],
					] ),
					'decliners' => new \Fieldmanager_Textfield( [
						'label' => __( 'Number of Decliners to show:', 'pmc-plugins' ),
						'default_value' => 3,
						'attributes' => [
							'size' => 2,
						],
					] ),
					'logo_id' => new \Fieldmanager_Media( [
						'label' => __( 'Sponsor Logo:', 'pmc-plugins' ),
						'button_label' => __( 'Select logo:', 'pmc-plugins' ),
					] ),
					'logo_url' => new \Fieldmanager_Link( [
						'label' => __( 'Sponsor URL:', 'pmc-plugins' ),
						'description' => __( 'Optional link to the sponsor\'s website.', 'pmc-plugins' ),
					] ),
				];
			}
		}

	}
// EOF