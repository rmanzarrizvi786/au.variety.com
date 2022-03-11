<?php
/**
 * Template part of digital feed admin form.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

$admin_url = admin_url( 'tools.php?page=variety-digital' );
?>
<p>
	<form method="POST" action="<?php echo esc_url( $admin_url ); ?>">
		<p>
			<button name="action" value="refresh" type="submit">
				<?php esc_html_e( 'Click here to refresh digital data', 'pmc-variety' ); ?>
			</button>
		</p>
	</form>
	<div>
		<?php
		/* translators: Message to display current issue id, status and total number of issues synced. */
		printf( esc_html__( 'Current issue id = %1$s is %2$s.  Total issue count = %3$s.', 'pmc-variety' ), esc_html( $latest_issue_id ), esc_html( $is_issue_synched ), esc_html( $total_issue_synced ) );
		?>
	</div>	
</p>
