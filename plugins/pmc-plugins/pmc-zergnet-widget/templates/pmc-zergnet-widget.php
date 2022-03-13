<?php
 $zergent_widget_id = isset($zergnet_id ) ? $zergnet_id : '';
?>
<?php if ( !empty( $title ) ) { ?>
<h5 class="widget-title"><?php echo esc_html( $title ); ?></h5>
<?php } ?>
<div id="<?php echo esc_attr( 'zergnet-widget-'.$zergent_widget_id ); ?>"></div>
<script language="javascript" type="text/javascript">
	(
		function () {
			var zergnet = document.createElement( 'script' );
			zergnet.type = 'text/javascript';
			zergnet.async = true;
			zergnet.src = 'https://www.zergnet.com/zerg.js?id=<?php echo esc_attr( $zergent_widget_id ); ?>';
			var znscr = document.getElementsByTagName( 'script' )[0];
			znscr.parentNode.insertBefore( zergnet, znscr );
		}
	)();
</script>