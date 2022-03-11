// jshint es3: false
// jshint esversion: 6

'use strict';

/**
 * Manage the 'Spotlight' component.
 */
const spotlightManager = {

  /**
   * Container element.
   *
   * @type {object}
   */
  $el: {},

  /**
   * Collection of slides.
   *
   * @type {array}
   */
  $slides: [],

  /**
   * Collection of triggers.
   *
   * @type {array}
   */
  $triggers: [],

  /**
   * Physical vertical position when Spotlight should be revealed.
   *
   * @type {Number}
   */
  revealPoint: 0,

  /**
   * Is the Spotlight revealed.
   *
   * @type {Boolean}
   */
  isRevealed: true,

  /**
   * Initialize.
   *
   * @returns {void}
   */
  init: function init() {
    const self = this;
    const $el = $('[data-trigger="spotlight-manager"]');

    // Continue only if there is a nav bar.
    if (!$el.length) {
      return;
    }

    // Get the container.
    self.$el = $el;
    _.bindAll(self, 'onNavLink', 'onResize', 'onScroll');

    // Setup the slider.
    self.setupSlider();

    if (2 > $el.data('slidesCount')) {
      return;
    }

    // Manage navigation links.
    self.goToSlide($el.data('activeSlide'));
    $el.on('click', '[data-spotlight-trigger]', self.onNavLink);
    $(window).on('resize', _.throttle(self.onResize, 10)).trigger('resize');
    $(window).on('scroll', _.throttle(self.onScroll, 10)).trigger('scroll');
  },

  /**
   * Setup Spotlight slider.
   *
   * @returns {void}
   */
  setupSlider: function setupSlider() {
    const self = this;
    const $el = self.$el;
    const $triggers = $el.find('[data-spotlight-trigger]');
    const $slides = $el.find('[data-spotlight-slide]');
    const multiples = $slides.filter('[data-spotlight-slide="1"]').length;
    const totalSlides = $slides.length / multiples;
    const $activeSlide = $slides.filter('.is-active:first');
    let activeSlide = 1;

    if (0 !== $activeSlide.length) {
      activeSlide = $activeSlide.data('spotlightSlide');
    }

    self.$slides = $slides;
    self.$triggers = $triggers;

    $el.data({
      totalSlides,
      activeSlide,
    });
  },

  /**
   * Handle navigation link click events.
   *
   * @param {Event} e
   * @returns {void}
   */
  onNavLink: function onNavLink(e) {
    const self = this;
    const $el = self.$el;
    const $link = $(e.currentTarget);
    const activeSlide = $el.data('activeSlide');
    const totalSlides = $el.data('totalSlides');
    const target = $link.data('spotlightTrigger');
    let newSlideIndex = false;

    if ('next' === target) {
      if (activeSlide === totalSlides) {
        newSlideIndex = 1;
      } else {
        newSlideIndex = activeSlide + 1;
      }
    } else if ('prev' === target) {
      if (1 === activeSlide) {
        newSlideIndex = totalSlides;
      } else {
        newSlideIndex = activeSlide - 1;
      }
    } else if (target <= totalSlides && 1 <= target && target !== activeSlide) {
      newSlideIndex = target;
    }

    if (false !== newSlideIndex) {
      self.goToSlide(newSlideIndex);
    }

    e.preventDefault();
  },

  /**
   * Toggle slide.
   *
   * @param {Number} slideIndex
   * @returns {void}
   */
  goToSlide: function goToSlide(slideIndex) {
    const self = this;
    const $el = self.$el;
    const $slides = self.$slides;
    const $triggers = self.$triggers;
    const previousSlideIndex = $el.data('activeSlide');
    const prevNextClass = (previousSlideIndex > slideIndex) ? 'is-prev' : 'is-next';

    $slides.filter('.is-active').removeClass('is-active');
    $triggers.filter('.is-active').removeClass('is-active is-next is-prev');

    $slides.filter(`[data-spotlight-slide="${slideIndex}"]`).addClass('is-active');
    $triggers.filter(`[data-spotlight-trigger="${slideIndex}"]`).addClass(`is-active ${prevNextClass}`);

    $el.data('activeSlide', slideIndex);
    self.setCopyHeight();
  },

  /**
   * Set height of the copy area based on the slide height.
   *
   * @returns {void}
   */
  setCopyHeight: function setCopyHeight() {
    const self = this;
    const $el = self.$el;
    const activeSlide = $el.data('activeSlide');
    const $slides = self.$slides;
    const $slide = $slides.filter(`[class*="copy-slide"][data-spotlight-slide="${activeSlide}"]`);
    const slideHeight = $slide.outerHeight();

    $slide.parent().css('min-height', `${slideHeight}px`);
  },

  /**
   * Hide slider.
   *
   * @returns {void}
   */
  hideSlider: function hideSlider() {
    const self = this;
    const $slides = self.$slides;
    const $triggers = self.$triggers;

    $slides.filter('.is-active').removeClass('is-active');
    $triggers.filter('.is-active').removeClass('is-active is-next is-prev');
  },

  /**
   * Show slider.
   *
   * @returns {void}
   */
  revealSlider: function revealSlider() {
    const self = this;
    const $el = self.$el;
    const activeSlide = $el.data('activeSlide');

    self.goToSlide(activeSlide);
  },

  /**
   * Handle (throttled) resize event.
   *
   * @returns {void}
   */
  onResize: function() {
    const self = this;
    const $el = self.$el;
    const screenHeight = $(window).height();
    const elTop = $el.offset().top;
    const revealPoint = elTop - screenHeight;

    self.revealPoint = revealPoint;
    self.setCopyHeight();
  },

  /**
   * Handle (throttled) scroll event.
   *
   * @returns {void}
   */
  onScroll: function onScroll() {
    const self = this;
    const $el = self.$el;
    const scrollTop = $(window).scrollTop();
    const revealPoint = self.revealPoint;
    const isRevealed = self.isRevealed;

    if (!isRevealed && scrollTop > revealPoint) {
      $el.removeClass('is-collapsed');
      self.revealSlider();
      self.isRevealed = true;
    } else if (isRevealed && scrollTop < revealPoint) {
      $el.addClass('is-collapsed');
      self.hideSlider();
      self.isRevealed = false;
    }
  },

};

export default spotlightManager;
