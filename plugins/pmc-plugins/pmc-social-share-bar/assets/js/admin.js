/* global PMC_Social_Share_Bar_Admin, pmc_social_share_bar_options */
jQuery(function () {

	PMC_Social_Share_Bar_Admin = {

		/**
		 * Register settings for JS object
		 *
		 * @since 2016-02-15
		 * @version 2016-02-15 Archana Mandhare PMCVIP-815
		 */
		settings: {
			ajax_url: pmc_social_share_bar_options.url,
			pmc_social_share_bar_nonce: pmc_social_share_bar_options.pmc_social_share_bar_nonce,
			current_order: pmc_social_share_bar_options.pmc_social_share_order
		},

		/**
		 * AJAX call to save share icons order
		 *
		 * @since 2016-02-15
		 * @version 2016-02-15 Archana Mandhare PMCVIP-815
		 */
		save: function () {
			var post_type = jQuery('#pmc-post-type').val();
			var primary_icons = [];
			var secondary_icons = [];
			jQuery('.spinner').css('visibility', 'visible');

			jQuery('#primary-icons').find('li').each(function () {
				var list_id = jQuery(this).attr('id');
				if (typeof undefined !== typeof list_id) {
					primary_icons.push(list_id);
				}
			});

			jQuery('#secondary-icons').find('li').each(function () {
				var list_id = jQuery(this).attr('id');
				if (typeof undefined !== typeof list_id) {
					secondary_icons.push(list_id);
				}
			});

			jQuery.ajax({
				type: 'post',
				url: this.settings.ajax_url,
				data: {
					action: 'save_order',
					post_type: post_type,
					primary_icons: primary_icons,
					secondary_icons: secondary_icons,
					pmc_social_share_bar_nonce: this.settings.pmc_social_share_bar_nonce
				},
				complete: function () {
					jQuery('.spinner').css('visibility', 'hidden');
				}
			});
		},

		/**
		 * AJAX call to get icon order according to post type.
		 *
		 * @since	2017-03-10
		 * @version 2017-03-10 Dhaval Parekh CDWE-247
		 * @returns	{void}
		 */
		get: function () {
			var spinner = jQuery('.spinner'),
				post_type = jQuery('#pmc-post-type').val(),
				self = this;
			spinner.css('visibility', 'visible');
			self.disable_sorting();

			jQuery.ajax({
				type: 'post',
				url: this.settings.ajax_url,
				data: {
					action: 'get_order',
					post_type: post_type,
					pmc_social_share_bar_nonce: this.settings.pmc_social_share_bar_nonce
				},
				success: function (response) {
					self.enable_sorting();
					spinner.css('visibility', 'hidden');

					if ( ! response.success ) {
						return false;
					}

					var data = response.data;

					self.reorder(data);
				},
				complete: function () {
					self.enable_sorting();
					spinner.css('visibility', 'hidden');
				}
			});
		},

		/**
		 * AJAX call to reset the position of share icons order to default
		 *
		 * @since 2016-02-28
		 * @version 2016-02-28 Archana Mandhare PMCVIP-815
		 */
		reset: function () {
			var post_type = jQuery('#pmc-post-type').val(),
					self = this;
			jQuery('.spinner').css('visibility', 'visible');
			self.disable_sorting();

			jQuery.ajax({
				type: 'post',
				url: this.settings.ajax_url,
				data: {
					action: 'reset_order',
					post_type: post_type,
					pmc_social_share_bar_nonce: this.settings.pmc_social_share_bar_nonce
				},
				success: function (response) {
					self.enable_sorting();
					if ( ! response.success ) {
						return false;
					}
					var data = response.data;
					self.reorder(data);

				},
				complete: function () {
					self.enable_sorting();
					jQuery('.spinner').css('visibility', 'hidden');
				}
			});
		},

		/**
		 * Function will execute initially page load.
		 *
		 * @since	2017-03-10
		 * @version 2017-03-10 Dhaval Parekh CDWE-247
		 * @returns {void}
		 */
		setup: function () {
			jQuery('#pmc-post-type').val('default');
			this.get();
		},

		/**
		 * To reorder icons according to given format.
		 *
		 * @since	2017-03-10
		 * @version 2017-03-10 Dhaval Parekh CDWE-247
		 * @param {Object} data Icon order.
		 * @returns {void}
		 */
		reorder: function (data) {
			var i = 0,
				j = 0;

			for (i in data.primary) {
				var cloned = jQuery('#' + data.primary[i]).clone();
				cloned = cloned.data('position', i + 1);
				jQuery('#' + data.primary[i]).remove();
				jQuery('#primary-icons').append(cloned);
			}

			for (j in data.secondary) {
				var cloned = jQuery('#' + data.secondary[j]).clone();
				cloned = cloned.data('position', j + 1);
				jQuery('#' + data.secondary[j]).remove();
				jQuery('#secondary-icons').append(cloned);
			}
			jQuery('#primary-icons li').sort(sort_li).appendTo('#primary-icons');
			jQuery('#secondary-icons li').sort(sort_li).appendTo('#secondary-icons');
		},

		/**
		 * Function to make icon sortable.
		 *
		 * @since	2017-03-10
		 * @version 2017-03-10 Dhaval Parekh CDWE-247
		 * @returns {void}
		 */
		init_sortable: function () {
			var self = this;
			jQuery('#primary-icons').sortable({
				opacity: 0.6,
				axis: 'x',
				revert: true,
				items: '.share-buttons-sortables',
				connectWith: '.dropme',
				cursor: 'pointer',
				receive: function (event, ui) {
					if ( jQuery('#primary-icons').children('li').length < pmc_social_share_bar_options.min_primary_count || jQuery('#primary-icons').children('li').length > pmc_social_share_bar_options.max_primary_count ) {
						jQuery(ui.sender).sortable('cancel');
					} else {
						self.add_icon_to_list(ui, this);
					}
				}
			});

			jQuery('#secondary-icons').sortable({
				opacity: 0.6,
				axis: 'y',
				revert: true,
				items: '.share-buttons-sortables',
				connectWith: '.dropme',
				cursor: 'pointer',
				receive: function (event, ui) {
					if ( jQuery('#primary-icons').children('li').length < pmc_social_share_bar_options.min_primary_count ) {
						jQuery(ui.sender).sortable('cancel');
					} else {
						self.add_icon_to_list(ui, this);
					}
				}
			});
		},

		/**
		 * To enable icon sortable.
		 *
		 * @since	2017-03-10
		 * @version 2017-03-10 Dhaval Parekh CDWE-247
		 * @returns {void}
		 */
		enable_sorting: function () {
			jQuery('#primary-icons').sortable('enable');
			jQuery('#secondary-icons').sortable('enable');
		},

		/**
		 * To disable sorting for icons.
		 *
		 * @since	2017-03-10
		 * @version 2017-03-10 Dhaval Parekh CDWE-247
		 * @returns {void}
		 */
		disable_sorting: function () {
			jQuery('#primary-icons').sortable('disable');
			jQuery('#secondary-icons').sortable('disable');
		},

		/**
		 * callback function when icon is swiching from one location to another
		 * i.g. when icon switch primary location to secondary.
		 *
		 * @param {Object} $item
		 * @param {Object} $target
		 * @returns {void}
		 */
		add_icon_to_list: function ($item, $target) {
			jQuery($item.item).fadeOut(function () {
				if ( 'secondary-icons' === jQuery($target).attr('id') ) {
					jQuery($item.item).find('span').removeClass('primary-hide');
				} else {
					jQuery($item.item).find('span').addClass('primary-hide');
				}
				jQuery($item.item).css('display', 'inline-block');
				jQuery($item.item).fadeIn();

			});
		}
	};
});

/**
 * Callback function to sorting icons.
 *
 * @param {Object} a
 * @param {Object} b
 * @returns {Boolean}
 */
function sort_li(a, b) {
	var a_int = parseInt(jQuery(a).data('position'), 0),
		b_int = parseInt(jQuery(b).data('position'), 0);
	return a_int > b_int;
}

jQuery(document).ready(function () {
	PMC_Social_Share_Bar_Admin.init_sortable();

	jQuery('#primary-icons, #secondary-icons').disableSelection();

	jQuery('#pmc-social-share-bar-save').on('click', function (e) {
		e.preventDefault();
		PMC_Social_Share_Bar_Admin.save();
	});

	jQuery('#pmc-social-share-bar-reset').on('click', function (e) {
		e.preventDefault();
		PMC_Social_Share_Bar_Admin.reset();
	});

	jQuery('#pmc-post-type').on('change', function () {
		PMC_Social_Share_Bar_Admin.get();
	});
	PMC_Social_Share_Bar_Admin.setup();
});

