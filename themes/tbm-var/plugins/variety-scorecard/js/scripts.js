// Version 1.02

var Variety_Scorecard = new function () {
	this.page = 0;
	this.network_id = 0;
	this.network = '';
	this.network_type_id = '';
	this.genre_id = '';
	this.status_id = '';
	this.pagesize = 0;

	jQuery( document ).ready( function ( $ ) {
		var regex, match;
		if ( window.location.hash ) {
			regex = /\/?network-(\d*)-?([^/]*)\/?/gi;
			match = regex.exec( window.location.hash );
			if ( match ) {
				Variety_Scorecard.network_id = match[1].toLowerCase();
				Variety_Scorecard.network = match[2].toLowerCase();
				if ( ! Variety_Scorecard.network_id && Variety_Scorecard.network ) {
					jQuery( '#scorecard-network-select option' ).each( function ( i, el ) {
						if ( jQuery( el ).text().toLowerCase() == Variety_Scorecard.network ) {
							Variety_Scorecard.network_id = jQuery( el ).attr( 'value' );
						}
					} );
				}
			}
			if ( Variety_Scorecard.network_id ) {
				Variety_Scorecard.network_type_id = '';
			}
			else {
				regex = /\/?networktype-([^/]+)\/?/gi;
				match = regex.exec( window.location.hash );
				if ( match ) {
					Variety_Scorecard.network_type_id = match[1];
				}
			}
			regex = /\/?page-(\d+)\/?/gi;
			match = regex.exec( window.location.hash );
			if ( match ) {
				Variety_Scorecard.page = match[1];
			}
			regex = /\/?pagesize-(\d+)\/?/gi;
			match = regex.exec( window.location.hash );
			if ( match ) {
				switch ( match[1] ) {
					case '30':
					case '60':
					case '100':
						Variety_Scorecard.pagesize = match[1];
						break;
				}
			}
			regex = /\/?genre-([^/]+)\/?/gi;
			match = regex.exec( window.location.hash );
			if ( match ) {
				Variety_Scorecard.genre_id = match[1];
			}
			regex = /\/?status-([^/]+)\/?/gi;
			match = regex.exec( window.location.hash );
			if ( match ) {
				Variety_Scorecard.status_id = match[1];
			}

		}
		else {
			if ( Variety_Scorecard.network_id ) {
				jQuery( '#scorecard-network-select' ).val( Variety_Scorecard.network_id );
				Variety_Scorecard.network_type_id = '';
			}
			else {
				if ( ! Variety_Scorecard.network_type_id ) {
					Variety_Scorecard.network_type_id = '1';
				}
				if ( ! Variety_Scorecard.status_id ) {
					Variety_Scorecard.status_id = '';  // default status
				}
			}
		}

		var regex = /\/[^/]*scorecard\/?/gi;
		// only do auto-refresh if we're on pilots scorecard landing page
		if ( regex.test( window.location.pathname ) ) {
			if ( Variety_Scorecard.page || Variety_Scorecard.pagesize || Variety_Scorecard.network_id || Variety_Scorecard.genre_id || Variety_Scorecard.status_id || Variety_Scorecard.network_type_id ) {
				Variety_Scorecard.Refresh();
			}
		}

		jQuery( '#scorecard-pagination' )
			.children( 'a' )
			.each( function () {
				var regex = /page[\=\-](\d+)/gi;
				match = regex.exec( jQuery( this ).attr( 'href' ) );
				if ( match ) {
					jQuery( this ).attr( 'href', '#page-' + match[1] )
				}
			} );
		Variety_Scorecard.AttachPaginationEvent();
		jQuery( '#scorecard-genre-select' ).change( function ( e ) {
			Variety_Scorecard.genre_id = jQuery( this ).val().toLowerCase();
			Variety_Scorecard.page = 1;
			Variety_Scorecard.Refresh();
		} );
		jQuery( '#scorecard-status-select' ).change( function ( e ) {
			Variety_Scorecard.status_id = jQuery( this ).val().toLowerCase();
			Variety_Scorecard.page = 1;
			Variety_Scorecard.Refresh();
		} );
		jQuery( '#scorecard-pagesize-select' ).change( function ( e ) {
			Variety_Scorecard.pagesize = jQuery( this ).val();
			Variety_Scorecard.page = 1;
			Variety_Scorecard.Refresh();
		} );

		Variety_Scorecard.AttachTrackingEvent();

		var config = {
			'#scorecard-network-select': {width: "200px"},
			'#scorecard-genre-select': {disable_search: true, width: "120px"},
			'#scorecard-status-select': {disable_search: true, width: "290px"},
			'#scorecard-pagesize-select': {disable_search: true, width: "100px"}
		}
		for ( var selector in config ) {
			jQuery( selector ).chosen( config[selector] );
		}

		jQuery( '#scorecard_network_select_chzn .chzn-single span' ).html( jQuery( 'select#scorecard-network-select optgroup[value="' + Variety_Scorecard.network_type_id + '"]' ).attr( 'label' ) );

		jQuery( '#scorecard-network-select' ).chosen().change( function ( event, data ) {


			if ( data.item !== undefined ) {
				Variety_Scorecard.network_id = 0;
				Variety_Scorecard.network_type_id = data.selected;
			}
			else {
				Variety_Scorecard.network_id = data.selected;
				Variety_Scorecard.network_type_id = '';
			}
			Variety_Scorecard.page = 1;
			Variety_Scorecard.Refresh();
		} );

	} );

	this.fix_pagination_link = function () {
		jQuery( '#scorecard-pagination a' ).each( function () {
			var regex = /#page[\=\-](\d+)/gi;

			match = regex.exec( jQuery( this ).attr( 'href' ) );
			if ( match ) {
				jQuery( this ).attr( 'href', '#page-' + match[1] )
			}
		} );
	};

	this.Refresh = function () {
		// Need to place these code here to do pre-option selection for chosen-jquery plugin dropdown during page load init
		jQuery( '#scorecard-network-select' ).val( Variety_Scorecard.network_id );
		jQuery( '#scorecard-genre-select' ).val( Variety_Scorecard.genre_id );
		jQuery( '#scorecard-status-select' ).val( Variety_Scorecard.status_id );
		jQuery( '#scorecard-pagesize-select' ).val( Variety_Scorecard.pagesize );

		jQuery.ajax( {
			url: scorecard_script_admin.ajax_endpoint,
			data: {
				action: 'get_scorecard',
				render: 'html',
				page: Variety_Scorecard.page,
				page_size: Variety_Scorecard.pagesize,
				network_id: Variety_Scorecard.network_id,
				network_type_id: Variety_Scorecard.network_type_id,
				genre_id: Variety_Scorecard.genre_id,
				status_id: Variety_Scorecard.status_id
			}
		} ).success( function ( data ) {
			jQuery( '#table-scorecard' ).html( jQuery( data.html ).html() );
			jQuery( '#scorecard-pagination' ).html( data.pagination );
			Variety_Scorecard.fix_pagination_link();
			Variety_Scorecard.AttachPaginationEvent();
			Variety_Scorecard.AttachTrackingEvent();
			Variety_Scorecard.network = jQuery( "#scorecard-network-select option[value='" + Variety_Scorecard.network_id + "']" ).text().toLowerCase();
			var anchor = (
				Variety_Scorecard.network_id ? 'network-' + Variety_Scorecard.network_id + '-' + Variety_Scorecard.network + '/' : ''
			)
			+ (
				Variety_Scorecard.network_type_id ? 'networktype-' + Variety_Scorecard.network_type_id + '/' : ''
			)
			+ (
				Variety_Scorecard.genre_id ? 'genre-' + Variety_Scorecard.genre_id + '/' : ''
			)
			+ (
				Variety_Scorecard.status_id ? 'status-' + Variety_Scorecard.status_id + '/' : ''
			)
			+ (
				Variety_Scorecard.pagesize ? 'pagesize-' + Variety_Scorecard.pagesize + '/' : ''
			)
			+ 'page-' + data.current_page;
			jQuery( "#anchor-scorecard" ).attr( 'name', anchor );
			// var url = jQuery('link[rel="canonical"]').attr('href') +'#'+ anchor;
			// window.location.href = url;
		} );
	};

	this.AttachPaginationEvent = function () {
		jQuery( '#scorecard-pagination' ).children( 'a' ).click( function ( e ) {
			var regex = /page[\=\-](\d+)/gi;
			match = regex.exec( jQuery( this ).attr( 'href' ) );
			if ( match ) {
				Variety_Scorecard.page = match[1];
				Variety_Scorecard.Refresh();
				try {
					if ( typeof global_urlhashchanged == 'function' ) {
						global_urlhashchanged();
					}
				} catch ( err ) {
				}
			}
			return false;
		} );
	};

	this.TrackView = function ( event, action, label ) {
		// Track PV in GA
		try {

			ga( 'send', 'event', event, action, label, 0, true );

		} catch ( err ) {
		}
	};

	this.AttachTrackingEvent = function () {
		try {
			jQuery( 'table#table-scorecard tbody tr td:first-child a' ).click( function ( e ) {
				Variety_Scorecard.TrackView( 'pilots-scorecard', 'network', 'click' );
			} );
		} catch ( err ) {
		}
	};
}
