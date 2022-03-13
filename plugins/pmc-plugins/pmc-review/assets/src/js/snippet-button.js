import set from 'lodash/set';

/**
 * We'll be using [pmc_review_snippet]..[/pmc_review_snippet] to mark the google review snippet text.
 * In visual editing, we translate that into <span class="pmc_review_snippet">..</span>
 * Relying on pmc_preview_snippet css class to provide various text highlight.
 */

class SnippetButton {

	/**
	 * Class constructor.
	 *
	 * @param {DOMElement} Object.targetBox A form element to receive the snippet value.
 	 */
	constructor( { targetBox } ) {
		this.targetBox = targetBox;

		this.editor = null;
		this.selectionLength = 0;
		this.snippet = '';

		this.bindMethods();
	}

	/**
	 * Binds methods to this class.
	 */
	bindMethods() {
		this.snippetCommand = this.snippetCommand.bind( this );
		this.onBeforeSetContent = this.onBeforeSetContent.bind( this );
		this.onPostProcess = this.onPostProcess.bind( this );
		this.dispatchSelectionLength = this.dispatchSelectionLength.bind( this );
		this.handleSelectionLengthChange = this.handleSelectionLengthChange.bind( this );
	}

	/**
	 * Starts the script.
	 */
	init() {
		this.initSelectionLengthElement();

		tinymce.PluginManager.add( 'pmc_review_snippet', ( editor ) => {
			if ( 'content' === editor.id ) {
				this.initEditor( editor );
			}
		} );

		addEventListener( 'pmc_selection_length', this.handleSelectionLengthChange );
	}

	/**
	 * Appends a span to the word count element to show the current selection length.
	 */
	initSelectionLengthElement() {
		const wordCount = document.getElementById( 'wp-word-count' );
		this.selectionLengthElement = document.createElement( 'SPAN' );
		this.selectionLengthElement.setAttribute( 'id', 'pmc-selection-length' );
		wordCount.appendChild( this.selectionLengthElement );
	}

	/**
	 * Sets up the editor.
	 *
	 * @param {Object} editor The tinymce editor.
	 */
	initEditor( editor ) {
		this.editor = editor;

		this.initButton();
		this.editor.addCommand( 'mceReviewSnippetTag', this.snippetCommand );
		this.editor.on( 'BeforeSetContent', this.onBeforeSetContent );
		this.editor.on( 'PostProcess', this.onPostProcess );
		this.editor.on( 'NodeChange', this.dispatchSelectionLength );
	}

	/**
	 * Creates the button.
	 */
	initButton() {
		this.editor.addButton( 'pmc_review_snippet', {
			text: this.editor.editorManager.i18n.translate( 'Review Snippet' ),
			cmd: 'mceReviewSnippetTag',
			tooltip: this.editor.editorManager.i18n.translate( 'Toggle Google review snippet selection. Selection will be highlighted in yellow' ),
			classes: 'widget btn review-snippet-btn',
			onpostrender: () => {
				this.button = document.querySelector( '.mce-review-snippet-btn' );
			}
		} );
	}

	/**
	 * Sends the snippet text to the target form element.
	 *
	 * @param {string} text The snippet text.
	 */
	injectTargetBoxValue( text = this.snippet ) {
		if ( this.targetBox ) {
			this.targetBox.value = text;
		}
	}

	/**
	 * Updates the selection length element.
	 *
	 * @param {number} Event.detail.selectionLength The current length of the editor selection.
	 */
	handleSelectionLengthChange( { detail: selectionLength } ) {
		this.selectionLengthElement.innerHTML = 0 < selectionLength ?
			` | ${this.editor.editorManager.i18n.translate( 'Selection length' )}: ${selectionLength}` :
			'';
	}

	/**
	 * Callback for clicks on the snippet button.
	 */
	snippetCommand() {

		// HTML content with empty paragraphs stripped.
		let selectionContent = this.editor.selection.getContent().replace( /<p>\s+<\/p>/g, '' );
		let selectionText = this.editor.selection.getContent( { format: 'text' } ).trim();

		if ( '' === this.snippet && '' === selectionText ) {
			alert( this.editor.editorManager.i18n.translate( 'Select text to choose snippet.' ) );
			return;
		}

		const snippetSpan = this.editor.dom.select( 'SPAN.pmc_review_snippet' );
		if ( ! snippetSpan ) {
			return;
		}

		tinyMCE.execCommand( 'mceRemoveNode', false, snippetSpan );

		if ( '' === selectionText && snippetSpan ) {
			this.snippet = '';
			this.injectTargetBoxValue();
			this.button.classList.remove( 'mce-active' );
			return;
		}


		if ( -1 < selectionContent.substring( 1 ).indexOf ( '<p>' ) ) {
			alert( this.editor.editorManager.i18n.translate( 'Snippet cannot span more than one paragraph.' ) );
			return;
		}

		if ( 200 < selectionText.length ) {
			alert( this.editor.editorManager.i18n.translate( 'The snippet cannot contain more than 200 characters.' ) );
			return;
		}

		// <span> needs to stay within <p> tag.
		if ( -1 < selectionContent.indexOf ( '<p>' ) ) {
			selectionContent = selectionContent.replace( /<p>/, '<p><span class="pmc_review_snippet">' ).replace( /<\/p>/, '</span></p>' );
		} else {
			const span = document.createElement( 'span' );
			span.setAttribute( 'class', 'pmc_review_snippet' );
			span.appendChild( document.createTextNode( selectionContent ) );
			selectionContent = span.outerHTML;
		}

		this.editor.selection.setContent( selectionContent );
		this.snippet = selectionText;
		this.injectTargetBoxValue();
		this.button.classList.add( 'mce-active' );
	}

	/**
	 * Converts a shortcode to a span and returns the content and the shortcode text.
	 *
	 * @param {string} originalContent Editor content.
	 * @return {Object} An object containing the updated editor content and the text from the shortcode.
	 */
	shortcodeToText( originalContent ) {
		let content = originalContent.slice( 0 );
		let text = '';
		while ( /\[pmc_review_snippet|pmc_film_review_snippet\][^\0]*?\[\/pmc_review_snippet|pmc_film_review_snippet\]/g.test( content ) ) {
			content = content.replace( /\[pmc_review_snippet\][^\0]*?\[\/pmc_review_snippet\]/g, function( tag ) {
				if ( -1 < tag.substring( 20 ).indexOf( '[pmc_review_snippet]' ) ) {

					// we have <span within a span...
					tag = tag.substring( 0, 20 ) + tag.substring( 20 ).replace( /\[pmc_review_snippet\]/g, '' ).replace( /\[\/pmc_review_snippet\]/, '' );
					return tag;
				}
				text = tag.replace( /\[pmc_review_snippet\]/, '' ).replace( /\[\/pmc_review_snippet\]/, '' );
				const span = document.createElement( 'span' );
				span.setAttribute( 'class', 'pmc_review_snippet' );
				span.appendChild( document.createTextNode( text ) );
				return span.outerHTML;
			} ).replace( /\[pmc_film_review_snippet\][^\0]*?\[\/pmc_film_review_snippet\]/g, function( tag ) {
				if ( -1 < tag.substring( 20 ).indexOf( '[pmc_film_review_snippet]' ) ) {

					// we have <span within a span...
					tag = tag.substring( 0, 20 ) + tag.substring( 20 ).replace( /\[pmc_film_review_snippet\]/g, '' ).replace( /\[\/pmc_film_review_snippet\]/, '' );
					return tag;
				}
				text = tag.replace( /\[pmc_film_review_snippet\]/, '' ).replace( /\[\/pmc_film_review_snippet\]/, '' );
				const span = document.createElement( 'span' );
				span.setAttribute( 'class', 'pmc_review_snippet' );
				span.appendChild( document.createTextNode( text ) );
				return span.outerHTML;
			} );
		}

		return { content, text };
	}

	/**
	 * Converts a review snippet span to a shortcode and returns the content and shortcode text.
	 *
	 * @param {string} originalContent Editor content.
	 * @return {Object} An object containing the updated editor content and the text from the span.
	 */
	textToShortcode( originalContent ) {
		let content = originalContent.slice( 0 );
		let text = '';
		while ( -1 < content.indexOf( '<span class="pmc_review_snippet">' ) ) {
			content = content.replace( /<span class="pmc_review_snippet">.*?<\/span>/g, ( tag ) => {
				if ( -1 < tag.substring( 33 ).indexOf( '<span class="pmc_review_snippet">' ) ) {

					// we have <span within a span...
					tag = tag.substring( 0, 33 ) + tag.substring( 33 ).replace( /<span class="pmc_review_snippet">/g, '' ).replace( /<\/span>/, '' );
					return tag;
				}
				text = tag.replace( /<span class="pmc_review_snippet">/, '' ).replace( /<\/span>/, '' );

				// save the text to be use by other area
				return '[pmc_review_snippet]' + text + '[/pmc_review_snippet]';
			} );
		}

		return { content, text };
	}

	/**
	 * Callback run before the editor content is set.
	 *
	 * @param {Object} e An editor event.
	 */
	onBeforeSetContent( e ) {
		if ( ! e.content ) {
			return;
		}

		const { text, content } = this.shortcodeToText( e.content );

		e.content = content;
		this.snippet = text;
		this.injectTargetBoxValue();

	}

	/**
	 * Callback after the post is processed.
	 *
	 * @param {Object} e An editor event.
	 */
	onPostProcess( e ) {
		if ( ! e.save ) {
			return;
		}

		const { text, content } = this.textToShortcode( e.content );

		if ( 0 < text.length ) {
			this.button.classList.add( 'mce-active' );
		} else {
			this.button.classList.remove( 'mce-active' );
		}

		e.content = content;
		this.snippet = text;

	}

	/**
	 * Dispatches an event containing the editor's current selection length.
	 */
	dispatchSelectionLength() {
		const { length } = this.editor.selection.getContent( { format: 'text' } );

		if ( this.selectionLength !== length ) {
			this.selectionLength = length;
			dispatchEvent( new CustomEvent( 'pmc_selection_length', { detail: this.selectionLength} ) );
		}
	}
}

if ( 'tinymce' in global ) {
	const snippetButton = new SnippetButton(
		{
			targetBox: document.getElementById( 'pmc-review-snippet' )
		}
	);
	snippetButton.init();

	set( global, 'snippetButton', snippetButton );
}
