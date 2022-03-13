<?php
/**
 * Template for the admin UI post meta metabox
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */


$meta = get_post_meta( $post->ID );

if ( empty( $meta ) || ! is_array( $meta ) ) {
	echo esc_html__( 'No meta data found for this post.', 'pmc-post-reviewer' );
	return;
}

?>
<table>
	<thead>
		<tr>
			<th class="col-key"><?php echo esc_html__( 'Key', 'pmc-post-reviewer' ); ?></th>
			<th class="col-value"><?php echo esc_html__( 'Value', 'pmc-post-reviewer' ); ?></th>
		</tr>
	</thead>

	<tbody>
	<?php
	foreach ( $meta as $key => $values ) {

		if ( ! is_array( $values ) ) {
			continue;
		}

		for ( $i = 0; $i < count( $values ); $i++ ) {

			/*
			 * This usage of var_export() is intentional and
			 * is not leftover debug code.
			 */
			// @codingStandardsIgnoreStart
			$display_value = var_export( $values[ $i ], true );
			// @codingStandardsIgnoreEnd

			?>
			<tr>
				<td class="col-key"><?php echo esc_html( $key ); ?></td>
				<td class="col-value"><code><?php echo esc_html( $display_value ); ?></code></td>
			</tr>
			<?php

			unset( $display_value );

		}    //end for loop

	}    //end foreach loop
	?>
	</tbody>
</table>

