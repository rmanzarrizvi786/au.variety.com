<div class="share-container">
	<ul class="share-buttons">
		<?php foreach ( $primary_share_icons as $id => $share_icon ) : ?>
			<li>
				<?php if( $share_icon->is_javascript() ) : ?>
					<a href="<?php echo esc_attr( $share_icon->url ); /* not using esc_url since this value is javascript */ ?>"
				<?php else: ?>
					<a data-href="<?php echo esc_url( $share_icon->url ); ?>" href="<?php echo esc_url( $share_icon->url ); ?>"
				 <?php endif; ?>
					class="<?php echo esc_attr( $share_icon->class ); ?>"
					<?php if( $share_icon->is_popup() ) : ?>
						target="_blank"
					<?php endif; ?>
						title="<?php echo esc_attr( $share_icon->title ); ?>">
						<svg>
							<use xlink:href="#<?php echo esc_attr( 'pmc-social-share-bar-' . $id ); ?>"></use>
						</svg>
						<?php if( $share_icon->is_comment() ) : ?>
							<span>
								<?php echo esc_html( $share_icon->get_comment_count() ) ?>
							</span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
				<li>
					<a href="#share-more" class="showShareMore btn-more" target="_blank" title="<?php esc_attr_e( 'More Share Options', 'pmc-social-share-bar' ); ?>">
					<svg>
						<use xlink:href="#pmc-social-share-bar-show-more"></use>
					</svg>
				</a>
			</li>
		</ul>
	<div class="shareMore">
		<ul class="shareModal">
			<li>
				<?php esc_html_e( 'Share This Article', 'pmc-social-share-bar' ); ?>
				<a class="closeShare" href="#closeShare">
					<svg>
						<use xlink:href="#pmc-social-share-bar-close"></use>
					</svg>
				</a>
			</li>
			<?php foreach ( $secondary_share_icons as $id => $share_icon ) : ?>
				<li>
					<?php if ( $share_icon->is_javascript() ) : ?>
						<a href="<?php echo esc_attr( $share_icon->url );  /*  not using esc_url since this value is javascript */ ?>"
					<?php else : ?>
						<a data-href="<?php echo esc_url( $share_icon->url ); ?>" href="<?php echo esc_url( $share_icon->url ); ?>"
					<?php endif; ?>
						class="<?php echo esc_attr( $share_icon->class ); ?>"
					<?php if ( $share_icon->is_popup() ) : ?>
						target="_blank"
					<?php endif; ?>
					<?php if ( \PMC\Social_Share_Bar\Config::WA === $id ) : ?>
						data-action="share/whatsapp/share"
					<?php endif; ?>
						title="<?php echo esc_attr( $share_icon->title ); ?>">
						<svg>
							<use xlink:href="#<?php echo esc_attr( 'pmc-social-share-bar-' . $id ); ?>"></use>
						</svg>
						<span>
							<?php echo esc_html( $share_icon->name ); ?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

