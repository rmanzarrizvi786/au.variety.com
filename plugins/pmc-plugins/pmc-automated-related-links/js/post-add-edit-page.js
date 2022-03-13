/*
 * PMC Automated Related Links plugin
 *
 * @author Amit Gupta
 * @since 2013-08-08
 * @version 2022-02-02
 */

jQuery( document ).ready( function( $ ) {

	//Initial UI changes
	(function () {
		var container_div = 'div[id*=' + pmc_arl.field_token + ']';

		//add title override textboxes
		$( container_div + ' .pmclinkcontent-post-result' ).each( function() {
			var div_id = $( this ).parent().attr( 'id' );
			var custom_title_id = 'custom_title_' + div_id;

			// Create Span.
			var title_override_node = document.createElement( "span" );
			title_override_node.setAttribute( "id", "span_" + custom_title_id );

			// Create Input of type "Text".
			var title_input_node = document.createElement( "input" );
			title_input_node.setAttribute( "id", custom_title_id );
			title_input_node.setAttribute( "name", custom_title_id );
			title_input_node.setAttribute( "type", "text" );
			title_input_node.setAttribute( "size", "50" );
			title_input_node.setAttribute( "value", $( '#' + div_id + ' a.pmclinkcontent-post' ).text().trim() );

			// Append the Input into the Span.
			title_override_node.appendChild( title_input_node );

			$( '#' + div_id + ' .pmclinkcontent-post-result' ).after( title_override_node );
			$( '#span_' + custom_title_id ).css( { 'display': 'block' } );
		} );

		$( container_div + ' .pmclinkcontent-post-search-label' ).hide();	//hide label before search box
		$( container_div + ' .description' ).hide();	//hide description after search box

		//remove text present before Article/Section Front radio buttons
		//and move the <p> up to the right of search box
		$( '.pmclinkcontent-search-wrapper input:radio[name*=' + pmc_arl.field_token + '].pmclinkcontent-link-article' ).each( function() {
			var parent_p = $( this ).parentsUntil( 'p' ).parent();
			var parent_p_html = $( parent_p ).html().replace( 'Choose What you want to Link.', '' );
			$( parent_p ).html( parent_p_html );
			$( parent_p ).css( { 'width': '30%', 'display': 'inline-block', 'margin-left': '10px' } );
		} );
	})();

	//UI changes on Automated Related Links checkbox state change
	$( '.pmclinkcontent-link-article.checkbox' ).on( 'change', function() {
		var parent_div = $( this ).parentsUntil( '.pmclinkcontent-search-wrapper' ).parent().attr( 'id' );

		if( this.checked ) {
			//checkbox has been checked
			//remove any post links
			$( 'div#' + parent_div + ' .pmclinkcontent-remove' ).trigger( 'click' );

			//hide title override
			$( '#span_custom_title_' + parent_div ).hide();
			//set search box placeholder
			$( 'div#' + parent_div + ' .pmclinkcontent-post-search' ).attr( 'placeholder', 'Automatic' );
			//disable search box
			$( 'div#' + parent_div + ' .pmclinkcontent-post-search' ).prop( 'disabled', true );
		} else {
			//checkbox has been un-checked
			//change placeholder
			var radio_btn = $( 'div#' + parent_div + ' input:radio[name^=pmc_type]:checked' ).val();

			//set search box placeholder
			$( 'div#' + parent_div + ' .pmclinkcontent-post-search' ).attr( 'placeholder', 'Search ' + radio_btn + 's' );
			//enable search box
			$( 'div#' + parent_div + ' .pmclinkcontent-post-search' ).prop( 'disabled', false );
		}
	} );

	//trigger Automated Related Links checkbox change event for first time
	$( '.pmclinkcontent-link-article.checkbox' ).trigger( 'change' );

	//UI changes on Automated Related Links radio button state change
	$( '.pmclinkcontent-search-wrapper input:radio[name*=' + pmc_arl.field_token + ']' ).on( 'change', function() {
		var parent_div = $( this ).parentsUntil( '.pmclinkcontent-search-wrapper' ).parent().attr( 'id' );

		if( ! $( 'div#' + parent_div + ' .checkbox' ).is( ':checked' ) ) {
			$( 'div#' + parent_div + ' .pmclinkcontent-post-search' ).attr( 'placeholder', 'Search ' + $( this ).val() + 's' );
		}
	} );

	//show custom title override when a post is selected from search results
	$( document ).on( 'pmclinkcontent_addpost', function( e ) {
		var elem_id = $( e.elem ).attr( 'id' );

		if( typeof elem_id == undefined || ! elem_id || elem_id.indexOf( 'pmc_arl' ) < 0 ) {
			//not our element
			return;
		}

		//set title override and show it
		$( '#custom_title_' + elem_id ).val( $( '#' + elem_id + ' a.pmclinkcontent-post' ).text() );
		$( '#span_custom_title_' + elem_id ).show();
	} );

	//delete custom title override and hide it when post is unselected
	$( document ).on( 'pmclinkcontent_remove', function( e ) {
		var elem_id = $( e.elem ).attr( 'id' );

		if( typeof elem_id == undefined || ! elem_id || elem_id.indexOf( 'pmc_arl' ) < 0 ) {
			//not our element
			return;
		}

		//delete title override and hide it
		$( '#custom_title_' + elem_id ).val( '' );
		$( '#span_custom_title_' + elem_id ).hide();
	} );

	//module name field focus
	$( '#' + pmc_arl.field_token + 'module_name' ).on( 'focus', function() {
		var module_name_placeholder = $( this ).attr( 'placeholder' );
		var module_name = $( this ).val();

		if( module_name_placeholder == module_name ) {
			$( this ).val( '' );
		}
	} );

	//module name field blur
	$( '#' + pmc_arl.field_token + 'module_name' ).on( 'blur', function() {
		var module_name_placeholder = $( this ).attr( 'placeholder' );
		var module_name = $( this ).val();

		if( ! module_name ) {
			$( this ).val( module_name_placeholder );
		}
	} );

} );

//EOF