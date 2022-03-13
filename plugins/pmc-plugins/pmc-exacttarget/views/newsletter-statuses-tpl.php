<h3>Newsletters Status</h3>
<?php if ( ! empty( $sailthru_prev_url ) ): ?>
	<a style="margin-right:20px;" href="<?php echo esc_url( $sailthru_prev_url ); ?>">Prev Page</a>
<?php endif; ?>
<?php if ( ! empty( $sailthru_next_url ) ): ?>
	<a style="margin-left:20px;" href="<?php echo esc_url( $sailthru_next_url ); ?>">Next Page</a>
<?php endif; ?>
<table class="widefat">
	<thead>
	<th>Name</th>
	<th width="350px">Subject</th>
	<th>List</th>
	<th>Status</th>
	<th>Start Time</th>
	<th>End Time</th>
	<th>View</th>
	</thead>
	<tbody>
	<?php for ( $i = ( count( $sailthru_blasts ) - 1 ); $i > 0; $i-- ): ?>
		<?php
		$blast = $sailthru_blasts[$i];
		$start_date = new DateTime( $blast->SendDate );
		$end_date   = new DateTime( $blast->SendDate );

		?>
	<tr>
		<td><?php echo esc_html( $blast->EmailName ); ?></td>
		<?php /** TODO: The subject returned by Sailthru's API is busted **/ ?>
		<td><?php echo esc_html( $blast->Subject )?></td>
		<?php $sent_count = isset( $blast->NumberDelivered ) ? $blast->NumberDelivered . '/' : ''; ?>
		<td><?php echo esc_html( '' . ' (' . $sent_count . $blast->NumberTargeted . ')' ); ?></td>
		<td><?php echo esc_html( $blast->Status)?></td>
		<td><?php echo esc_html( $start_date->format( 'D - Y-m-d H:i:s' ) ) ?></td>
		<td><?php echo esc_html( $end_date->format( 'D - Y-m-d H:i:s' ) ) ?></td>
		<td><?php if ( isset( $blast->PreviewURL ) ): ?>
			<?php /** TODO: The URL returned by Sailthru's API is busted **/ ?>
			<a href="<?php echo esc_url( $blast->PreviewURL )?>" target="_blank">
				<img src="<?php echo esc_url( plugins_url( 'images/magnifier.png', __DIR__ ) ); ?>"/>
			</a>
			<?php endif;?></td>
	</tr>
		<?php endfor?>
	</tbody>
</table>
