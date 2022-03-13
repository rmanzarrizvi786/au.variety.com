<?php 
	/*
		Initially copied from the wp_link_dialog() function within wp-includes/class-wp-editor.php 
	 */
	
	// Set a variable for use below which indicates if the link 
	// search panel was previously opened (to search for a post / use a post's link)
	$search_panel_visible = '';	//initialize the var

	if ( get_user_setting( 'relatedlink', '0' ) == 1 ) {
		$search_panel_visible =  ' search-panel-visible';
	}

	// display: none is required here, see #WP27605
?>

<div id="related-link-backdrop" style="display: none"></div>
<div id="related-link-wrap" class="wp-core-ui<?php echo esc_attr( $search_panel_visible ) ?>" style="display: none">
	<form id="related-link" tabindex="-1">
		<div id="link-modal-title">
			<?php esc_html_e( 'Insert/edit link' ) ?>
			<div id="related-link-close" tabindex="0"></div>
	 	</div>
		<div id="link-selector">
			<div id="link-options">
				<p class="howto"><?php esc_html_e( 'Enter the destination URL' ); ?></p>
				<div>
					<label><span><?php esc_html_e( 'URL' ); ?></span><input id="related-url-field" type="text" name="href" /></label>
				</div>
				<div>
					<label><span><?php esc_html_e( 'Title' ); ?></span><input id="related-link-title-field" type="text" name="linktitle" /></label>
				</div>
				<div>
					<label><span><?php esc_html_e( 'Type' ); ?></span>
					
					<?php 
						// Fetch the link types as created in the admin settings page
						$related_link_types_setting = get_option ( 'related-link-types-setting' );
						$exclude_selectone_setting = intval( get_option ( 'related-link-excludeselectone-setting' ) );


						// The link types are entered one-per-line in a textarea
						// convert them into an array
						$related_link_types = preg_split( "/\r\n|\n|\r/", $related_link_types_setting );
					?>

					<select id="related-link-type-field" type="text" name="linktype">
						<?php if( $exclude_selectone_setting == 0 ){ ?>
							<option>Select One</option><?php
					}
						if ( is_array( $related_link_types ) && ! empty( $related_link_types ) ) { 
							foreach ( $related_link_types as $link_type ) { ?>

						<option value="<?php echo esc_attr( $link_type ) ?>"><?php echo esc_html( $link_type ) ?></option><?php 
						
							} // foreach 
						} // if ?>
					</select>
					</label>
                    <label class="manualtype"><span><?php esc_html_e( 'Manual' ); ?></span><input id="related-link-manualtype-field" type="text" name="manuallinktype" /></label>
				</div>
				<div class="link-target">
					<label><span>&nbsp;</span><input type="checkbox" id="related-link-target-checkbox" /> <?php esc_html_e( 'Open link in a new window/tab' ); ?></label>
				</div>
			</div>
			<p class="howto" id="related-link-search-toggle"><?php esc_html_e( 'Or link to existing content' ); ?></p>
			<div id="search-panel">
				<div class="link-search-wrapper">
					<label>
						<span class="search-label"><?php esc_html_e( 'Search' ); ?></span>
						<input type="search" id="related-search-field" class="link-search-field" autocomplete="off" />
						<span class="spinner"></span>
					</label>
				</div>
				<div id="related-search-results" class="query-results">
					<ul></ul>
					<div class="river-waiting">
						<span class="spinner"></span>
					</div>
				</div>
				<div id="related-most-recent-results" class="query-results">
					<div class="query-notice"><em><?php esc_html_e( 'No search term specified. Showing recent items.' ); ?></em></div>
					<ul></ul>
					<div class="river-waiting">
						<span class="spinner"></span>
					</div>
				</div>
			</div>
		</div>
		<div class="submitbox">
			<div id="related-link-update">
				<input type="submit" value="<?php esc_attr_e( 'Add Link' ); ?>" class="button button-primary" id="related-link-submit" name="related-link-submit">
			</div>
			<div id="related-link-cancel">
				<a class="submitdelete deletion" href="#"><?php esc_html_e( 'Cancel' ); ?></a>
			</div>
		</div>
	</form>
</div>
