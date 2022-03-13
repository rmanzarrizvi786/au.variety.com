(function ($, media) {
	'use strict';
	$(document).ready(function () {
		var pmc = window.pmc || {},
			l10n = media.view.l10n;
		pmc.view = pmc.view || {};

		pmc.view.selectionAll = media.View.extend({
			tagName: 'div',
			className: 'media-selection select-all',
			template: _.template('<div class="selection-info"><button type="button" class="button-link select-all">' + l10n.selectAll + '</button></div>'),

			/**
			 * List of events.
			 */
			events: {
				'click .select-all': 'selectAll'
			},

			/**
			 * Initialize functions to add event listner on collection.
			 *
			 * @param {Object} args argument for toolbar controll.
			 * @returns {void}
			 */
			initialize: function (args) {
				this.state = args.state;
				this.library = this.state.get('library');
				this.selection = this.state.get('selection');
				this.selection.on('add remove reset', this.refresh, this);
				this.controller.on('content:activate', this.refresh, this);
			},

			/**
			 * Click event of `Select All` link.
			 * Add all attachment in media library to selection.
			 *
			 * @param {Object} event Click event object.
			 * @returns {void}
			 */
			selectAll: function (event) {
				event.preventDefault();
				this.selection.reset(this.library.toArray());
			},

			/**
			 * Fucntion will execute when tab change.
			 *
			 * @returns {void}
			 */
			ready: function () {
				this.refresh();
			},

			/**
			 * Function is reponsiable to hide/show the `Select All` link
			 * according to selection.
			 *
			 * @returns {void}
			 */
			refresh: function () {
				if (0 < this.selection.length) {
					this.$el.addClass('empty');
				} else {
					this.$el.removeClass('empty');
				}
			}
		});
		_.extend(window.pmc, pmc);
	});
})(jQuery, wp.media);

