/**
 * Script to handle admin media library edit attachment events and validation
 *
 * @author Jignesh Nakrani <jignesh.nakrani@rtcamp.com>
 * @author Mike Auteri <mauteri@pmc.com>
 *
 * @since 2020-04-06 ROP-2058
 * @since 2021-01-03 BR-978
 *
 * Version 1.5
 *
 * - npm install uglifyjs -g (to install uglifyjs)
 * - uglifyjs admin-media-lib-image-restriction.js -c -o admin-media-lib-image-restriction.min.js
 */
(function($) {

	var adminMediaLibraryRestrictedFrame = {

		restrictionTypeSelector: '.compat-field-restricted_image_type input',
		restrictedNoticeSelector: '.attachment-info .restricted-notice',
		captionSelector: 'span[data-setting="caption"] textarea',
		creditSelector: 'form.compat-item .compat-field-image_credit input',
		currentPostId: $('#post_ID').val() ? $('#post_ID').val() : 0,
		attachmentModel: null,

		/**
		 * Initialization.
		 */
		init: function() {
			var _self = this,
			restrictionTypeChangeEvent = function() {
				_self.updateNotice();
			},
			updateAttachmentListenerEvent = function(e) {
				var events = ['selection:single', 'change:loaded'];

				if (events.includes(e)) {
					_self.updateAttachmentListener(null);
					_self.updateNotice();
				} else if (e.id) {
					_self.updateAttachmentListener(e.id);
					_self.updateNotice();
				}
			}

			/**
			 * Bind event to check notice when attachment data updated.
			 */
			wp.media.view.Modal.prototype.on('open', function(e) {
				$(function() {
					var frame = wp.media.frame,
						id    = _self.getAttachmentIdFromUrl();

					if (id) {
						_self.updateAttachmentListener(id);
					}

					_self.updateNotice();

					// add event handler for radio button checked change event
					$(frame.$el).on(
						'change',
						_self.restrictionTypeSelector,
						restrictionTypeChangeEvent
					);

					wp.media.frame.on('refresh', updateAttachmentListenerEvent);

					// State may not be set when modal is used by Gutenberg's MediaUpload component.
					var state = frame.state();
					if ('undefined' !== typeof state) {
						var selection = frame.state().get('selection');

						if ('undefined' !== typeof selection) {
							selection.on('all', updateAttachmentListenerEvent);
						}
					}
				});
			});

			wp.media.view.Modal.prototype.on('close', function(e) {
				var frame = wp.media.frame;

				// add event handler for radio button checked change event
				$(frame.$el).off('change', _self.restrictionTypeSelector, restrictionTypeChangeEvent);

				frame.off('refresh', updateAttachmentListenerEvent);

				// State may not be set when modal is used by Gutenberg's MediaUpload component.
				var state = frame.state();
				if ('undefined' !== typeof state) {
					var selection = frame.state().get('selection');

					if ('undefined' !== typeof selection) {
						selection.off('all', updateAttachmentListenerEvent);
					}
				}
			});
		},

		/**
		 * Get Attachment ID from URL if it exists (Media Library).
		 *
		 * @returns {null|*}
		 */
		getAttachmentIdFromUrl: function() {
			var _self  = this,
				params = _self.getParams();

			if (params.item) {
				return params.item;
			}

			return null;
		},
		/**
		 * Pass Attachment ID or null (to use selection) to this listener function
		 * to stop listening to an old model and start listening to a new one.
		 *
		 * @param id
		 */
		updateAttachmentListener: function(id) {
			var _self     = this,
				view      = wp.media.view.AttachmentCompat.prototype,
				selection = wp.media.frame.state().get('selection');

			if (_self.attachmentModel) {
				view.stopListening(_self.attachmentModel);
			}

			if (id) {
				_self.attachmentModel = wp.media.attachment(id);
			} else if ('undefined' !== typeof selection && selection.single()) {
				_self.attachmentModel = selection.single();
			} else {
				return;
			}

			view.listenTo(
				_self.attachmentModel,
				'change input',
				function() {
					_self.verifyImageCredits();
					_self.updateNotice();
				}
			);
		},

		/**
		 * Get parameters from current URL.
		 *
		 * @returns {{}}
		 */
		getParams: function () {
			var params = {},
				query  = window.location.search.substring(1),
				items  = query.split('&'),
				pair   = '';

			for (var i = 0; i < items.length; i++) {
				pair = items[i].split('=');

				params[pair[0]] = decodeURIComponent(pair[1]);
			}

			return params;
		},

		/**
		 * Function to hide, show, or update image restriction notice
		 */
		updateNotice: function() {
			var _self  = this,
				$frame = $(wp.media.frame.$el);

			// Get Notice div if added to frame
			var $restrictedNotice = $frame.find(_self.restrictedNoticeSelector);

			// Create new Div for notice/warning if dot added to frame
			if (0 === $restrictedNotice.length) {

				// add this created div before attachment-details label
				$restrictedNotice = $('<div>')
					.addClass('restricted-notice')
					.hide();

				if ($frame.find('.attachment-info .details').length) {
					$frame.find('.attachment-info .details').prepend($restrictedNotice);
				} else {
					$('.attachment-info .details').prepend($restrictedNotice);
				}
			}

			if (null === _self.attachmentModel) {
				return;
			}

			if ('undefined' === typeof _self.attachmentModel.attributes.compat) {
				return;
			}

			var imageRestrictedTypeVal = $(_self.attachmentModel.attributes.compat.item).find(_self.restrictionTypeSelector).filter(':checked').val(),
				defaultClass           = 'restricted-notice',
				noticeList             = {
					single_use: {
						class: 'restricted-single-use-notice',
						text: 'This is a Single Use Image - restricted to use for a specific post.'
					},
					site_restricted: {
						class: 'site-restricted-notice',
						text: 'This image is restricted to use on this site only.'
					}
				};

			switch (imageRestrictedTypeVal) {
				case 'single_use':
				case 'site_restricted':
					var notice = noticeList[imageRestrictedTypeVal];

					$restrictedNotice.attr('class', notice.class + ' ' + defaultClass)
						.text(notice.text)
						.show();
					break;
				default:
					$restrictedNotice.hide()
						.attr('class', defaultClass)
						.text('');
			}

			_self.validateImageRestriction();
		},

		/**
		 * Show or hide Insert Media button depending on restriction type.
		 */
		validateImageRestriction: function() {
			var _self         = this,
				$frame        = $(wp.media.frame.$el),
				$insertButton = $frame.find('.media-toolbar button.media-button');

			$insertButton
				.css('visibility', 'visible')
				.show()

			if (null === _self.attachmentModel) {
				return;
			}

			var singleUsePost = $(_self.attachmentModel.attributes.compat.item)
				.find(_self.restrictionTypeSelector)
				.filter(':checked')
				.data('singleusepost');

			if (singleUsePost && parseInt(_self.currentPostId, 10) !== parseInt(singleUsePost, 10)) {
				$insertButton.attr('disabled', true);
				$insertButton.css('visibility', 'hidden');
			} else {
				$insertButton.attr('disabled', false);
				$insertButton.css('visibility', 'visible');
			}
		},

		/**
		 * function to update image restriction type if image credit/ caption contains Associated Press tags(AP,Associated Press)
		 */
		verifyImageCredits: function() {
			var _self            = this,
				$frame           = $(wp.media.frame.$el),
				$credit          = $frame.find(_self.creditSelector),
				$caption         = $frame.find(_self.captionSelector),
				$restrictionType = $frame.find(_self.restrictionTypeSelector),
				regex            = /\bap\b|\bassociated press\b/i;

			if ($restrictionType.length && true !== $restrictionType[2].checked) {
				var captionText = $caption.val(),
					creditText  = $credit.val();

				if (null !== (regex.exec(captionText)) || null !== (regex.exec(creditText))) {
					// Update front end to single use.
					$restrictionType.eq(2).attr('checked', true);

					// Save restricted image type after updating front end.
					if (_self.attachmentModel) {
						var id   = _self.attachmentModel.attributes.id,
							data = {
								attachments: {}
							};

						data.attachments[id.toString()] = {
							restricted_image_type: 'single_use'
						};

						// Async behavior where FE caption/credit gets overwritten with old data
						// even when new data was actually saved. This fixes that behavior.
						_self.attachmentModel.saveCompat(data).done(function(res) {
							res.caption      = captionText;
							res.image_credit = creditText;

							_self.attachmentModel.save(res);
						});
					}
				}
			}
		}
	};

	adminMediaLibraryRestrictedFrame.init();
}(jQuery));
