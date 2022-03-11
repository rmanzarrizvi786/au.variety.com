<?php
/**
 * The template part for product grid row.
 *
 * @since 2017-08-16 Milind More CDWE-473
 *
 * @package pmc-variety-2017
 */

if ( empty( $item['title_html'] ) || empty( $item['status'] ) ) {

	return;

}
// This template is later escpaed with wp_kses_post in class Variety_Production_Grid.
?>
<tr valign="middle">
	<td><?php echo $item['title_html']; // xss ok ?></td>
	<td><?php echo $item['logo_html'] . $item['studio']; // xss ok ?></td>
	<td><?php echo $item['genre_final']; // xss ok ?></td>
	<td><?php echo $item['dates_final']; // xss ok ?></td>
	<td><?php echo $item['location_final']; // xss ok ?></td>
	<td><?php echo $item['commitment_final']; // xss ok ?></td>
	<td><?php echo $item['status']; // xss ok ?></td>
</tr>
