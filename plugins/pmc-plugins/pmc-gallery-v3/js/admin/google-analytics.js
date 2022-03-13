(function ($) {
	'use strict';
	$(document).ready(function () {
		var pmc = window.pmc || {};
		pmc.GoogleAnalyticEvents = Backbone.View.extend({
			initialize: function (args) {
				var self = this;
				this.location = window.location.href;
				if ('undefined' !== typeof pmc_gallery_admin_user) {
					this.user = pmc_gallery_admin_user.user_login;
				}

				this.mediaFrame = args.mediaFrame;
				this.mediaFrame.on('update', this.save, this);
				$('#publish, #save-post').click(function(event){
					self.save(event);
				});
				this.mediaFrame.state('gallery-edit').get('library').on('change', this.onMetaChange, this);
				this.mediaFrame.state('gallery-library').get('library').on('change', this.onMetaChange, this);
				this.mediaFrame.state('gallery-edit').get('library').on('edit:attachments:move:next', this.editAttachmentMoveNext, this);
				this.mediaFrame.state('gallery-edit').get('library').on('edit:attachments:move:previous', this.editAttachmentMovePrevious, this);
				this.mediaFrame.state('gallery-library').get('library').on('edit:attachments:move:next', this.editAttachmentMoveNext, this);
				this.mediaFrame.state('gallery-library').get('library').on('edit:attachments:move:previous', this.editAttachmentMovePrevious, this);
				this.mediaFrame.on('edit-attachments:open', this.editAttachmentOpen, this);
			},
			events: {
				'keyup #media-search-input': 'search',
				'change #media-search-input': 'search',
				'search #media-search-input': 'search',
				'click .search-form .media-button-reverse': 'reverse_order',
				'click .search-form .media-button-sortNumerically': 'sort_number',
				'click .search-form .media-button-sortAlphabetically': 'sort_A_Z',
				'click .search-form .media-button-sortCreatedDate': 'sort_created_date',
				'click .media-button-sendToFront': 'send_to_front',
				'click .media-button-sendToBack': 'send_to_back'
			},
			onMetaChange: function (model) {
				if ('undefined' === typeof (model)) {
					return false;
				}
				var mode = this.mediaFrame.content.mode(),
					edit_mode = 'single';
				if ('gallery_edit' === mode && 1 < this.mediaFrame.state('gallery-edit').get('selection').length) {
					edit_mode = 'bulk';
				}
				var changed = Object.keys(model.changed).pop(),
					data,
					eventCategory,
					eventAction = 'edit',
					eventLabel = model.changed[changed];
				if ( '' === model._previousAttributes[changed] ) {
					eventAction = 'add';
				}
				switch( changed ){
					case 'compat':
						eventCategory = 'image_credit_' + edit_mode;
						eventLabel = $('.compat-field-image_credit input', model.changed.compat.item).val();
						break;
					case 'alt':
						eventCategory = 'image_alt_text_' + edit_mode;
						break;
					case 'image_credit':
						eventCategory = 'image_credit_' + edit_mode;
						break;
					default:
						eventCategory = 'image_' + changed + '_' + edit_mode;
						break;
				}
				data = {
					eventCategory: eventCategory,
					eventAction: eventAction,
					eventLabel: this.user + '_' + this.location + '_' + eventLabel
				};
				this.push(data);
			},
			search: function (event) {
				var data = {
					eventCategory: 'search',
					eventAction: 'perform',
					eventLabel: this.user + '_' + this.location + '_' + event.target.value
				};
				this.push(data);
			},
			save: function (event) {
				var eventAction = false;
				if ( 'undefined' !== typeof event.target ) {
					eventAction = event.target.value;
					eventAction = eventAction.toLowerCase();
					if ( 'save draft' === eventAction ) {
						eventAction = 'save';
					}
				} else {
					eventAction = 'update';
				}
				var data = {
					eventCategory: 'content',
					eventAction: eventAction,
					eventLabel: this.user + '_' + this.location
				};
				this.push(data);
			},
			reverse_order: function (event) {
				this.sort(event, 'reverse_order');
			},
			sort_number: function (event) {
				this.sort(event, 'sort_number');
			},
			sort_A_Z: function (event) {
				this.sort(event, 'sort_A_Z');
			},
			sort_created_date: function (event) {
				this.sort(event, 'sort_created_date');
			},
			sort: function (event, category) {
				var data = {
					eventCategory: category,
					eventAction: event.type,
					eventLabel: this.user + '_' + this.location
				};
				this.push(data);
			},
			send_to_front: function (event) {
				var data = {
					eventCategory: 'send_to_front',
					eventAction: event.type,
					eventLabel: this.user + '_' + this.location
				};
				this.push(data);
			},
			send_to_back: function (event) {
				var data = {
					eventCategory: 'send_to_back',
					eventAction: event.type,
					eventLabel: this.user + '_' + this.location
				};
				this.push(data);
			},
			editAttachmentMoveNext: function (event) {
				var data = {
					eventCategory: 'next_image',
					eventAction: event.type,
					eventLabel: this.user + '_' + this.location
				};
				this.push(data);
			},
			editAttachmentMovePrevious: function (event) {
				var data = {
					eventCategory: 'prev_image',
					eventAction: event.type,
					eventLabel: this.user + '_' + this.location
				};
				this.push(data);
			},
			editAttachmentOpen: function (self) {
				var selection = self.controller.state('gallery-edit').get('selection'),
					data,
					eventCategory;
				if (1 < selection.length) {
					eventCategory = 'bulk_image_edit';
				} else {
					eventCategory = 'single_image_edit';
				}
				data = {
					eventCategory: eventCategory,
					eventAction: 'view',
					eventLabel: this.user + '_' + this.location
				};
				this.push(data);
			},
			push: function (data) {
				if ('function' === typeof (ga)) {
					ga('send', 'event', data);
				}
			}
		});

	});
})(jQuery);