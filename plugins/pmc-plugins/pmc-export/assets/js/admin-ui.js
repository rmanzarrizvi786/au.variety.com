/**
 * Common Javascript file.
 * This file is loaded across all the pages of the plugin so add code accordingly.
 */
jQuery(document).ready(function () {

	/**
	 * Export Posts Report JS.
	 */
	jQuery('#pmc-export-posts #submit').on( 'click', function() {

		var reporting_fields = jQuery( '#reporting_fields_filter' ).val();
	
		var data = {
				report: 'posts',
				post_type: jQuery('#post_type').val(),
				date_filter: jQuery( '#date_filter' ).val(),
				reporting_fields_filter: ( null != reporting_fields ) ? reporting_fields.join( ',' ) : '',
			};
		pmc.stream.download( 'csv-posts', data, 'report-' + data.post_type + '-' + data.date_filter );
	} );

	/**
	 * Chosen ( Reporting Fields selection )
	 */
	if ( window.Chosen ) {
		jQuery( '#reporting_fields_filter' ).chosen(
			{
				width: "300px",
				display_disabled_options: true
			}
		);
	}

});
