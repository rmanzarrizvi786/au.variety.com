// jshint es3: false
// jshint esversion: 6

'use strict';

/**
 * Manage hero video
 */
const heroVideoManager = {

  /**
   * Hero container element.
   *
   * @type {object}
   */
  $heroContainer: {},

  /**
   * Site header element.
   *
   * @type {object}
   */
  $siteHeader: {},

  /**
   * Body element.
   *
   * @type {object}
   */
  $bodyEl: {},

  /**
   * Hero video element.
   *
   * @type {object}
   */
  $heroVideo: {},

  /**
   * Initialize.
   *
   * @returns {void}
   */
  init: function init() {
    const self = this;
    const $heroContainer = $('.l-hero-video__container');
    const $heroVideo = $('.c-hero-video');

    // Continue if there is a hero container.
    if (0 === $heroContainer.length) {
      return;
    }

    // Load YouTube API.
    self.loadYTAPI();

    // Get the elements.
    self.$heroContainer = $heroContainer;
    self.$heroVideo = $heroVideo;
    self.$siteHeader = $('.c-site-header');
    self.$bodyEl = $('body');

    // Add event listeners.
    _.bindAll(self, 'onScroll', 'playVideo');
    $(window).on('scroll', _.throttle(self.onScroll, 8));
    self.onScroll();
    $heroContainer.addClass('has-loaded');

    // Add video playback related features only if there is a video.
    if (0 !== $heroContainer.find('.c-hero-video__video').length) {
      $heroContainer.on('click', '.c-button--hero-video', self.playVideo);
    }
    $heroContainer.on('click', '.c-scroll__cta-link', () => {
      $('html, body').animate({ scrollTop: 815 }, 'slow');
    });
  },

  /**
   * Handle (throttled) scroll event.
   *
   * @returns {void}
   */
  onScroll: function onScroll() {
    const self = this;
    const $heroContainer = self.$heroContainer;
    const scrollTop = $(window).scrollTop();
    const limit = scrollTop / 7;

    if (0 < scrollTop && 90 > limit) {
      let scale = (100 - limit) / 100;
      const opacity = scale;

      if (0.3 > scale) {
        scale = 0.3;
      }

      $heroContainer.css({
        transform: `scale(${scale})`,
        opacity: opacity,
      });
    } else if (4 >= scrollTop) {
      $heroContainer.css({
        transform: 'none',
        opacity: 1,
      });
    }
  },

  /**
   * Create video iFrame and start the playback.
   *
   * @returns {void}
   */
  playVideo: function playVideo() {
    const self = this;
    const $player = self.$heroVideo.find('.c-hero-video__video');
    const videoId = $player.data('videoId');
    const $iframe = $('<iframe>', {
      id: 'the-yt-video',
      height: '100%',
      width: '100%',
      src: `https://www.youtube.com/embed/${videoId}?autoplay=1&modestbranding=1&showinfo=0&width=1440&height=810&enablejsapi=1&rel=0`,
      frameborder: 0,
      allowfullscreen: '',
    });

    self.$heroVideo.addClass('c-hero-video--is-playing');
    $player.append($iframe);
    self.onAfterEnded();

  },

  /**
   * What to do when YouTube video ends.
   *
   * @returns {void}
   */
  onAfterEnded() {
    const self = this;
    // eslint-disable-next-line no-unused-vars, no-undef
    const player = new YT.Player('the-yt-video', {
      events: {
        onStateChange: self.onPlayerStateChange,
      },
    });
  },

  /**
   * Observes YouTube video states.
   *
   * @param event
   * @return {boolean}
   */
  onPlayerStateChange(event) {
    const $scrollCTA = $('.c-scroll__cta');
    const $heroVideo = $('.c-hero-video');
    const $player = $heroVideo.find('.c-hero-video__video');
    // If video has ended, scroll to the next section.
    switch (event.data) {
      case 0: // Video `ended`
        $('html, body').animate({ scrollTop: 815 }, 'slow');
        $scrollCTA.show();
        // Remove iframe from player and remove class is playing from heroVideo.
        $player.find('iframe').remove();
        $heroVideo.removeClass('c-hero-video--is-playing');
        break;
      case 1: // Video `playing`
      case 2: // Video `paused`
      case 3: // Video `buffering`
        $scrollCTA.hide();
        break;
      default:
        $scrollCTA.show();
    }
  },

  /**
   * If YouTube IFrame_API has not loaded add it.
   *
   */
  loadYTAPI() {
    // eslint-disable-next-line no-unused-vars, no-undef
    if ('undefined' === typeof (YT) || 'undefined' === typeof (YT.Player)) {
      const tag = document.createElement('script');
      tag.src = 'https://www.youtube.com/iframe_api';
      const firstScriptTag = document.getElementsByTagName('script')[0];
      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }
  },
};

export default heroVideoManager;
