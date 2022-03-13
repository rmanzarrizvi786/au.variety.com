jQuery(document).ready(function () {

	if (typeof pmc_primary_taxonomy_data == "undefined") {
		return;
	}

	var data = pmc_primary_taxonomy_data;

	for (i = 0; i < data.length; i++) {

		var div_id = data[i][0];

		var taxonomy_slug = data[i][1];

		jQuery( '#' + div_id + '_div>.handlediv' ).remove();
		jQuery( '#' + div_id + '_div>.hndle' ).remove();

		if (jQuery('label[for="' + div_id + '_div-hide"]').length) {
			jQuery('label[for="' + div_id + '_div-hide"]').remove();
		}

		jQuery('#' + div_id + "_div").attr('id',div_id+'_div_box').hide().detach().insertAfter(jQuery('#' + taxonomy_slug + '-all'));

		// Hide primary taxonomy box
		jQuery('#primary-' + data[i][1] + '-div').hide();

		// Set events
		jQuery('#' + taxonomy_slug + 'checklist').find('input:checkbox').click({ div_id: div_id, taxonomy_slug: taxonomy_slug }, function (event) {

			var checked = jQuery(this).closest('ul').find('input:checked');
			pmc_primary_taxonomy(checked, event.data.div_id, event.data.taxonomy_slug);

		});

		jQuery('#'+div_id).change({taxonomy_slug: taxonomy_slug}, function (event) {
			pmc_primary_selected_arr[event.data.taxonomy_slug] = this.value;
		});

		jQuery('#'+div_id).val(pmc_primary_selected_arr[taxonomy_slug]);

		//On Load
		var checked_load = jQuery('#' + taxonomy_slug + 'checklist').find('input:checked');

		pmc_primary_taxonomy(checked_load, div_id, taxonomy_slug);

	}

});

function pmc_primary_taxonomy(checked, div_id, taxonomy_slug) {

	// Reset options
	jQuery('#'+div_id).empty();

	// Add options based on checked inputs
	if (checked.length > 1) {

		jQuery('#'+div_id + "_div_box").show();

		checked.each(function () {

			var self = jQuery(this);

			jQuery('<option/>', {
				value   : self.val(),
				text    : self.parent().text().trim(),
				selected: (pmc_primary_selected_arr[taxonomy_slug] == self.val())
			}).appendTo(jQuery('#'+div_id));
		});
	} else {
		jQuery('#'+div_id + "_div_box").hide();
		pmc_primary_selected_arr[taxonomy_slug] = null;
	}
}
