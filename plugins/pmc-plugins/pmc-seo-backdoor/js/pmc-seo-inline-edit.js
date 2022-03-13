(
	function ( $ ) {
		pmc_seo_inline_edit = {

			init: function () {
				var t = this, qeRow = $( '#inline-edit' );

				t.type = 'post';
				t.what = '#post-';

				// prepare the edit rows
				qeRow.keyup( function ( e ) {
					if ( e.which === 27 ) {
						return pmc_seo_inline_edit.revert();
					}
				} );

				$( 'a.cancel', qeRow ).click( function () {
					return pmc_seo_inline_edit.revert();
				} );
				$( 'a.save', qeRow ).click( function () {
					return pmc_seo_inline_edit.save( this );
				} );
				$( 'td', qeRow ).keydown( function ( e ) {
					if ( e.which === 13 ) {
						return pmc_seo_inline_edit.save( this );
					}
				} );

				// add events
				$( 'a.editinline' ).live( 'click', function () {
					pmc_seo_inline_edit.edit( this );
					return false;
				} );

				$( '#post-query-submit' ).mousedown( function ( e ) {
					t.revert();
					$( 'select[name^="action"]' ).val( '-1' );
				} );
			},

			toggle: function ( el ) {
				var t = this;
				$( t.what + t.getId( el ) ).css( 'display' ) == 'none' ? t.revert() : t.edit( el );
			},

			edit: function ( id ) {
				var t = this, fields, editRow, rowData, f;
				t.revert();

				if ( typeof(
						id
					) == 'object' ) {
					id = t.getId( id );
				}

				fields = [
					"pmc_seo_title",
					"pmc_seo_description",
					"pmc_seo_keywords",
					"pmc_seo_slug",
					"post_status",
					"post_content"
				];

				// add the new blank row
				editRow = $( '#inline-edit' ).clone( true );
				$( 'td', editRow ).attr( 'colspan', $( '.widefat:first thead th:visible' ).length );

				if ( $( t.what + id ).hasClass( 'alternate' ) ) {
					$( editRow ).addClass( 'alternate' );
				}
				$( t.what + id ).hide().after( editRow );

				// populate the data
				rowData = $( '#inline_' + id );

				for ( f = 0; f < fields.length; f ++ ) {
					$( ':input[name="' + fields[f] + '"]', editRow ).val( $( '.' + fields[f], rowData ).text() );
				}

				// prevent editing the slug if the post is published
				var post_status = $( "#inline_" + id + " .post_status" ).text();
				if ( "publish" === post_status ) {
					$( editRow ).find( ':input[name="pmc_seo_slug"]' ).prop( "disabled", true ).css( "background-color", "#eee" );
				}

				$( editRow ).attr( 'id', 'edit-' + id ).addClass( 'inline-editor' ).show();
				$( '.ptitle', editRow ).focus();

				return false;
			},

			save: function ( id ) {
				var params, fields, page = $( '.post_status_page' ).val() || '';

				if ( typeof(
						id
					) == 'object' ) {
					id = this.getId( id );
				}

				$( 'table.widefat .inline-edit-save .waiting.spinner' ).css( 'visibility', 'visible' );

				params = {
					action: 'pmc-seo-inline-save',
					post_ID: id,
					post_status: page
				};

				// If the SEO title is the same as the original title, don't save it
				// We have this exception here because the SEO title is pre-populated,
				// whereas other fields are not.
				// This also lets us check off the "has-seo" image.
				var original_title = $( "span.original-title-" + id ).text();
				var seo_title = $( "#edit-" + id + " :input[name='pmc_seo_title']" ).val();
				if ( original_title === seo_title ) {
					$( "#edit-" + id + " :input[name='pmc_seo_title']" ).val( "" );
					$( "#post-" + id + " span.has-seo" ).removeClass( 'dashicon-yes' ).addClass( 'dashicon-no' );
				} else {
					$( "#post-" + id + " span.has-seo" ).removeClass( 'dashicon-no' ).addClass( 'dashicon-yes' );
				}

				fields = $( '#edit-' + id + ' :input' ).serialize();
				params = fields + '&' + $.param( params );

				// make ajax request
				$.post( ajaxurl, params,
					function ( r ) {
						$( 'table.widefat .inline-edit-save .waiting.spinner' ).css( 'visibility', 'hidden' );

						if ( r ) {
							if ( - 1 != r.indexOf( '<tr' ) ) {
								$( pmc_seo_inline_edit.what + id ).remove();
								$( '#edit-' + id ).before( r ).remove();
								$( pmc_seo_inline_edit.what + id ).hide().fadeIn();
							} else {
								r = r.replace( /<.[^<>]*?>/g, '' );
								$( '#edit-' + id + ' .inline-edit-save .error' ).html( '' ).text( r ).show();
							}
						} else {
							$( '#edit-' + id + ' .inline-edit-save .error' ).html( '' ).text( pmc_seo_inline_edit_l10n.error ).show();
						}
					}
					, 'html' );
				return false;
			},

			revert: function () {
				var id = $( 'table.widefat tr.inline-editor' ).attr( 'id' );

				if ( id ) {
					$( 'table.widefat .inline-edit-save .waiting' ).hide();

					$( '#' + id ).remove();
					id = id.substr( id.lastIndexOf( '-' ) + 1 );
					$( this.what + id ).show();
				}

				return false;
			},

			getId: function ( o ) {
				var id = $( o ).closest( 'tr' ).attr( 'id' ),
					parts = id.split( '-' );
				return parts[parts.length - 1];
			}
		};

		$( document ).ready( function () {
			pmc_seo_inline_edit.init();
		} );

		// Show/hide locks on featured image backdoors
		$( document ).on( 'heartbeat-tick.wp-check-locked-featured-image-backdoor', function ( e, data ) {
			var locked = data['wp-check-locked-posts'] || {};

			$( '#the-list tr' ).each( function ( i, el ) {
				var key = el.id, row = $( el ), lock_data, avatar;

				if ( locked.hasOwnProperty( key ) ) {
					if ( ! row.hasClass( 'wp-locked' ) ) {
						lock_data = locked[key];
						row.find( '.column-title .locked-text' ).text( lock_data.text );
						row.find( '.check-column checkbox' ).prop( 'checked', false );

						if ( lock_data.avatar_src ) {
							avatar = $( '<img class="avatar avatar-18 photo" width="18" height="18" alt="" />' ).attr( 'src', lock_data.avatar_src.replace( /&amp;/g, '&' ) );
							row.find( '.column-title .locked-avatar' ).empty().append( avatar );
						}
						row.addClass( 'wp-locked' );
					}
				} else if ( row.hasClass( 'wp-locked' ) ) {
					// Make room for the CSS animation
					row.removeClass( 'wp-locked' ).delay( 1000 ).find( '.locked-info span' ).empty();
				}
			} );

		} ).on( 'heartbeat-send.wp-check-locked-featured-image-backdoor', function ( e, data ) {
			var check = [];

			$( '#the-list tr' ).each( function ( i, el ) {
				if ( el.id ) {
					check.push( el.id );
				}
			} );

			if ( check.length ) {
				data['wp-check-locked-posts'] = check;
			}
		} ).ready( function () {
			// Set the heartbeat interval to 15 sec.
			if ( typeof wp !== 'undefined' && wp.heartbeat ) {
				wp.heartbeat.interval( 15 );
			}
		} );
	}
)( jQuery );
