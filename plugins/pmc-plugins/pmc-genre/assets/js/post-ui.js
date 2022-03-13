/**
 * Javascript for the post screen
 *
 * @package PMC Genre 1.0
 */

function PMC_Genre_Post_UI() {
	this.mapped_genres = [];
	this.unmapped_genres = [];
	this.selected_terms = [];
}

PMC_Genre_Post_UI.prototype.setup = function() {
	if ( typeof pmc_genre_vars.mapped_genres !== 'undefined' && ! jQuery.isEmptyObject( pmc_genre_vars.mapped_genres ) ) {
		this.mapped_genres = pmc_genre_vars.mapped_genres;
	}

	if ( typeof pmc_genre_vars.unmapped_genres !== 'undefined' && ! jQuery.isEmptyObject( pmc_genre_vars.unmapped_genres ) ) {
		this.unmapped_genres = pmc_genre_vars.unmapped_genres;
	}
};

PMC_Genre_Post_UI.prototype.grab_checked_terms = function() {
	var checked_categories = jQuery( '#taxonomy-category input:checkbox:checked' ).map( function() {
		return jQuery( this ).val();
	} ).get();

	var checked_verticals = jQuery( '#taxonomy-vertical input:checkbox:checked' ).map( function() {
		return jQuery( this ).val();
	} ).get();

	this.selected_terms = checked_categories.concat( checked_verticals );

	if ( ! this.selected_terms || jQuery.isEmptyObject( this.selected_terms ) ) {
		this.selected_terms = [];
	} else {
		this.selected_terms = jQuery.unique( this.selected_terms );
	}
};

PMC_Genre_Post_UI.prototype.show_hide_genres = function() {
	this.grab_checked_terms();

	var self = this;

	jQuery( '#genrechecklist input[type=checkbox]' ).each( function() {
		var current_genre_id = parseInt( jQuery( this ).val() );
		var current_genre_list_id = '#genre-' + current_genre_id;

		//check if genre is unmapped
		if ( jQuery.inArray( current_genre_id, self.unmapped_genres ) !== -1 ) {
			//genre is unmapped, so it must stay as is
			//move to next checkbox
			return;
		} else {
			var reject_score = 0;
			var selected_terms_count = self.selected_terms.length;

			if ( jQuery.isEmptyObject( self.selected_terms ) ) {
				//no terms selected, genre is mapped to a term
				//so it must not be available for use
				reject_score = selected_terms_count;
			} else {
				for ( var i = 0; i < selected_terms_count; i++ ) {
					var current_term = self.selected_terms[ i ];

					if ( typeof self.mapped_genres[ current_term ] == 'undefined' || jQuery.isEmptyObject( self.mapped_genres[ current_term ] ) ) {
						continue;
					}

					if ( jQuery.inArray( current_genre_id, self.mapped_genres[ current_term ] ) === -1 ) {
						//current genre is not mapped to currently iterated term
						reject_score++;
					}
				}
			}

			if ( reject_score < selected_terms_count ) {
				//current genre is mapped to atleast one selected term
				//so lets show it but don't make any change to its state
				jQuery( current_genre_list_id ).show();
			} else {
				//current genre is not mapped to any selected term
				//so we uncheck it and hide it
				jQuery( current_genre_list_id + ' input[type=checkbox]' ).prop( 'checked', false );
				jQuery( current_genre_list_id ).hide();
			}
		}
	} );
};

/*
 * a) Add listener to check & uncheck events of Category & Vertical CBs
 *    1) Grab the term IDs of selected categories & verticals in an array
 *    2) Loop through the genres list & match each item against
 *       selected categories & verticals.
 *       a) If genre is mapped to one of selected categories/verticals
 *          then display it but dont change check state.
 *       b) If genre is not mapped to one of selected categories/verticals
 *          then uncheck it and hide it.
 *       c) If genre is unmapped then let it remain as is.
 */

jQuery( document ).ready( function( $ ) {

	$( 'a[href="#genre-all"]' ).trigger( 'click' );	//just in case

	var pmc_gn_post_ui = new PMC_Genre_Post_UI();
	pmc_gn_post_ui.setup();

	pmc_gn_post_ui.show_hide_genres();

	$( '#taxonomy-category input[type=checkbox]' ).on( 'change', function() {
		/*
		 * Delay execution because WP has 2 tabs for terms list and
		 * if a term is (un)selected in one tab its automatically (un)selected
		 * in the other. There's very slight delay in operation on other tab list
		 * and if this function is run immediately on change event then the last
		 * unchecked checkbox's value would also be grabbed as its state would still
		 * be checked before WP's JS can uncheck it.
		 * Hence a slight delay has been added here to avoid that.
		 */
		setTimeout( function() {
			pmc_gn_post_ui.show_hide_genres();
		}, 10 );
	} );

	$( '#taxonomy-vertical input[type=checkbox]' ).on( 'change', function() {
		/*
		 * Delay execution because WP has 2 tabs for terms list and
		 * if a term is (un)selected in one tab its automatically (un)selected
		 * in the other. There's very slight delay in operation on other tab list
		 * and if this function is run immediately on change event then the last
		 * unchecked checkbox's value would also be grabbed as its state would still
		 * be checked before WP's JS can uncheck it.
		 * Hence a slight delay has been added here to avoid that.
		 */
		setTimeout( function() {
			pmc_gn_post_ui.show_hide_genres();
		}, 10 );
	} );

} );

//EOF