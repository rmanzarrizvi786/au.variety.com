import fastdom from 'fastdom';
/**
 * Component for instantiating Headroom for sticky header
 */
const UberMenu = {

  element: 'header',
  elements: {
    navMenu: '.l-header__nav',
    navArea: '.l-header__nav-area',
    uberNav: '.uber-nav',
    uberNavPanel: '.uber-nav__panel',
  },
  options: {
    uberNavTrigger: 'site-header__link',
    uberOpenClass: 'uber-nav--open',
    uberActiveClass: 'uber-nav__panel--active',
    uberNavPanel: 'uber-nav__panel',
  },
  /**
   * Start the component
   */
  /* eslint-disable react/sort-comp */
  start: function start() {
    this.uberNav = $(this.elements.uberNav);
    this.uberNavPanels = $(this.elements.uberNavPanel);
    this.uberNavOpen = false;

    var isMobile = $('body').hasClass('pmc-mobile');

    if (!isMobile && null !== $(this.elements.navArea)) {
      this.initUberNav();
    }
  },

  initUber: function initUber(){
    $(this.elements.navMenu).on('mouseover',function(){
      alert('x');
    });
  },


  /**
   * Initialize uber nav
   */
  initUberNav: function initUberNav() {

    $(this.elements.navMenu).on('mouseover', (e) =>  {
      let currentTrigger = false;

      // Check for current target
      if (e.target.classList.contains(this.options.uberNavTrigger)) {
        currentTrigger = e.target;
      } else if (
        e.target.parentElement.classList
          .contains(this.options.uberNavTrigger)
      ) {
        currentTrigger = e.target.parentElement;
      }

      // If current target is an <li> or <a> element in the primary nav, check for an uber nav target
      if (currentTrigger && currentTrigger.dataset.uberNavTarget) {
        const uberNavTarget = $(`#uber-nav-${currentTrigger.dataset.uberNavTarget}`);

        // If an uber nav target exists, open it. Else close the uber nav.
        if (uberNavTarget) {
          this.openUberNav();
          this.openNavPanel(uberNavTarget);
        } else {
          this.closeUberNav();
        }
      }
    });

    // Logic for closing the uber nav when mousing out of either the nav area or the uber nav
    $(this.elements.navArea).on('mouseout', (e) => {

      if (
        // The element user mouses OUT of must be or be contained within nav area
        UberMenu.matchesWithChildren( $(e.target), $(this.elements.navArea)) &&
        // element user mouses INTO must _not_ be or be contained within nav area
        ! UberMenu.matchesWithChildren($(e.relatedTarget), $(this.elements.navArea))
      ) {

      UberMenu.closeUberNav();
      }
    });
  },

  /**
   * Open uber nav
   */
  openUberNav: function openUberNav() {
    var current_element = this;
    if (
      ! $(this.element).hasClass(this.options.uberOpenClass) &&
      ! this.hoverIntentTimeout
    ) {
      this.hoverIntentTimeout = window.setTimeout(() => {
          $(current_element.element).addClass(current_element.options.uberOpenClass);
      }, 500);
    }
  },

  /**
   * Close uber nav
   */
  closeUberNav: function closeUberNav() {
    window.clearTimeout(this.hoverIntentTimeout);
    this.hoverIntentTimeout = null;
    $(this.element).removeClass(this.options.uberOpenClass);
  },

  /**
   * Open a specific uber nav panel
   *
   * @param {HTMLElement} uberNavTarget - Element corresponding to single uber nav panel (.uber-nav__panel)
   */
  openNavPanel: function openNavPanel(uberNavTarget) {
    const target = uberNavTarget;

    fastdom.mutate(() => {
      this.uberNavPanels = Array.prototype.map.call(this.uberNavPanels, (panel) => {
          panel.classList.remove(this.options.uberActiveClass);
          return panel;
        });
      target.addClass(this.options.uberActiveClass);
    });
  },

  /**
   * Check if a node matches a reference node or any of its children
   *
   * @param {HTMLElement} node - node to check
   * @param {HTMLElement} reference - node to compare against
   */
  matchesWithChildren: function matchesWithChildren(node, reference) {
    node = $(node)[0];
    reference= $(reference)[0];

    return (node === reference || reference.contains(node));

  }
}

export default UberMenu;