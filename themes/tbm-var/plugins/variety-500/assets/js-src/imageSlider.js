// jshint es3: false
// jshint esversion: 6

'use strict';

/**
 * Animates glimpse image slider
 */
const imageSlider = {

  /**
   * Slides container element.
   *
   * @type {object}
   */
  $slidesContainer: {},

  /**
   * Initialize.
   *
   * @returns {void}
   */
  init: function init() {
    const self = this;
    const $slidesContainer = $('[data-trigger="glimpse-slick-slider"]');

    // Continue if there is a slides container and the Slick library is loaded.
    if (0 === $slidesContainer.length || _.isUndefined($.fn.slick)) {
      return;
    }

    // Get the elements.
    self.$slidesContainer = $slidesContainer;

    // Initialise Slick plug-in
    this.$slidesContainer.not('.slick-initialized').slick({
      centerMode: true,
      centerPadding: '25px',
      slidesToShow: 5,
      lazyLoad: 'progressive',
      mobileFirst: true,
      arrows: false,
      prevArrow: '<div class="c-slider__nav c-slider__nav--prev"><div class="c-slider__nav-arrow"><button class="c-nav-arrow c-nav-arrow--prev"></button></div></div>',
      nextArrow: '<div class="c-slider__nav c-slider__nav--next"><div class="c-slider__nav-arrow"><button class="c-nav-arrow c-nav-arrow--next"></button></div></div>',
      respondTo: 'min',
      responsive: [
        {
          breakpoint: 319,
          settings: {
          arrows: true,
          centerMode: true,
          centerPadding: '50px',
          slidesToShow: 1,
        },
        },
        {
          breakpoint: 480,
          settings: {
            arrows: true,
            centerMode: true,
            centerPadding: '50px',
            slidesToShow: 1,
          },
        },
        {
          breakpoint: 769,
          settings: {
            centerMode: true,
            centerPadding: '60px',
            slidesToShow: 3,
          },
        },
        {
          breakpoint: 980,
          settings: {
            slidesToShow: 5,
            centerMode: true,
            arrows: true,
            centerPadding: '70px',
          },
        },
      ],
    });
  },

};

export default imageSlider;
