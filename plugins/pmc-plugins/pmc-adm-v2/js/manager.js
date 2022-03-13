
(function(window, $) {

var AdManager = function() {
	var self = this,
		AJAX_URL = PMC_ADM.url;

	/**
	 * Bind all events and prepare the class.
	 */
	self.initialize = function() {
		// Ads
		$('#new-ad').click(self.createAd);
		$(document).on('click', '.adm-ajax-edit', self.updateAd);
		$(document).on('click', '.adm-ajax-delete', self.deleteAd);
		$(document).on('click', '#action-delete', self.deleteSelectedAds);

		// Forms
		$(document).on('click', '#upload-ad-image', self.uploadImage);
		$(document).on('click', '.adm-provider form .attachment', self.selectImage);
		$(document).on('click', '.adm-provider form .attachment .check span.media-modal-icon', self.deleteImage);
		$(document).on('click', '.adm-form-cancel', self.cancelForm);
		$(document).on('submit', '.adm-provider form', self.handleForm);

		/**
		 * bind event listener to location drop-down change event to show/hide
		 * extra fields for ads interruptus
		 *
		 * @since 2014-05-13 Amit Gupta
		 */
		$( document ).on( 'change', 'select[id$="-location"]', self.showHideInterruptusFields );
		$( document ).on( 'change', 'select[id$="-location"]', self.showHideFloatingPrerollFields );
		$( document ).on( 'change', 'select[id$="-location"]', self.showHideContextualPlayerFields );

		// Conditionals
		$(document).on('click', '.adm-new-condition', self.addCondition);
		$(document).on( 'click', '.adm-new-targeting_data', self.addTargetPair );
		$(document).on('click', '.adm-condition .del-x', self.deleteCondition);
		$(document).on( 'click', '.adm-target_data .del-x', self.deleteTargetPair );

		//Checkbox select all for export
		$('.ad-post-cb-all').change(function () {
			$('input.ad-post-cb').prop( 'checked', $('.ad-post-cb-all').prop('checked') );
			//Disable bulk delete button if nothing is selected
			if( $('.ad-post-cb-all').prop('checked') ) {
				$('#action-delete').prop( "disabled", false );
			} else {
				$('#action-delete').prop( "disabled", true );
			}
		});

		//Disable bulk delete button if nothing is selected
		$('input.ad-post-cb').on('change', function () {
			if ( ! $('input.ad-post-cb:checked').length ) {
				$('#action-delete').prop( "disabled", true );
			} else {
				$('#action-delete').prop( "disabled", false );
			}
		});

		//Submit button for exporter clicked
		$(document).on('click', '#pmc-ads-exporter', self.exportAds)
	};

	/**
	 * Show the image as selected for deletions on click
	 */
	self.selectImage = function() {
		$(this).addClass('selected');
	};

	/**
	 * Delete / Unbind the image that was selected
	 */
	self.deleteImage = function () {
		$('#ad-image-preview')
			.attr('data-id', '')
			.attr('src', '')
			.attr('alt', '')
			.attr('title', '');
		$('#ad-image').val('');
	};

	/**
	 * Cancel the form and reset everything to defaults.
	 *
	 * @return {Boolean}
	 */
	self.cancelForm = function() {
		$(this).parents('.adm-provider').remove();

		return false;
	};

	/**
	 * Handle form submit by posting form data and validating.
	 *
	 * @return {Boolean}
	 */
	self.handleForm = function() {
		var form = $(this),
			inputs = form.find('.form-required'),
			errors = 0;

		inputs.removeClass('form-error');

		// Validate the form
		inputs.find('input').each(function() {
			var input = $(this),
				value = input.val(),
				fail = false;

			// if input is disable, do not do any validation
			if ( input.attr('disabled') ) {
				return;
			}

			if (!value) {
				fail = true;
			}

			switch (input.prop('type').toLowerCase()) {
				case 'number':
					if (isNaN(value))
						fail = true;
				break;
			}

			if ( !fail ) {
				switch (input.attr('validator')) {
					case 'gpt-ad-width':
						try {

							/*
							 * http://jsfiddle.net/coreygilmore/VdY9v/5/
							 */

							var rx = /^\s*(?:\[\s*\d+\s*,\s*\d+\s*\])(?:\s*,\s*\[\s*\d+,\s*\d+\s*\])*\s*$/;
							fail = ! rx.test( value );
						} catch ( e ) {
							fail = true;
						}
						break;
				}
			}

			if (fail) {
				input.parent().addClass('form-error');
				errors++;
			}
		});

		// Submit if no errors
		if (!errors) {
			$.post(AJAX_URL, form.serialize(), self.ajaxCallback, 'json');
		}

		return false;
	};

	/**
	 * Handle the response of an AJAX call.
	 * If an error arises, output it.
	 * If successful, refresh the page.
	 *
	 * @param {Object} response
	 */
	self.ajaxCallback = function(response) {
		if (response.success) {
			location.reload(true);
		} else {
			// @todo
			alert(response.message);
		}
	};

	/**
	 * Fetch the ad create form via AJAX.
	 */
	self.createAd = function() {

		var provider = $('#provider').val();

		jQuery('#provider-forms').empty().html('<div class="loading"></div>');
		jQuery(window).scrollTop(jQuery('#ad-configurations').position().top);

		$.get(AJAX_URL, {
			action: 'adm_view',
			method: 'provider-form',
			provider: provider,
			provider_id: provider
		}, function(response) {
			jQuery('#provider-forms').empty().append(response);
			if ( typeof window.pmc_ad_condition != 'undefined' ) {
				window.pmc_ad_condition.refresh();
			}
			jQuery(window).scrollTop(jQuery('#ad-configurations').position().top);
		});
	};

	/**
	 * Fetch the ad update form via AJAX.
	 */
	self.updateAd = function() {

		var row = $(this).parents('tr'),
			id = row.data('id');

		jQuery('#provider-forms').empty().html('<div class="loading"></div>');
		jQuery(window).scrollTop(jQuery('#ad-configurations').position().top);

		$.get(AJAX_URL, {
			action: 'adm_view',
			method: 'provider-form',
			provider: row.data('provider'),
			provider_id: row.data('provider'),
			id: row.data('id')
		}, function(response) {
			jQuery('#provider-forms').empty().append(response);

			//trigger location drop-down change event
			$( 'select[id$="-location"]' ).trigger( 'change' );

			if ( typeof window.pmc_ad_condition != 'undefined' ) {
				window.pmc_ad_condition.refresh();
			}

			if ( 'oop' == jQuery('#google-publisher-slot-type').val() ) {
				jQuery('#google-publisher-ad-width').attr('disabled',true).parent().hide();
			}

			jQuery(window).scrollTop(jQuery('#ad-configurations').position().top);
		});
	};

	/**
	 * Delete an ad via AJAX.
	 */
	self.deleteAd = function() {
		if (!confirm('Are you sure you want to delete?')) {
			return;
		}

		$.post(AJAX_URL, {
			action: 'adm_crud',
			method: 'delete',
			id: $(this).parents('tr').data('id')
		}, self.ajaxCallback, 'json');
	};

	self.deleteSelectedAds = function () {

		if ( ! $('input.ad-post-cb:checked').length ) {
			alert('You must select at least one Ad to delete.');
			return false;
		}

		if ( ! confirm('Are you sure you want to delete the selected Ads?') ) {
			return;
		}

		var post_ids = $('input.ad-post-cb:checked').map(function () {
			return this.value;
		}).get().join(',');

		$.post(AJAX_URL, {
			action: 'adm_crud',
			method: 'delete',
			post_ids: post_ids
		}, self.ajaxCallback, 'json');
	}

	/**
	 * Add another conditional.
	 */
	self.addCondition = function() {
		var base = $(this).parents('.adm-provider').find('.primary-condition'),
			clone = base.clone(true);

		clone.removeClass('primary-condition').find('input[type!="hidden"], select').val('');
		base.parents('.adm-conditions').append( clone );
	};

	/**
	 * Add another target key/value.
	 */
	self.addTargetPair = function() {
		var base = $(this).parents('.adm-provider').find('.primary-target_data'),
			clone = base.clone(true);

		clone.removeClass('primary-target_data').find('input, select').val('');
		base.parents('.adm-targeting_data').append( clone );
	};

	/**
	 * Delete conditional.
	 */
	self.deleteCondition = function() {
		$(this).parent().remove();
	};

	/**
	 * Delete Targeting Key/Value pair.
	 */
	self.deleteTargetPair = function() {
		$(this).parent().remove();
	};

	self.exportAds = function () {
		var iframe, qstr, url;
		if( ! $('input.ad-post-cb:checked').length ) {
			alert('You must select at least one ad to export.');
			return false;
		}

		url = jQuery("#pmc-ads-exporter").data('url');

		iframe = jQuery("#pmc-ads-exporter-iframe");

		var post_ids = $('input.ad-post-cb:checked').map(function () {
			return this.value;
		}).get().join(',');

		url = url + '&post_ids=' + post_ids;

		if (iframe.length) {
			iframe.attr('src', url);
		}
	};

	self.showHideInterruptusFields = function() {
		if ( typeof pmcadm_interruptus_locations === 'undefined' || ! pmcadm_interruptus_locations ) {
			return false;
		}

		var current_location = $( this ).val();
		var class_name = 'hidden';

		if ( pmcadm_interruptus_locations.indexOf( current_location.toLowerCase() ) !== -1 ) {
			//show fields
			$( '.field-interruptus' ).removeClass( class_name );
		} else {
			//hide fields
			$( '.field-interruptus' ).addClass( class_name );
		}
	};

	self.showHideFloatingPrerollFields = function() {

		var current_location = $( this ).val();
		var class_name = 'hidden';

		if ( typeof pmcadm_floating_preroll_location === 'undefined' || ! pmcadm_floating_preroll_location ) {
			return false;
		}

		if ( pmcadm_floating_preroll_location.indexOf( current_location.toLowerCase() ) !== -1 ) {
			//show fields
			$( '.floating-preroll' ).removeClass( class_name );
		} else {
			//hide fields
			$( '.floating-preroll' ).addClass( class_name );
		}
	};

	self.showHideContextualPlayerFields = function() {

		var current_location = $( this ).val();
		var class_name = 'hidden';

		if ( 'undefined' === typeof pmcadm_contextual_player_location || ! pmcadm_contextual_player_location ) { // eslint-disable-line no-undef
			return false;
		}

		if ( -1 !== pmcadm_contextual_player_location.indexOf( current_location.toLowerCase() ) ) { // eslint-disable-line no-undef
			$( '.contextual-player' ).removeClass( class_name );
		} else {
			$( '.contextual-player' ).addClass( class_name );
		}
	};

	self.uploadImage = function() {

		// If the uploader object has already been created, reopen the dialog
		if (media_uploader) {
		  media_uploader.open();
		  return;
		}

		var media_uploader = wp.media({
			title: 'Select Ad Image',
			button: {
				text: 'Insert into Ad'
			},
			multiple: false,
		});

		media_uploader.on("select", function () {
			var json = media_uploader.state().get("selection").first().toJSON();
			$('#ad-image-preview')
				.attr('data-id', json.id)
				.attr('src', json.url)
				.attr('alt', json.caption)
				.attr('title', json.title);
			$('#ad-image').val(json.url);
		});

		media_uploader.open();
	};

	// Initialize!
	self.initialize();
};

$(function() {
	window.AdManager = new AdManager();
});

})(window, jQuery);


jQuery(document).on('change','#google-publisher-slot-type', function(e){
	switch( jQuery(this).val() ) {
		case 'oop':
			jQuery('#google-publisher-ad-width').attr('disabled',true).parent().hide()
			break;
		default:
			jQuery('#google-publisher-ad-width').attr('disabled',false).parent().show()
			break;
	}
});
