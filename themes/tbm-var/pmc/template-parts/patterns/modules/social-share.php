<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="social-share // lrv-u-align-items-center lrv-u-flex <?php echo esc_attr( $social_share_classes ?? '' ); ?>">
	<?php if ( ! empty( $social_share_prefix ) ) { ?>
		<span class="<?php echo esc_attr( $social_share_prefix_classes ?? '' ); ?>">
			<?php echo esc_html( $social_share_prefix_text ?? '' ); ?>
		</span>
	<?php } ?>

	<h2 id="article-social-share" class="lrv-a-screen-reader-only">Services to share this page.</h2>
	<ul class="lrv-a-unstyle-list lrv-u-flex lrv-u-flex-wrap-wrap <?php echo esc_attr( $social_share_items_classes ?? '' ); ?>" data-collapsible="collapsed" aria-labelledby="article-social-share">
		<?php foreach ( $social_share_primary ?? [] as $item ) { ?>
			<li class="<?php echo esc_attr( $social_share_item_classes ?? '' ); ?>">
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $item, true ); ?>
			</li>
		<?php } ?>

		<?php if ( ! empty( $social_share_secondary ) ) { ?>
			<li data-collapsible-toggle>
				<button  class="c-button <?php echo esc_attr( $c_icon_plus['c_icon_link_classes'] ?? '' ); ?>" title="Show more sharing options" data-toggle>
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $c_icon_plus, true ); ?>
				</button>
			</li>

			<?php foreach ( $social_share_secondary ?? [] as $item ) { ?>
				<li class="<?php echo esc_attr( $social_share_item_classes ?? '' ); ?>" data-collapsible-panel>
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $item, true ); ?>
				</li>
			<?php } ?>
		<?php } ?>
	</ul>
</div>
