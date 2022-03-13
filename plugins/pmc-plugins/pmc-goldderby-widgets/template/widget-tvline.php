<?php
/**
 * Template file for GD offsite predictions widget for tvline.com.
 */
?>

<div class="offsite-goldderby-prediction-widget">
	<div class="heading">
		<?php echo wp_kses_post( $instance['title'] ); ?>
		
		<div class="type">
			<?php echo esc_html( ucfirst( $user_type ) ); ?>' Predictions
		</div>
	</div>
	<div class="category-name-container">
		<div class="category-name">
			<?php echo esc_html( strtoupper( $widget_data['category_name'] ) ); ?>
		</div>
		<div class="odds-label">
			ODDS
		</div>
	</div>
	
	<ul>
		<?php
			if( ! empty( $widget_data['data'] ) ) {
				foreach( (array) $widget_data['data'] as $rank => $odds ) {
					$class_name = "arrow-none";
					if ( ! empty( $odds['movement'] ) ) {
						$class_name = "arrow-{$odds['movement']}";
					}
					?>
					<li>
						<div class="rank">
							<?php echo esc_html( $rank ); ?>.
						</div>
						
						<div class="odds-movement <?php echo esc_attr( $class_name ); ?>"></div>
						
						<div class="candidate">
							<?php echo esc_html( $odds['candidate_title'] ); ?>
							
							<?php if ( ! empty( $odds['related_candidate_title'] ) ) { ?>
								<div class="related-candidate">
									<?php echo esc_html( $odds['related_candidate_title'] ); ?>
								</div>
							<?php } ?>
						</div>
						
						<div class="odds">
							<?php echo esc_html( $odds['odds'] ); ?>
						</div>
					</li>
					<?php
				}
			}
		?>
	</ul>
	
	<?php
		$campaign = "#utm_source=network_widget&utm_campaign=tvline&utm_medium=web";

		$user      = rtrim( $user_type, "s" );
		$link      = strtolower( "http://www.goldderby.com/odds/{$user}-odds/{$slug}/{$campaign}" );
		$user_type = strtolower( $user_type );
	?>
	
	<div class="buttons-area">
		<a href="<?php echo esc_url( $link ); ?>" class="button">See All Categories</a>
		<a href="<?php echo esc_url( "http://www.goldderby.com/member-login/{$campaign}"); ?>" class="button">Make Your Predictions</a>
	</div>
	
	<div class="clear"></div>
	
	<div class="logo-area">
		<div class="powered-by">Powered by:</div>
		<a href="<?php echo esc_url( "http://www.goldderby.com/{$campaign}"); ?>" class="logo"> 
			<img src="<?php echo esc_url( plugins_url( 'images/tvl-gd-logo.png', __DIR__ ) ); ?>">
		</a>
	</div>
</div>