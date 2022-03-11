// jshint es3: false
// jshint esversion: 6

'use strict';

/**
 * Manage Social Glimpse component (Profile page).
 */
const socialGlimpseManager = {

  /**
   * Container element.
   *
   * @type {object}
   */
  $el: {},

  /**
   * Header element.
   *
   * @type {object}
   */
  $header: {},

  /**
   * Slides container.
   *
   * @type {Object}
   */
  $slider: {},

  /**
   * Slides collection.
   *
   * @type {Array}
   */
  $slides: [],

  /**
   * Current X position of the slider.
   *
   * @type {Number}
   */
  currentPositionX: 0,

  /**
   * The slider is in the end position.
   *
   * @type {Boolean}
   */
  canSlideNext: false,

  /**
   * The slider is in the initial position.
   *
   * @type {Boolean}
   */
  canSlidePrev: false,

  /**
   * Fade slides that were already viewed.
   *
   * @type {Boolean}
   */
  fadeSlides: false,

  /**
   * Container width.
   *
   * @type {Number}
   */
  containerWidth: 0,

  /**
   * Total slots count.
   *
   * @type {Number}
   */
  totalSlots: 0,

  /**
   * Single item width.
   *
   * @type {Number}
   */
  itemWidth: 0,

  /**
   * Step value.
   *
   * @type {Number}
   */
  stepValue: 0,

  /**
   * Header left position.
   *
   * @type {Number}
   */
  headerLeft: 0,

  /**
   * Initialize.
   *
   * @returns {void}
   */
  init: function init() {
    const $el = $('[data-trigger="social-glimpse-manager"]');

    // Continue only if there is a slider and we're not dealing with touch device.
    if (!$el.length || window.Modernizr.touchevents) {
      return;
    }

    // Get the elements.
    this.$el = $el;
    this.$header = $el.find('[class*="header"]');
    this.$slider = $el.find('[data-slides]');
    this.$slides = $el.find('li');

    _.bindAll(this, 'calculateLayout', 'resetLayout', 'onNavLink');

    // Initialize layout.
    this.resetLayout();

    // Add event listeners.
    $el.on('click', '[data-slider-trigger]', this.onNavLink);
    $(window).on('resize', _.throttle(this.resetLayout, 10));
  },

  /**
   * Reset the slider layout.
   *
   * @returns {void}
   */
  resetLayout: function resetLayout() {
    this.currentPositionX = 0;
    this.calculateLayout(true);
  },

  /**
   * Calculate slider layout.
   *
   * @param {Boolean} isInit Whether is it an initial set up of the slider.
   * @returns {void}
   */
  calculateLayout: function calculateLayout(isInit) {
    this.containerWidth = this.$el.outerWidth();
    this.headerLeft = parseInt(this.$header.css('margin-left'), 0);
    this.itemWidth = this.$slider.find('li:last').outerWidth();
    this.totalSlots = Math.floor(this.containerWidth / this.itemWidth);
    this.stepValue = this.itemWidth * (this.totalSlots - 1);
    this.fadeSlides = (0 > parseInt(this.$slider.parent().css('margin-top'), 0));

    if (isInit) {
      const initialPosition = this.getInitialPosition();
      this.setPosition(initialPosition);
    }
  },

  /**
   * Calculate initial position.
   *
   * @returns {number}
   */
  getInitialPosition: function getInitialPosition() {
    let initialPosition = this.headerLeft;

    if (this.fadeSlides) {
      initialPosition += this.itemWidth * 0.9;
    }

    return initialPosition;
  },

  /**
   * Handle navigation link click events.
   *
   * @param {Event} e
   * @returns {void}
   */
  onNavLink: function onNavLink(e) {
    e.preventDefault();

    const direction = $(e.currentTarget).data('sliderTrigger');
    this.slideTo(direction);
  },

  /**
   * Slide to next/previous slides.
   *
   * @param {String} direction Slide to `next` or `prev` slides.
   * @returns {void}
   */
  slideTo: function goToNext(direction) {
    if ('next' === direction && this.canSlideNext) {
      this.setPosition(-this.stepValue);
    }
    if ('prev' === direction && this.canSlidePrev) {
      this.setPosition(this.stepValue);
    }
  },

  /**
   * Set slider position based on the shift value.
   *
   * @param {Number} distance Horizontal shift value.
   * @returns {void}
   */
  setPosition: function setPosition(distance) {
    const slidesTotalLength = this.$slides.length * this.itemWidth;
    let newPosition = this.currentPositionX + distance;

    while (-newPosition + this.headerLeft + this.itemWidth > slidesTotalLength) {
      newPosition += this.itemWidth;
    }

    const initialPosition = this.getInitialPosition();
    if (newPosition > initialPosition) {
      newPosition = initialPosition;
    }

    this.currentPositionX = newPosition;
    this.$slider.css('transform', `translateX(${this.currentPositionX}px)`);

    this.canSlideNext = this.containerWidth < slidesTotalLength + this.currentPositionX;
    this.canSlidePrev = (0 > this.currentPositionX);

    this.$el.toggleClass('has-next-nav', this.canSlideNext);
    this.$el.toggleClass('has-prev-nav', this.canSlidePrev);

    this.toggleActiveSlides();
  },

  /**
   * Toggle an active state on visible slides.
   *
   * @returns {void}
   */
  toggleActiveSlides: function toggleActiveSlides() {
    if (!this.fadeSlides) {
      this.$slides.addClass('is-active');
      return;
    }

    let sliceStart = 0;
    if (0 > this.currentPositionX) {
      const totalLeft = this.headerLeft - this.currentPositionX;
      sliceStart = Math.round(totalLeft / this.itemWidth) + 1;
    }

    const $activeSlides = this.$slides.slice(sliceStart);
    this.$slides.removeClass('is-active');
    $activeSlides.addClass('is-active');
  },
};

export default socialGlimpseManager;
