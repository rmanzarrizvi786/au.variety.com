(function($){

	//Append the exclude post checkbox markup to the dom
	$('#mt_seo.postbox .inside').append( $('#exclude_post_checkbox').html() );

	// Check the checkbox if the user previously did so
	// This variable is localized into wp_footer
	if ( mt_pmc_exclude_from_seo == 'on' ) {
		$('.mt_pmc_exclude_from_seo input[type="checkbox"]').prop('checked', true);
	}

})(jQuery);