<?php if ( $byline_twitter ) : ?>
	<a class="twt-link" href="<?php echo esc_url( $byline_twitter->_pmc_user_twitter ); ?>" target="_blank">
		&nbsp;
		<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/svg/twitter.php', [], true ); ?>
		&nbsp;@<?php echo esc_html( $byline_twitter->user_nicename ); ?>
	</a>
<?php endif; ?>
