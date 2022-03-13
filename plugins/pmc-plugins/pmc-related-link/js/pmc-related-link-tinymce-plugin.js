/**
 * @uses tinymce
 */
( function( $, window ) {
	'use strict';

	/*
		The following was originally copied from wp-includes/js/tinymce/plugins/wplink/plugin.js
	 */

	/* global tinymce */
	tinymce.PluginManager.add( 'pmc_related_link', function( editor ) {
		var relatedLinkButton;

		// Register a command so that it can be invoked by using tinyMCE.activeEditor.execCommand( 'pmc_related_link' );
		editor.addCommand( 'pmc_related_link', function() {
			if ( ( ! relatedLinkButton || ! relatedLinkButton.disabled() ) && typeof window.relatedLink !== 'undefined' ) {
				window.relatedLink.open( editor.id );
			}
		}); // editor.addCommand

		// Add our 'Related Link' button to the MCE editor
		editor.addButton( 'pmc_related_link', {
			//icon: 'link',
			tooltip : 'Insert/edit Related link',
			cmd     : 'pmc_related_link',
			text    : 'Related Link'
		}); // editor.addButton

		// Run a function when the user clicks the 'Visual' tab
		// We'll tap in here to display a 'Preview' of the shortcode output
		// This is done by simply sleight of hand--with a little string find/replace
		editor.on( 'BeforeSetContent', function( editor ) {
			// If there is any content in the 'Text tab'
			if ( editor.content ) {
				// If our [pmc-related-link] shortcode is within the content
				if ( editor.content.indexOf( '[pmc-related-link' ) !== -1 ) {
					// Utilize WordPress' wp.shortcode method which will nicely
					// pass us an object of the shortcode content and attrs
					editor.content = wp.shortcode.replace( 'pmc-related-link', editor.content, function (shortcode_obj) {
						// Quick fix to prevent JS errors breaking the rich editor
						var valid_shortcode_obj = ( typeof shortcode_obj === "object" && shortcode_obj.hasOwnProperty('attrs') && shortcode_obj.attrs.hasOwnProperty('named') && shortcode_obj.attrs.named.hasOwnProperty('type') );
						if( !valid_shortcode_obj ) {
							return;
						}

                        var type = '';
                        var type_slug = '';
                        var content = '';
                        var href = '';
                        var target = '';

                        if( typeof  shortcode_obj.attrs.named.type !== 'undefined'){
                            // Create a 'slug' version of the link type
                            // 'RELATED' becomes 'related'
                            // 'Important Link' becomes 'important-link'
                            type = shortcode_obj.attrs.named.type;
                            type_slug = type
                                .toLowerCase()
                                .replace(/ +/g,'-');
                        }

                        if( typeof  shortcode_obj.content !== 'undefined' ){
                            content = shortcode_obj.content.trim();
                        }
                        if( typeof  shortcode_obj.attrs.named.href !== 'undefined'){
                            href = shortcode_obj.attrs.named.href;
                        }
                        if( typeof  shortcode_obj.attrs.named.target !== 'undefined'){
                            target = shortcode_obj.attrs.named.target;
                        }
                        if( target == '' ){
                            target ='_self';
                        }

                        var strong_element = jQuery('<strong/>').html( type + '&nbsp;' );
                        var anchor_element = jQuery('<a/>').attr('title',content).attr('href',href).attr('target',target).html( content );


                        var html = jQuery('<aside/>')
	                        .attr('class','pmc-related-link '+ type_slug )
	                        .attr('data-related-link-type',type )
	                        .attr('data-related-link-url',href)
	                        .attr('data-related-link-content',content)
	                        .attr( 'data-related-link-target',target )
	                        .append( strong_element )
	                        .append( anchor_element );
	                     // get the raw html data containing <p> we build above section
                        html = jQuery('<aside/>').html( html ).html();

						// Return our assembled html string
						return html ;

					}); // wp.shortcode.replace()
				} // end if [pmc-related-link] is in the content
			} // end if there is any content in the 'Text' tab
		}); // editor.on BeforeSetContent

		// Run a function when the post is processed?? Not sure what this really means :\
		editor.on( 'PostProcess', function( editor ) {
			if ( editor.get ) {
				if ( editor.content.indexOf( 'class="pmc-related-link' ) !== -1 ) {
					editor.content = editor.content.replace(/<(p|h1|h2|h3|h4|h5|h6|aside) class="pmc-related-link(.*?)">(.*?)<\/(p|h1|h2|h3|h4|h5|h6|aside)>/g, function (match) {
						// match now contains something like..
						// <div class="pmc-related-link"><strong>RELATED</strong> <a title="Etiam rhoncus lorem augue, non molestie tortor condimentum ac." href="http://daddsda" target=""> Etiam rhoncus lorem augue, non molestie tortor condimentum ac. </a></div>

						// Utilize jQuery to parse the html string of our markup
						var $html = $( $.parseHTML( match ) );

						// Ensure the div .related-link element contains our html..
						// see the notes in else below
						// Reason we are checking both pmc-related-link & <strong> is because Tinymce editor adds bogus element after our anchor tag. To make sure its our genuine element, we insert strong inside and hence checking for that too.
						if ( match.indexOf( 'pmc-related-link' ) !== -1 && match.indexOf( '<strong>' ) !== -1 ) {
                            //first make sure all the data we need actually exists
                            var related_link_url = typeof $html.data('related-link-url') !== 'undefined' ? $html.data('related-link-url').trim() :'';
                            var related_link_type = typeof $html.data('related-link-type') !== 'undefined' ? $html.data('related-link-type').trim() : '';
                            var related_link_target = typeof $html.data('related-link-target') !== 'undefined' ? $html.data('related-link-target').trim() : '';
                            var related_link_content = typeof $html.data('related-link-content') !== 'undefined'? $html.data('related-link-content').trim() : '';

                            var shortcode_string  = '[pmc-related-link';
                            shortcode_string += ' href="' +related_link_url + '"';
                            shortcode_string += ' type="' + related_link_type + '"';

                            // Only tack on the target attribute if it was given
                            if ( related_link_target){
                                shortcode_string += ' target="' + related_link_target + '"';
                            }

                            shortcode_string += ']';
                            shortcode_string += related_link_content;
                            shortcode_string += '[/pmc-related-link]';

                            var related_link_shortcode = jQuery('<div/>').html( jQuery('</p>').html( shortcode_string) ).html();
                            // Return the assembled shortcode string
                            return related_link_shortcode;
						} else {

							// the user tried to remove the shortcode preview previously
							// (selecting the related link and hitting delete or another replacement key)
							// now there is an orphan <div class="pmc-related-link"> and </div>
							// lets remove it by returning the content without the wrapping markup

							return $html.html() ;

						} // if else match contains 'RELATED'
					}); // editor.content.replace()
				} // if content contains div .related-link
			} // if editor contains .get() method
		}); // editor.on PostProcess
	}); // tinymce pmc_related_link plugin

} )( jQuery, window );
