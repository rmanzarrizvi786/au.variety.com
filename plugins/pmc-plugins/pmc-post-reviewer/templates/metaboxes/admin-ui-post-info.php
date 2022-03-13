<?php
/**
 * Template for the admin UI post info metabox
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */


$default_post_statuses = [
	'publish'    => 'publish',
	'future'     => 'scheduled',
	'draft'      => 'draft',
	'pending'    => 'pending review',
	'private'    => 'private',
	'auto-draft' => 'revision',
	'inherit'    => 'inherit',
];

$post_status = get_post_status( $post );

if ( array_key_exists( strtolower( $post_status ), $default_post_statuses ) ) {
	$post_status = $default_post_statuses[ strtolower( $post_status ) ];
} else {
	$post_status = 'unknown';
}


if ( ! empty( $post->post_password ) ) {
	$post_visibility = 'password protected';
} elseif ( ! empty( $post->post_status ) && 'private' === strtolower( $post->post_status ) ) {
	$post_visibility = 'private';
} else {
	$post_visibility = 'public';
}


if ( $admin_ui->is_post_draft( $post->ID ) ) {
	$post_date = '';
} else {

	$post_date = get_the_date( 'M j, Y @ H:i', $post );

	if ( 'future' === strtolower( $post->post_status ) ) {
		$post_date_label = __( 'Scheduled for', 'pmc-post-reviewer' );
	} else {
		$post_date_label = __( 'Published on', 'pmc-post-reviewer' );
	}

}
?>
<ul>
	<li>
		<?php echo esc_html__( 'Status', 'pmc-post-reviewer' ); ?>:&nbsp;
		<strong><?php echo esc_html( ucwords( $post_status ) ); ?></strong>
	</li>
	<li>
		<?php echo esc_html__( 'Visibility', 'pmc-post-reviewer' ); ?>:&nbsp;
		<strong><?php echo esc_html( ucwords( $post_visibility ) ); ?></strong>
	</li>
	<?php if ( ! empty( $post_date ) ) { ?>
	<li>
		<?php echo esc_html( $post_date_label ); ?>:&nbsp;
		<strong><?php echo esc_html( $post_date ); ?></strong>
	</li>
	<?php } ?>
</ul>
