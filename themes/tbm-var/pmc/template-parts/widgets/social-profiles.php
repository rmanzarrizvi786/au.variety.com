<ul class="pmc-core-social-profiles-widget">
	<?php foreach ( $data as $key => $value ) : ?>
		<?php
		$path = locate_template( 'template-parts/svg/' . sanitize_key( $key ) . '.php' );
		?>
		<?php if ( ! empty( $value ) && ! empty( $path ) ) : ?>
			<li class="<?php echo esc_attr( 'pmc-core-social-profiles-widget-' . sanitize_key( $key ) ); ?>">
				<a href="<?php echo esc_url( $value ); ?>" target="_blank"><?php echo \PMC::render_template( $path ); ?></a>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>
