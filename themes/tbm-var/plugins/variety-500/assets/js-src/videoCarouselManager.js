/**
 * Provide video carousel functionality.
 */
const VideoCarouselManager = {
	/**
	 * Active slide index.
	 * @type {Number}
	 */
	activeSlideIndex: 3,

	/**
	 * Total number of slides.
	 * @type {Number}
	 */
	slidesCount: 0,

	/**
	 * Slider object.
	 * @type {jQuery}
	 */
	$slider: {},

	/**
	 * Collection of slides (jQuery)
	 * @type {Array}
	 */
	$slides: [],

	/**
	 * Collection of slide triggers (jQuery)
	 * @type {Array}
	 */
	$triggers: [],

	/**
	 * Next slide trigger.
	 * @type {jQuery}
	 */
	$nextTrigger: {},

	/**
	 * Previous slide trigger.
	 * @type {jQuery}
	 */
	$prevTrigger: {},

	/**
	 * Initialize.
	 *
	 * @returns {void}
	 */
	init: function init() {
		const $el = jQuery('[data-video-carousel]');

		this.$slider = $el.find('[data-video-slider]');
		this.$slides = $el.find('[data-video]');
		this.$triggers = $el.find('[data-video-trigger]');
		this.$nextTrigger = this.$triggers.filter('[data-video-trigger="next"]');
		this.$prevTrigger = this.$triggers.filter('[data-video-trigger="prev"]');
		this.slidesCount = this.$slides.length;

		const $activeSlide = this.$slides.filter('.is-active').first();
		if ($activeSlide.length === 1) {
			this.setActiveSlide($activeSlide);
		} else {
			this.setActiveSlide(this.$slides.first());
		}

		this.$triggers.on('click', e => {
			e.preventDefault();
			this.changeSlide(e.currentTarget.dataset.videoTrigger);
		});
	},

	/**
	 * Determine the active slide.
	 *
	 * @param {jQuery} $video New active slide
	 * @returns {void}
	 */
	setActiveSlide: function setActiveSlide($video) {
		const videoSlug = $video.data('video');
		const index = this.$slides.index($video);

		if (index === this.activeSlideIndex) {
			return;
		}

		// Shift the carousel and set active item.
		this.$slides.eq(0).css('margin-left', `${-index * 100}%`);
		this.$slides.filter('.is-active')
			.removeClass('is-active')
			.find('.c-player')
			.trigger('player:reset');
		this.$slides.filter(`[data-video='${videoSlug}']`)
			.addClass('is-active');

		// Set active trigger (thumb).
		this.$triggers.removeClass('is-active')
			.filter(`[data-video-trigger='${videoSlug}']`).addClass('is-active');

		// Toggle prev/next triggers.
		this.$nextTrigger.toggleClass('is-hidden', index === this.slidesCount - 1);
		this.$prevTrigger.toggleClass('is-hidden', index === 0);

		this.activeSlideIndex = index;
	},

	/**
	 * Change the active slide.
	 *
	 * @param {String} videoSlug Video slug
	 * @returns {void}
	 */
	changeSlide: function changeSlide(videoSlug) {
		let targetIndex;

		if (videoSlug === 'next') {
			targetIndex = this.activeSlideIndex + 1;
		} else if (videoSlug === 'prev') {
			targetIndex = this.activeSlideIndex - 1;
		} else {
			const $video = this.$slides.filter(`[data-video='${videoSlug}']`);
			targetIndex = this.$slides.index($video);
		}

		if (targetIndex >= 0 && targetIndex < this.slidesCount) {
			this.setActiveSlide(this.$slides.eq(targetIndex));
		}
	},
};

export default VideoCarouselManager;
