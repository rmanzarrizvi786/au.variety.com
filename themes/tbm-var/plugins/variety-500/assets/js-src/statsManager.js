// jshint es3: false
// jshint esversion: 6

'use strict';

/**
 * Manage the Stats (By The Numbers) component.
 */
const statsManager = {

  /**
   * Container element.
   *
   * @type {object}
   */
  $el: {},

  /**
   * Slider header element.
   *
   * @type {object}
   */
  $header: {},

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
   * Collection of excising targets.
   *
   * @type {array}
   */
  targets: [],

  /**
   * Initialize.
   *
   * @returns {void}
   */
  init: function init() {
    const self = this;
    const $el = $('[data-trigger="stats-manager"]');

    // Continue only if there is a nav bar.
    if (!$el.length) {
      return;
    }

    // Get the container.
    self.$el = $el;
    self.$header = $el.find('.l-stats__header');

    _.bindAll(self, 'onNavLink', 'setContentHeight');

    // Setup the slider.
    self.setupSlider();

    // Manage navigation links.
    self.goToSlide($el.data('activeSlide'));
    $el.on('click', '[data-stats-trigger]', self.onNavLink);
    $(window).on('resize', _.throttle(self.setContentHeight, 10));

    // Update the layout with delay so that everything loads properly.
    _.delay(self.setContentHeight, 100);
  },

  /**
   * Setup the slider.
   *
   * @returns {void}
   */
  setupSlider: function setupSlider() {
    const self = this;
    const $el = self.$el;
    const $slides = $el.find('[data-stats-slide]');
    const $activeSlide = $slides.filter('.is-active:first');
    let activeSlide;

    if (0 !== $activeSlide.length) {
      activeSlide = $activeSlide.data('statsSlide');
    } else {
      activeSlide = $slides.first().data('statsSlide');
    }

    self.$slides = $slides;
    self.$triggers = $el.find('[data-stats-trigger]');
    self.targets = _.map($slides, (item) => item.dataset.statsSlide);

    $el.data({
      activeSlide,
    });

    $el.addClass('has-loaded');
    $slides.addClass('has-loaded');
  },

  /**
   * Handle navigation link click events.
   *
   * @param {Event} e
   * @returns {void}
   */
  onNavLink: function onNavLink(e) {
    const self = this;
    const $link = $(e.currentTarget);
    const activeSlide = self.$el.data('activeSlide');
    const target = $link.data('statsTrigger');

    e.preventDefault();

    if (target === activeSlide) {
      return;
    }

    if (_.contains(self.targets, target)) {
      self.goToSlide(target);
    }
  },

  /**
   * Toggle slide.
   *
   * @param {String} slideName
   * @returns {void}
   */
  goToSlide: function goToSlide(slideName) {
    const self = this;
    const $el = self.$el;
    const $slides = self.$slides;
    const $triggers = self.$triggers;
    const previousSlide = $el.data('activeSlide');
    const targets = self.targets;
    let prevNextClass;

    if (_.indexOf(targets, previousSlide) > _.indexOf(targets, slideName)) {
      prevNextClass = 'is-prev';
    } else {
      prevNextClass = 'is-next';
    }

    $slides.filter('.is-active').removeClass('is-active');
    $triggers.filter('.is-active').removeClass('is-active is-next is-prev');

    $slides.filter(`[data-stats-slide="${slideName}"]`).addClass('is-active');
    $triggers.filter(`[data-stats-trigger="${slideName}"]`).addClass(`is-active ${prevNextClass}`);

    $el.data('activeSlide', slideName);
    self.setContentHeight();
  },

  /**
   * Set height of the content area based on the slide height.
   *
   * @returns {void}
   */
  setContentHeight: function setCopyHeight() {
    const self = this;
    const $el = self.$el;
    const activeSlide = $el.data('activeSlide');
    const $slide = self.$slides.filter(`[data-stats-slide="${activeSlide}"]`);
    const slideHeight = $slide.outerHeight();
    const headerHeight = self.$header.outerHeight();
    const $caption = $slide.find('.l-stats__caption');

    $slide.parent().height(slideHeight);
    $caption.css('top', `-${headerHeight}px`);
  },
};

export default statsManager;
