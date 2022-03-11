// jshint es3: false
// jshint esversion: 6

'use strict';

/**
 * Manage navigation on the 'Profile' page.
 */
const profileNavManager = {

  /**
   * Nav element.
   *
   * @type {object}
   */
  $nav: {},

  /**
   * Initialize.
   *
   * @returns {void}
   */
  init: function init() {
    const self = this;

    // Get the nav element.
    self.$nav = $('[data-trigger="profile-nav-manager"]');

    // Continue only if there is a nav bar.
    if (self.$nav.length) {
      self.$nav.on('click', 'a[href^="#"]', self.scrollToAnchor);
    }
  },

  /**
   * Scroll to the page element pointed by the anchor.
   *
   * @param {Event} e Event object
   */
  scrollToAnchor: function(e) {
    const anchor = this.hash;

    if (anchor.length) {
      e.preventDefault();

      const startPosition = $('body').scrollTop();
      const headerHeight = $('.l-site-header:first').outerHeight();
      const htmlMarginTop = parseInt($('html').css('margin-top'), 0);
      const topBoundary = headerHeight + htmlMarginTop;
      let stopPosition = $(anchor).offset().top;

      if (stopPosition > topBoundary) {
        stopPosition = stopPosition - topBoundary;
      }

      const durationMultiplier = Math.abs(stopPosition - startPosition) / 1600;
      const duration = 160 + (160 * durationMultiplier);

      $('html, body').stop().animate({
        scrollTop: stopPosition,
      }, duration);
    }
  },

};

export default profileNavManager;
