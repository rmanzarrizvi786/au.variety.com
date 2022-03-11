import throttle from './throttle';

/**
 * Manage site header component.
 */
const SiteHeaderManager = {

	/**
	 * Window object.
	 *
	 * @type {object}
	 */
	$window: {},

	/**
	 * Body element.
	 *
	 * @type {object}
	 */
	$bodyEl: {},

	/**
	 * HTML element.
	 *
	 * @type {object}
	 */
	$htmlEl: {},

	/**
	 * Header element.
	 *
	 * @type {object}
	 */
	$header: {},

	/**
	 * Header height.
	 *
	 * @type {number}
	 */
	headerHeight: 0,

	/**
	 * Resize timer (debouncer).
	 *
	 * @type {number}
	 */
	resizeTimer: 0,

	/**
	 * Last `scrollTop` value.
	 *
	 * @type {number}
	 */
	lastScrollTopValue: 0,

	/**
	 * Initialize.
	 *
	 * @returns {void}
	 */
	init: function init() {
		const $header = $('.l-header');

		if (0 === $header.length) {
			return;
		}

		this.$header = $header;
		this.$window = $(window);
		this.$htmlEl = $('html');
		this.$bodyEl = $('body');

		this.initExpandableSearchForm();
		this.initEditionMenu();
		this.initPersistentNavigation();
		this.initMegaMenuToggles();
		this.initNewsletter();
	},

	/**
	 * Search form toggle in the top bar.
	 */
	initExpandableSearchForm: function initExpandableSearchForm() {
		const $search = this.$header.find('.c-search--expandable').parent();

		if (0 === $search.length) {
			return;
		}

		$search.on('click', '.search-form', (e) => {
			if ($search.hasClass('is-expanded')) {
			return;
		}

		e.preventDefault();
		e.stopPropagation();
		$search.addClass('is-expanded');
		$search.find('input[type="text"]').focus();
		this.$bodyEl.on('click', { self: this, $search }, this.collapseSearchForm);
	});
	},

	/**
	 * Collapse the search form if clicked outside of the form.
	 *se
	 * @param {Event} e
	 */
	collapseSearchForm: function toggleSearchForm(e) {
		const self = e.data.self;
		const $search = e.data.$search;
		const $target = $(e.target);

		if (!$target.closest('.c-search--expandable').length) {
			$search.removeClass('is-expanded');
			$search.find('[type="text"]').val('');
			self.$bodyEl.off('click', self.collapseSearchForm);
		}
	},

	/**
	 * Initialize the 'Edition' menu.
	 */
	initEditionMenu: function initEditionMenu() {
		const $menu = this.$bodyEl.find('[data-toggle="header-edition"]');

		if (0 === $menu.length) {
			return;
		}

		$menu.on('click', 'a', (e) => {
			const $targetParent = $(e.currentTarget).parent();

		if ($targetParent.hasClass('is-current')) {
			e.preventDefault();
		} else {
			$menu.find('.is-current').removeClass('is-current');
			$targetParent.addClass('is-current');
		}

		$menu.toggleClass('is-expanded');
	});
	},

	/**
	 * Initialize persistent navigation functionality.
	 */
	initPersistentNavigation: function initPersistentNavigation() {
		this.headerHeight = this.$header.height();
		this.toggleFixedHeader();

		this.$header.attr('data-height', this.headerHeight);

		this.$window.on('resize', () => this.determineHeaderHeight());
		this.$window.on('scroll', () => this.toggleFixedHeader());
	},

	/**
	 * Determine header height.
	 */
	determineHeaderHeight: throttle(function determineHeaderHeight() {
		clearTimeout(this.resizeTimer);
		this.resizeTimer = setTimeout(() => {
				const isFixed = this.$header.hasClass('is-fixed');

		if (isFixed) {
			this.$header.attr('data-fixed-height', this.$header.height());
			this.$header.removeClass('is-fixed');
			this.headerHeight = this.$header.height() + $('#leaderboard-no-padding').height();
			this.$header.addClass('is-fixed');
		} else {
			this.headerHeight = this.$header.height() + $('#leaderboard-no-padding').height();
		}

		this.$header.attr('data-height', this.headerHeight);
	}, 240);
	}, 120),

	/**
	 * Toggle fixed header.
	 */
	toggleFixedHeader: throttle(function toggleFixedHeader() {
		const scrollTop = this.$window.scrollTop();
		const scrollDirection = (0 > (this.lastScrollTopValue - scrollTop)) ? 'down' : 'up';
		const isFixed = this.$header.hasClass('is-fixed');
		const isVisible = this.$header.hasClass('is-visible');
		let fixAt = this.headerHeight;

		this.lastScrollTopValue = scrollTop;

		if ('down' === scrollDirection && !isFixed && scrollTop > fixAt) {
			this.determineHeaderHeight();
			this.headerHeight = this.$header.height();
			fixAt = this.headerHeight;

			if (scrollTop > fixAt) {
				this.$header.addClass('is-fixed');
				this.$bodyEl.css('padding-top', `${fixAt}px`);
			}
		}

		if ('down' === scrollDirection && !isVisible && scrollTop > fixAt + 20) {
			this.$header.addClass('has-transitions is-visible');
		}

		if ('up' === scrollDirection && isVisible && scrollTop < fixAt + 50) {
			this.$header.removeClass('is-visible');
		}

		if ('up' === scrollDirection && isFixed && scrollTop < fixAt) {
			this.$bodyEl.css('padding-top', '');
			this.$header.removeClass('has-transitions is-fixed');
		}
	}, 16),

	/**
	 * Initialize mega menu toggles.
	 */
	initMegaMenuToggles: function initMegaMenuToggles() {
		const $mega = this.$bodyEl.find('#mega-menu');

		if (0 === $mega.length) {
			return;
		}

		this.$bodyEl.on('click', '.l-page__content', (e) => {
			if (this.$htmlEl.hasClass('is-mega-expanded') && 'mega-menu' !== $(e.target).data('toggle')) {
			e.preventDefault();
			this.$htmlEl.removeClass('is-mega-expanded');
		}
	});

		this.$bodyEl.on('click', '[data-toggle="mega-menu"]', (e) => {
			e.preventDefault();

		if (this.$htmlEl.hasClass('is-mega-expanded')) {
			this.$htmlEl.removeClass('is-mega-expanded');

			const previousScrollTop = this.$htmlEl.data('previousScrollTop');
			if (previousScrollTop) {
				this.$htmlEl.data('previousScrollTop', '');
				window.scrollTo(0, previousScrollTop);
			}
		} else {
			const scrollTop = this.$window.scrollTop();
			this.$htmlEl.data('previousScrollTop', scrollTop);
			this.$htmlEl.addClass('is-mega-expanded');
		}
	});

		this.$bodyEl.on('click', '.js-expander', (e) => {
			e.preventDefault();
		$(e.target).parent()
			.toggleClass('is-expanded')
			.siblings('.is-expanded')
			.removeClass('is-expanded');
	});
	},

	/**
	 * Initialize newsletter sign up form.
	 */
	initNewsletter: function initNewsletter() {

		const $FormButton = this.$bodyEl.find('.c-newsletter__button');
		const $emailField = this.$bodyEl.find('.js-newsletter-email');
		const $successPageField = this.$bodyEl.find('.js-newsletter-successpage');
		const $ToolTiptext = this.$bodyEl.find('.c-newsletter__tooltiptext');

		// Check for email validation on click of submit.
		$FormButton.on('click', (e) => {

			if (0 !== $emailField.length && !this.validateEmail($emailField.val())) {

			$emailField.addClass('invalid');
			$ToolTiptext.addClass('active');

			setTimeout(() => {
				$ToolTiptext.removeClass('active');
		}, 4000);

			e.preventDefault();
		}
	});

		if (0 === $emailField.length || 0 === $successPageField.length) {
			return;
		}

		const successPageBaseURL = $successPageField.data('base-url');

		$emailField.on('keyup blur', (e) => {

			// validate email and check for empty value.
			if ('' === $(e.target).val() || this.validateEmail($(e.target).val())) {
			$(e.target).removeClass('invalid');
			$ToolTiptext.removeClass('active');
		}

		const emailAddress = encodeURIComponent($(e.target).val());
		$successPageField.val(`${successPageBaseURL}&email=${emailAddress}`);

	});

	},

	// Validate email will return true if valid and false if invalid.
	validateEmail: function validateEmail(email) {
		const expr = /^([\w-]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
		return expr.test(email);
	},
};

export default SiteHeaderManager;