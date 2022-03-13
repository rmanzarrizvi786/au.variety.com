// Set up the constructor for our hidden text class.
(function( window ){
	window.wp = window.wp || {};

	function HiddenText( shortcode, pid ) {

		// Check if the `wp.shortcode` & `wp.html` API exist.
		if ( ( ! window.wp.shortcode ) || ( ! window.wp.html ) )
			return;

		// Check to make sure the passed shortcode is in fact a shortcode object
		if ( 'object' !== typeof shortcode || 'function' !== typeof shortcode.string || ! shortcode.content )
			return;

		// Set up our protected variables.
		pid = pid || 0;
		var initalContent = trim( shortcode.content ),
			htmlObject = {
			tag: 'div',
			content: '',
			attrs: {
				'data-hide-id': pid,
				'class': 'pmc-hidden-text',
				'id': 'pmc-hidden-text-' + pid
			}
		},
		classes = [ 'pmc-hidden-text' ];
		updateContent( initalContent );

		/**
		 * Updates our objects content to a new string.
		 * 
		 * @param {string} newContent The new content to set.
		 * @return {boolean} Whether or not the content was updated.
		 */
		function updateContent( newContent ) {
			//Validate input
			newContent = trim( newContent );

			shortcode.content = newContent;
			htmlObject.content = ' ' + newContent + ' ';
			return true;
		}
		/**
		 * Gets the raw content of this object
		 * 
		 * @return {string} The raw content contained in this object.
		 */
		function getContent() {
			return htmlObject.content;
		}
		/**
		 * Checks to see if this objet has a certain class name.
		 * @param {string} className The class name to check for.
		 * @return {Boolean} True if the object has the class, false if not.
		 */
		function hasClass( className ) {
			var i, length;
			for ( i = 0, length = classes.length; i < length; i++ ) {
				if ( classes[ i ] === className ) {
					return true;
				}
			}
			return false;
		}
		/**
		 * Adds a class to the list of classes if not already present.
		 * 
		 * @param {string} className The class name that should be added.
		 * @return {boolean} Whether or not the class was added.
		 */
		function addClass( className ) {
			//Validate input
			if ( 'string' !== typeof className || '' === className ) {
				return false;
			}

			//Whitelist acceptable characters, Alphanumeric, underscore, and dash
			if ( -1 === className.search( /^[\w-]+?$/ ) ) {
				return false;
			}

			// If it's not present, add it
			if ( ! hasClass( className ) ) {
				classes.push( className );
				htmlObject.attrs['class'] = classes.join( ' ' );
				return true;
			}
			// if we're here, we failed for some reason.
			return false;
		}

		/**
		 * Removes a class from the list of classes if it is present and fires a change.
		 * 
		 * @param  {string} className The class name that should be removed
		 * @return {boolean} Whether or not the class was removed.
		 */
		function removeClass( className ) {
			//Validate input
			if ( 'string' !== typeof className || '' === className || 'pmc-hidden-text' === className ) {
				return false;
			}

			var i, length, hasChanged = false;
			
			// Check to see if it's already here.
			for ( i = 0, length = classes.length; i < length; i++ ) {
				if ( classes[ i ] === className ) {
					classes.splice( i, 1 );
					hasChanged = true;
				}
			}

			// if we've changed, update the object and trigger a change event
			if ( hasChanged ) {
				htmlObject.attrs['class'] = classes.join( ' ' );
				return true;
			}
			// We didn't change, let's report!
			return false;
		}

		/**
		 * Returns the current output for use. If in shortcode mode, the shortcode, if in element mode, the element.
		 * 
		 * @return {string} The current output, determined based on the current mode.
		 */
		function string( type ) {
			if ( 'shortcode' === type ) {
				return shortcode.string();
			} else {
				return window.wp.html.string( htmlObject );
			}
		}

		/**
		 * Trims all whitespace off the ends of a string
		 *
		 * @return {string} The passed string with all the whitespace removed from the front and back.
		 */
		function trim( string ) {
			if ( 'string' !== typeof string ) {
				return '';
			}
			// Make sure to catch non-breacking space entities as well.
			string = string.replace( /&nbsp;/g, ' ' );
			// Kill all beginning and trailing whitespace.
			return string.replace( /(?:^\s\s*|\s+?$)/g, '' );
		}

		// Expose these methods
		this.updateContent = updateContent;
		this.getContent = getContent;
		this.hasClass = hasClass;
		this.addClass = addClass;
		this.removeClass = removeClass;
		this.string = string;
	}
	// Expose our contructor globally.
	window.HiddenText = HiddenText;
})( window );

(function() {
	// Sanity check
	if ( 'function' !== typeof window.HiddenText ) {
		return;
	}

	// Define a closure for getting the tinyMCE pluing object.
	function getHiddenTextTinyMCEObject() {
		var index = 0, instances = {};
		
		return {
			init: init,
			getInfo: getInfo
		};

		function init( ed, url ) {
			_createButtons( ed );

			// Register Commands
			ed.addCommand( 'pmcHideText', createNewHideText );
			
			// Set up events
			ed.onInit.add( initEd );
			ed.onBeforeSetContent.add( replaceShortcodes );
			ed.onPostProcess.add( replaceSpans );
			ed.onBeforeExecCommand.add( disallowProgramaticContentInsertion );

			// Register buttons
			ed.addButton('pmc-hidden-text', {
				title : 'pmc-hidden-text',
				cmd : 'pmcHideText'
			});
		}
		function initEd( ed ) {
			ed.dom.events.add( ed.getBody(), 'keydown', disallowReturns );
			ed.selection.selectorChanged( '.pmc-hidden-text', handleSelectionChanged );
			ed.dom.events.add( ed.getBody(), 'click', handleClick );
			_hideButtons();
		}
		function replaceShortcodes( ed, o ) {
			o.content = wp.shortcode.replace( 'pmc_hidden_text', o.content, replaceShortcodesCallback );
		}
		function replaceShortcodesCallback( sc ){
			var newHiddenText = new HiddenText( sc, index );
			instances[ index ] = newHiddenText;
			index++;
			return newHiddenText.string();
		}
		function replaceSpans( ed, o ) {
			var id, node, currentContent, regex;
			for ( id in instances ) {
				// Check for the element in the dom, bail if not present.
				node = ed.dom.get( 'pmc-hidden-text-' + id );
				if ( ! node ) {
					continue;
				}

				// if is present, get and set the latest content.
				currentContent = node.innerHTML;
				instances[ id ].updateContent( currentContent );
				regex = new RegExp( '<div[^>]+data-hide-id="' + id + '".*?<\\/div>' );
				o.content = o.content.replace( regex, instances[ id ].string( 'shortcode' ) );
			}
			_hideButtons();
		}
		function disallowReturns( e ) {
			if ( 13 === e.keyCode ) {
				var p, ed = tinymce.activeEditor,
				node = ed.dom.getParent( ed.selection.getNode(), '.pmc-hidden-text' );
				if ( null !== node ) {
					ed.dom.events.cancel(e);
					p = ed.dom.create('p', {}, '\uFEFF');
					ed.dom.insertAfter( p, node );
					ed.selection.setCursorLocation(p, 0);
					return false;
				}
			}
		}
		function disallowProgramaticContentInsertion( ed, cmd, ui, val ) {
			var node, p;

			if ( cmd == 'mceInsertContent' ) {
				node = ed.dom.getParent( ed.selection.getNode(), '.pmc-hidden-text' );

				if ( null !== node ) {
					p = ed.dom.create('p');
					ed.dom.insertAfter( p, node );
					ed.selection.setCursorLocation( p, 0 );
				}
			}
		}
		function createNewHideText( u, v ) {
			var content, shortcode, ed = tinymce.activeEditor,
			isAlreadyHidden = ( !! ed.dom.getParent( ed.selection.getNode(), '.pmc-hidden-text' ) );
			
			if ( ! isAlreadyHidden ) {
				content = ed.selection.getContent();
				if ( '' === content || -1 !== content.search( /<\/?(?!\/?(?:strong|em|i|b|u|a|span|p)(?:\s|>)).*?(?:\s|>)(?:.*?>)?/ ) ) {
					return;
				}
				shortcode = new wp.shortcode({
					tag: 'pmc_hidden_text',
					type: 'closed',
					content: content
				});
				ed.selection.setContent( replaceShortcodesCallback( shortcode ) );
				ed.selection.collapse( false );
			}
		}
		function handleClick( e ) {
			var id, obj, ed = tinymce.activeEditor,
			node = ed.dom.getParent( e.target, '.pmc-hidden-text' );
			if ( null === node ) {
				return;
			}

			id = node.getAttribute( 'data-hide-id' );
			obj = instances[ id ];
			if ( ! obj.hasClass( 'pmc-show-hidden' ) ) {
				ed.selection.select( node );
			}
		}
		function handleSelectionChanged( e ) {
			var id, obj, ed = tinymce.activeEditor,
			node = ed.dom.getParent( ed.selection.getNode(), '.pmc-hidden-text' );
			if ( null === node ) {
				_hideButtons();
			} else {
				id = node.getAttribute( 'data-hide-id' );
				obj = instances[ id ];
				if ( ! obj.hasClass( 'pmc-show-hidden' ) ) {
					ed.selection.select( node );
				}
				_showButtons( node );
			}
		}
		function handleToggleButton( e ) {
			var id, obj, ed = tinymce.activeEditor,
			node = ed.dom.getParent( ed.selection.getNode(), '.pmc-hidden-text' );

			if ( null === node ) {
				_hideButtons();
				return;
			}

			id = node.getAttribute( 'data-hide-id' );
			obj = instances[ id ];

			if ( obj.hasClass( 'pmc-show-hidden' ) ) {
				obj.removeClass( 'pmc-show-hidden' );
				tinymce.DOM.removeClass( 'pmc-hidden-text-buttons', 'pmc-make-hidden' );
			} else {
				obj.addClass( 'pmc-show-hidden' );
				tinymce.DOM.addClass( 'pmc-hidden-text-buttons', 'pmc-make-hidden' );
			}

			ed.selection.select( node );
			ed.selection.setContent( obj.string() );
			ed.selection.select( ed.dom.get( 'pmc-hidden-text-' + id ) );
			// If we don't do this on a timeout, it fails.
			setTimeout( function(){ ed.focus(); ed.selection.collapse(); }, 1 );
		}
		function handleRemoveButton( e ) {
			var id, obj, ed = tinymce.activeEditor,
			node = ed.dom.getParent( ed.selection.getNode(), '.pmc-hidden-text' );

			if ( null === node ) {
				_hideButtons();
			}

			id = node.getAttribute( 'data-hide-id' );
			obj = instances[ id ];

			ed.selection.select( node );
			ed.selection.setContent( obj.getContent() );
			_hideButtons();
		}
		function _createButtons( ed ) {
			var DOM = tinymce.DOM, toggleButton, removeButton;

			if ( DOM.get('pmc-hidden-text-buttons') )
				return;

			DOM.add( document.body, 'div', {
				'id' : 'pmc-hidden-text-buttons',
				'class': 'pmc-hidden-text-buttons',
				'style' : 'display:none;'
			});

			toggleButton = DOM.add( 'pmc-hidden-text-buttons', 'div', {
				'id' : 'pmc-hidden-text-toggle',
				'class': 'pmc-hidden-text-toggle',
				width : '24',
				height : '24'
			});

			tinymce.dom.Event.add( toggleButton, 'click', handleToggleButton );

			removeButton = DOM.add( 'pmc-hidden-text-buttons', 'div', {
				'id' : 'pmc-hidden-text-remove',
				'class': 'pmc-hidden-text-remove',
				width : '24',
				height : '24',
			});

			tinymce.dom.Event.add( removeButton, 'click', handleRemoveButton );
		}
		function _showButtons( n ) {
			var ed = tinymce.activeEditor,
			id, obj, p1, p2, vp, DOM = tinymce.DOM, X, Y;

			vp = ed.dom.getViewPort( ed.getWin() );
			p1 = DOM.getPos( ed.getContentAreaContainer() );
			p2 = ed.dom.getPos( n );

			X = Math.max( p2.x - vp.x, 0 ) + p1.x;
			Y = Math.max( p2.y - vp.y, 0 ) + p1.y;

			id = n.getAttribute( 'data-hide-id' );
			obj = instances[ id ];

			if ( obj.hasClass( 'pmc-show-hidden' ) ) {
				DOM.addClass( 'pmc-hidden-text-buttons', 'pmc-make-hidden' );
			} else  {
				DOM.removeClass( 'pmc-hidden-text-buttons', 'pmc-make-hidden' );
			}

			DOM.setStyles( 'pmc-hidden-text-buttons', {
				'top' : Y+5+'px',
				'left' : X+5+'px',
				'display' : 'block'
			});
		}
		function _hideButtons() {
			var DOM = tinymce.DOM;
			DOM.hide( DOM.select( '#pmc-hidden-text-buttons' ) );
		}
		function getInfo() {
			return {
				longname : 'PMC HiddenText',
				author : '10up',
				authorurl : 'http://10up.com',
				infourl : '',
				version : "1.0"
			};
		}
	}

	// Create the plugin.
	tinymce.create( 'tinymce.plugins.pmcHiddenText', getHiddenTextTinyMCEObject() );
	tinymce.PluginManager.add( 'pmcHiddenText', tinymce.plugins.pmcHiddenText );
})();