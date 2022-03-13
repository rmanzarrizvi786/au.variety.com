/*
 * This script is written to handle PMC Video Playlist manager admin functionality.
 */

/* global PMC_VIDEO_PLAYLIST_MANAGER */

( function( window, $ ) {

	var PMCVideoPlaylistManager = function() {
		var self = this,
			AJAX_URL = PMC_VIDEO_PLAYLIST_MANAGER.url,
			NONCE = PMC_VIDEO_PLAYLIST_MANAGER.nonce;

		/**
		 * Bind all events and prepare the class.
		 */
		self.initialize = function() {

			// Video Provider
			var actionDelete = $( '#action-delete' );

			$( '#new-video-config' ).click( self.createVideoConfig );

			$( document ).on( 'click', '.pvm-ajax-edit', self.updatePvm );
			$( document ).on( 'click', '.pvm-ajax-delete', self.deletePvm );
			$( document ).on( 'click', '#action-delete', self.deleteSelectedPvms );

			// Forms
			$( document ).on( 'click', '.pvm-form-cancel', self.cancelForm );
			$( document ).on( 'submit', '.pvm-provider form', self.handleForm );
			$( document ).on( 'input', '.playlist-search-autocomplete', self.updatePlaylist );
			$( document ).on( 'change', 'input[name="video-count"]', self.updatePlaylistDetail );
			$( document ).on( 'click', '.add-tax-relation', self.addTaxRelation );
			$( document ).on( 'click', '.remove-tax-relation', self.removeTaxRelation );

			$( document ).on( 'keydown', 'input.pvm-target-term', self.updateTerm );
			$( document ).on( 'change', 'select.pvm-target-tax', self.updateTermFieldText );

			$( '.pvm-post-cb-all' ).change( function() {

				var checkBoxAll = $( '.pvm-post-cb-all' );

				$( 'input.pvm-post-cb' ).prop( 'checked', checkBoxAll.prop( 'checked' ) );

				//Disable bulk delete button if nothing is selected
				if ( checkBoxAll.prop( 'checked' ) ) {
					actionDelete.prop( 'disabled', false );
				} else {
					actionDelete.prop( 'disabled', true );
				}
			});

			//Disable bulk delete button if nothing is selected
			$( 'input.pvm-post-cb' ).on( 'change', function() {
				if ( ! $( 'input.pvm-post-cb:checked' ).length ) {
					actionDelete.prop( 'disabled', true );
				} else {
					actionDelete.prop( 'disabled', false );
				}
			});

		};

		/**
		 * Fetch the PVM create form via AJAX.
		 */
		self.createVideoConfig = function() {

			var div = $( '<div>', { 'class': 'loading' });

			$( '#provider-forms' ).empty().append( div );
			$( window ).scrollTop( $( '#video-configurations' ).position().top );

			$.get( AJAX_URL, {
				action: 'pvm_view',
				nonce: NONCE
			}, function( response ) {
				$( '#provider-forms' ).empty().append( response );
				$( window ).scrollTop( $( '#video-configurations' ).position().top );
				self.updateAddTaxButton();
			});
		};

		/**
		 * Cancel the form and reset everything to defaults.
		 *
		 * @return {Boolean}
		 */
		self.cancelForm = function() {
			$( this ).parents( '.pvm-provider' ).remove();

			return false;
		};

		/**
		 * Handle form submit by posting form data and validating.
		 *
		 * @return {Boolean}
		 */
		self.handleForm = function() {

			var form = $( this ),
				inputs = form.find( '.form-required' ),
				errors = 0,
				timeFrame, sTime, eTime, startTime, endTime;

			inputs.removeClass( 'form-error' );

			// Validate the form
			inputs.find( 'input' ).each( function() {
				var input = $( this ),
					value = input.val(),
					fail = false;

				// if input is disable, do not do any validation
				if ( input.attr( 'disabled' ) ) {
					return;
				}

				if ( ! value ) {
					fail = true;
				}

				switch ( input.prop( 'type' ).toLowerCase() ) {

					case 'number':
						if ( isNaN( value ) ) {
							fail = true;
						}
						break;
				}

				if ( fail ) {
					input.parent().addClass( 'form-error' );
					errors++;
				}
			});

			timeFrame = $( 'input.timeframe-end' ).parent();
			sTime = $( 'input.timeframe-start' ).val();
			eTime = $( 'input.timeframe-end' ).val();
			startTime = new Date( sTime ).valueOf();
			endTime = new Date( eTime ).valueOf();

			if ( ( '' !== sTime && isNaN( startTime ) ) || ( '' !== eTime && isNaN( endTime ) ) ) {
				timeFrame.addClass( 'form-error' );
				alert( 'Please enter valid date' );
				errors++;
			} else if ( startTime > endTime ) {
				timeFrame.addClass( 'form-error' );
				$( 'span.error-msg' ).show();
				errors++;
			} else {
				$( 'span.error-msg' ).hide();
				timeFrame.removeClass( 'form-error' );
			}

			// Submit if no errors
			if ( ! errors ) {
				$( 'input[type="submit"]' ).attr( 'disabled', true );
				$.post( AJAX_URL, form.serialize() + '&nonce=' + NONCE, self.ajaxCallback, 'json' );
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
		self.ajaxCallback = function( response ) {

			if ( response.success ) {
				location.reload( true );
			} else {
				$( 'input[type="submit"]' ).attr( 'disabled', false );
				alert( response.message );
			}
		};

		/**
		 * Fetch the PVM update form via AJAX.
		 */
		self.updatePvm = function() {

			var row = $( this ).parents( 'tr' );
			var div = $( '<div>', { 'class': 'loading' });

			$( '#provider-forms' ).empty().append( div );
			$( window ).scrollTop( $( '#video-configurations' ).position().top );

			$.get( AJAX_URL, {
				action: 'pvm_view',
				nonce: NONCE,
				id: row.data( 'id' )
			}, function( response ) {
				$( '#provider-forms' ).empty().append( response );
				$( window ).scrollTop( $( '#video-configurations' ).position().top );
				self.updateAddTaxButton();
			});
		};

		/**
		 * Delete an pvm via AJAX.
		 */
		self.deletePvm = function() {

			if ( ! confirm( 'Are you sure you want to delete?' ) ) {
				return;
			}

			$( 'input[type="submit"]' ).attr( 'disabled', true );

			$.post( AJAX_URL, {
				action: 'pvm_crud',
				nonce: NONCE,
				method: 'delete',
				id: $( this ).parents( 'tr' ).data( 'id' )
			}, self.ajaxCallback, 'json' );
		};

		/**
		 * Delete selected pvms via AJAX.
		 */
		self.deleteSelectedPvms = function() {

			var checkedBox = $( 'input.pvm-post-cb:checked' ),
				post_ids;
			if ( ! checkedBox.length ) {
				alert( 'You must select at least one Video module to delete.' );
				return false;
			}

			if ( ! confirm( 'Are you sure you want to delete the selected Video modules?' ) ) {
				return;
			}

			$( 'input[type="submit"]' ).attr( 'disabled', true );

			post_ids = checkedBox.map( function() {
				return this.value;
			}).get().join( ',' );

			$.post( AJAX_URL, {
				action: 'pvm_crud',
				method: 'delete',
				nonce: NONCE,
				post_ids: post_ids
			}, self.ajaxCallback, 'json' );
		};

		/**
		 * Fetch terms for autocomplete term textbox
		 * @param event
		 */
		self.updateTerm = function( event ) {

			var that = $( this );

			if ( event.keyCode === $.ui.keyCode.TAB &&
				$( this ).autocomplete( 'instance' ).menu.active ) {
				event.preventDefault();
			}

			$( this ).autocomplete({
				minLength: 3,
				source: function( request, response ) {

					// delegate back to autocomplete, but extract the last term
					var tax = that.parent().find( '.pvm-target-tax option:selected' ).val();

					$.post( AJAX_URL, {
						action: 'search_post_term',
						nonce: NONCE,
						taxonomy: tax,
						search: self.extractLast( request.term )
					}, function( data ) {
						response( data.data );
					});

				},
				select: function( event, ui ) {

					var terms = self.split( this.value );

					// remove the current input
					terms.pop();

					// add the selected item
					terms.push( ui.item.value );

					// add placeholder to get the comma-and-space at the end
					terms.push( '' );
					this.value = terms.join( ', ' );
					return false;

				}
			});
		};

		/**
		 * Extract last term from string,
		 * terms separated by comma(,)
		 *
		 * @param term
		 */
		self.extractLast = function( term ) {
			return self.split( term ).pop();
		};

		/**
		 * converts strings into array spits it by ','
		 * @param val string
		 *
		 * @return array
		 */
		self.split = function( val ) {
			return val.split( /,\s*/ );
		};

		/**
		 * Fetch playlist terms list on user input to fill autocomplete suggestion
		 */
		self.updatePlaylist = function() {

			var count = $( 'input[name="video-count" ]' );

			$( this ).autocomplete({
				minLength: 3,
				autoFocus: true,
				source: function( request, response ) {

					count.attr( 'min', '5' );
					count.attr( 'max', '10' );
					count.val( 5 );

					$.post( AJAX_URL, {
						action: 'search_playlist',
						nonce: NONCE,
						search: request.term
					}, function( data ) {
						response( data.data );
					});
				},
				select: function( request, response ) {
					self.fillPlaylistDetail( response.item.value );

				}
			});
		};

		/**
		 * Fires when video count number change
		 * Update the Featured video dropdown list
		 */
		self.updatePlaylistDetail = function() {
			var check = this.checkValidity();
			var playlist = $( '.playlist-search-autocomplete' ).val();
			if ( check && playlist ) {
				self.fillPlaylistDetail( playlist );
			}
		};

		/**
		 * Update the Featured video dropdown list
		 * @param playlist Playlist term slug to fetch videos from
		 */
		self.fillPlaylistDetail = function( playlist ) {

			var count = $( 'input[name="video-count"]' ),
				select = document.getElementById( 'get-featured-video' ),
				opt;

			$.post( AJAX_URL, {
				action: 'get_playlist_details',
				nonce: NONCE,
				count: count.val(),
				playlist: playlist
			}, function( data ) {

				$( select ).find( 'option' ).remove();

				if ( data.success ) {
					select.disabled = false;

					if ( 5 > data.data.count ) {
						count.attr( 'min', data.data.count );
						count.attr( 'max', data.data.count );
						count.val( data.data.count );
					} else if ( 10 > data.data.count ) {
						count.attr( 'min', '5' );
						count.attr( 'max', data.data.count );
					}

					$.each( data.data.items, function( key, value ) {
						var opt = document.createElement( 'option' );
						opt.value = key;
						opt.innerHTML = value;
						select.appendChild( opt );
					});
				} else {
					opt = document.createElement( 'option' );
					opt.innerHTML = data.data.msg;
					select.appendChild( opt );
				}
			});
		};

		/**
		 * Adds new target page rule.
		 * also validates the current target rule (empty check)
		 */
		self.addTaxRelation = function() {

			var inputTax = $( 'input[name="target-term[]"]' ),
				fail = false,
				cloneEl;

			$.each( inputTax, function() {
				var input = $( this ),
					value = input.val();

				if ( ! value ) {
					fail = input;
					input.focus();
				}
			});

			if ( ! fail ) {

				cloneEl = $( '.add-tax-relation-row:first' )[ 0 ];
				cloneEl = $( cloneEl ).clone();

				cloneEl.find( 'select[name="target-tax[]"]' ).val( $( 'select[name="target-tax[]"] option:first' ).val() );
				cloneEl.find( 'input[name="target-term[]"]' ).val( '' );
				$( 'div.pvm-tax-relation' ).append( cloneEl );

				self.updateAddTaxButton();
			}
		};

		/**
		 * Removes target page rule
		 * And updates the add/delete rule button
		 */
		self.removeTaxRelation = function() {
			$( this ).parent( 'div.add-tax-relation-row' ).remove();
			self.updateAddTaxButton();
		};

		/**
		 * updates the add/delete target rule button.
		 */
		self.updateAddTaxButton = function() {
			var addButton = $( '.add-tax-relation-row a' );

			if ( 1 === $( addButton ).size() ) {
				$( addButton ).addClass( 'add-tax-relation' ).removeClass( 'remove-tax-relation' ).text( '+' );
			} else {
				$( addButton ).removeClass( 'add-tax-relation' ).addClass( 'remove-tax-relation' ).text( 'x' );
			}

		};

		/**
		 * Update placeholder for target term textbox
		 */
		self.updateTermFieldText = function() {
			$( this ).parent().find( 'input.pvm-target-term' ).attr( 'placeholder', 'Insert ' + $( this ).val() + ', separated by commas' );
		};

		// Initialize!
		self.initialize();
	};

	$( function() {
		window.PMCVideoPlaylistManager = new PMCVideoPlaylistManager();
	});

}( window, jQuery ) );
