<?php
/**
 * Template for the admin page
 *
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2017-12-27
 */

$post_permalink = get_permalink( $post );
$post_statuses  = [ 'draft', 'future', 'pending' ];

if ( $admin_ui->is_post_draft( $post->ID ) || 'future' === strtolower( $post->post_status ) ) {
	$post_permalink = add_query_arg( 'preview', 'true', $post_permalink );
}

?>
<div id="howto-metaboxes-general" class="wrap">

	<h2><?php echo esc_html__( 'Post Reviewer', 'pmc-post-reviewer' ); ?></h2>

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<div id="post-body-content" style="position: relative;">

				<div id="titlediv">
					<div id="titlewrap">
						<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo esc_html__( 'Post title', 'pmc-post-reviewer' ); ?></label>
						<input type="text" name="post_title" size="30" value="<?php echo esc_attr( $post->post_title ); ?>" id="title" spellcheck="true" autocomplete="off">
					</div>
					<div class="inside">
						<div id="edit-slug-box">

							<strong><?php echo esc_html__( 'Permalink', 'pmc-post-reviewer' ); ?>:</strong>
							<span id="sample-permalink"><a href="<?php echo esc_url( $post_permalink ); ?>"><?php echo esc_url( $post_permalink ); ?></a></span>
							<div style="padding-top:4px">
								<strong><?php echo esc_html__( 'Full Slug', 'pmc-post-reviewer' ); ?>:</strong> <?php echo esc_html( $post->post_name ); ?>
							</div>

						</div>
					</div>
				</div>

				<div id="postdivrich" class="postarea wp-editor-expand">

					<div id="wp-content-wrap" class="wp-core-ui wp-editor-wrap" style="padding-top: 10px;">
						<div id="wp-content-editor-container" class="wp-editor-container">
							<textarea class="wp-editor-area" style="height: 400px; width: 100%; padding: 10px 5px;" autocomplete="off" name="content" id="content"><?php echo wp_kses_post( $post->post_content ); ?></textarea>
						</div>
					</div>

					<table id="post-status-info" style="padding-top: 5px; padding-bottom: 5px;">
						<tbody>
						<tr>
							<td id="wp-word-count">
								<strong><?php echo esc_html__( 'Word count', 'pmc-post-reviewer' ); ?>: </strong>
								<span class="word-count"><?php echo esc_html( \PMC::get_word_count( $post->post_content ) ); ?></span>
							</td>
							<td class="autosave-info">
								<span id="last-edit">
									<strong><?php echo esc_html__( 'Last edited on', 'pmc-post-reviewer' ); ?> </strong>
									<?php echo esc_html( get_the_modified_date( 'F j, Y', $post ) ); ?> at <?php echo esc_html( get_post_modified_time( 'g:i a', false, $post ) ); ?>
								</span>
							</td>
							<td id="content-resize-handle"><br></td>
						</tr>
						</tbody>
					</table>

				</div>
			</div>

			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( $pagehook, 'side', [] ); ?>
			</div>

			<div id="postbox-container-2" class="postbox-container">
				<?php do_meta_boxes( $pagehook, 'normal', [] ); ?>
			</div>

		</div>

		<br class="clear"/>

	</div>

</div>

<script type="text/javascript">
	//<![CDATA[
	jQuery( document ).ready( function( $ ) {
		// close postboxes that should be closed
		$( '.if-js-closed' ).removeClass( 'if-js-closed' ).addClass( 'closed' );
		// postboxes setup
		postboxes.add_postbox_toggles( '<?php echo esc_js( $pagehook ); ?>' );
	});
	//]]>
</script>

