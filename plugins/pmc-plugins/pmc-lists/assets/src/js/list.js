/* global jQuery, pmcList, ajaxurl */
( function( $ ) {

	var pmcListSource = function( name, response ) {
		if ( 'object' !== typeof name || ! name.term ) {
			return;
		}

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajaxurl,
			data: {
				'action': 'pmc_get_lists',
				'query': name.term,
				'nonce': pmcList.nonce
			},
			success: function( data ) {
				if ( 'function' !== typeof response ) {
					return;
				}

				if ( 'object' !== typeof data || true !== data.success || ! data.data ) {
					return;
				}

				response(
					$.map( data.data, function( item, i ) {
						return {
							label: item,
							value: i
						};
					})
				);

			}
		});
	};

	if ( 'undefined' === typeof ajaxurl  || 'object' !== typeof pmcList || ! pmcList.nonce ) {
		return;
	}

	$( '#pmc_list_name' ).autocomplete({
		source: pmcListSource,
		select: function( e, ui ) {
			e.preventDefault();
			$( '.pmc_list_selected' ).html( ui.item.label );
			$( '#pmc_list_id' ).val( ui.item.value );
			$( '#pmc_list_name' ).val( '' );
		}
	});

	$( '#pmc_filter_list' ).autocomplete({
		source: pmcListSource,
		select: function( e, ui ) {
			e.preventDefault();
			$( '#pmc_filter_list_id' ).val( ui.item.value );
			$( '#pmc_filter_list' ).val( ui.item.label );
		}
	});
}( jQuery ) );
