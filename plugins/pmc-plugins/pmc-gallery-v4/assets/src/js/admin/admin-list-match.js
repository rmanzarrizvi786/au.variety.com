/* global jQuery, pmcListV4, ajaxurl */
( function( $ ) {
	var pmcListSource = function( request, response ) {
		if ( 'object' !== typeof request || ! request.term ) {
			return;
		}
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajaxurl,
			data: {
				'action': 'pmc-get-lists',
				'query': request.term,
				'_nonce': pmcListV4._nonce
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

	if ( 'undefined' === typeof ajaxurl  || 'object' !== typeof pmcListV4 || ! pmcListV4._nonce ) {
		console.error( 'No pmcListV4 or nonce' );
		return;
	}
	$( '#pmc_list_name_v4' ).autocomplete({
		source: pmcListSource,
		select: function( e, ui ) {
			e.preventDefault();
			$( '.pmc_list_selected' ).html( ui.item.label );
			$( '#pmc_list_name_v4' ).val( ui.item.label );
			$( '#pmc_list_id' ).val( ui.item.value );
		}
	});

	var recentLists = document.getElementById( 'pmc-recent-lists' );

	if ( recentLists ) {
		recentLists.addEventListener( 'click', function( ev ) {
			if ( ev.target.classList.contains( 'no-click' ) ) {
				ev.preventDefault();
				document.getElementById(
					'pmc_list_id' ).value = ev.target.dataset.pmcListId;
				var listSelected = document.getElementsByClassName( 'pmc_list_selected' );
				listSelected[0].innerHTML = ev.target.dataset.pmcListName;
			}
		} );
	}
}( jQuery ) );
