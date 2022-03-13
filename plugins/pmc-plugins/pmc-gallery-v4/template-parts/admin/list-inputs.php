<?php

if ( isset( $taxonomy ) && isset( $list_id ) ) {
	?>

	<input type="text" id="pmc_list_name_v4" placeholder="<?php esc_attr_e( 'Filter By List', 'pmc-gallery-v4' ); ?>">
	<input type="hidden" id="pmc_list_id" name="<?php echo esc_attr( $taxonomy ); ?>" value="<?php echo intval( $list_id ); ?>"/>

	<?php
}

if ( ! empty( $list_id ) ) {
	?>
	<a class="alignright" href="<?php echo esc_url( admin_url( '/edit.php?post_type=' . \PMC\Gallery\Lists_Settings::LIST_ITEM_POST_TYPE ) ); ?>">
		<input type="button" class="button" value="<?php esc_attr_e( 'Clear Filters', 'pmc-gallery-v4' ); ?>"/>
	</a>
	<?php
}
