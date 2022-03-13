<?php
/**
 * Template for the history metabox
 */

if ( empty( $ceo_content->audit ) ) {
	return;
}
?>
<table class="wp-list-table widefat fixed striped">
<?php
foreach ( $ceo_content->audit as $key => $value ) {
	$local = new \DateTimeZone( date_default_timezone_get() );
	$utc   = new \DateTimeZone( 'UTC' );

	$dt = \DateTime::createFromFormat( 'Y-m-d H:i:s', $value->created_at, $utc );
	$dt->setTimezone( $local );

	$created = $dt->format( 'Y-m-d h:ia' );
	?>

	<tr>
		<td><?php echo esc_html( $value->user->name ); ?></td>
		<td><?php echo esc_html( $created ); ?></td>
		<td><?php echo esc_html( $value->message ); ?></td>
	</tr>
	<?php
}
?>
</table>
