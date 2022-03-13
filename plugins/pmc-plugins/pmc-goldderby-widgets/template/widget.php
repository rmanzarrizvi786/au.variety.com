<div id="offsite-goldderby-prediction-widget">
	<?php
	echo wp_kses_post( $args['before_title'] );
	?>
	<span class="title"> <?php echo wp_kses_post( $instance['title'] ); ?>
	</span>
	<?php
	echo wp_kses_post( $args['after_title'] );
	?>
	<div class="widget-wrap">
	<div class="offsite-goldderby-subtitle-text">
		<div class="candidates-count"><?php echo esc_html( $widget_data['category_name'] ); ?><i> Predictions by <?php echo esc_html( number_format( $total_no_of_candidates ) ); ?> <?php echo esc_html( $user_type ); ?></i></div>
		<div class="odds-txt">Odds</div>
		<div style="clear: both"></div>
	</div>
	<div class="offsite-goldderby-hr"></div>
	<div class="row-wrap">
		<?php
			if( ! empty( $widget_data['data'] ) ) {
				foreach( $widget_data['data'] as $sno => $odds ) {
					$class_name = "fa fa-arrows-h";
					if ( ! empty( $odds['movement'] ) && 'none' !== $odds['movement'] ) {
						$class_name = "fa fa-arrow-{$odds['movement']} {$odds['movement']}";
					}

					?>
					<div class="offsite-goldderby-prediction-row">
						<div class="sno"><?php echo esc_html($sno); ?></div>
						<div class="candidate">
							<?php
							echo wp_kses_post( $odds['candidate_title'] );
							if ( $odds['related_candidate_title'] ) {
								echo wp_kses_post( '<br/>' . $odds['related_candidate_title'] );
							}
							?>
						</div>
						<div class="odds"><?php echo esc_html($odds['odds']) ?></div>
						<div class="odds-movement <?php echo esc_attr( $class_name ); ?>"></div>
					</div>
					<?php
				}
			}
		?>
	</div>
	<div class="experts">
		<?php
		$sitename = '';
		if ( defined( 'PMC_SITE_NAME' ) ) {
			$sitename = strtolower( PMC_SITE_NAME );
		}
		$campaign = "#utm_source=network_widget&utm_campaign={$sitename}&utm_medium=web";

		$user      = rtrim( $user_type, "s" );
		$link      = "http://www.goldderby.com/odds/$user-odds/$slug/$campaign";
		$link      = strtolower( $link );
		$user_type = strtolower( $user_type );
		?>
		<a class="link" href="<?php echo esc_url( $link ) ?>"><span>See more <?php echo esc_html( $user_type ); ?>’ predictions</span></a>
	</div>
	<div class="offsite-goldderby-prediction-bottom-row"><a href="<?php echo esc_url( "http://www.goldderby.com/member-login/{$campaign}"); ?>"> Make your predictions and win prizes</a></div>
	<div class="offsite-goldderby-prediction-logo-row">
		<a href="<?php echo esc_url( "http://www.goldderby.com/{$campaign}"); ?>">
			<img src="<?php echo esc_url( plugins_url( 'images/GD_Logo.png', __DIR__ ) ); ?>">
		</a>
	</div>
	</div>
</div>