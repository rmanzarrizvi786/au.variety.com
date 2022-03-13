<nav class="pmc-toc--navigation">
	<h4 class="lrv-u-text-align-center lrv-u-padding-a-050 lrv-u-color-white lrv-u-background-color-black lrv-u-margin-b-00"><?php esc_html_e( 'Table of Contents', 'pmc-toc' ); ?></h4>
	<ul class="lrv-u-flex lrv-u-flex-wrap-wrap lrv-u-background-color-grey-lightest lrv-u-padding-a-050 lrv-u-margin-b-1">
		<?php
		foreach ( $items as $item ) {
			$uid  = $item[4];
			$text = $item[3];
			?>
			<li class="pmc-toc--navigation-item lrv-u-line-height-large lrv-u-width-50p lrv-u-width-100p@mobile-max lrv-u-text-align-center@mobile-max">
				<a class="pmc-toc--navigation-anchor lrv-u-display-block lrv-u-whitespace-nowrap lrv-u-overflow-hidden" href="<?php echo esc_url( '#' . $uid ); ?>" title="<?php echo esc_attr( $text ); ?>">
					<?php
					$allowed_html = PMC::allowed_html( 'post', [ 'i', 'em' ] );
					echo wp_kses( $text, $allowed_html );
					?>
				</a>
			</li>
			<?php
		}
		?>
	</ul>
</nav>
