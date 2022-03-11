<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="<?php echo esc_attr( $o_story_arc_item_classes ?? '' ); ?>">
	<span class="a-span-sandwich lrv-u-margin-b-1">
		<?php if ( ! empty( $c_timestamp ) ) { ?>
			<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-timestamp.php', $c_timestamp, true ); ?>
		<?php } ?>
	</span>

	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-tease.php', $o_tease, true ); ?>
</div>
