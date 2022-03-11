var Production_Grid = new function() {
	this.page_num = 1;
	this.page_size = 30;
	this.sort_column = '';
	this.sort_direction = '';

	/* Document Ready
	 *
	 * 1. Refresh the grid
	 * 2. Column click event handlers
	 * 3. Refresh filter menus
	 * 4. Filter menu event handler
	 */
	jQuery(document).ready(function($) {
		if( ( uls.session.can_access( 'vy-digital' ) || 1 == variety_authentication_object.bypass_authentication ) && jQuery( '.production-grid' ).length ) {
			jQuery('#pg_selections').show();
			jQuery('#scorecard-table').show();

			Production_Grid.refresh_production_grid();

			Production_Grid.clear_all_sort_classes();

			Production_Grid.refresh_menus();

			jQuery( '.pg-filter-select' ).change( function( e ) {
				Production_Grid.page_num = 1;
				Production_Grid.refresh_production_grid();
			});

			jQuery( '#col-title-asc' ).click( function( e ) { Production_Grid.column_click('title', 'asc'); });
			jQuery( '#col-title-des' ).click( function( e ) { Production_Grid.column_click('title', 'des'); });
			jQuery( '#col-studio-asc' ).click( function( e ) { Production_Grid.column_click('studio', 'asc'); });
			jQuery( '#col-studio-des' ).click( function( e ) { Production_Grid.column_click('studio', 'des'); });
			jQuery( '#col-genre-asc' ).click( function( e ) { Production_Grid.column_click('genre', 'asc'); });
			jQuery( '#col-genre-des' ).click( function( e ) { Production_Grid.column_click('genre', 'des'); });
			jQuery( '#col-dates-asc' ).click( function( e ) { Production_Grid.column_click('dates', 'asc'); });
			jQuery( '#col-dates-des' ).click( function( e ) { Production_Grid.column_click('dates', 'des'); });
			jQuery( '#col-location-asc' ).click( function( e ) { Production_Grid.column_click('location', 'asc'); });
			jQuery( '#col-location-des' ).click( function( e ) { Production_Grid.column_click('location', 'des'); });
			jQuery( '#col-commitment-asc' ).click( function( e ) { Production_Grid.column_click('commitment', 'asc'); });
			jQuery( '#col-commitment-des' ).click( function( e ) { Production_Grid.column_click('commitment', 'des'); });
			jQuery( '#col-status-asc' ).click( function( e ) { Production_Grid.column_click('status', 'asc'); });
			jQuery( '#col-status-des' ).click( function( e ) { Production_Grid.column_click('status', 'des'); });

			$('body').on('click', '#pg-pagination-page', function ( evt ) {
			     Production_Grid.refresh_production_grid_to_page_num( jQuery(evt.target).data('pagenum') );
			});
		} else {
			jQuery('#pg-please-login').show();
		}
	});

	/* refresh_production_grid_to_page_num( num )
	 *
	 * num    int   the page number to move to
	 *
	 * Moves the grid to the specified page number
	 */
	this.refresh_production_grid_to_page_num = function( num ) {
		Production_Grid.page_num = parseInt(num);
		Production_Grid.refresh_production_grid();
	}

	/* refresh_production_grid()
	 *
	 * Pulls new grid data via ajax from backend
	 */
	this.refresh_production_grid = function() {
		if( Production_Grid.isOnScreen( '#pg-type-select' ) == false ) {
			jQuery('body').scrollTo( '#container', {duration: 350} );
		}

		jQuery('#pg-table-body').hide();
		jQuery('#pg-pagination').hide();
		jQuery('#pg-table-body-loading').show();

		Variety_Authentication.get_protected_data({
			async: true,
			data_type: 'production_grid',
			data_args: {
				page_num:       Production_Grid.page_num,
				page_size:      jQuery('#pg-page-size-select :selected').val(),
				sort_column:    Production_Grid.sort_column,
				sort_direction: Production_Grid.sort_direction,
				type:           jQuery('#pg-type-select :selected').val(),
				genre:          jQuery('#pg-genre-select :selected').val(),
				location:       jQuery('#pg-location-select :selected').val(),
				status:         jQuery('#pg-status-select :selected').val()
			},
			success: function ( data ) {
				if ( typeof data.status != 'undefined' && data.status && typeof data.results != 'undefined') {
					jQuery('#pg-table-body').html( data.results.grid );
					jQuery('#pg-header-text').html( data.results.header_text );
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
		jQuery( '#pg-genre-select' ).chosen({disable_search_threshold: 100});
		jQuery( '#pg-location-select' ).chosen({disable_search_threshold: 10});
		jQuery( '#pg-type-select' ).chosen({disable_search_threshold: 10});
		jQuery( '#pg-status-select' ).chosen({disable_search_threshold: 10});
		jQuery( '#pg-page-size-select' ).chosen({disable_search_threshold: 10});
	}

	/* clear_all_sort_classes( )
	 *
	 * Removes classes from the sort column headers
	 */
	this.clear_all_sort_classes = function( ) {
		var col_titles = ['title', 'studio', 'genre', 'dates', 'location', 'commitment', 'status'];

		for( var title in col_titles ) {
			jQuery( '#col-'+col_titles[title]+'-asc' ).removeClass().addClass('pg_sort_off');
			jQuery( '#col-'+col_titles[title]+'-des' ).removeClass().addClass('pg_sort_off');
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
		if( Production_Grid.sort_column != '') {
			jQuery( '#col-'+Production_Grid.sort_column+'-'+Production_Grid.sort_direction ).removeClass().addClass( 'pg_sort_off' );
		}

		// If clicking twice on sort element, remove sort and return to default, otherwise sort accordingly
		if( col_name == Production_Grid.sort_column && direction == Production_Grid.sort_direction ) {
			Production_Grid.sort_column = '';
			Production_Grid.sort_direction = '';
		} else {
			Production_Grid.sort_column = col_name;
			Production_Grid.sort_direction = direction;

			jQuery( '#col-'+col_name+'-'+direction ).removeClass().addClass( 'pg_sort_on' );
		}

		Production_Grid.refresh_production_grid();
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