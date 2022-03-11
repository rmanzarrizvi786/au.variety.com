// jshint es3: false
// jshint esversion: 6

'use strict';

/**
 * Manage home intro
 */
const homeIntroManager = {

  /**
   * Intro text container element.
   *
   * @type {object}
   */
  $introContainer: {},

  /**
   * Initialize.
   *
   * @returns {void}
   */
  init: function init() {
    const self = this;
    const $introContainer = $('.c-page-intro--homepage .c-page-intro__background-text');

    // Continue if there is a intro container.
    if (0 === $introContainer.length) {
      return;
    }

    // Get the elements.
    self.$introContainer = $introContainer;

    // Add event listeners.
    _.bindAll(self, 'onScroll');
    $(window).on('scroll', _.throttle(self.onScroll, 10));
    self.onScroll();
  },

  /**
   * Handle (throttled) scroll event.
   *
   * @returns {void}
   */
  onScroll: function onScroll() {
    const self = this;
    const $introContainer = self.$introContainer;
    const scrollTop = $(window).scrollTop();
    const offTop = (200 - scrollTop) / 5;

    $introContainer.css({
      transform: `translateY(${offTop}px)`,
    });
  },

};

export default homeIntroManager;
