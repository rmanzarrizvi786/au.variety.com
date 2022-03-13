<?php
/**
 * Piano AMP logout script
 */
?>
<script>
	if ( "undefined" !== tp && "undefined" !== tp.amp ) {
		tp.push( ["init", function() {
			tp.amp.logout();
		}] );
	}
</script>
