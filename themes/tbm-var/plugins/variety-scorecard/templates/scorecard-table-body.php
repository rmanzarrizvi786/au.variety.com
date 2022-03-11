<?php
/**
 * Template part for Scorecard Table Body.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

?>
<tr>
	<td>
		<?php
		// Escaped previously.
		// @codingStandardsIgnoreLine
		echo $network;
		?>
		<div>
			<?php echo esc_html( $genre ); ?>
		</div>
	</td>
	<td>
		<?php
		// Escaped previously.
		// @codingStandardsIgnoreLine
		echo $title;
		?>
	</td>
	<td>
		<?php echo esc_html( $studios ); ?>
	</td>
	<td>
		<?php echo esc_html( $writers ); ?>
	</td>
	<td>
		<?php echo esc_html( $logline ); ?>
	</td>
	<td>
		<?php echo esc_html( $status ); ?>
	</td>
</tr>
