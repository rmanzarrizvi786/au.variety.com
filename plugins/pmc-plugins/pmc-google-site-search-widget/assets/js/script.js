if ( typeof google !== 'undefined' ) {

	google.load('search', '1', {language : 'en', style : google.loader.themes.V2_DEFAULT});
	google.setOnLoadCallback(function() {
			var customSearchOptions = {};

			jQuery.each( _cse_options, function( idx, opt ) {

				var customSearchControl = new google.search.CustomSearchControl( opt.cse_id, customSearchOptions );
				var options = new google.search.DrawOptions();

				options.setAutoComplete( true );
				options.enableSearchboxOnly( opt.search_url );

				customSearchControl.setResultSetSize( google.search.Search.FILTERED_CSE_RESULTSET );
				customSearchControl.draw( opt.search_box, options );

			});

		}, true);

}