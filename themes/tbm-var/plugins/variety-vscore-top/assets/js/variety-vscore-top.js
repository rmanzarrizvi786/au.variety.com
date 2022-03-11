var Vscore_Top = new function() {
	this.page_num = 1;
	this.page_size = 30;
	this.sort_column = '';
	this.sort_direction = '';
	this.col_titles = ['name', 'age', 'gender', 'ethnicity', 'country', 'film-score', 'tv-score', 'social-score', 'vscore'];

	/* Document Ready
	 *
	 * 1. Refresh the grid
	 * 2. Column click event handlers
	 * 3. Refresh filter menus
	 * 4. Filter menu event handler
	 */
	jQuery(document).ready(function($) {
		if( ( uls.session.can_access( 'vy-digital' ) || 1 == variety_authentication_object.bypass_authentication ) && jQuery( '#vscore-top' ).length ) {
			jQuery('#pg_selections').show();
			jQuery('#scorecard-table').show();

			Vscore_Top.refresh_vscore_top();

			Vscore_Top.clear_all_sort_classes();

			Vscore_Top.refresh_menus();

			jQuery( '.pg-filter-select' ).change( function( e ) {
				Vscore_Top.page_num = 1;
				Vscore_Top.refresh_vscore_top();
			});

			jQuery( '#col-name-asc' ).click( function( e ) { Vscore_Top.column_click('name', 'asc'); });
			jQuery( '#col-name-des' ).click( function( e ) { Vscore_Top.column_click('name', 'des'); });
			jQuery( '#col-age-asc' ).click( function( e ) { Vscore_Top.column_click('age', 'asc'); });
			jQuery( '#col-age-des' ).click( function( e ) { Vscore_Top.column_click('age', 'des'); });
			jQuery( '#col-ethnicity-asc' ).click( function( e ) { Vscore_Top.column_click('ethnicity', 'asc'); });
			jQuery( '#col-ethnicity-des' ).click( function( e ) { Vscore_Top.column_click('ethnicity', 'des'); });
			jQuery( '#col-film-score-asc' ).click( function( e ) { Vscore_Top.column_click('film-score', 'asc'); });
			jQuery( '#col-film-score-des' ).click( function( e ) { Vscore_Top.column_click('film-score', 'des'); });
			jQuery( '#col-tv-score-asc' ).click( function( e ) { Vscore_Top.column_click('tv-score', 'asc'); });
			jQuery( '#col-tv-score-des' ).click( function( e ) { Vscore_Top.column_click('tv-score', 'des'); });
			jQuery( '#col-gender-asc' ).click( function( e ) { Vscore_Top.column_click('gender', 'asc'); });
			jQuery( '#col-gender-des' ).click( function( e ) { Vscore_Top.column_click('gender', 'des'); });
			jQuery( '#col-country-asc' ).click( function( e ) { Vscore_Top.column_click('country', 'asc'); });
			jQuery( '#col-country-des' ).click( function( e ) { Vscore_Top.column_click('country', 'des'); });
			jQuery( '#col-social-score-asc' ).click( function( e ) { Vscore_Top.column_click('social-score', 'asc'); });
			jQuery( '#col-social-score-des' ).click( function( e ) { Vscore_Top.column_click('social-score', 'des'); });
			jQuery( '#col-vscore-asc' ).click( function( e ) { Vscore_Top.column_click('vscore', 'asc'); });
			jQuery( '#col-vscore-des' ).click( function( e ) { Vscore_Top.column_click('vscore', 'des'); });


			jQuery('body').on('click', '#pg-pagination-page', function ( evt ) {
			     Vscore_Top.refresh_vscore_top_to_page_num( jQuery(evt.target).data('pagenum') );
			});
		} else {
			jQuery('#vst-please-login').show();
		}
	});

	/* refresh_vscore_top_to_page_num( num )
	 *
	 * num    int   the page number to move to
	 *
	 * Moves the grid to the specified page number
	 */
	this.refresh_vscore_top_to_page_num = function( num ) {
		Vscore_Top.page_num = parseInt(num);
		Vscore_Top.refresh_vscore_top();
	}

	/* refresh_vscore_top()
	 *
	 * Pulls new grid data via ajax from backend
	 */
	this.refresh_vscore_top = function() {
		if( Vscore_Top.isOnScreen( '#pg-gender-select' ) == false ) {
			jQuery('body').scrollTo( '#container', {duration: 350} );
		}

		jQuery('#pg-table-body').hide();
		jQuery('#pg-pagination').hide();
		jQuery('#pg-table-body-loading').show();

		Variety_Authentication.get_protected_data({
			async: true,
			data_type: 'vscore_top',
			data_args: {
				page_num:       Vscore_Top.page_num,
				page_size:      jQuery('#pg-page-size-select :selected').val(),
				sort_column:    Vscore_Top.sort_column,
				sort_direction: Vscore_Top.sort_direction,
				age:            jQuery('#pg-age-select :selected').val(),
				gender:         jQuery('#pg-gender-select :selected').val(),
				race:           jQuery('#pg-ethnicity-select :selected').val(),
				country:        jQuery('#pg-country-select :selected').val()
			},
			success: function ( data ) {
				if ( typeof data.status != 'undefined' && data.status && typeof data.results != 'undefined') {

					jQuery('#pg-table-body').html( data.results.grid );
					jQuery('#pg-header-text-results').html( data.results.header_text_results );
					jQuery('#pg-pagination').html( data.results.pagination_html );

					jQuery('#pg-table-body').show();
					jQuery('#pg-pagination').show();
				} else {
					jQuery('#pg-table-body').html('<tr><td colspan="9">Access denied</td></tr>').show();
				}
				jQuery('#pg-table-body-loading').hide();
			},
			error: function() {
				jQuery('#pg-table-body').html('<tr><td colspan="9">Error retrieving data</td></tr>').show();
				jQuery('#pg-table-body-loading').hide();
			}
		});

	}

	/* refresh_menus()
	 *
	 * Setup all the menus for the production grid, dynamic and statis
	 *
	 */
	this.refresh_menus = function() {
		// Static Menus
		jQuery( '#pg-age-select' ).chosen({disable_search_threshold: 100});
		jQuery( '#pg-gender-select' ).chosen({disable_search_threshold: 10});
		jQuery( '#pg-ethnicity-select' ).chosen({disable_search_threshold: 10});
		jQuery( '#pg-country-select' ).chosen({disable_search_threshold: 100});
	}

	/* clear_all_sort_classes( )
	 *
	 * Removes classes from the sort column headers
	 */
	this.clear_all_sort_classes = function( ) {
		for( var title in Vscore_Top.col_titles ) {
			jQuery( '#col-'+Vscore_Top.col_titles[title]+'-asc' ).removeClass().addClass('pg_sort_off');
			jQuery( '#col-'+Vscore_Top.col_titles[title]+'-des' ).removeClass().addClass('pg_sort_off');
		}
	}

	/* column_click( col_name, direction )
	 *
	 * col_name 	string 		Name of the column, must correspond to the name of column id (sans 'col-')
	 * direction 	string		asc | des
	 *
	 * 1. Switches the ascending/descending order of the column, depending on input
	 * 2. Force a refresh of the grid
	 */
	this.column_click = function( col_name, direction ) {
		if( Vscore_Top.sort_column != '') {
			jQuery( '#col-'+Vscore_Top.sort_column+'-'+Vscore_Top.sort_direction ).removeClass().addClass( 'pg_sort_off' );
		}

		// If clicking twice on sort element, remove sort and return to default, otherwise sort accordingly
		if( col_name == Vscore_Top.sort_column && direction == Vscore_Top.sort_direction ) {
			Vscore_Top.sort_column = '';
			Vscore_Top.sort_direction = '';
		} else {
			Vscore_Top.sort_column = col_name;
			Vscore_Top.sort_direction = direction;

			jQuery( '#col-'+col_name+'-'+direction ).removeClass().addClass( 'pg_sort_on' );
		}

		Vscore_Top.refresh_vscore_top();
	}

	/* isOnnScreen( element )
	 *
	 * Tests if the given element is on screen.
	 *
	 * element = text id, ie: "#my-element"
	 */
	this.isOnScreen = function( element ) {
	    var curPos = jQuery(element).offset();
	    var curTop = curPos.top;
	    var screenHeight = jQuery(window).height();
	    return (curTop > screenHeight) ? false : true;
	}
}