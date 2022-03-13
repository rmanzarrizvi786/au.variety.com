/**
 * script to handle tag autocomplete in sailthru
 *
 * @author Amit Gupta
 */

jQuery( document ).ready( function($) {
	//var containing config for tag autocomplete
	var pmc_sailthru_ac_cfg = {
		min_length: 2,
		current_tags: []
	};

	/**
	 * this function is called by jq-ui autocomplete as data
	 * source for our tag textbox
	 */
	function sailthru_fetch_tag_autocomplete_list( req, res ) {
		if( ! req.term ) {
			res( {} );
		}

		$( "#sailthru-filter-tags option" ).each( function() {
			pmc_sailthru_ac_cfg.current_tags.push( $( this ).val() );
		} );

		$.get(
				ajaxurl,
				{
					action: "sailthru-tag-autocomplete-get-list",
					_sailthru_t_ac_nonce: sailthru_admin_t_ac.nonce,
					current_tags: pmc_sailthru_ac_cfg.current_tags.join( "," ),
					search_on: req.term
				},
				function( data ) {
					if( data ) {
						res( data );
					} else {
						res( {} );
					}
				},
				"json"
		);
	}

	/**
	 * This function is called by jq-ui autocomplete when
	 * a tag from tag list is selected. Here we add the selected
	 * tag to the select list in form
	 */
	function sailthru_add_from_tag_autocomplete_list( event, ui ) {
		if( ! ui.item.value || ! ui.item.label ) {
			return false;
		}

		$( "#sailthru-filter-tags" ).append( $( "<option>", {
			value: ui.item.value,
			text: ui.item.label
		} ) );

		/* return false here to prevent jq-ui autocomplete from adding
		 * ui.item.value to the textbox after this function has been called
		 */
		return false;
	}

	//autocomplete tags
	$( "#sailthru-autocomplete-tags" ).autocomplete( {
		source: sailthru_fetch_tag_autocomplete_list,
		minLength: pmc_sailthru_ac_cfg.min_length,
		select: sailthru_add_from_tag_autocomplete_list
	} )

	//handle selected tag removal
	$( "#btn-sailthru-remove-tags" ).on( "click", function() {
		$( "#sailthru-filter-tags option:selected" ).remove();
	} );

	//select all options from the tag list box on form submit
	$( "#sailthru-edit-recurring-newsletter-form" ).on( 'submit', function() {
		$( "#sailthru-filter-tags option" ).prop( "selected", "selected" );
	} );

	//trigger search on click of textbox if it still has required length of search chars
	$( "#sailthru-autocomplete-tags" ).on( "click", function() {
		if( $( this ).val().length >= pmc_sailthru_ac_cfg.min_length ) {
			$( this ).autocomplete( "search", $( this ).val() );
		}
	} );

} );


//EOF
