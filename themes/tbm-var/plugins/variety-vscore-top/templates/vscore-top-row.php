<?php
/**
 * The template part for Vscore top 250 row.
 *
 * @since 2017-08-16 Milind More CDWE-474
 *
 * @package pmc-variety-2017
 */

if ( empty( $entry['Name'] ) ) {

	return;

}

?>
<tr valign="middle">
	<td><?php echo wp_kses_post( $entry['Photo'] ) . esc_html( $entry['Name'] ); ?></td>
	<td style="font-size: 14px; font-weight: bold !important;"><?php echo esc_html( $entry['Vscore'] ); ?></td>
	<td><?php echo esc_html( $entry['Age'] ); ?></td>
	<td><?php echo esc_html( $entry['Gender'] ); ?></td>
	<td><?php echo esc_html( $entry['Ethnicity'] ); ?></td>
	<td><?php echo esc_html( $entry['Country'] ); ?></td>
	<td><?php echo esc_html( number_format( floatval( $entry['Film_Score'] ) * 100 ) ); ?></td>
	<td><?php echo esc_html( $entry['TV_Score'] ); ?></td>
	<td><?php echo esc_html( $entry['Social_Score'] ); ?></td>
</tr>
<?php
