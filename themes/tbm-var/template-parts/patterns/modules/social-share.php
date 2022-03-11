<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="social-share lrv-u-align-items-center lrv-u-flex <?php echo esc_attr( $social_share_classes ?? '' ); ?>">
	<?php if ( ! empty( $social_share_prefix ) ) { ?>
		<span class="<?php echo esc_attr( $social_share_prefix_classes ?? '' ); ?>">
			<?php echo esc_html( $social_share_prefix_text ?? '' ); ?>
		</span>
	<?php } ?>

	<?php if ( ! empty( $social_share_comments_link ) ) { ?>
		<a href="#comments">15</a>
	<?php } ?>

	<ul class="lrv-a-unstyle-list lrv-u-flex u-flex-wrap-wrap <?php echo esc_attr( $social_share_items_classes ?? '' ); ?>" data-collapsible="collapsed">
		<?php foreach ( $primary ?? [] as $item ) { ?>
			<li class="<?php echo esc_attr( $social_share_item_classes ?? '' ); ?>">
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $item, true ); ?>
			</li>
		<?php } ?>

		<?php if ( ! empty( $secondary ) ) { ?>
			<li class="<?php echo esc_attr( $social_share_item_classes ?? '' ); ?>" data-collapsible-toggle>
				<span class="<?php echo esc_attr( $social_share_plus_classes ?? '' ); ?>" tabindex="0">
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $plus_icon, true ); ?>
				</span>
			</li>

			<?php foreach ( $secondary ?? [] as $item ) { ?>
				<li class="<?php echo esc_attr( $social_share_item_classes ?? '' ); ?>" data-collapsible-panel>
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $item, true ); ?>
				</li>
			<?php } ?>
		<?php } ?>
	</ul>
</div>
