// jshint es3: false
// jshint esversion: 6

'use strict';

/**
 * Manage site header component.
 */
const siteHeaderManager = {

  /**
   * Window object.
   *
   * @type {object}
   */
  $window: {},

  /**
   * HTML element.
   *
   * @type {object}
   */
  $htmlEl: {},

  /**
   * Body element.
   *
   * @type {object}
   */
  $bodyEl: {},

  /**
   * Is header fixed by default.
   *
   * @type {boolean}
   */
  isHeaderFixedByDefault: false,

  /**
   * Header element.
   *
   * @type {object}
   */
  $header: {},

  /**
   * Ghost header element.
   *
   * @type {object}
   */
  $headerGhost: {},

  /**
   * Header search element.
   *
   * @type {object}
   */
  $headerSearch: {},

  /**
   * Collection of header elements
   *
   * @type {object}
   */
  headerEls: {},
  
  /**
   * Height of the header
   *
   * @type {Number}
   */
  headerHeight: 0,

  /**
   * Top position of the header
   *
   * @type {Number}
   */
  headerTop: 0,

  /**
   * HTML top margin (set by WP admin bar)
   *
   * @type {Number}
   */
  htmlMarginTop: 0,

  /**
   * Offcanvas nav element.
   *
   * @type {object}
   */
  $offcanvasNav: 0,

  /**
   * Initialize.
   *
   * @returns {void}
   */
  init: function init() {
    const self = this;

    self.$window = $(window);
    self.$htmlEl = $('html');
    self.$bodyEl = $('body');
    self.$header = self.$bodyEl.find('[data-trigger="header-manager"]');

    if (0 === self.$header.length) {
      return;
    }

    _.bindAll(self, 'mobileToggleClick', 'searchToggleClick', 'toggleFixedHeader', 'refresh', 'mainNavClick');

    self.initHeaderSearch();
    self.initFixedHeader();

    self.$bodyEl.on('click', '[data-header-trigger="mobile-nav"]', self.mobileToggleClick);
    self.$bodyEl.on('click', '[data-header-trigger="search"]', self.searchToggleClick);
    self.$bodyEl.on('click', '.c-site-header__menu a[href*="#"]', self.mainNavClick);
    self.$headerSearch.on('submit', 'form', self.handleFormSubmit);
    self.$window.on('resize', _.throttle(self.refresh, 10));

    // Scroll to section if location hash is a valid selector.
    const hash = document.location.hash;
    if (hash.length) {
      let target;
      try {
        target = $(hash);
      } catch (e) {
        target = false;
      }

      if (0 !== target && target.length) {
        _.delay(() => {
          self.scrollToAnchor(hash, null);
        }, 50);
      }
    }
    _.delay(self.refresh, 10);
  },

  /**
   * Cache DOM elements.
   *
   * @returns {void}
   */
  initHeaderSearch: function initHeaderSearch() {
    const self = this;

    self.$headerSearch = $('[data-header-module="search"]');
    self.headerEls = {
      $container: self.$header.find('[class*="container"]:first'),
      $backButton: self.$header.find('[class*="back"]:first'),
      $logo: self.$header.find('[class*="logo"]:first'),
      $searchToggle: self.$header.find('[class*="search-toggle"]:first'),
      $mobileToggle: self.$header.find('[class*="mobile-toggle"]:first'),
    };
  },

  /**
   * Initialize fixed header feature.
   *
   * @returns {void}
   */
  initFixedHeader: function initFixedHeader() {
    const self = this;

    self.headerTop = self.$header.offset().top;

    // Create a header ghost element.
    self.$headerGhost = $('<div>');
    self.$header.after(self.$headerGhost).css('top', `${self.headerTop}px`);
    self.$header.detach().addClass('has-loaded');
    self.$bodyEl.append(self.$header);

    // Listen for scroll event only if the header is not fixed from the very beginning.
    if (0 !== self.headerTop) {
      self.$window.on('scroll', _.throttle(self.toggleFixedHeader, 8) );
    }

    // Initialize the offcanvas menu.
    self.$offcanvasNav = self.$bodyEl.find('.l-offcanvas__nav');

    self.refresh();
  },

  /**
   * Toggle sticky header.
   *
   * @returns {void}
   */
  toggleFixedHeader: function toggleFixedHeader() {
    const self = this;
    const scrollTop = self.$window.scrollTop();
    const limit = self.headerTop - self.htmlMarginTop;
    const isFixed = self.$header.hasClass('is-fixed');

    if (scrollTop >= limit && !isFixed) {
      self.$header.addClass('is-fixed');
      self.repositionHeader();
    } else if (scrollTop < limit && isFixed) {
      self.$header.removeClass('is-fixed');
      self.repositionHeader();
    }
  },

  /**
   * Refresh header - recalculate dimensions.
   *
   * @returns {void}
   */
  refresh: function onResize() {
    const self = this;

    self.calculateSearchFormPosition();
    self.calculateHeaderDimensions();
    self.repositionHeader();
    self.toggleFixedHeader();
  },

  /**
   * Determine header dimensions.
   *
   * @returns {void}
   */
  calculateHeaderDimensions: function calculateHeaderDimensions() {
    const self = this;

    self.headerHeight = self.$header.outerHeight();
    self.headerTop = self.$headerGhost.offset().top;
    self.htmlMarginTop = parseInt(self.$htmlEl.css('margin-top'), 0);

    // Set the proper height of the header ghost element.
    self.$headerGhost.height(self.headerHeight);
  },

  /**
   * Position the header based on the context.
   *
   * @returns {void}
   */
  repositionHeader: function repositionHeader() {
    const self = this;
    const isFixed = self.$header.hasClass('is-fixed');

    if (isFixed) {
      self.$header.css('top', `${self.htmlMarginTop}px`);
    } else {
      self.$header.css('top', `${self.headerTop}px`);
    }

    self.$offcanvasNav.css('top', `${self.htmlMarginTop}px`);
  },

  /**
   * Determine search form position.
   *
   * @returns {void}
   */
  calculateSearchFormPosition: function calculateSearchFormPosition() {
    const self = this;
    const $backButton = self.headerEls.$backButton;
    let headerCss = {
      left: '',
      right: '',
    };

    if ('none' !== $backButton.css('display')) {
      const logoWidth = self.headerEls.$logo.outerWidth();
      const containerWidth = self.headerEls.$container.outerWidth();
      const searchToggleLeft = self.headerEls.$searchToggle.offset().left;
      const $mobileToggle = self.headerEls.$mobileToggle;
      let mobileToggleWidth = 0;

      if ('none' !== $mobileToggle.css('display')) {
        mobileToggleWidth = $mobileToggle.outerWidth();
      }

      headerCss = {
        left: `${mobileToggleWidth + logoWidth}px`,
        right: `${containerWidth - searchToggleLeft}px`,
      };
    }

    self.$headerSearch.css(headerCss);
  },

  /**
   * Handle mobile toggle click.
   *
   * @param {Event} e
   *
   * @returns {void}
   */
  mobileToggleClick: function mobileToggleClick(e) {
    const self = this;

    self.$htmlEl.toggleClass('is-menu-open');
    self.refresh();
    e.preventDefault();
  },

  /**
   * Handle search toggle click.
   *
   * @param {Event} e
   *
   * @returns {void}
   */
  searchToggleClick: function searchToggleClick(e) {
    const self = this;
    const $container = self.$headerSearch;
    const $field = $container.find('input:first');
    const $form = $container.find('form:first');

    if ($container.hasClass('is-expanded')) {
      if ('' !== $field.val()) {
        $form.submit();
      } else {
        $field.blur();
      }
    } else {
      _.delay(() => {
        $field.focus();
      }, 800);
    }

    $container.toggleClass('is-expanded');
    e.preventDefault();
  },

  /**
   * Replace placeholder with query string on search form submit.
   *
   * @param {Event} e
   *
   * @returns {void}
   */
  handleFormSubmit: function handleFormSubmit(e) {
    const form = e.currentTarget;

    if (-1 !== form.action.indexOf( 'v500searchterm' )) {
      const input = form.elements[0];
      const query = input.value;
      input.disabled = true;
      form.action = form.action.replace( 'v500searchterm', query );
    }
  },

  /**
   * Handle main navigation click events.
   *
   * @param {Event} e Event object
   */
  mainNavClick: function mainNavClick(e) {
    const self = this;
    const sectionId = e.currentTarget.hash;

    if (sectionId.length && e.currentTarget.pathname === document.location.pathname) {
      e.preventDefault();
      self.scrollToAnchor(sectionId, e.currentTarget);
      return false;
    }

    return this;
  },

  /**
   * Scroll to the page element pointed by the anchor.
   *
   * @param {string} sectionId Destination section ID
   * @param {object} target Current target element
   */
  scrollToAnchor: function scrollToAnchor(sectionId, target) {
    const self = this;
    const startPosition = self.$bodyEl.scrollTop();
    const topBoundary = self.headerHeight + self.htmlMarginTop;
    let stopPosition = $(sectionId).offset().top;

    if (stopPosition > topBoundary) {
      stopPosition -= topBoundary;
    }

    const durationMultiplier = Math.abs(stopPosition - startPosition) / 1600;
    const duration = 160 + (160 * durationMultiplier);
    let delayTime = 0;
    if (!_.isUndefined(target) && $(target).parents('.l-offcanvas__nav').length) {
      delayTime = 240;
    }

    _.delay(() => {
      $('html, body').stop().animate({
        scrollTop: stopPosition,
      }, duration);
    }, delayTime);

    if (self.$htmlEl.hasClass('is-menu-open')) {
      self.$htmlEl.removeClass( 'is-menu-open' );
      self.refresh();
    }
  },

};

export default siteHeaderManager;
