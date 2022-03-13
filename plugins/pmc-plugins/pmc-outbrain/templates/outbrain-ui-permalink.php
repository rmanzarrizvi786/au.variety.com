<?php
/*
 * Template to render outbrain HTML
 *
 * @since 2015-10-13 Archana Mandhare PMCVIP-309
 * @version 2018-01-08 - Jignesh Nakrani - PMCP-98
 */

?>
<div class="outbrain-widget">
	<?php foreach ( $widget_ids as $widget_id ) { ?>
		<div class="OUTBRAIN" data-src="<?php echo esc_url( $permalink ); ?>" data-widget-id="<?php echo esc_attr( $widget_id ) ?>" data-ob-template="<?php echo esc_attr( $template ) ?>" ></div>
	<?php  } ?>
</div>
