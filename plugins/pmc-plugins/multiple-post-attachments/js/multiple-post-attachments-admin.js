// ref: http://plugins.svn.wordpress.org/multiple-post-thumbnails/tags/1.6.4/js/multi-post-thumbnails-admin.js
window.MultiplePostAttachments = {

	// ref: http://plugins.svn.wordpress.org/multiple-post-thumbnails/tags/1.6.4/js/media-modal.js
	MediaModal: function (options) {
	  'use strict';
	  this.settings = {
	    calling_selector: false,
	    cb: function (attachment) {}
	  };
	  var that = this,
	  frame = wp.media.frames.file_frame;
	  that.options = options;
	  if ( typeof that.options.type == 'undefined' ) {
	    that.options.type = 'image'; // default to images
	  }

	  this.attachEvents = function attachEvents() {
	    jQuery(this.settings.calling_selector).on('click', this.openFrame);
	  };

	  this.openFrame = function openFrame(e) {
	    e.preventDefault();

	    // Create the media frame.
	    frame = wp.media.frames.file_frame = wp.media({
	      title: jQuery(this).data('uploader_title'),
	      button: {
	        text: jQuery(this).data('uploader_button_text')
	      },
	      library : {
	        type : that.options.type
	      }
	    });

	    // Set filterable state to uploaded to get select to show (setting this
	    // when creating the frame doesn't work)
	    frame.on('toolbar:create:select', function(){
	      frame.state().set('filterable', 'uploaded');
	    });

	    // When an image is selected, run the callback.
	    frame.on('select', function () {
	      // We set multiple to false so only get one image from the uploader
	      var attachment = frame.state().get('selection').first().toJSON();
	      that.settings.cb(attachment);
	    });

	    frame.on('open activate', function() {
	      // Get the link/button/etc that called us
	      var $caller = jQuery(that.settings.calling_selector);

	      // Select the attachment if we have one
	      if ($caller.data('attachment_id')) {
	        var Attachment = wp.media.model.Attachment;
	        var selection = frame.state().get('selection');
	        selection.add(Attachment.get($caller.data('attachment_id')));
	      }
	    });

	    frame.open();
	  };

	  this.init = function init() {
	    this.settings = jQuery.extend(this.settings, options);
	    this.attachEvents();
	  };
	  this.init();

	  return this;
	},

    setAttachmentHTML: function(html, id, post_type){
	    jQuery('.inside', '#' + post_type + '-' + id).html(html);
    },

    setAttachmentID: function(attachment_id, id, post_type){
	    var field = jQuery('input[value=_' + post_type + '_' + id + '_attachment_id]', '#list-table');
	    if ( field.size() > 0 ) {
		    jQuery('#meta\\[' + field.attr('id').match(/[0-9]+/) + '\\]\\[value\\]').text(attachment_id);
	    }
    },

    removeAttachment: function(id, post_type, nonce){
	    jQuery.post(ajaxurl, {
		    action:'set-' + post_type + '-' + id + '-attachment', post_id: jQuery('#post_ID').val(), attachment_id: -1, _ajax_nonce: nonce, cookie: encodeURIComponent(document.cookie)
	    }, function(str){
		    if ( str == '0' ) {
			    alert( setPostThumbnailL10n.error );
		    } else {
			    MultiplePostAttachments.setAttachmentHTML(str, id, post_type);
		    }
	    }
	    );
    },


    setAsAttachment: function(attachment_id, id, post_type, nonce){
	    var $link = jQuery('a#' + post_type + '-' + id + '-attachment-' + attachment_id);
		$link.data('attachment_id', attachment_id);
	    $link.text( setPostThumbnailL10n.saving );
	    jQuery.post(ajaxurl, {
		    action:'set-' + post_type + '-' + id + '-attachment', post_id: post_id, attachment_id: attachment_id, _ajax_nonce: nonce, cookie: encodeURIComponent(document.cookie)
	    }, function(str){
		    var win = window.dialogArguments || opener || parent || top;
		    $link.text( setPostThumbnailL10n.setAttachment );
		    if ( str == '0' ) {
			    alert( setPostThumbnailL10n.error );
		    } else {
			    $link.show();
			    $link.text( setPostThumbnailL10n.done );
			    $link.fadeOut( 2000, function() {
				    jQuery('tr.' + post_type + '-' + id + '-attachment').hide();
			    });
			    win.MultiplePostAttachments.setAttachmentID(attachment_id, id, post_type);
			    win.MultiplePostAttachments.setAttachmentHTML(str, id, post_type);
		    }
	    }
	    );
    }
}