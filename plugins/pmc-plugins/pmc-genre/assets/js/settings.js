/**
 * Javascript for the settings page
 *
 * @package PMC Genre 1.0
 */

function PMC_Genre_Settings() {
	this.mapped_genres = [];
}

PMC_Genre_Settings.prototype.get_field_name = function( name ) {
	if ( ! name || typeof name !== 'string' || pmc.is_empty( name ) ) {
		return '';
	}

	return pmc.sanitize_key( pmc_genre_vars.plugin_id + '-' + name );
};

/**
 * Function to count number of items in our associative arrays because
 * Array.length wouldn't cut it as our keys are numeric.
 */
PMC_Genre_Settings.prototype.get_array_count = function( arr ) {
	if ( ! arr || typeof arr !== 'object' ) {
		return 0;
	}

	var arr_length = 0;

	for ( var i in arr ) {
		if ( Object.prototype.hasOwnProperty.call( arr, i ) ) {
			arr_length++;
		}
	}

	return arr_length;
};

PMC_Genre_Settings.prototype.set_mapped_genres = function( mapping ) {
	if ( ! mapping || typeof mapping === 'undefined' ) {
		return;
	}

	this.mapped_genres = mapping;
};

PMC_Genre_Settings.prototype.toggle_mapping_buttons = function( term ) {
	var add_button = jQuery( '#' + this.get_field_name( 'add-btn' ) );
	var remove_button = jQuery( '#' + this.get_field_name( 'remove-btn' ) );

	if ( typeof term === 'undefined' || ! term || pmc.is_empty( term ) ) {
		add_button.prop( 'disabled', true );
		remove_button.prop( 'disabled', true );
	} else {
		add_button.prop( 'disabled', false );
		remove_button.prop( 'disabled', false );
	}
};

PMC_Genre_Settings.prototype.get_term_group = function( opt_value, term_list ) {
	if ( typeof opt_value === 'undefined' || ! opt_value || pmc.is_empty( opt_value ) ) {
		return false;
	}

	return jQuery( term_list + ' option[value="' + opt_value + '"]' ).parent().attr( 'label' ).toLowerCase();
};

PMC_Genre_Settings.prototype.add_option = function( opt_value, opt_text, destination ) {
	jQuery( destination ).append(
		jQuery( '<option></option>' ).attr( 'value', opt_value ).text( opt_text )
	);
};

PMC_Genre_Settings.prototype.remove_option = function( opt_value, source ) {
	jQuery( source + ' option[value="' + opt_value + '"]' ).remove();
};

PMC_Genre_Settings.prototype.add_mapping = function( opt_value, opt_text, destination ) {
	if (
		typeof opt_value === 'undefined' || ! opt_value || pmc.is_empty( opt_value )
		|| typeof opt_text === 'undefined' || ! opt_text || pmc.is_empty( opt_text )
	) {
		return false;
	}

	var term = jQuery( ':selected', jQuery( destination ) );
	var term_value = term.val();
	var term_group = this.get_term_group( term_value, destination );

	if ( typeof this.mapped_genres[ term_group ] === 'undefined' ) {
		alert( 'Term group ' + term_group + ' does not exist in map' );
		return false;
	}

	if ( typeof this.mapped_genres[ term_group ][ term_value ] === 'undefined' ) {
		this.mapped_genres[ term_group ][ term_value ] = [];
	}

	this.mapped_genres[ term_group ][ term_value ][ opt_value ] = opt_text;

	return true;
};

PMC_Genre_Settings.prototype.remove_mapping = function( opt_value, source ) {
	if ( typeof opt_value === 'undefined' || ! opt_value || pmc.is_empty( opt_value ) ) {
		return false;
	}

	var term = jQuery( ':selected', jQuery( source ) );
	var term_value = term.val();
	var term_group = this.get_term_group( term_value, source );

	if ( typeof this.mapped_genres[ term_group ] === 'undefined' ) {
		alert( 'Term group ' + term_group + ' does not exist in map' );
		return false;
	}

	if ( typeof this.mapped_genres[ term_group ][ term_value ] === 'undefined' ) {
		alert( 'Term ' + term_value + ' does not exist in map' );
		return false;
	}

	delete this.mapped_genres[ term_group ][ term_value ][ opt_value ];

	if ( this.get_array_count( this.mapped_genres[ term_group ][ term_value ] ) < 1 ) {
		delete this.mapped_genres[ term_group ][ term_value ];
	}

	return true;
};

PMC_Genre_Settings.prototype.add_mapped_genres_to_list = function( term_value ) {
	if ( typeof term_value === 'undefined' ) {
		return false;
	}

	/*
	 * Empty select list
	 */
	var map_list = '#' + this.get_field_name( 'mapped-genres' );
	jQuery( map_list ).children().remove();

	/*
	 * If term value is empty then there's no point in proceeding forward,
	 * so bail out.
	 */
	if ( ! term_value || pmc.is_empty( term_value ) ) {
		return false;
	}

	var term_group = this.get_term_group( term_value, '#' + this.get_field_name( 'terms' ) );

	if ( typeof this.mapped_genres[ term_group ][ term_value ] === 'undefined' || this.get_array_count( this.mapped_genres[ term_group ][ term_value ] ) < 1 ) {
		return false;
	}

	var self = this;

	jQuery.each( this.mapped_genres[ term_group ][ term_value ], function( index, value ) {
		if ( typeof index === 'undefined' || parseInt( index ) < 1 || typeof value === 'undefined' ) {
			return;
		}

		value = jQuery.trim( value );

		if ( ! value || pmc.is_empty( value ) ) {
			return;
		}

		self.add_option( index, value, map_list );
	} );
};

PMC_Genre_Settings.prototype.set_map_payload_in_form = function( destination ) {
	if ( typeof destination !== 'string' || ! destination ) {
		return false;
	}

	var map_payload = JSON.stringify( this.mapped_genres );
	jQuery( destination ).val( map_payload );

	return true;
};


jQuery( document ).ready( function( $ ) {

	var pmc_gn_settings = new PMC_Genre_Settings();
	pmc_gn_settings.set_mapped_genres( pmc_genre_vars.mapped_genres );

	/*
	 * Map a genre with a selected category/vertical
	 */
	$( '#' + pmc_gn_settings.get_field_name( 'add-btn' ) ).on( 'click', function() {
		var source = '#' + pmc_gn_settings.get_field_name( 'unmapped-genres' );
		var destination = '#' + pmc_gn_settings.get_field_name( 'mapped-genres' );

		var opt = $( ':selected', $( source ) );

		if ( ! opt.val() ) {
			alert( 'Select an available Genre to map' );
			return;
		}

		//add to map array
		var status = pmc_gn_settings.add_mapping( opt.val(), opt.text(), '#' + pmc_gn_settings.get_field_name( 'terms' ) );

		//proceed with list item addition/removal only if genre sucessfully mapped
		if ( status === true ) {
			//add option to mapped genre list
			pmc_gn_settings.add_option( opt.val(), opt.text(), destination );

			//remove from unmapped genre list
			pmc_gn_settings.remove_option( opt.val(), source );
		}
	} );

	/*
	 * Un-map a genre from a selected category/vertical
	 */
	$( '#' + pmc_gn_settings.get_field_name( 'remove-btn' ) ).on( 'click', function() {
		var source = '#' + pmc_gn_settings.get_field_name( 'mapped-genres' );
		var destination = '#' + pmc_gn_settings.get_field_name( 'unmapped-genres' );

		var opt = $( ':selected', $( source ) );

		if ( ! opt.val() ) {
			alert( 'Select a mapped Genre to un-map' );
			return;
		}

		//remove from map array
		var status = pmc_gn_settings.remove_mapping( opt.val(), '#' + pmc_gn_settings.get_field_name( 'terms' ) );

		//proceed with list item addition/removal only if genre sucessfully un-mapped
		if ( status === true ) {
			//remove from mapped genre list
			pmc_gn_settings.remove_option( opt.val(), source );

			//add option to unmapped genre list
			pmc_gn_settings.add_option( opt.val(), opt.text(), destination );
		}
	} );

	/*
	 * Actions that happen when a category/vertical is selected
	 */
	$( '#' + pmc_gn_settings.get_field_name( 'terms' ) ).on( 'change', function() {
		var term_value = $( this ).val();

		/*
		 * Toggle mapping buttons. If no category/vertical is selected
		 * then disable the buttons else enable them.
		 */
		pmc_gn_settings.toggle_mapping_buttons( term_value );

		/*
		 * Populate mapped genre list with genres mapped to currently
		 * selected category/vertical, if any.
		 */
		pmc_gn_settings.add_mapped_genres_to_list( term_value );
	} );

	/*
	 * Handle form submit
	 */
	$( '#' + pmc_gn_settings.get_field_name( 'form' ) ).on( 'submit', function() {
		/*
		 * Set genre map in form
		 */
		pmc_gn_settings.set_map_payload_in_form( '#' + pmc_gn_settings.get_field_name( 'mappings-hdn' ) );
	} );

} );


//EOF