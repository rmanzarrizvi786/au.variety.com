/**
 * JS for the admin page of the plugin
 *
 * @author Amit Gupta
 */

const { __, _x, _n, _nx } = window.wp.i18n;

class PMCCerosEmbedsAdmin {

	/**
	 * Class constructor
	 */
	constructor() {
		this.setupHooks();
	}

	/**
	 * Method to set up listeners to different hooks
	 *
	 * @return {void}
	 */
	setupHooks() {

		const $    = jQuery;
		const self = this;

		$( '#btn-pmc-ceros-embeds-converter' ).on( 'click', ( e ) => {
			self.convertHTML( e, self );
		} );

		$( '#pmc-ceros-embeds-html, #pmc-ceros-embeds-shortcode' ).on( 'click', self.selectAllText );

	}

	/**
	 * Method to select all text in current textarea
	 *
	 * @return {void}
	 */
	selectAllText() {

		const $ = jQuery;
		$( this ).select();

	}

	/**
	 * Method hooked to click event of button.
	 * This method handles conversion of Ceros embed HTML to a WP shortcode and output of that in UI
	 *
	 * @param {object} event
	 * @param {object} self  Object of the class passed to it since it cannot access class directly
	 *
	 * @return {void}
	 */
	convertHTML( event, self ) {

		self.hideMsg();

		const $ = jQuery;

		let msgTxt  = __( 'Unable to convert HTML to Shortcode. Please make sure HTML is correct.', 'pmc-ceros-embeds' );
		let msgType = 'error';

		let txtAreaHTML      = $( '#pmc-ceros-embeds-html' );
		let txtAreaShortcode = $( '#pmc-ceros-embeds-shortcode' );

		let embedHTML      = $.trim( txtAreaHTML.val() );
		let embedShortcode = this.getHTMLToShortcodeConversion( embedHTML );

		txtAreaShortcode.val( '' );

		if ( '' !== embedShortcode ) {

			txtAreaShortcode.val( embedShortcode );

			msgTxt  = __( 'HTML converted to Shortcode successfully', 'pmc-ceros-embeds' );
 			msgType = 'success';

		}

		self.setMsg( msgTxt, msgType );

		event.preventDefault();

	}

	/**
	 * Method to convert Ceros embed HTML to WP Shortcode with relevant/important data values and return the shortcode.
	 *
	 * @param {string} strHTML
	 *
	 * @return {string}
	 */
	getHTMLToShortcodeConversion( strHTML ) {

		if ( 'undefined' === typeof strHTML || ! strHTML ) {
			return '';
		}

		if ( 'undefined' === typeof window.pmc_ceros_embeds_config ) {
			return '';
		}

		let oConfig = window.pmc_ceros_embeds_config;

		if ( 'undefined' === typeof oConfig.tag || ! oConfig.tag ) {
			return '';
		}

		const $ = jQuery;

		let txtShortcode = '[' + oConfig.tag;
		let elemHTML     = $( $.parseHTML( strHTML ) );
		let elemIframe   = $( elemHTML ).find( 'iframe' );

		let containerId                    = elemHTML.attr( 'id' );
		let containerStyle                 = elemHTML.attr( 'style' );
		let containerDataAspectRatio       = elemHTML.attr( 'data-aspectRatio' );
		let containerDataMobileAspectRatio = elemHTML.attr( 'data-mobile-aspectRatio' );

		let iframeSrc   = elemIframe.attr( 'src' );
		let iframeStyle = elemIframe.attr( 'style' );
		let iframeCss   = elemIframe.attr( 'class' );
		let iframeTitle = elemIframe.attr( 'title' );

		// Check on some critical attributes.
		// If these are not available then the embed HTML is bad code
		if (
			'undefined' === typeof containerId || ! containerId
			|| 'undefined' === typeof containerStyle || ! containerStyle
			|| 'undefined' === typeof iframeSrc || ! iframeSrc
		) {
			return '';
		}

		txtShortcode += ' container_id="' + containerId + '"';
		txtShortcode += ' container_style="' + containerStyle + '"';
		txtShortcode += ( 'undefined' === typeof containerDataAspectRatio || ! containerDataAspectRatio ) ? '' : ' container_aspect_ratio="' + containerDataAspectRatio + '"';
		txtShortcode += ( 'undefined' === typeof containerDataMobileAspectRatio || ! containerDataMobileAspectRatio ) ? '' : ' container_mobile_aspect_ratio="' + containerDataMobileAspectRatio + '"';
		txtShortcode += ' iframe_src="' + iframeSrc + '"';
		txtShortcode += ( 'undefined' === typeof iframeStyle || ! iframeStyle ) ? '' : ' iframe_style="' + iframeStyle + '"';
		txtShortcode += ( 'undefined' === typeof iframeCss || ! iframeCss ) ? '' : ' iframe_css="' + iframeCss + '"';
		txtShortcode += ( 'undefined' === typeof iframeTitle || ! iframeTitle ) ? '' : ' iframe_title="' + iframeTitle + '"';
		txtShortcode += ']';

		return txtShortcode;

	}

	/**
	 * Method to hide message from UI
	 *
	 * @return {void}
	 */
	hideMsg() {

		const $ = jQuery;

		let elemNotice = $( '.section .notice' );

		elemNotice.parent().slideUp( 500 );

	}

	/**
	 * Method to show message on UI
	 *
	 * @return {void}
	 */
	showMsg() {

		const $ = jQuery;

		let elemNotice = $( '.section .notice' );

		elemNotice.parent().slideDown( 1000 );

		// Let us hide the message after 10 seconds
		setTimeout( this.hideMsg, 10000 );

	}

	/**
	 * Method to set message on UI and display it
	 *
	 * @return {void}
	 */
	setMsg( msg, msgType = 'success' ) {

		msgType = ( 'success' !== msgType ) ? 'error' : msgType;

		const $ = jQuery;

		let elemNotice    = $( '.section .notice' );
		let msgSuccessCss = 'notice-success';
		let msgErrorCss   = 'notice-error';

		elemNotice.text( msg );

		if ( 'success' === msgType ) {
			elemNotice.removeClass( msgErrorCss ).addClass( msgSuccessCss );
		} else {
			elemNotice.removeClass( msgSuccessCss ).addClass( msgErrorCss );
		}

		this.showMsg();

	}

}

new PMCCerosEmbedsAdmin();

//EOF
