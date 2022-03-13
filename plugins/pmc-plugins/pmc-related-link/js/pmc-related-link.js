/**
 * The following was originally copied from wp-includes/js/wplink.js
 * And we modified WP_Link and wpLink to relatedLink and modified
 * the mceUpdate() and htmlUpdate() functions to return a shortcode
 * instead of an anchor tag
 */

/**
 * @uses ajaxurl
 * @uses tinymce
 * @uses relatedLinkL10n
 * @uses setUserSetting
 * @uses wpActiveEditor

 */
var relatedLink;

( function( $, window, document ) {
    'use strict';

    var inputs = {}, rivers = {}, editor, searchTimer, RelatedLinkRiver, RelatedLinkQuery;

    relatedLink = {
        timeToTriggerRiver: 150,
        minRiverAJAXDuration: 200,
        riverBottomThreshold: 5,
        keySensitivity: 100,
        lastSearch: '',
        textarea: '',

        init: function() {
            inputs.wrap = $('#related-link-wrap');
            inputs.dialog = $( '#related-link' );
            inputs.backdrop = $( '#related-link-backdrop' );
            inputs.submit = $( '#related-link-submit' );
            inputs.close = $( '#related-link-close' );
            // URL
            inputs.url = $( '#related-url-field' );
            inputs.nonce = $( '#_ajax_linking_nonce' );
            // Secondary options
            inputs.title = $( '#related-link-title-field' );
            inputs.type = $( '#related-link-type-field' );
            inputs.manualtype = $( '#related-link-manualtype-field' );
            // Advanced Options
            inputs.openInNewTab = $( '#related-link-target-checkbox' );
            inputs.search = $( '#related-search-field' );
            // Build Rivers
            rivers.search = new RelatedLinkRiver( $( '#related-search-results' ) );
            rivers.recent = new RelatedLinkRiver( $( '#related-most-recent-results' ) );
            rivers.elements = inputs.dialog.find( '.query-results' );

            // Bind event handlers
            inputs.dialog.keydown( relatedLink.keydown );
            inputs.dialog.keyup( relatedLink.keyup );
            inputs.submit.click( function( event ) {
                event.preventDefault();
                relatedLink.update();
            });
            inputs.close.add( inputs.backdrop ).add( '#related-link-cancel a' ).click( function( event ) {
				event.preventDefault();
				relatedLink.close();
			});

			$( '#related-link-search-toggle' ).click( relatedLink.toggleInternalLinking );

			rivers.elements.on( 'river-select', relatedLink.updateFields );

			inputs.search.keyup( function() {
				var self = this;

				window.clearTimeout( searchTimer );
				searchTimer = window.setTimeout( function() {
					relatedLink.searchInternalLinks.call( self );
				}, 500 );
			});
		},

		open: function( editorId ) {
			var ed;

			relatedLink.range = null;

			if ( editorId ) {
				window.wpActiveEditor = editorId;
			}

			if ( ! window.wpActiveEditor ) {
				return;
			}

			this.textarea = $( '#' + window.wpActiveEditor ).get( 0 );

			if ( typeof tinymce !== 'undefined' ) {
				ed = tinymce.get( wpActiveEditor );

				if ( ed && ! ed.isHidden() ) {
					editor = ed;
				} else {
					editor = null;
				}

				if ( editor && tinymce.isIE ) {
					editor.windowManager.bookmark = editor.selection.getBookmark();
				}
			}

			if ( ! relatedLink.isMCE() && document.selection ) {
				this.textarea.focus();
				this.range = document.selection.createRange();
			}

			inputs.wrap.show();
			inputs.backdrop.show();

			var openinnewtab = ( "" === relatedLinkL10n.openInNewTab ) ? false : true;
			inputs.openInNewTab.prop( 'checked',  openinnewtab );

            if( "" === relatedLinkL10n.showManualType) {
                $('label.manualtype').hide();
            }

			relatedLink.refresh();
		},

		isMCE: function() {
			return editor && ! editor.isHidden();
		},

		refresh: function() {
			// Refresh rivers (clear links, check visibility)
			rivers.search.refresh();
			rivers.recent.refresh();

			if ( relatedLink.isMCE() ) {
				relatedLink.mceRefresh();
            } else {
				relatedLink.setDefaultValues();
            }

			// Focus the URL field and highlight its contents.
			//     If this is moved above the selection changes,
			//     IE will show a flashing cursor over the dialog.
			inputs.url.focus()[0].select();
			// Load the most recent results if this is the first time opening the panel.
			if ( ! rivers.recent.ul.children().length ) {
				rivers.recent.ajax();
            }
		},

		// This method fires when the 'Related Link' button is clicked
		// it's purpose is to populate the add link popup input fields
		// with either the currently selected related link info
		// or by populatting with default values
		mceRefresh: function() {
			var relatedLinkMarkup, $relatedLinkMarkupAnchor;

			// Select the p.pmc-related-link
			relatedLinkMarkup = editor.dom.getParent(editor.selection.getNode(), 'p.pmc-related-link' );

			if ( relatedLinkMarkup ) {

                $relatedLinkMarkupAnchor = $(relatedLinkMarkup).find('a');
				// Set URL and description.
				inputs.url.val( $(relatedLinkMarkup).attrs('related-link-url') );
				inputs.title.val( $(relatedLinkMarkup).attrs('related-link-content'));

				// Set open in new tab.
				inputs.openInNewTab.prop( 'checked', ( '_blank' === $relatedLinkMarkupAnchor.prop('target') ) );

				inputs.type.val ( $(relatedLinkMarkup).attrs('related-link-content' ));

				// Update save prompt.
				inputs.submit.val( relatedLinkL10n.update );
			} else {
				// If there's no related link, set the default values.
				relatedLink.setDefaultValues();
			}
		},

		close: function() {
			if ( ! relatedLink.isMCE() ) {
				relatedLink.textarea.focus();

				if ( relatedLink.range ) {
					relatedLink.range.moveToBookmark( relatedLink.range.getBookmark() );
					relatedLink.range.select();
				}
			} else {
				editor.focus();
			}

			inputs.backdrop.hide();
			inputs.wrap.hide();
		},

		getAttrs: function() {
			return {
				href: inputs.url.val(),
				title: inputs.title.val(),
				type: inputs.type.val(),
                                manualtype: inputs.manualtype.val(),
				target: inputs.openInNewTab.prop( 'checked' ) ? '_blank' : ''
			};
		},

		update: function() {
			if ( relatedLink.isMCE() ) {
				relatedLink.mceUpdate();
            } else {
				relatedLink.htmlUpdate();
            }
		},

		htmlUpdate: function() {
			var attrs, html, begin, end, cursor, selection,
				textarea = relatedLink.textarea;

			if ( ! textarea ) {
				return;
            }

			attrs = relatedLink.getAttrs();

			// If there's no href, return.
			if ( ! attrs.href || attrs.href === 'http://' ) {
				return;
            }

			// Build HTML / Shortcode
			html = '[pmc-related-link url="' + attrs.href + '"]';

			// Insert HTML
			if ( document.selection && relatedLink.range ) {
				// IE
				// Note: If no text is selected, IE will not place the cursor
				//       inside the closing tag.
				textarea.focus();
				relatedLink.range.text = html + relatedLink.range.text + '[/pmc-related-link]';
				relatedLink.range.moveToBookmark( relatedLink.range.getBookmark() );
				relatedLink.range.select();

				relatedLink.range = null;
			} else if ( typeof textarea.selectionStart !== 'undefined' ) {
				// W3C
				begin       = textarea.selectionStart;
				end         = textarea.selectionEnd;
				selection   = textarea.value.substring( begin, end );
				//html        = html + selection + '</a>';
				html        = html + selection + '[/pmc-related-link]';
				cursor      = begin + html.length;

				// If no text is selected, place the cursor inside the closing tag.
				if ( begin === end ) {
					cursor -= '[/pmc-related-link]'.length;
                }

				textarea.value = textarea.value.substring( 0, begin ) + html +
					textarea.value.substring( end, textarea.value.length );

				// Update cursor position
				textarea.selectionStart = textarea.selectionEnd = cursor;
			}

			relatedLink.close();
			textarea.focus();
		},

		mceUpdate: function() {
			var relatedLinkMarkup, $relatedLinkMarkupAnchor, $relatedLinkMarkupStrong, type_slug, manualtype_slug, shortcode;
			var attrs = relatedLink.getAttrs();

			relatedLink.close();
			editor.focus();

			if ( tinymce.isIE ) {
				editor.selection.moveToBookmark( editor.windowManager.bookmark );
			}

			// If the values are empty, unlink and return
			if ( ! attrs.href || attrs.href === 'http://' || attrs.href === 'https://' || (attrs.type === "Select One" && attrs.manualtype =="") ) {
				editor.execCommand( 'unlink' );
                if( attrs.type === "Select One" && attrs.manualtype=='' ){
                    alert( 'Please select a related link type when inserting a related link. Thank you.');

                }
				return;
			}

			// Select the div.pmc-related-link
			relatedLinkMarkup = editor.dom.getParent(editor.selection.getNode(), 'div.pmc-related-link' );

			if ( relatedLinkMarkup ) {
				$relatedLinkMarkupAnchor = $(relatedLinkMarkup).find('a');
				$relatedLinkMarkupStrong = $(relatedLinkMarkup).find('strong');

				// Create a 'slug' version of the link type
				// 'RELATED' becomes 'related'
				// 'Important Link' becomes 'important-link'
				type_slug = attrs.type
					.toLowerCase()
					.replace(/[^\w ]+/g,'')
					.replace(/ +/g,'-');

                manualtype_slug = attrs.manualtype
					.toLowerCase()
					.replace(/[^\w ]+/g,'')
					.replace(/ +/g,'-');

				editor.dom.setAttrib( relatedLinkMarkup, 'class', 'pmc-related-link');
				editor.dom.setAttribs( $relatedLinkMarkupAnchor, attrs );
				editor.dom.setHTML( $relatedLinkMarkupAnchor, attrs.title );

                if ( attrs.manualtype != '') {
                    editor.dom.addClass( relatedLinkMarkup, manualtype_slug );
					editor.dom.setHTML( $relatedLinkMarkupStrong, attrs.manualtype );
                }
				else if ( attrs.type !== 'Select One' ) {
					editor.dom.addClass( relatedLinkMarkup, type_slug );
					editor.dom.setHTML( $relatedLinkMarkupStrong, attrs.type );
				}
			} else {

				// Begin the starting shortcode section
				shortcode = '[pmc-related-link href="' + attrs.href + '"';

				// Add the target attribute if it was selected in the modal
				if (attrs.target.length > 0) {
					shortcode += ' target="' + attrs.target + '"';
				}

                // Add the target attribute if it was selected in the modal
                if (attrs.manualtype != '') {
					shortcode += ' type="' + attrs.manualtype + '"';
				} else if (attrs.type !== 'Select One') {
					shortcode += ' type="' + attrs.type + '"';
				}

				// Close the starting shortcode section
				shortcode += ']';

				// If a title is given use that as the shortcode contents
				// Otherwise, if text was selected in the editor prior to clicking 'Related Link',
				// Use the selected text as the shortcode contents
				if (attrs.title.length > 0) {
					shortcode += attrs.title;
				} else {
					var selected_text = editor.selection.getContent();
					if (selected_text.length > 0) {
						shortcode += selected_text ;
					}
				}

				// Close the last shortcode section
				shortcode += '[/pmc-related-link]';

				// Insert the shortcode html into the editor
				editor.execCommand( 'mceInsertContent', false, shortcode );
			}

			// Move the cursor to the end of the selection
			editor.selection.collapse();
		},

		updateFields: function( e, li, originalEvent ) {
			inputs.url.val( li.children( '.item-permalink' ).val() );
			inputs.title.val( li.hasClass( 'no-title' ) ? '' : li.children( '.item-title' ).text() );
			if ( originalEvent && originalEvent.type === 'click' ) {
				inputs.url.focus();
            }
		},

		setDefaultValues: function() {
			var relatedLinkMarkup;

            // Set URL and description to defaults.
			// Leave the new tab setting as-is.
			inputs.url.val( 'http://' );

			// If text was selected add it as the title
			// unless the selected text is the whole related link markup
			// i.e. the user selected the whole related link then clicked 'Related Link'
			var selected_text = editor.selection.getContent();

			// Select the div.pmc-related-link
			relatedLinkMarkup = editor.dom.getParent(editor.selection.getNode(), 'div.pmc-related-link' );

			if ( relatedLinkMarkup ) {
				// the user has selected the whole <div related link code
				// and they are actually trying to update not start a new
				// @todo bounce back to the update routine
			} else {
				inputs.title.val(selected_text);
			}

            var openinnewtab = ( "" === relatedLinkL10n.openInNewTab ) ? false : true;
            inputs.openInNewTab.prop( 'checked',  openinnewtab );

            if( "" === relatedLinkL10n.showManualType) {
                $('label.manualtype').hide();
            }

			// Update save prompt.
			inputs.submit.val( relatedLinkL10n.save );
		},

		searchInternalLinks: function() {
			var t = $( this ), waiting,
				search = t.val();

			if ( search.length > 2 ) {
				rivers.recent.hide();
				rivers.search.show();

				// Don't search if the keypress didn't change the title.
				if ( relatedLink.lastSearch === search ){
					return;
                }

				relatedLink.lastSearch = search;
				waiting = t.parent().find('.spinner').show();

				rivers.search.change( search );
				rivers.search.ajax( function() {
					waiting.hide();
				});
			} else {
				rivers.search.hide();
				rivers.recent.show();
			}
		},

		next: function() {
			rivers.search.next();
			rivers.recent.next();
		},

		prev: function() {
			rivers.search.prev();
			rivers.recent.prev();
		},

		keydown: function( event ) {
			var fn, id,
				key = $.ui.keyCode;

			if ( key.ESCAPE === event.keyCode ) {
				relatedLink.close();
				event.stopImmediatePropagation();
			} else if ( key.TAB === event.keyCode ) {
				id = event.target.id;

				if ( id === 'related-link-submit' && ! event.shiftKey ) {
					inputs.close.focus();
					event.preventDefault();
				} else if ( id === 'related-link-close' && event.shiftKey ) {
					inputs.submit.focus();
					event.preventDefault();
				}
			}

			if ( event.keyCode !== key.UP && event.keyCode !== key.DOWN ) {
				return;
			}

			fn = event.keyCode === key.UP ? 'prev' : 'next';
			clearInterval( relatedLink.keyInterval );
			relatedLink[ fn ]();
			relatedLink.keyInterval = setInterval( relatedLink[ fn ], relatedLink.keySensitivity );
			event.preventDefault();
		},

		keyup: function( event ) {
			var key = $.ui.keyCode;

			if ( event.which === key.UP || event.which === key.DOWN ) {
				clearInterval( relatedLink.keyInterval );
				event.preventDefault();
			}
		},

		delayedCallback: function( func, delay ) {
			var timeoutTriggered, funcTriggered, funcArgs, funcContext;

			if ( ! delay ) {
				return func;
            }

			setTimeout( function() {
				if ( funcTriggered ) {
					return func.apply( funcContext, funcArgs );
                }
				// Otherwise, wait.
				timeoutTriggered = true;
			}, delay );

			return function() {
				if ( timeoutTriggered ) {
					return func.apply( this, arguments );
                }
				// Otherwise, wait.
				funcArgs = arguments;
				funcContext = this;
				funcTriggered = true;
			};
		},

		toggleInternalLinking: function() {
			var visible = inputs.wrap.hasClass( 'search-panel-visible' );

			inputs.wrap.toggleClass( 'search-panel-visible', ! visible );
			setUserSetting( 'relatedLink', visible ? '0' : '1' );
			inputs[ ! visible ? 'search' : 'url' ].focus();
		}
	};

	RelatedLinkRiver = function( element, search ) {
		var self = this;
		this.element = element;
		this.ul = element.children( 'ul' );
		this.contentHeight = element.children( '#link-selector-height' );
		this.waiting = element.find('.river-waiting');

		this.change( search );
		this.refresh();

		$( '#related-link .query-results, #related-link #link-selector' ).scroll( function() {
			self.maybeLoad();
		});
		element.on( 'click', 'li', function( event ) {
			self.select( $( this ), event );
		});
	};

	$.extend( RelatedLinkRiver.prototype, {
		refresh: function() {
			this.deselect();
			this.visible = this.element.is( ':visible' );
		},
		show: function() {
			if ( ! this.visible ) {
				this.deselect();
				this.element.show();
				this.visible = true;
			}
		},
		hide: function() {
			this.element.hide();
			this.visible = false;
		},
		// Selects a list item and triggers the river-select event.
		select: function( li, event ) {
			var liHeight, elHeight, liTop, elTop;

			if ( li.hasClass( 'unselectable' ) || li === this.selected ) {
				return;
            }

			this.deselect();
			this.selected =  li.addClass( 'selected' );

			// Make sure the element is visible
			liHeight = li.outerHeight();
			elHeight = this.element.height();
			liTop    = li.position().top;
			elTop    = this.element.scrollTop();

			if ( liTop < 0 ) { // Make first visible element
				this.element.scrollTop( elTop + liTop );
            } else if ( liTop + liHeight > elHeight ) { // Make last visible element
				this.element.scrollTop( elTop + liTop - elHeight + liHeight );
            }

			// Trigger the river-select event
			this.element.trigger( 'river-select', [ li, event, this ] );
		},
		deselect: function() {
			if ( this.selected ) {
				this.selected.removeClass( 'selected' );
            }
			this.selected = false;
		},
		prev: function() {
			if ( ! this.visible ) {
				return;
            }

			var to;
			if ( this.selected ) {
				to = this.selected.prev( 'li' );
				if ( to.length ) {
					this.select( to );
                }
			}
		},
		next: function() {
			if ( ! this.visible ) {
				return;
            }

			var to = this.selected ? this.selected.next( 'li' ) : $( 'li:not(.unselectable):first', this.element );
			if ( to.length ) {
				this.select( to );
            }
		},
		ajax: function( callback ) {
			var self = this,
				delay = this.query.page === 1 ? 0 : relatedLink.minRiverAJAXDuration,
				response = relatedLink.delayedCallback( function( results, params ) {
					self.process( results, params );
					if ( callback ) {
						callback( results, params );
                    }
				}, delay );

			this.query.ajax( response );
		},
		change: function( search ) {
			if ( this.query && this._search === search ) {
				return;
            }

			this._search = search;
			this.query = new RelatedLinkQuery( search );
			this.element.scrollTop( 0 );
		},
		process: function( results, params ) {
			var list = '', alt = true, classes = '', firstPage = params.page = 1;

			if ( ! results ) {
				if ( firstPage ) {
					list += '<li class="unselectable"><span class="item-title"><em>' +
						relatedLinkL10n.noMatchesFound + '</em></span></li>';
				}
			} else {
				$.each( results, function() {
					classes = alt ? 'alternate' : '';
					classes += this.title ? '' : ' no-title';
					list += classes ? '<li class="' + classes + '">' : '<li>';
					list += '<input type="hidden" class="item-permalink" value="' + this.permalink + '" />';
					list += '<span class="item-title">';
					list += this.title ? this.title : relatedLinkL10n.noTitle;
					list += '</span><span class="item-info">' + this.info + '</span></li>';
					alt = ! alt;
				});
			}

			this.ul[ firstPage ? 'html' : 'append' ]( list );
		},
		maybeLoad: function() {
			var self = this,
				el = this.element,
				bottom = el.scrollTop() + el.height();

			if ( ! this.query.ready() || bottom < this.contentHeight.height() - relatedLink.riverBottomThreshold ) {
				return;
            }

			setTimeout(function() {
				var newTop = el.scrollTop(),
					newBottom = newTop + el.height();

				if ( ! self.query.ready() || newBottom < self.contentHeight.height() - relatedLink.riverBottomThreshold ) {
					return;
                }

				self.waiting.show();
				el.scrollTop( newTop + self.waiting.outerHeight() );

				self.ajax( function() {
					self.waiting.hide();
				});
			}, relatedLink.timeToTriggerRiver );
		}
	});

	RelatedLinkQuery = function( search ) {
		this.page = 1;
		this.allLoaded = false;
		this.querying = false;
		this.search = search;
	};

	$.extend( RelatedLinkQuery.prototype, {
		ready: function() {
			return ! ( this.querying || this.allLoaded );
		},
		ajax: function( callback ) {
			var self = this,
				query = {
					action : 'wp-link-ajax',
					page : this.page,
					'_ajax_linking_nonce' : inputs.nonce.val()
				};

			if ( this.search ) {
				query.search = this.search;
            }

			this.querying = true;

			$.post( ajaxurl, query, function( r ) {
				self.page++;
				self.querying = false;
				self.allLoaded = ! r;
				callback( r, query );
			}, 'json' );
		}
	});

	$( document ).ready( relatedLink.init );
})( jQuery, window, document );
