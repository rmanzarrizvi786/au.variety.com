<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>

<?php if ( ! empty( $o_tab_url ) ) { ?>
	<a href="<?php echo esc_url( $o_tab_url ?? '' ); ?>" class="<?php echo esc_attr( $o_tab_link_classes ?? '' ); ?>">
<?php } ?>
	<div class="o-tab <?php echo esc_attr( $o_tab_classes ?? '' ); ?>">
		<?php if ( ! empty( $c_span ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-span.php', $c_span, true ); ?>
		<?php } ?>
		<?php if ( ! empty( $c_icon ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $c_icon, true ); ?>
		<?php } ?>
	</div>
<?php if ( ! empty( $o_tab_url ) ) { ?>
	</a>
<?php } ?>
