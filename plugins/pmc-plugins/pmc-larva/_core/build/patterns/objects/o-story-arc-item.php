<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="<?php echo esc_attr( $o_story_arc_item_classes ?? '' ); ?>">
	<span class="a-span-sandwich lrv-u-margin-b-1">
		<?php if ( ! empty( $c_timestamp ) ) { ?>
			<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-timestamp', $c_timestamp, true ); ?>
		<?php } ?>
	</span>

	<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'objects/o-tease', $o_tease, true ); ?>
</div>
