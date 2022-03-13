<?php
/**
 * Class containing PMC Stocks Market Movers widget.
 *
 * @since 2016-07-18 - Mike Auteri - PPT-6906
 * @version 2016-07-18 - Mike Auteri - PPT-6906
 *
 */
namespace PMC\Stocks;

use \PMC;

class Widget_Market_Movers extends \WP_Widget {

	const widget_id = 'pmc_stocks_market_movers_widget';

	/*
	 * Defines the widget name
	 */
	function __construct() {
		// Instantiate the parent object
		parent::__construct( self::widget_id, __( 'PMC Stocks Market Movers', 'pmc-plugins' ), array(
			'description' => 'Render PMC Stocks Market Movers widget',
		) );
	} // __construct

	public function widget( $args, $instance ) {
		$api = Api::get_instance();

		$stocks = $api->stock_data();

		$gainers   = array();
		$decliners = array();

		foreach ( $stocks as $stock ) {
			$sample = floatval( $stock['usd_adj_1m_pct_change'] );
			if ( 0 < $sample ) {
				$gainers[] = array(
					'symbol' => $stock['symbol'],
					'name'   => $stock['name'],
					'usd_adj_1m_pct_change' => $stock['usd_adj_1m_pct_change'],
				);
			} else if ( 0 > $sample ) {
				$decliners[] = array(
					'symbol' => $stock['symbol'],
					'name'   => $stock['name'],
					'usd_adj_1m_pct_change' => $stock['usd_adj_1m_pct_change'],
				);
			}
		}

		usort( $gainers, function( $a, $b ) {
			$a_value = floatval( $a['usd_adj_1m_pct_change'] );
			$b_value = floatval( $b['usd_adj_1m_pct_change'] );
			if ( $a_value < $b_value ) {
				return 1;
			} else if ( $a_value === $b_value ) {
				return 0;
			} else {
				return -1;
			}
		});

		usort( $decliners, function( $a, $b ) {
			$a_value = floatval( $a['usd_adj_1m_pct_change'] );
			$b_value = floatval( $b['usd_adj_1m_pct_change'] );
			if ( $a_value > $b_value ) {
				return 1;
			} else if ( $a_value === $b_value ) {
				return 0;
			} else {
				return -1;
			}
		});

		$instance['gainers'] = intval( $instance['gainers'] ) ?: 10;
		$gainers = array_slice( $gainers, 0, $instance['gainers'] );

		$instance['decliners'] = intval( $instance['decliners'] ) ?: 10;
		$decliners = array_slice( $decliners, 0, $instance['decliners'] );

		$widget_data = array(
			'title'      => sanitize_text_field( $instance['title'] ),
			'url'        => esc_url_raw( $instance['url'] ),
		);

		Plugin::get_instance()->enqueue();

		echo wp_kses_post( $args['before_widget'] );
		echo PMC::render_template ( PMC_STOCKS_ROOT . '/assets/templates/market-movers.php', array(
			'widget_data' => $widget_data,
			'gainers'     => $gainers,
			'decliners'   => $decliners,
		) );
		echo wp_kses_post( $args['after_widget'] );
	}

	public function form( $instance ) {
		$instance['title'] = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Market Movers', 'pmc-plugins' );
		$instance['gainers'] = intval( $instance['gainers'] ) ?: 10;
		$instance['decliners'] = intval( $instance['decliners'] ) ?: 10;
		$instance['url'] = ! empty( $instance['url'] ) ? $instance['url'] : '';

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>"><?php _e( 'Business Page URL:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'url' ) ); ?>" type="text" value="<?php echo esc_url( $instance['url'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'gainers' ) ); ?>"><?php _e( 'Number of Gainers to show:' ); ?></label>
			<input size="2" id="<?php echo esc_attr( $this->get_field_id( 'gainers' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'gainers' ) ); ?>" type="text" value="<?php echo intval( $instance['gainers'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'decliners' ) ); ?>"><?php _e( 'Number of Decliners to show:' ); ?></label>
			<input size="2" id="<?php echo esc_attr( $this->get_field_id( 'decliners' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'decliners' ) ); ?>" type="text" value="<?php echo intval( $instance['decliners'] ); ?>" />
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ! empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['gainers'] = intval( $new_instance['gainers'] ) ?: 10;
		$instance['decliners'] = intval( $new_instance['decliners'] ) ?: 10;
		$instance['url'] = ! empty( $new_instance['url'] ) ? esc_url_raw( $new_instance['url'] ) : '';

		return $instance;
	}

}

// EOF