/*
 * Version 1.04
 */

var PmcGalleryAdminScript = {
	TinyMceCaptionId         :'',
	TinyMceCount             :0,
	TinyMceTextAreaId        :'',
	Init                     :function () {
		jQuery(document).ready(function () {
			PmcGalleryAdminScript.BindEvents();

			// Override wp.media.view.Settings.Gallery intercept media model readyness...
			(function($) {

				var _GallerySetting = wp.media.view.Settings.Gallery;
				wp.media.view.Settings.Gallery = _GallerySetting.extend({
					ready:function () {
						_GallerySetting.prototype.ready.apply(this, arguments);
						PmcGalleryAdminScript.BindEvents();
					}
				});

			})(jQuery);
		});
	},
	BindEvents               :function () {
		var $attachments = jQuery('.attachments-browser ul.attachments');
		$attachments.on('click focus', 'textarea.caption', PmcGalleryAdminScript.CaptionTextAreaFocus);
		$attachments.on('focusin', 'textarea.caption', PmcGalleryAdminScript.CaptionTextAreaFocusIn);
		$attachments.on('focusout', 'textarea.caption', PmcGalleryAdminScript.CaptionTextAreaFocusOut);
		$attachments.on('click', '.attachment .thumbnail', PmcGalleryAdminScript.BindMetaBoxCaptionTinyMce);
	},
	CaptionTextAreaFocusIn   :function (e) {
		var $attachment = jQuery(this).closest('li.attachment');
		$attachment.addClass('caption-focus');
	},
	CaptionTextAreaFocusOut  :function (e) {
		var $attachment = jQuery(this).closest('li.attachment');
		$attachment.removeClass('caption-focus');
	},
	CaptionTextAreaFocus     :function (e) {
		if (!jQuery(this).parents('li').hasClass('selected')) {
			jQuery(this).parent().find('img').trigger('click');
		}
	},
	HoverIn                  :function (e) {
		jQuery(this).find('textarea').animate({ height:"200px" }, 400);
	},
	HoverOut                 :function (e) {
		jQuery(this).find('textarea').animate({ height:"40px" }, 400);
	},
	BindMetaBoxCaptionTinyMce: function() {
		if ( PmcGalleryAdminScript.TinyMceCaptionId != '' ) {
			tinyMCE.execCommand('mceRemoveEditor', false, PmcGalleryAdminScript.TinyMceCaptionId);
		}
		if (jQuery(".attachment-details .setting[data-setting='caption']").length) {
			PmcGalleryAdminScript.TinyMceCaptionId = 'pmc-caption-' + (PmcGalleryAdminScript.TinyMceCount++);
			jQuery(".attachment-details .setting[data-setting='caption']").find('textarea').addClass("mceEditor").attr('id', PmcGalleryAdminScript.TinyMceCaptionId);
			PmcGalleryAdminScript.AttachTinyMce(PmcGalleryAdminScript.TinyMceCaptionId);
		}
	},
	AttachTinyMce: function (selector) {
		setTimeout(function () {
		tinyMCE.init({
				theme        :"modern",
				plugins      :"paste",
			width: "100%",
				resize       :true, //allows only vertical resizing, not horizontal
				statusbar    :true,
				menubar      :false,
				toolbar1     :"bold italic | strikethrough | link unlink",
				paste_as_text:true, //dont paste rich text - setting available only if 'paste' plugin is loaded

			onchange_callback: function(ed) {
				tinyMCE.triggerSave();
			},
			setup: function(ed){
					ed.onChange.add(function (l) {
						ed.save();
					});
			}
		});
			tinyMCE.execCommand("mceAddEditor", true, selector);
		}, 100);
	}

};

PmcGalleryAdminScript.Init();

//EOF
