<?php
/**
 * Renders Taboola header code.
 *
 * @var string $taboola_script_id Taboola path ID for site.
 *
 * @package pmc
 */

if ( empty( $taboola_script_id ) ) {
	return;
}

?>

<script type="text/javascript">
	!function (e, f, u) {
		e.async = 1;
		e.src = u;
		f.parentNode.insertBefore(e, f);
	}(document.createElement('script'),
	document.getElementsByTagName('script')[0],
	'//cdn.taboola.com/libtrc/<?php echo esc_attr( $taboola_script_id ); ?>/loader.js');
	window.taboolaDivCount   = 0;
	window.taboolaDivsLoaded = [];
</script>