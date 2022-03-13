;(function($) {
	$(document).on('change', '.fm-categories select', function() {
		var $this = $(this),
			$subcategories = $('.fm-subcategories');
		$subcategories.find('select').html('');

		if ( 0 === $subcategories.find('div.spin').length ) {
			var pmc_span_spin = $('<div/>').addClass('spin').text('Loading...');

			$subcategories.append(pmc_span_spin);
		}

		$subcategories.find('div.spin').show();

		$.ajax({
			method: 'POST',
			url: ajaxurl,
			data: {
				action: 'pmc_get_subcategories',
				pmc_nonce: pmc_fp_admin_fields.nonce,
				cat_parent_id: $this.val(),
			}
		}).done(function(r) {
			if (r.success) {
				$.each(r.data, function(id, name) {
					$subcategories.find('select').append(new Option(name, id));
				});
			}

			$subcategories.find('div.spin').hide();
		});
	});
})(jQuery);
