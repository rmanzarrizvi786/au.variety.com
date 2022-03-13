<?php
/**
Plugin Name: PMC marketplace ads
License: PMC Proprietary. All rights reserved.
*/
/**
 * This is sort of a clever way of implementing the "marketplace" widget, which in itself, actually holds many potential single ad widgets.
 * The idea here is that there's another widgetized area called "Marketplace Ads", and a widget that actually outputs that "Marketplace Ads" widgetized area.
 */

class PMC_Marketplace_Ads extends WP_Widget {

	public function __construct() {
		// Instantiate the parent object
		parent::__construct( false, 'PMC - Marketplace Ads' );

		// we need to prevent this widget from ever being inside of the Marketplace Ads sidebar, to prevent an infinite loop
		add_filter( 'sidebars_widgets', array( $this, 'prevent_recursion' ), 1 );
	}

	public function widget( $args, $instance ) {
		extract( $args );
		?>
		<style type="text/css">
			.markplace-ads {
				padding: 20px 0;
			}

			.markplace-ads ul {
				list-style: none;
			}

			.marketplace-title {
				display: none;
			}
		</style>
		<?php
		echo $before_widget;

		// support GA tracking
		$ga_event_tracking_data = apply_filters( 'pmc_marketplace_ads_event_tracking_data', '' );
		printf('<div class="markplace-ads" data-event-tracking="%1$s" >', esc_attr( $ga_event_tracking_data ) );
		unset( $ga_event_tracking_data );


		$sidebar_id = 'pmc-marketplace-ads-' . sanitize_title_with_dashes( $instance['marketplace'] );

		echo "<h3 id='" . esc_attr( $sidebar_id ) . "' class='widget-title marketplace-title'><span>Marketplace</span></h3>";
		echo '<ul aria-labelledby="' . esc_attr( $sidebar_id ) . '">';
		dynamic_sidebar( $sidebar_id );
		echo '</ul>';
		echo "</div>";
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {

		$instance = array();
		$instance['marketplace'] = sanitize_title_with_dashes( $new_instance['marketplace'] );

		return $instance;
	}

  	public function form( $instance ) {

		  $instance = wp_parse_args( (array) $instance, array( 'marketplace' => 1 ) );

		  $marketplace = sanitize_title_with_dashes( $instance['marketplace'] );

		  $marketplace_sidebar_in_site = pmc_marketplace_sidebar_instances();
		?>
	  		<p>
		  	Place ads in the marketplace widget by adding them to the widgetized area,"Marketplace Ads".
		  	</p>
	  		<p>
				<label for="<?php echo $this->get_field_id( 'marketplace' ); ?>"></label>
				<select id="<?php echo $this->get_field_id( 'marketplace' ); ?>" name="<?php echo $this->get_field_name( 'marketplace' ); ?>" class="widefat" style="width:100%;">
					<?php

		 		if( is_array( $marketplace_sidebar_in_site ) ) {

		  			$count = count( $marketplace_sidebar_in_site );

		  			for( $i=0; $i< $count; $i++ ) {

		  				$id = sanitize_title_with_dashes( $marketplace_sidebar_in_site[$i] );
					?>
					<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $marketplace, $id ) ?>>PMC Marketplace Ads #<?php echo esc_html( $id ); ?></option>
				<?php
						}
				} ?>
				</select>
			</p>
		<?php
  	}

  	public function prevent_recursion( $sidebar_widgets ) {

		$marketplace_sidebar_in_site = pmc_marketplace_sidebar_instances();

		if( is_array( $marketplace_sidebar_in_site ) ) {

			foreach( $marketplace_sidebar_in_site as $marketplace ){

				$marketplace = 'pmc-marketplace-ads-'.sanitize_title_with_dashes( $marketplace );

				if ( isset( $sidebar_widgets[$marketplace] ) && ! empty( $sidebar_widgets[$marketplace] ) ) {
					foreach ( $sidebar_widgets[$marketplace] as $key => $widget_name ) {
						if ( strstr( $widget_name, 'pmc_marketplace_ads' ) ) {
							unset( $sidebar_widgets[$marketplace][$key] );
							wp_unregister_sidebar_widget( $widget_name );
						}
					}
				}
			}
		}

		return $sidebar_widgets;
  	}
}

/*
 * Return names & ids of marketplace sidebar.
 * Also count of arry will tell the no of instances.
 */
function pmc_marketplace_sidebar_instances() {

	$default = array( 1, 2, 3 );

	$modified_marketplaces = apply_filters( 'pmc_marketplace_sidebar_instances', $default );

	if( is_array( $modified_marketplaces ) ) {
		return $modified_marketplaces;
	}

	return $default;
}

function pmc_register_marketplace_ads_widget() {
	// created widgetized area

	$marketplace_instances = pmc_marketplace_sidebar_instances();

	if( is_array( $marketplace_instances ) ) {

		$count = count( $marketplace_instances );

		for( $i=0; $i< $count; $i++ ) {

			$id = sanitize_title_with_dashes( $marketplace_instances[$i] );

			register_sidebar( array(
				'name'          => 'PMC Marketplace Ads #'.$id,
				'id'            => 'pmc-marketplace-ads-'.$id,
				'description'   => 'Populates the Marketplace widget',
				'before_widget' => '<li id="%1$s" class="marketplace-ad %2$s">',
				'after_widget'  => '</li>',
			) );
		}
	}

    // register the widget that holds the widgetized area
	register_widget( 'PMC_Marketplace_Ads' );
}

add_action( 'widgets_init', 'pmc_register_marketplace_ads_widget', 20 );
