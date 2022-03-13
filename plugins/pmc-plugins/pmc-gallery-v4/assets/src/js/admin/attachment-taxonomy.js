window.pmc_gallery_attachment = new function () {

	this.tax_settings_element = '';

	this.set_suggest = function ( id ) {
		if ( jQuery.isFunction( jQuery.suggest ) ) {
			jQuery( '#' + id ).suggest( ajaxurl + "?action=ajax-tag-search&tax=pmc_attachment_tags", {
				multiple: true,
				multipleSep: ","
			} );
		}

		this.add_checkbox();
	};

	this.add_checkbox = function () {
		var ck_name = 'pmc_gallery_mediainput';

		var modal_windows = jQuery( ".attachments-browser .media-toolbar-primary.search-form" );
		var current_form  = '';

		jQuery.each( modal_windows, function( index, form ) {
			if ( false === ( jQuery( form ).is( ':hidden' ) ) ) {
				return current_form = jQuery( form );
			}
		} );

		if ( '' !== current_form && ! current_form.find( '#tmpl-pmc-gallery-attachment-tax-settings' ).length ) {
			if ( '' === this.tax_settings_element ) {
				this.tax_settings_element = jQuery( '#tmpl-pmc-gallery-attachment-tax-settings' );
			}

			current_form.prepend( this.tax_settings_element );

			//Dont have better way to pass if the checkbox is checked therefore use cookie
			jQuery( '#pmc-gallery-media-search-input' ).on( 'change', function () {
				// From the other examples
				if ( this.checked ) {
					pmc.cookie.set( ck_name, 1 );
				} else {
					pmc.cookie.expire( ck_name );
				}
			} );
		}
		//Make sure that if the checkbox is checked or unchecked clear the cookie
		if ( jQuery( '#pmc-gallery-media-search-input' ).prop( 'checked' ) ) {
			pmc.cookie.set( ck_name, 1 );
		} else {
			pmc.cookie.expire( ck_name );
		}

	}
};

//Document Ready
jQuery(
	function () {
		jQuery( "#insert-media-button" ).on( 'click', function () {
			setTimeout( function () {
				window.pmc_gallery_attachment.add_checkbox();
				jQuery( ".media-modal-content .media-router .media-menu-item" ).on( 'click', function () {
					window.pmc_gallery_attachment.add_checkbox();
				} );
			}, 100 );
		} );

		setTimeout( function () {
			jQuery( "#pmc-gallery .media-frame-menu .media-menu-item" ).on( 'click', function () {
				setTimeout( function () {
					window.pmc_gallery_attachment.add_checkbox();
				}, 1000 );
			} );
		}, 1000 );
	}
);
