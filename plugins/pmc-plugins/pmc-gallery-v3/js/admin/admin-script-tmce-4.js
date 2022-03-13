/* global tinyMCE,jQuery, console */
/* eslint no-magic-numbers: [ "error", { "ignore": [0,100,400,1000] } ]*/
/* eslint max-nested-callbacks: ["error", 5] */
/* eslint complexity: ["error", 7] */
(function ($) {
	'use strict';
	var PmcGalleryAdminScript = {
		TinyMceCaptionId: '',
		CaptionTextAreaObj: false,

		BindMetaBoxCaptionTinyMce: function () {
			var sideBarDetails;
			tinyMCE.EditorManager.editors = [];

			sideBarDetails = $('.attachment-details .setting[data-setting="caption"]');
			//checks if media-modal is open, then attach tinyMCE to sidebar caption field, else to we are on gallery edit screen
			if (sideBarDetails.length) {

				PmcGalleryAdminScript.TinyMceCaptionId = 'pmc-details-caption-' + $('.attachment-details').data('id');
				$(sideBarDetails).find('textarea').addClass('mceEditor').attr('id', PmcGalleryAdminScript.TinyMceCaptionId);
				PmcGalleryAdminScript.delegateToolbar = false;
				PmcGalleryAdminScript.AttachTinyMce(PmcGalleryAdminScript.TinyMceCaptionId);

			}
		},
		AttachTinyMce: function (selector) {
			var inLine = false,
				selectorId = '#' + selector;

			//checks if tinyMCE is attached to any tags other then 'textare' and 'input', it will
			//set 'inline' property true to show tinyMCE inline
			if (!/TEXTAREA|INPUT/i.test($(selectorId)[0].nodeName)) {
				inLine = true;
			}

			tinyMCE.execCommand( 'mceRemoveEditor', false, selector );

			setTimeout(function () {

				//By Default TinyMCE only provides a dropdown menu for style formats (p, h2, h5..)
				//The following is a custom TinyMCE plugin which creates buttons for each of the given block formats
				//Afterwards, names like 'style-h3' can be used in the tinyMCE.init.toolbar arguments
				tinyMCE.PluginManager.add('formatoptions', function (editor) {
					// Add more block formats to the array below as needed, i.e. ['h3', 'h4']
					// will allow you to use 'style-h4' when building the tool bar.
					['h3'].forEach(function (name) {
						editor.addButton('style-' + name, {
							tooltip: 'Toggle ' + name,
							text: name.toUpperCase(),
							onClick: function () {
								editor.execCommand('mceToggleFormat', false, name);
							},
							onPostRender: function () {
								var self = this,
									setup = function () {
										editor.formatter.formatChanged(name, function (state) {
											self.active(state);
										});
									};
								if ( editor.formatter ) {
									setup();
								} else {
									editor.on('init', setup);
								}
							}
						});
					});
				});
				//see the 'formatoptions' tinyMCE plugin below for more info
				var tPlugins = 'paste, formatoptions, wordpress, wplink';
				 //style-h3 is a custom button name (see the 'formatoptions' tinyMCE plugin below)
				var tToolbar1 = 'bold italic style-h3 | strikethrough | link unlink';
				if ( window.pmcGalleryV3AdminGallery.pmcStoreProductsEnabled ) {
					tPlugins += ', pmc_buy_now_button';
					tToolbar1 += ' | pmc_buy_now_button';
				}

				if ( window.pmcGalleryV3AdminGallery.pmcBuyNowEnabled ) {
					tPlugins += ', pmc_buy_now_button';
					tToolbar1 += ' | pmc_buy_now_button';
				}
				tinyMCE.init({
					selector: selectorId,
					theme: 'modern',
					inline: inLine,
					plugins: tPlugins, //see the 'formatoptions' tinyMCE plugin below for more info
					width: '100%',
					resize: true, //allows only vertical resizing, not horizontal
					statusbar: true,
					menubar: false,
					toolbar1: tToolbar1, //style-h3 is a custom button name (see the 'formatoptions' tinyMCE plugin below)
					paste_as_text: true, //dont paste rich text - setting available only if 'paste' plugin is loaded
					setup: function (ed) {
						ed.on('change', function () {
							ed.save();
						});
						ed.on('blur', function () {
							$(this.targetElm).change();
						});
						try {
							// workaround issue with wplink tinymce plugin
							if (!ed.wp && tinyMCE.editors && tinyMCE.editors[0] && tinyMCE.editors[0].wp) {
								ed.wp = tinyMCE.editors[0].wp;
							} else if (!ed.wp) {
								ed.wp = {
									_createToolbar: function () {
										return {
											on: function () {}
										};
									}
								};
							}
						} catch (ignore) {
							if ( console && console.error ) {
								console.error('Something went wrong!');
							}
						}
					}
				});
			}, 100);
		}
	};
	window.PmcGalleryAdminScript = PmcGalleryAdminScript;
})(jQuery);
