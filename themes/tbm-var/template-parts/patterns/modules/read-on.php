<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="read-on // <?php echo esc_attr( $read_on_classes ?? '' ); ?>">
	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>

	<div class="read-on__inner // <?php echo esc_attr( $read_on_inner_classes ?? '' ); ?>">
		<?php foreach ( $read_on_items ?? [] as $item ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-read-on-item.php', $item, true ); ?>
		<?php } ?>
	</div>
</div>
