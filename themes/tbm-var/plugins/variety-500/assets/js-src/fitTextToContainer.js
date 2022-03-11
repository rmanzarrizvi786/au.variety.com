// jshint es3: false
// jshint esversion: 6
/* eslint arrow-body-style: 0 */

'use strict';

/**
 * Fit text content to the container width on resize.
 */
const fitTextToContainer = {

  /**
   * Elements affected.
   *
   * @type {array}
   */
  $elements: [],

  /**
   * Initialize.
   *
   * @returns {void}
   */
  init: function init() {
    const self = this;

    // Get all elements.
    self.$elements = $('[data-trigger="fit-text-to-container"]');

    // Bind self.
    _.bindAll(self, 'fitOnResize', 'fitText');

    // Continue only if there are any elements to be resized.
    if (_.size(self.$elements)) {
      self.$elements.wrapInner('<span>');
      $(window).on('resize', self.fitOnResize).trigger('resize');
    }
  },

  /**
   * Fit each element with on resize event.
   *
   * @returns {void}
   */
  fitOnResize: function fitOnResize() {
    const self = this;
    _.each(self.$elements, self.fitText);
  },

  /**
   * Compute new font size so that text fits into the container.
   *
   * @param {Object} el Container DOM object.
   * @returns {void}
   */
  fitText: function fitText(el) {
    const self = this;
    const element = el;
    const span = el.children[0];
    const textWidth = span.offsetWidth;
    const availableWidth = element.offsetWidth;
    const threshold = 2;
    let fontSize;
    let newFontSize;

    if ('' === el.style.fontSize && availableWidth > textWidth) {

      // Exit if the text fits the container.
      self.$elements = _.reject(self.$elements, (item) => {
        return item === element;
      });
    } else {
      if (availableWidth < textWidth || availableWidth > textWidth + threshold) {
        fontSize = parseInt($(element).css('font-size'), 0);
        newFontSize = fontSize * availableWidth / textWidth;
        element.style.fontSize = newFontSize + 'px';
      }
    }
  },

};

export default fitTextToContainer;
