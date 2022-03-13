/*!
 * PMC Gallery v4 JavaScript Library
 */
import './../scss/admin-list.scss';

( function( $ ) {
	let animateToPmcListItem; // eslint-disable-line prefer-const
	let showOrderChange;
	let updateAction; // eslint-disable-line prefer-const
	let updateIndexes; // eslint-disable-line prefer-const
	let updateBySortOrder; // eslint-disable-line prefer-const
	const pmc_list_items = window.pmc_list_items; // eslint-disable-line camelcase

	const $pmcListItem = $( '#pmc-list-items' );
	const itemTemplate = wp.template( 'list-item-template' );

	updateAction = function() { // eslint-disable-line prefer-const
		$( this ).closest( 'li' ).find( '.pmcListItem-action' ).val( 'update' );
	};

	/* Load initial set of pmcListItems into pmcListItems list */
	if ( pmc_list_items && pmc_list_items.length ) { //eslint-disable-line camelcase
		for ( let i = 0; i < pmc_list_items.length; i++ ) {
			const item = pmc_list_items[ i ];

			item.index = i;

			const $li = $( '<li>', { class: 'sortable-item' } );

			$li.html( itemTemplate( item ).replace( /^\s+/, '' ).replace( /\s+$/, '' ) );

			$li.data( 'item', item );

			$li.addClass( 'existing' );
			$li.addClass( item.type + '-media' );

			if ( 'image' === item.type ) {
				if ( item.imageSrc ) {
					$li.addClass( 'attached-image' );
				} else if ( item.src ) {
					$li.addClass( 'remote-image' );
				}
			}

			$pmcListItem.append( $li );
			$pmcListItem.closest( '.admin-pmcListItem-container' ).removeClass( 'empty-pmcListItem' );
		}
	}

	updateIndexes = function() { // eslint-disable-line prefer-const
		const updateAttributes = [ 'name' ];
		const $lis = $pmcListItem.find( 'li' );
		let itemOrder = 0;

		$lis.each( function() {
			const $li = $( this );

			const itemData = $li.data( 'item' );

			if ( 'delete' !== $li.find( '.pmcListItem-action' ).val() ) {
				$li.find( '.sort-order' ).val( itemOrder + 1 );

				$li.find( 'input,textarea' ).each( function() {
					let j;
					let attr;
					let val;
					const $this = $( this );

					for ( j = 0; j < updateAttributes.length; j++ ) {
						attr = updateAttributes[ j ];
						val = $this.attr( attr );
						if ( val ) {
							$this.attr( attr, val.replace( /\d+/, itemOrder ) );
						}
					}
				} );

				if ( ! itemData || ( itemData && itemData.index !== itemOrder ) ) {
					$li.each( updateAction );
				}
				itemOrder++;
			}
		} );
	};
	// Allow media items to be sorted by dragging
	$pmcListItem.sortable( {
		update: updateIndexes,
	} );

	updateBySortOrder = function() { // eslint-disable-line prefer-const
		const listitems = $pmcListItem.children( 'li' ).get();
		listitems.sort( function( a, b ) {
			const compA = parseInt( $( a ).find( 'input.sort-order' ).val(), 10 );
			const compB = parseInt( $( b ).find( 'input.sort-order' ).val(), 10 );

			return ( compA < compB ) ? -1 : ( compA > compB ) ? 1 : -1; // eslint-disable-line no-nested-ternary
		} );

		$.each( listitems, function( idx, itm ) {
			$pmcListItem.append( itm );
		} );

		// Toggle empty-pmcListItem class on top-level container if it is indeed empty
		if ( 0 < listitems.length ) {
			$pmcListItem.closest( '.admin-pmcListItem-container' ).removeClass( 'empty-pmcListItem' );
		} else {
			$pmcListItem.closest( '.admin-pmcListItem-container' ).addClass( 'empty-pmcListItem' );
		}

		updateIndexes();
	};

	animateToPmcListItem = function( $item ) { // eslint-disable-line prefer-const
		$( 'html, body' ).animate( {
			scrollTop: $item.offset().top - 100,
		}, 500 );
	};

	$pmcListItem.on( 'click', '.remove-list-item', function( ev ) {
		const removeInput = document.createElement( 'input' );
		removeInput.setAttribute( 'name', 'pmc-listitems-remove[]' );
		removeInput.setAttribute( 'type', 'hidden' );
		removeInput.setAttribute( 'class', 'pmc-listitems-remove' );
		let listItemLi = $( ev.target ).parents( 'li' );
		if ( listItemLi && listItemLi[ 0 ] ) {
			listItemLi = listItemLi[ 0 ];
			const listItemId = listItemLi.getElementsByClassName( 'pmcListItem-id' )[ 0 ].value;
			removeInput.value = listItemId;
			document.getElementById( 'post' ).append( removeInput );
			listItemLi.remove();
			updateIndexes();
		}
	} );

	showOrderChange = function( el ) { // eslint-disable-line prefer-const
		if ( $( el ).length ) {
			animateToPmcListItem( $( el ).parent( 'li' ) );
			$( el ).parent( 'li' ).fadeTo( 200, 0.3 ).fadeTo( 200, 1 ).fadeTo( 200, 0.5 ).fadeTo( 200, 1 ).fadeTo( 200, 0.5 ).fadeTo( 200, 1 );
		}
	};

	$( document ).on( 'keyup', '#pmc-list-items .sort-order', function() {
		this.value = this.value.replace( /[^0-9.]/g, '' );
	} ).on( 'keydown', '#pmc-list-items .sort-order', function( ev ) {
		if ( 13 === ev.keyCode ) {
			ev.preventDefault();
		}
	} ).on( 'keypress keyup', '#pmc-list-items .sort-order', function( ev ) {
		if ( 13 === ev.keyCode ) {
			updateBySortOrder();
			showOrderChange( $( this ) );
			ev.preventDefault();
			ev.stopPropagation();
		}
	} ).on( 'change', '#pmc-list-items .sort-order', function() {
		updateBySortOrder();
		showOrderChange( $( this ) );
	} );

	// Override original post submission behavior. Save each item with a marked action
	// in turn until they are all done, then let the form submit
	$( '#post' ).off( 'submit' );

	// Let me use Array.shift!
	$.fn.shift = [].shift;

	$( '#pmc-list-items' ).on( 'change', 'input, textarea', updateAction );
}( jQuery ) );
