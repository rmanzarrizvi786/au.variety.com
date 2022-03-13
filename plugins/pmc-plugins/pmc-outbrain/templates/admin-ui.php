<?php
/*
 * Template to render Widget HTML in admin
 *
 * @since 2015-10-13 Archana Mandhare PMCVIP-309
 */
?>
<p>
	<strong><?php esc_html_e( 'Renders the Outbrain HTML that gets populated with the sponsored content', 'pmc-plugins' ); ?></strong>
</p>
<p>
	<label
		for="<?php echo esc_attr( $sidebar_id ); ?>"><?php esc_html_e( 'Sidebar:' ); ?></label>
	<br/>
	<select id="<?php echo esc_attr( $sidebar_id ); ?>"
	        name="<?php echo esc_attr( $sidebar_name ); ?>">
		<option value="0" <?php selected( $sidebar, 0 ); ?>>No</option>
		<option value="1" <?php selected( $sidebar, 1 ); ?>>Yes</option>
	</select>
</p>