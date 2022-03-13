/* globals pmc_admin_zoninator_options, zoninator, ajaxurl */
/* eslint complexity: ["error", 7] */
var pmc = pmc || {};
pmc.zoninator_extended = pmc.zoninator_extended || {};

(function ($) {
	'use strict';

	/**
	 * Following part is extend the jQuery.append().
	 * so it we can trigger event.
	 *
	 * @type {*}
	 */
	var origin_append = $.fn.append;
	$.fn.append = function () {
		return origin_append.apply(this, arguments).trigger('append');
	};

	/**
	 * Extend pmc global to extend zoninator.
	 */
	pmc.zoninator_extended = {
		dialog: false,

		/**
		 * Initial function.
		 *
		 * @return {void}
		 */
		init: function () {
			this.dialog = $('#pmc-zoninator-quick-post-edit-modal').dialog({
				autoOpen     : false,
				draggable    : false,
				closeOnEscape: true,
				minWidth     : 500,
				close        : this.on_dialog_close,
				buttons      : [
					{
						text : 'Save',
						click: this.on_post_save
					}
				]
			});
			this.add_quick_edit_link();
			this.events();

			/**
			 * Set WordPress heartbeat rate.
			 */
			wp.heartbeat.interval( 15 );

		},

		/**
		 * Function to bind all events.
		 *
		 * @return {void}
		 */
		events: function () {
			var self = this,
				zone_post = $('.zone-post');

			zone_post.on('dblclick', this.on_dblclick_zone_post);

			zoninator.$zonePostsList.bind('append', function (e) {

				if ('undefined' !== e.target.tagName && 'DIV' === e.target.tagName && $(e.target).hasClass('zone-posts-list')) {
					self.add_quick_edit_link();

					zone_post.unbind('dblclick', self.on_dblclick_zone_post);
					zone_post.on('dblclick', self.on_dblclick_zone_post);
				}
			});

			$(document).on( 'heartbeat-send', this.on_heartbeat_send );
			$(document).on( 'heartbeat-tick', this.on_heartbeat_tick );
		},

		/**
		 * Call back function of heartbeat on send event.
		 * To add post id listin heartbeat data
		 *
		 * @param {object} event Event Object
		 * @param {object} data Date that will send in Heartbeat.
		 *
		 * @returns {void}
		 */
		on_heartbeat_send: function ( event, data ) {

			data.post_ids = [];

			$('.zone-posts-list > div').each(function () {

				var id = $(this).data('post-id');
				id = parseInt(id, 0);

				data.post_ids.push(id);
			});

		},

		/**
		 * Callback function of heartbeat on tick event.
		 * To update status of quick edit link according to user lock status.
		 *
		 * @param {object} event Event Object
		 * @param {object} response Reponse from server.
		 *
		 * @returns {void}
		 */
		on_heartbeat_tick: function ( event, response ) {

			var post_lock_status = response.post_lock_status;

			for ( var i in post_lock_status ){

				var post_id = post_lock_status[ i ].post_id,
					zone_row = $('#zone-post-' + post_id, '.zone-posts-list'),
					action_row = $('.row-actions', zone_row);

				$('.lock-message', action_row).remove();
				$('.quick-edit', action_row).remove();
				zone_row.unbind('dblclick', pmc.zoninator_extended.on_dblclick_zone_post);

				if ( post_lock_status[ i ].lock_holder ) {

					var content = document.createElement('span');
					content = $(content);
					content.text( post_lock_status[ i ].message ).addClass('lock-message');
					action_row.append( content );

				} else {

					var anchor = document.createElement('a');
					anchor = $(anchor);
					anchor.text('Quick Edit').attr('href', 'javascript:').addClass('quick-edit').on('click', pmc.zoninator_extended.on_click_quick_edit);
					action_row.append(anchor);

					zone_row.bind('dblclick', pmc.zoninator_extended.on_dblclick_zone_post);
				}
			}
		},

		/**
		 * Function to add quick edit link in each post of the zone.
		 *
		 * @return {void}
		 */
		add_quick_edit_link: function () {
			var action_row = $('.zone-post .row-actions'),
				content = ' | ',
				anchor = document.createElement('a');

			anchor = $(anchor);
			anchor.text('Quick Edit').attr('href', 'javascript:').addClass('quick-edit').on('click', this.on_click_quick_edit);

			$('.quick-edit', action_row).remove();

			action_row.append(content);
			action_row.append(anchor);

		},

		/**
		 * Callback function of quick edit link for post in zone.
		 *
		 * @return {void}
		 */
		on_click_quick_edit: function () {
			var container = $(this).parents('div.zone-post'),
				post_id = $(container).data('post-id');

			post_id = parseInt(post_id, 0);
			pmc.zoninator_extended.open_modal(post_id);
		},

		/**
		 * Function to open quick edit modal box when double click on post.
		 *
		 * @return {void}
		 */
		on_dblclick_zone_post: function () {
			var post_id = $(this).data('post-id');
			post_id = parseInt(post_id, 0);
			pmc.zoninator_extended.open_modal(post_id);
		},

		/**
		 * Function to oepn post edit modal for given post_id.
		 * It will fetch data for related post. and open modal for edit.
		 *
		 * @param {int} post_id Post id for which dialog box is opening.
		 * @return {void}
		 */
		open_modal: function (post_id) {
			var self = this;

			// Start loading.
			zoninator.$zonePostSearch.trigger('loading.start');

			$.ajax({
				url    : ajaxurl,
				type   : 'POST',
				data   : {
					action  : 'pmc_zoninator_get_post',
					nonce   : pmc_admin_zoninator_options.get_nonce,
					security: pmc_admin_zoninator_options.security,
					zone_id : pmc_admin_zoninator_options.zone_id,
					post_id : post_id
				},
				success: function (response) {

					// Stop loading.
					zoninator.$zonePostSearch.trigger('loading.end');

					if (true !== response.success) {
						if ('undefined' !== typeof console) {
							console.error('Failed to retrieve post data');
							return;
						}
					}

					self.set_modal(response.data);

					self.dialog.dialog('open');
				},
				error  : function () {

					// Stop loading.
					zoninator.$zonePostSearch.trigger('loading.end');

					if ( 'undefined' !== typeof console ) {
						console.error('Failed to retrieve post data');
						return;
					}
				}
			});

		},

		/**
		 * Callback function to close post edit dialog.
		 * Function will reset dialog box data.
		 *
		 * @return {void}
		 */
		on_dialog_close: function () {
			pmc.zoninator_extended.reset_modal();
		},

		/**
		 * Callback function when editor click on save button.
		 * which is used to save post data.
		 *
		 * @param {Object} e Event
		 * @return {void}
		 */
		on_post_save: function (e) {
			var container = $('#pmc-zoninator-quick-post-edit-modal'),
				category_checklist = $('#checklist-category input[type=checkbox]', container),
				editorial_checklist = $('#checklist-editorial input[type=checkbox]', container),
				save_button = false,
				checked_category = [],
				checked_editorial = [],
				term_id = 0,
				post_id = $('#post_id', container).val();

			post_id = parseInt(post_id, 0);

			if ('undefined' !== typeof e.target) {
				save_button = $(e.target);
			}

			for (var i = 0; i < category_checklist.length; i++) {
				if (true === category_checklist[i].checked) {
					term_id = parseInt(category_checklist[i].value, 0);
					checked_category.push(term_id);
				}
			}

			for (var i = 0; i < editorial_checklist.length; i++) {
				if (true === editorial_checklist[i].checked) {
					term_id = parseInt(editorial_checklist[i].value, 0);
					checked_editorial.push(term_id);
				}
			}

			save_button.attr('disabled', 'disabled').children('span').text('Saving...');

			$.ajax({
				url    : ajaxurl,
				type   : 'POST',
				data   : {
					action  : 'pmc_zoninator_update_post',
					nonce   : pmc_admin_zoninator_options.update_nonce,
					security: pmc_admin_zoninator_options.security,
					zone_id : pmc_admin_zoninator_options.zone_id,
					post_id : post_id,
					data    : {
						category : checked_category,
						editorial: checked_editorial
					}
				},
				success: function (response) {

					save_button.removeAttr('disabled').children('span').text('Save');

					pmc.zoninator_extended.reset_modal();
					pmc.zoninator_extended.dialog.dialog('close');

					if (true !== response.success) {
						if ('undefined' !== typeof console) {
							console.error('Failed to update post data');
							return;
						}
					}
				},
				error  : function () {

					save_button.removeAttr('disabled').children('span').text('Save');

					pmc.zoninator_extended.reset_modal();
					pmc.zoninator_extended.dialog.dialog('close');

					if ('undefined' !== typeof console) {
						console.error('Failed to update post data');
						return;
					}
				}
			});
		},

		/**
		 * Function to reset post data from dialog box.
		 *
		 * @return {void}
		 */
		reset_modal: function () {
			var container = $('#pmc-zoninator-quick-post-edit-modal'),
				category_checklist = $('#checklist-category input[type=checkbox]', container),
				editorial_checklist = $('#checklist-editorial input[type=checkbox]', container),
				i;

			$('#post_id', container).val('');
			$('#post_title', container).val('');
			$('#post_slug', container).val('');
			$('#editorial-row', container).addClass('hide');

			for (i = 0; i < category_checklist.length; i++) {
				category_checklist[i].checked = false;
			}
			for (i = 0; i < editorial_checklist.length; i++) {
				editorial_checklist[i].checked = false;
			}
		},

		/**
		 * Function to fill dialog box with post data.
		 *
		 * @param {Object} post_data Post data.
		 * @return {void}
		 */
		set_modal: function (post_data) {
			var container = $('#pmc-zoninator-quick-post-edit-modal'),
				term_id = 0,
				i = 0;

			this.reset_modal();

			$('#post_id', container).val(post_data.ID);
			$('#post_title', container).val(post_data.post_title);
			$('#post_slug', container).val(post_data.post_name);

			if ('undefined' !== typeof post_data.post_category) {
				for (i in post_data.post_category) {
					term_id = post_data.post_category[i];
					$('#in-category-' + term_id)[0].checked = true;
				}
			}

			if ('undefined' !== typeof post_data.post_editorial) {
				$('#editorial-row', container).removeClass('hide');
				for (i in post_data.post_editorial) {
					term_id = post_data.post_editorial[i];
					$('#in-editorial-' + term_id)[0].checked = true;
				}
			}
		}
	};


	$(document).ready(function () {

		/**
		 * Extend default zoninator plugin.
		 */
		pmc.zoninator_extended.init();
	});
})(jQuery);
