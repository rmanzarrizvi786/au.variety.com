<div id="pmc-social-share-bar-modal">
	<div id="share-container">
		<div class="submit">
			<span>
				<label><?php esc_html_e( 'Setting Type', 'pmc-social-share-bar' ); ?></label>
				<?php
				if ( isset( $post_types ) && is_array( $post_types ) ) {
					printf( '<select id="%s">', esc_attr( 'pmc-post-type' ) );
					printf( '<option selected value="default">%s</option>', esc_html__( 'Default Configuration', 'pmc-social-share-bar' ) );
					foreach ( $post_types as $post_type ) {
						$label = trim( $post_type );
						$label = str_replace( array( '_', '-' ), ' ', $post_type );
						$label = ucfirst( $label );
						printf( '<option value="%s">%s</option>', esc_attr( $post_type ), esc_html( $label ) );
					}
					printf( '</select>' );
				}
				?>
			</span>
			<input id="pmc-social-share-bar-save" type="button" class="button-primary" value="<?php esc_html_e( 'Save Settings', 'pmc-social-share-bar' ); ?>">
			<input id="pmc-social-share-bar-reset" type="button" class="button-primary" value="<?php esc_html_e( 'Reset', 'pmc-social-share-bar' ); ?>">
			<div class="spinner"></div>
		</div>
		<div class="share-icons-box">
			<div class="share-box">
				<h2><?php esc_html_e( 'Primary Share Bar', 'pmc-social-share-bar' ); ?></h2>
				<ul id="primary-icons" class="share-buttons dropme">
					<?php
					foreach ( $social_share_icons as $id => $share_icons ) { ?>
						<li class="share-buttons-sortables share-buttons-draggable" id="<?php echo esc_attr( $id ); ?>">
							<a href="javascript:void(0);" class="<?php echo esc_attr( $share_icons->class ); ?>" target="_blank" title="<?php echo esc_attr( $share_icons->title ); ?>">
								<svg>
									<use xlink:href="#<?php echo esc_attr( 'pmc-social-share-bar-' . $id ); ?>"></use>
								</svg>
								<span class="primary-hide"><?php echo esc_html( $share_icons->name ); ?></span>
							</a>
						</li>
					<?php } ?>
					<?php if( ! empty( $lob_special_share_icons ) ) {
						foreach ( $lob_special_share_icons as $id => $share_icons ) { ?>
							<li style="border:1px solid red;" id="<?php echo esc_attr( $id ); ?>">
								<a href="javascript:void(0);" class="<?php echo esc_attr( $share_icons->class ); ?>" target="_blank" title="<?php echo esc_attr( $share_icons->title ); ?>">
								<svg>
									<use xlink:href="#<?php echo esc_attr( 'pmc-social-share-bar-' . $id ); ?>"></use>
								</svg>
									<span class="primary-hide"><?php echo esc_html( $share_icons->name ); ?></span>
								</a>
							</li>
						<?php }
					} ?>
				</ul>
			</div>

			<div class="share-box">
				<h2><?php esc_html_e( 'More Share Icons', 'pmc-social-share-bar' ); ?></h2>

				<div class="shareMore">
					<ul id="secondary-icons" class="shareModal dropme">
						<li><?php esc_html_e( 'Share This Article', 'pmc-social-share-bar' ); ?></li>
						<?php
						foreach ( $more_social_share_icons as $id => $more_share_icons ) { ?>
							<li class="share-buttons-sortables share-buttons-draggable" id="<?php echo esc_attr( $id ); ?>">
								<a href="javascript:void(0);" class="<?php echo esc_attr( $more_share_icons->class ); ?>" title="<?php echo esc_attr( $more_share_icons->title ); ?>">
									<svg>
										<use xlink:href="#<?php echo esc_attr( 'pmc-social-share-bar-' . $id ); ?>"></use>
									</svg>
									<span><?php echo esc_html( $more_share_icons->name ); ?></span>
								</a>
							</li>
						<?php } ?>

					</ul>
				</div>
			</div>
		</div>
	</div>
</div>



