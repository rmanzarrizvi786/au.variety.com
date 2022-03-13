<?php
/*
 * Template to render outbrain HTML
 *
 * @since 2015-10-13 Archana Mandhare PMCVIP-309
 */
?>
<div class="outbrain-widget">
	<?php foreach ( $widget_ids as $widget_id ) { ?>
		<div class="OUTBRAIN" data-src="DROP_PERMALINK_HERE" data-widget-id="<?php echo esc_attr( $widget_id ) ?>" data-ob-template="<?php echo esc_attr( $template ) ?>" ></div>
	<?php  } ?>
</div>
