<?php

if ( isset( $taxonomy ) && isset( $list_id ) ) {
?>

<input type="text" id="pmc_filter_list" placeholder="<?php esc_attr_e( 'Filter By List', 'pmc-lists' ); ?>">
<input type="hidden" id="pmc_filter_list_id" name="<?php echo esc_attr( $taxonomy ); ?>" value="<?php echo intval( $list_id ); ?>" />

<?php
}

if ( ! empty( $list_id ) ) {
	?>
	<a class="alignright" href="<?php echo esc_url( admin_url( '/edit.php?post_type=' . \PMC\Lists\Lists::LIST_ITEM_POST_TYPE ) ); ?>">
		<input type="button" class="button" value="<?php esc_attr_e( 'Clear Filters', 'pmc-lists' ); ?>" />
	</a>
	<?php
}
