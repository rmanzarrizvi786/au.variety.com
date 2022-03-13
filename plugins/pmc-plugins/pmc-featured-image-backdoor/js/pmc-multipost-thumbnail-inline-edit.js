(
    function ( $ ) {
        // global variable. Use in featured-image-inline-edit.js as a when multiple editing of posts is enabled
        pmc_multipost_thumbnail_image_inline_edit = {

            init: function () {
                // add events
                $( '#the-list .editinline-multi-thumb' ).click(
                    function () {
                        pmc_multipost_thumbnail_image_inline_edit.choose( this );
                    }
                );
            },

            choose: function ( id ) {

                var t = pmc_multipost_thumbnail_image_inline_edit;

                if ( typeof id === 'object' ) {
                    t.currentId = t.getId( id );
                }

                t.currentSlug = $( id ).data( 'slug' );
                t.currentText = $( id ).data( 'text' );
                var text = $( id ).data( 'slug' );

                t.currentThumb = $( document.getElementById( t.currentId + '-' + t.currentSlug + '-image' ) );

                // If the media frame doesn't exist, create it.
                if ( ! t.file_frame ) {

                    // Create the media frame.
                    t.backDoorFrame = wp.media.frames.backDoorFrame = wp.media( {
                        title: 'Set ' + text + ' Image',
                        button: {
                            text: 'Set ' + text + ' Image'
                        },
                        multiple: false  // Set to true to allow multiple files to be selected
                    } );

                    // When we open the modal, make sure the correct image is selected
                    t.backDoorFrame.on( 'open', t.selectCorrectThumb );

                    // When an image is selected, run a callback.
                    t.backDoorFrame.on( 'select', t.handleSelection );
                }

                // Finally, open the modal
                t.backDoorFrame.open();
            },

            selectCorrectThumb: function () {
                var t = pmc_multipost_thumbnail_image_inline_edit;
                var attachment, selection;

                t.currentAttachmentId = t.currentThumb.data( 'image-id' );
                if ( 0 < t.currentAttachmentId ) {
                    selection = t.backDoorFrame.state().get( 'selection' );
                    attachment = wp.media.attachment( t.currentAttachmentId );
                    attachment.fetch();
                    selection.add( attachment );
                }
            },

            handleSelection: function () {
                var t = pmc_multipost_thumbnail_image_inline_edit;
                var attachment, post_id, params;

                // We set multiple to false so only get one image from the uploader
                attachment = t.backDoorFrame.state().get( 'selection' ).first().toJSON();

                t.currentAttachmentId = attachment.id;
                params = {
                    action: 'pmc-multipost_thumbnail_backdoor_image',
                    backdoor_nonce: pmc_featured_image_inline_edit_l10n.nonce,
                    attachment_id: t.currentAttachmentId,
                    post_id: t.currentId,
                    thumbnail_id: t.currentSlug,
                    thumbnail_text: t.currentText
                };

                t.currentThumb.animate( {'opacity': '0.5'} );


                // make ajax request
                $.post( ajaxurl, params, t.handleResponse );

                return false;
            },

            handleResponse: function ( r ) {
                var t = pmc_multipost_thumbnail_image_inline_edit;

                if ( false === r.error ) {
                    t.currentThumb.animate( {'opacity': '0'}, 200, function () {
                        t.currentThumb.html( r.markup );
                        t.currentThumb.data( 'image-id', t.currentAttachmentId );
                        t.currentThumb.animate( {'opacity': '1'} );
                    } );
                } else {
                    // @TODO custom popup
                    alert( r.message );
                    t.currentThumb.animate( {'opacity': '1'} );
                }
            },

            getId: function ( o ) {
                var id = $( o ).closest( 'tr' ).attr( 'id' ),
                    parts = id.split( '-' );
                return parts[parts.length - 1];
            }
        };

    }
)( jQuery );

// EOF
