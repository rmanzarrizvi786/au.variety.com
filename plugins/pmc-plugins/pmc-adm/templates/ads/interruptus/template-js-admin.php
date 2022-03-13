<?php

if ( ! empty( $locations ) && is_array( $locations ) ) {
	$locations = '"' . implode( '", "', array_map( 'esc_js', array_map( 'strtolower', $locations ) ) ) . '"';
?>
<script type="text/javascript">
var pmcadm_interruptus_locations = [<?php echo $locations; ?>];
</script>
<?php
}

//EOF