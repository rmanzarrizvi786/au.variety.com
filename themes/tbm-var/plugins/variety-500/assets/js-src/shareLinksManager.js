// jshint es3: false
// jshint esversion: 6

'use strict';

/**
 * Provide social share icons toggle functionality.
 */
const shareLinksManager = {

  /**
   * Initialize.
   *
   * @returns {void}
   */
  init: function init() {
    const $el = $('[data-trigger="share-links-manager"]');
    const $icons = $el.find('[data-share-icon]');

    $el.on('click', '[data-toggle]', (e) => {
      e.preventDefault();
      $el.find('[data-toggle]').remove();

      if (0 < $icons.length) {
        this.showIcons($icons);
      }
    });
  },

  /**
   * Recursively reveal social icons.
   *
   * @param {jQuery} $icons jQuery collection of icon objects.
   */
  showIcons: function showIcons($icons) {
    $icons.eq(0).removeClass('is-hidden');

    const $reducedIcons = $icons.slice(1);
    if (0 < $reducedIcons.length) {
      _.delay(() => {
        this.showIcons($reducedIcons);
      }, 80);
    }
  },

};

export default shareLinksManager;
