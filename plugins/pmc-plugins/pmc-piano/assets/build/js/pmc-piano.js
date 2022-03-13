var pmcPiano;
/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/pmc-piano.js":
/*!*****************************!*\
  !*** ./src/js/pmc-piano.js ***!
  \*****************************/
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

/* eslint-disable no-console */

/* global tp, pmc */

/**
 * Piano scripts to render on page.
 *
 * 1) Initializes Piano for use on site
 * 2) Facilitates logging-in/out
 * 3) Provides analytics reporting
 *
 * @package pmc-piano
 *
 */
const pmcPiano = {
  initialized: false,
  scriptUrl: window.pmcPianoData.scriptUrl,
  isProd: window.pmcPianoData.isProd,
  canDebug: window.pmcPianoData.canDebug,

  /**
   * WordPress route for licensee details.
   *
   * @type string
   */
  licenseeEndpoint: '/wp-json/pmc/piano/v1/licensee',

  /**
   * Label of the reporting cookie.
   *
   * @type string
   */
  cookieLabel: 'pmc_piano_reporting',
  user: null,

  /**
   * Provides data for Google Analytics reporting.
   *
   * @typedef {Object} Reporting
   * @property {string} entitlements Resource name if granted, or blank
   * @property {'STAFF'|'ANONYMOUS'|'REGISTERED'|'SUBSCRIBER'|'KNOWN'} user_type User's access level
   * @property {string|null} acct_id The user's UID if logged in
   * @property {'individual'|'site_license'|null} acct_type The conversion term type
   * @property {string|null} org_id The conversion term ID if corporate or the contract id
   * @property {string|null} org_name The name of the corporate org
   * @property {boolean} paywall_logged_in If the user is logged into the paywall
   */
  reporting: {
    entitlements: '',
    user_type: 'ANONYMOUS',
    acct_id: null,
    acct_type: null,
    org_id: null,
    org_name: null,
    paywall_logged_in: false
  },

  /**
   * @typedef {Object} LicenseeData
   * @property {string} [name] - The name or the organization
   * @property {string} [id] - The License ID of the organiation
   */
  onLoggedInUserCallbacks: [],
  onLoggedOutUserCallbacks: [],
  onPageLoadNotHasUserCallbacks: [],
  // My Account page
  myAccountPageSelector: '.js-subscription-my-account-component',
  // Password Reset page
  passwordResetPageSelector: '.js-subscription-password-reset-component',
  // Piano Paywall selector (this is an HTML element ID)
  // The container which piano inserts a paywall experience within
  pianoPaywallSelector: '#piano-paywall',
  // PMC Paywall selector (this is a CSS class)
  // The container wrapping our content which hides content when a paywall is shown
  pmcPaywallSelector: '.pmc-paywall',
  // Truncated content selector (this is a CSS class)
  truncatedContentSelector: 'a-article-cropped',

  /**
   * Check to see if tp is loaded
   */
  checkTp: function () {
    // Note that pmcPiano calls piano APIs and all other methods in tp object during its initialization
    // Make sure tp is fully loaded. (i.e. it's not an array)
    if (tp && tp.pianoId) {
      this.tpLoaded();
      return;
    } // tp is not loaded yet. Wait and check again


    setTimeout(this.checkTp.bind(this), 500);
  },

  /**
   * Callback fired once tp has loaded.
   */
  tpLoaded: function () {
    const self = pmcPiano;

    if (tp) {
      tp.push(['init', self.initialize.bind(self)]);
    }
  },

  /**
   * Setup Piano environment.
   *
   * Called via a callback after piano (tp) is initialized.
   */
  initialize: function () {
    const self = this;

    if (!pmcPiano.isProd) {
      tp.push(['setSandbox', true]);
    }

    if (pmcPiano.canDebug) {
      tp.push(['setDebug', true]);
    }

    tp.push(['setUsePianoIdUserProvider', true]); // Listen for user login events

    tp.push(['addHandler', 'loginSuccess', function (data) {
      if (self.canDebug) {
        console.log('PMC: Piano: loginSuccess', data);
      }

      self.setUserData.apply(self);
    }]); // Automatically close the subscribe window for amp customers after
    // checkout is complete

    tp.push(['addHandler', 'checkoutComplete', function () {
      const isAmp = !!/reader_id=amp/gi.exec(window.location.search);

      if (isAmp) {
        window.close();
      }
    }]); // Listen for user registration postMessage events

    window.onmessage = self.registrationSuccess; // Do work if we already have a user

    self.setUserData.apply(self); // Maybe render the My Account page

    self.registerOnPageLoadNotHasUserCallback(self.maybeRenderMyAccountPage.bind(this));
    self.registerOnLoggedInUserCallback(self.maybeRenderMyAccountPage.bind(this));
    self.registerOnLoggedOutUserCallback(self.maybeRenderMyAccountPage.bind(this)); // Re-Render Experiences on user login
    // e.g. remove the paywall

    self.registerOnLoggedInUserCallback(self.reRenderExperiences.bind(this)); // Re-Render Experiences on user logout
    // e.g. display the paywall

    self.registerOnLoggedOutUserCallback(self.reRenderExperiences.bind(this)); // Maybe render the My Account page

    self.maybeRenderPasswordResetPage(); // Update reporting cookie

    self.registerOnLoggedInUserCallback(self.setReportingForAuthUser.bind(this));
    self.registerOnLoggedInUserCallback(self.setAuthenticatedUserId.bind(this));
    self.registerOnLoggedOutUserCallback(self.deleteReportingCookie.bind(this));
  },

  /**
   * PMCS-4616 - Subscriptions Team Passing User ID to Atlas
   */
  setAuthenticatedUserId: function () {
    const self = pmcPiano;
    const blogherads = window.blogherads || {};
    const userid = self.getUser().uid;

    if (self.canDebug) {
      console.log('PMC: Piano: setAuthenticatedUserId', userid);
    }

    blogherads.adq = blogherads.adq || [];
    blogherads.adq.push(function () {
      try {
        blogherads.setAuthenticatedUserId(userid, 'piano');
      } catch (e) {// Do nothing
      }
    });
  },

  /**
   * Perform actions after a user successfully registers.
   *
   * @param {MessageEvent} event
   */
  registrationSuccess: function (event) {
    const self = pmcPiano;

    if (typeof event.data === 'string') {
      let eventData;

      try {
        eventData = JSON.parse(event.data);
      } catch (err) {
        if (self.canDebug) {
          console.warn('PMC: Piano: registration', err);
        }

        return;
      }

      if (eventData.sender && eventData.sender.startsWith('piano-id')) {
        if (eventData.event === 'registrationSuccess') {
          // User just registered
          if (self.canDebug) {
            console.log('PMC: Piano: registrationSuccess', event);
          } // Don't re-render experiences on loginSuccess
          // This is a bit of a workaround; See PMCS-4460
          //
          // Piano's loginSuccess event is fired when a user logs in
          // AND after the user completes a registration form.
          // In the later scenario, loginSuccess is fired
          // BEFORE startCheckout and submitPayment.
          // This means the user is logged in before they are
          // assigned a resource in piano.
          //
          // reRenderExperiences purposefully removes offers on the page
          // and maybe adds them back, however, removing the offer
          // during registration prevents startCheckout from running
          // and in-turn prevents the user from receiving a resource
          // (they have no access)
          //
          // to prevent this, on registrationSuccess we remove
          // reRenderExperiences from the list of logged-in callbacks,
          // preventing it from running in that scenario.
          //
          // after registration is complete, and the user clicks
          // "No thanks, please take me to my article.", the
          // full page is refreshed, getting us back into a
          // typical page load scenario.


          self.onLoggedInUserCallbacks = self.onLoggedInUserCallbacks.filter(callback => // Because we assign reRenderExperiences like so:
          // self.reRenderExperiences.bind( this )
          // the function.name value will contain 'bound' as below.
          // Also, because we use .bind() on all functions assigned to this
          // array, we're unable to compare the functions using .toString()
          // they will always return "function () { [native code] }"
          // For now, below is the best working method I was able to locate
          callback.name !== 'bound reRenderExperiences');
        }
      }
    }
  },

  /**
   * Display the login modal.
   *
   * This function is generally attached to a "Log In" element's click listener.
   *
   * @param e
   */
  login: e => {
    e.preventDefault();
    tp.pianoId.show({
      screen: 'login'
    });
  },

  /**
   * Log a user out.
   *
   * This function is generally attached to a "Log Out" element's click listener.
   *
   * 'this' will refer to a clicked element, we must
   * us window.pmcPiano.piano in order to call onLogout.
   *
   * @param e
   */
  logout: function (e) {
    e.preventDefault();
    tp.pianoId.logout(pmcPiano.onLogout);
  },

  /**
   * Piano callback fired after a user logs out.
   *
   * Removes internal user store and triggers any register callbacks.
   *
   * 'this' will refer to the Window object, we must
   * use pmcPiano in order to call ourself.
   */
  onLogout: function () {
    if (pmcPiano.canDebug) {
      console.log('PMC: Piano: User logged-out', pmcPiano.user);
    }

    pmcPiano.user = null;
    pmcPiano.triggerOnLoggedOutUserCallbacks();
  },

  /**
   * Do we have a user?
   *
   * @return {boolean} if we have a user
   */
  hasUser: function () {
    return !!this.user;
  },

  /**
   * Get the current user's data.
   *
   * @return {*} a user's data
   */
  getUser: function () {
    return this.user;
  },

  /**
   * Get all of the users data from Piano and set to this.user
   * Includes account information, access, and terms.
   *
   * This method is called once on page load, and
   * any time a user logs in.
   */
  setUserData: function () {
    const self = this;

    if (tp.user.isUserValid()) {
      // There is also the another method to get email and name : tp.api.callApi('/access/list', {}, (re) => {})
      // This method provides user entitlements/access/terms
      // getUser just provides email and name. It does not provide user entitlements/access/terms
      // tp.pianoId.getUser() method will only return a value when a user is authenticated, otherwise it will be null.
      if (tp.pianoId.getUser()) {
        self.initialized = true;
        self.user = tp.pianoId.getUser();

        if (self.canDebug) {
          console.log('PMC: Piano: User logged-in', self.user);
        }

        self.triggerOnLoggedInUserCallbacks();
      }

      return;
    }

    if (self.canDebug) {
      console.log('PMC: Piano: Page load, no user');
    }

    self.initialized = true;
    self.user = null;
    self.setReportingCookie();
    self.triggerOnPageLoadNotHasUserCallbacks();
  },

  /**
   * Set sticky footer for subscription
   *
   * @param config
   */
  registerStickyFooter: config => {
    const self = window.pmcPiano.piano;
    /**
     * Set position and send postMessage to piano template for sticky footer
     * (i.e. subsequent page views should have the sticky footer collapsed)
     *
     */

    const setStickyFooter = () => {
      const stickyFooter = document.querySelector(config.className || '.js-subscription-sticky-footer');
      const stickyFooterTarget = document.getElementsByClassName(config.targetClassName || 'footer');
      const cookieName = config.cookieName || '__is_subscription_sticky_footer_loaded__'; // First set position so that sticky footer sticks at bottom
      // when user scrolls window and is above the footer

      if (!stickyFooter) {
        return;
      }

      const position = () => {
        // Check to see if footer is in viewport
        const isInsideViewport = () => {
          const windowScrollTop = window.scrollY;
          const windowHeight = window.innerHeight;
          const footerOffsetTop = stickyFooterTarget[0].offsetTop;
          const extraHeight = stickyFooter.style.position === 'static' ? stickyFooter.clientHeight : 0;
          return windowScrollTop + windowHeight + extraHeight > footerOffsetTop;
        };

        setTimeout(function () {
          // Set throttle for checking position to prevent sticky footer from jumping up and down fast.
          this.stickyFooterPrevTime = this.stickyFooterPrevTime || Date.now();

          if (this.stickyFooterPrevTime && 500 > Date.now() - this.stickyFooterPrevTime) {
            return;
          }

          this.stickyFooterPrevTime = Date.now();

          if (!isInsideViewport() && stickyFooter.style.position !== 'fixed') {
            stickyFooter.style.position = 'fixed';
            stickyFooter.style.bottom = '0px';
          } else if (isInsideViewport() && stickyFooter.style.position !== 'static') {
            stickyFooter.style.position = 'static';
          }
        }.bind(this), 500);
      };

      position();
      window.addEventListener('load', position);
      window.addEventListener('scroll', position);
      window.addEventListener('resize', position);

      const checkAndSendPostMessage = function (callback) {
        const iframe = stickyFooter.querySelector('iframe');

        if (!iframe) {
          return false;
        }

        iframe.addEventListener('load', function () {
          if (document.cookie.indexOf(cookieName) > -1) {
            iframe.contentWindow.postMessage('subscription_sticky_footer_loaded', '*');
          }

          document.cookie = cookieName + '=1'; // eslint-disable-next-line no-unused-expressions

          callback && callback();
        });
        return true;
      }; // Watch for an event that piano template is added to div element
      // and send postMessage to iframe as needed
      // eslint-disable-next-line no-undef


      if (!checkAndSendPostMessage() && MutationObserver) {
        // eslint-disable-next-line no-undef
        const observer = new MutationObserver(function () {
          checkAndSendPostMessage(function () {
            observer.disconnect();
          });
        });
        observer.observe(stickyFooter, {
          subtree: false,
          childList: true
        });
      }
    }; //Register callback for sticky footer


    self.registerOnPageLoadNotHasUserCallback(setStickyFooter.bind(self));
  },

  /**
   * Makes API call to access list and returns promise.
   *
   * @return {Promise} Promise resolving in results of API call.
   */
  accessListPromise: () => new Promise(resolve => {
    tp.api.callApi('/access/list', {}, resolve);
  }),

  /**
   * Updates the reporting property with information about the active user.
   *
   * @param user {object} - Self.user property
   * @param user.uid {string} - 	Current user's id as provided by Piano
   *
   * @return {void}
   */
  setReportingForAuthUser: async function ({
    uid
  }) {
    var _results$term, _results$term2, _results$resource;

    const self = this; // Check for cookie presence.

    let reportingCookie = {};

    try {
      reportingCookie = JSON.parse(pmc.cookie.get(this.cookieLabel) || null) || {};
    } catch (error) {
      if (self.canDebug) {
        console.log('PMC: Piano: No reporting cookie', error);
      }
    } // If user does already have data, do nothing.


    if (reportingCookie.acct_id === uid) {
      return Promise.resolve();
    } // Get user data and parse results.


    const {
      data = []
    } = (await this.accessListPromise()) || {};
    const [results = {}] = data;
    const accountType = this.getAccountType((_results$term = results.term) === null || _results$term === void 0 ? void 0 : _results$term.type);
    const isSiteLicense = accountType === 'site_license';
    const licenseeData = isSiteLicense ? await this.getLicenseeData((_results$term2 = results.term) === null || _results$term2 === void 0 ? void 0 : _results$term2.term_id) : {}; // Build reporting object with new user data from Piano.

    const newReporting = {
      acct_id: uid,
      acct_type: accountType,
      entitlements: results.granted ? (_results$resource = results.resource) === null || _results$resource === void 0 ? void 0 : _results$resource.name : '',
      paywall_logged_in: true,
      user_type: this.getAuthUserType(results),
      org_id: (licenseeData === null || licenseeData === void 0 ? void 0 : licenseeData.id) || null,
      org_name: (licenseeData === null || licenseeData === void 0 ? void 0 : licenseeData.name) || null
    }; // Merge with existing reporting data.

    this.reporting = { ...self.reporting,
      ...newReporting
    }; // Save to cookie for future pageviews.

    this.setReportingCookie();
    return Promise.resolve();
  },

  /**
   * Make call to server to request licensee data.
   *
   * @param {string} contractId Value of the contract ID
   * @return {LicenseeData} - The data of the licensee
   */
  getLicenseeData: async function (contractId) {
    let response;

    try {
      // This must be dynamically imported, as when it is imported at the top of the file, there is a race condition
      // where the theme javascript will register its event listeners before this class is initialized.
      const {
        default: apiFetch
      } = await __webpack_require__.e(/*! import() */ "vendors-node_modules_wordpress_api-fetch_build-module_index_js-node_modules_wordpress_api-fet-3a0e92").then(__webpack_require__.bind(__webpack_require__, /*! @wordpress/api-fetch */ "./node_modules/@wordpress/api-fetch/build-module/index.js"));
      response = await apiFetch({
        path: `${this.licenseeEndpoint}/${contractId}`
      });
      return response;
    } catch (err) {
      if (this.canDebug) {
        console.warn('PMC: Piano: Could not retrieve licensee details', response, err);
      }
    } // Default case; return nothing.


    return {};
  },

  /**
   * Determine the user type for reporting from /access/list results
   *
   * @param {Object} [results] - first element from data array of access/list
   * @param {boolean} results.granted - if the user has access.
   * @param {Object} results.resource - type of user's access.
   * @param {string} results.resource.name - name of resource.
   * @param {Object} results.term - type of user's term.
   * @param {string} results.term.name - name of term.
   * @return {'KNOWN'|'REGISTERED'|'SUBSCRIBER'} types of authenticated users
   */
  getAuthUserType: ({
    granted,
    resource: {
      name: resourceName
    } = {},
    term: {
      name: termName
    } = {}
  } = {}) => {
    if (!resourceName || resourceName === '') {
      return 'KNOWN';
    }

    if (granted && /registration/i.exec(termName)) {
      return 'REGISTERED';
    }

    if (granted) {
      return 'SUBSCRIBER';
    }
  },

  /**
   * Transforms the term type into an account type description.
   *
   * @param {string} termType - The type property of the term
   * @return {'site_license'|'individual'} - Account type description
   */
  getAccountType: termType => ['email_domain_contract', 'specific_email_addresses_contract'].includes(termType) ? 'site_license' : 'individual',

  /**
   * Update the reporting information for analytics.
   *
   * @return {void}
   */
  setReportingCookie: function () {
    if (this.canDebug) {
      console.log('PMC: Piano: Setting pmc_piano_reporting cookie', this.reporting);
    }

    pmc.cookie.set(this.cookieLabel, JSON.stringify(this.reporting), 604800, // 1 week
    '/');
  },

  /**
   * Delete the reporting cookie.
   *
   * @return {void}
   */
  deleteReportingCookie: function () {
    if (this.canDebug) {
      console.log('PMC: Piano: Deleting pmc_piano_reporting cookie', this.reporting);
    }

    pmc.cookie.expire(this.cookieLabel, '/');
  },

  /**
   * Register a callback to fire when we have a user.
   *
   * These callbacks are fired in the following scenarios:
   * 1) On initialization if the current user is logged-in
   * 2) On user log-in
   *
   * @param callback
   */
  registerOnLoggedInUserCallback: function (callback) {
    // Fire the callback immediately if we already have user data
    if (this.initialized && this.hasUser()) {
      callback(this.getUser());
    } // Fire the callback later if we don't already have user data


    this.onLoggedInUserCallbacks.push(callback);
  },

  /**
   * Register a callback to fire when we have a user.
   *
   * These callbacks are fired when the user logs out.
   *
   * @param callback
   */
  registerOnLoggedOutUserCallback: function (callback) {
    this.onLoggedOutUserCallbacks.push(callback);
  },

  /**
   * Register a callback to fire when we do not have a user.
   *
   * These callbacks are fired on page load
   * if the current user is not logged-in.
   *
   * @param callback A callable function.
   */
  registerOnPageLoadNotHasUserCallback: function (callback) {
    if (this.initialized && !this.hasUser()) {
      callback();
    }

    this.onPageLoadNotHasUserCallbacks.push(callback);
  },

  /**
   * Execute all the listeners who asked to be notified
   * when we have a logged-in user.
   */
  triggerOnLoggedInUserCallbacks: function () {
    const self = this;

    if (self.canDebug) {
      console.log('PMC: Piano: Running login callbacks', self.onLoggedInUserCallbacks);
    }

    self.onLoggedInUserCallbacks.forEach(function (callback) {
      callback(self.user);
    });
  },

  /**
   * Execute all the listeners who asked to be notified
   * when a user logs out.
   */
  triggerOnLoggedOutUserCallbacks: function () {
    const self = this;

    if (self.canDebug) {
      console.log('PMC: Piano: Running logout callbacks', self.onLoggedOutUserCallbacks);
    }

    self.onLoggedOutUserCallbacks.forEach(function (callback) {
      callback();
    });
  },

  /**
   * Execute all the listeners who asked to be notified
   * when the current page load is not made by a logged-in user.
   */
  triggerOnPageLoadNotHasUserCallbacks: function () {
    const self = this;

    if (self.canDebug) {
      console.log('PMC: Piano: Running no-user callbacks', self.onPageLoadNotHasUserCallbacks);
    }

    self.onPageLoadNotHasUserCallbacks.forEach(function (callback) {
      callback();
    });
  },

  /**
   * Renders My Account component only when /my-account page is loaded
   */
  maybeRenderMyAccountPage: function () {
    const self = this;

    if (!document.querySelector(self.myAccountPageSelector)) {
      return;
    }

    tp.myaccount.show({
      displayMode: 'inline',
      containerSelector: self.myAccountPageSelector
    });
  },

  /**
   * Renders Password Reset modal only when /password-reset page is loaded
   *
   * Users who click Reset Password, or have their password Reset from within Piano UI
   * will receive an email containing a link which looks something like this:
   *
   * https://sportico-com.test/password-reset?reset_token=RSTpp8yYiqtqnih
   *
   * On page load, the below code displays a modal allowing the user to set a
   * new password. Afterwards, they are prompted to Log In—after which,
   * the below code will redirect them to the home page (because the password
   * reset page contains nothing to display).
   *
   * Return statements are used only for Jest tests.
   */
  maybeRenderPasswordResetPage: function () {
    const self = this; // Only proceed if we're on the password reset page

    if (!document.querySelector(self.passwordResetPageSelector)) {
      return 'password reset element missing';
    } // Do we have a valid reset token?


    const resetTokenMatch = window.location.search.match(/reset_token=([A-Za-z0-9]+)/);

    if (!resetTokenMatch) {
      return 'invalid token';
    } // Redirect to the homepage if the user closes the password reset modal


    window.onmessage = self.redirectHomeOnClosedMessage; // Display the password reset modal
    // After changing password, and logging in, redirect to the homepage

    return tp.pianoId.show({
      resetPasswordToken: resetTokenMatch[1],
      loggedIn:
      /* istanbul ignore next */
      function () {
        window.location = '/'; // phpcs:disable WordPressVIPMinimum.JS.Window.location -- not a security risk
      }
    });
  },

  /**
   * Redirect home when we capture the 'closed' event.
   *
   * This event is sent whenever a modal is closed.
   *
   * @param {MessageEvent} event A postMessage event sent from a Piano iFrame
   */
  redirectHomeOnClosedMessage: function (event) {
    if (typeof event.data === 'string') {
      const eventData = JSON.parse(event.data);

      if (eventData.sender && eventData.sender.startsWith('piano-id')) {
        if (eventData.event === 'closed') {
          window.location = '/'; // phpcs:disable WordPressVIPMinimum.JS.Window.location -- not a security risk
        }
      }
    }
  },

  /**
   * Re-render Piano Experiences.
   *
   * Called when a user logs in or out. Allows for showing/hiding the paywall without a page refresh.
   * Closes all Piano experiences
   * Since all below are standard elements which would be on all our sites I feel it's OK to keep them in this shared plugin/no need for a filter/set per brand, etc.
   */
  reRenderExperiences: function () {
    const self = this;

    if (self.canDebug) {
      console.log('PMC: Piano: Re-rendering Experiences');
    } // Close the Piano inline paywall


    tp.offer.closeInline(self.pianoPaywallSelector); // Close the right rail

    tp.offer.closeInline('#piano-right-rail'); // Close the midriver

    tp.offer.closeInline('#piano-mid-river'); // Close the sticky footer

    tp.offer.closeInline('#piano-sticky-footer'); // Close the flyout

    tp.offer.closeInline('#piano-fly-out');
    const pmcPaywall = document.querySelector(self.pmcPaywallSelector);

    if (pmcPaywall) {
      // Pay|Regwall Experiences add the following CSS class
      // in order to display truncated content to logged out users.
      // The following displays the content by removing that class
      pmcPaywall.classList.remove(self.truncatedContentSelector);
    } // re-check access and add the paywall or other experiences we removed above back if the user doesn't have access,
    // e.g. a reg user should still see the mid-river/right rail offers


    tp.experience.execute();
  }
};
pmcPiano.checkTp(); // Export element only for jest test cases.

if (true) {
  module.exports = pmcPiano;
}

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/ensure chunk */
/******/ 	!function() {
/******/ 		__webpack_require__.f = {};
/******/ 		// This file contains only the entry chunk.
/******/ 		// The chunk loading function for additional chunks
/******/ 		__webpack_require__.e = function(chunkId) {
/******/ 			return Promise.all(Object.keys(__webpack_require__.f).reduce(function(promises, key) {
/******/ 				__webpack_require__.f[key](chunkId, promises);
/******/ 				return promises;
/******/ 			}, []));
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/get javascript chunk filename */
/******/ 	!function() {
/******/ 		// This function allow to reference async chunks
/******/ 		__webpack_require__.u = function(chunkId) {
/******/ 			// return url for filenames based on template
/******/ 			return "pmc-" + chunkId + ".js";
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/get mini-css chunk filename */
/******/ 	!function() {
/******/ 		// This function allow to reference all chunks
/******/ 		__webpack_require__.miniCssF = function(chunkId) {
/******/ 			// return url for filenames based on template
/******/ 			return undefined;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/global */
/******/ 	!function() {
/******/ 		__webpack_require__.g = (function() {
/******/ 			if (typeof globalThis === 'object') return globalThis;
/******/ 			try {
/******/ 				return this || new Function('return this')();
/******/ 			} catch (e) {
/******/ 				if (typeof window === 'object') return window;
/******/ 			}
/******/ 		})();
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/load script */
/******/ 	!function() {
/******/ 		var inProgress = {};
/******/ 		var dataWebpackPrefix = "pmcPiano.[name]:";
/******/ 		// loadScript function to load a script via script tag
/******/ 		__webpack_require__.l = function(url, done, key, chunkId) {
/******/ 			if(inProgress[url]) { inProgress[url].push(done); return; }
/******/ 			var script, needAttach;
/******/ 			if(key !== undefined) {
/******/ 				var scripts = document.getElementsByTagName("script");
/******/ 				for(var i = 0; i < scripts.length; i++) {
/******/ 					var s = scripts[i];
/******/ 					if(s.getAttribute("src") == url || s.getAttribute("data-webpack") == dataWebpackPrefix + key) { script = s; break; }
/******/ 				}
/******/ 			}
/******/ 			if(!script) {
/******/ 				needAttach = true;
/******/ 				script = document.createElement('script');
/******/ 		
/******/ 				script.charset = 'utf-8';
/******/ 				script.timeout = 120;
/******/ 				if (__webpack_require__.nc) {
/******/ 					script.setAttribute("nonce", __webpack_require__.nc);
/******/ 				}
/******/ 				script.setAttribute("data-webpack", dataWebpackPrefix + key);
/******/ 				script.src = url;
/******/ 			}
/******/ 			inProgress[url] = [done];
/******/ 			var onScriptComplete = function(prev, event) {
/******/ 				// avoid mem leaks in IE.
/******/ 				script.onerror = script.onload = null;
/******/ 				clearTimeout(timeout);
/******/ 				var doneFns = inProgress[url];
/******/ 				delete inProgress[url];
/******/ 				script.parentNode && script.parentNode.removeChild(script);
/******/ 				doneFns && doneFns.forEach(function(fn) { return fn(event); });
/******/ 				if(prev) return prev(event);
/******/ 			}
/******/ 			;
/******/ 			var timeout = setTimeout(onScriptComplete.bind(null, undefined, { type: 'timeout', target: script }), 120000);
/******/ 			script.onerror = onScriptComplete.bind(null, script.onerror);
/******/ 			script.onload = onScriptComplete.bind(null, script.onload);
/******/ 			needAttach && document.head.appendChild(script);
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/publicPath */
/******/ 	!function() {
/******/ 		var scriptUrl;
/******/ 		if (__webpack_require__.g.importScripts) scriptUrl = __webpack_require__.g.location + "";
/******/ 		var document = __webpack_require__.g.document;
/******/ 		if (!scriptUrl && document) {
/******/ 			if (document.currentScript)
/******/ 				scriptUrl = document.currentScript.src
/******/ 			if (!scriptUrl) {
/******/ 				var scripts = document.getElementsByTagName("script");
/******/ 				if(scripts.length) scriptUrl = scripts[scripts.length - 1].src
/******/ 			}
/******/ 		}
/******/ 		// When supporting browsers where an automatic publicPath is not supported you must specify an output.publicPath manually via configuration
/******/ 		// or pass an empty string ("") and set the __webpack_public_path__ variable from your code to use your own logic.
/******/ 		if (!scriptUrl) throw new Error("Automatic publicPath is not supported in this browser");
/******/ 		scriptUrl = scriptUrl.replace(/#.*$/, "").replace(/\?.*$/, "").replace(/\/[^\/]+$/, "/");
/******/ 		__webpack_require__.p = scriptUrl;
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	!function() {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"piano": 0
/******/ 		};
/******/ 		
/******/ 		__webpack_require__.f.j = function(chunkId, promises) {
/******/ 				// JSONP chunk loading for javascript
/******/ 				var installedChunkData = __webpack_require__.o(installedChunks, chunkId) ? installedChunks[chunkId] : undefined;
/******/ 				if(installedChunkData !== 0) { // 0 means "already installed".
/******/ 		
/******/ 					// a Promise means "currently loading".
/******/ 					if(installedChunkData) {
/******/ 						promises.push(installedChunkData[2]);
/******/ 					} else {
/******/ 						if(true) { // all chunks have JS
/******/ 							// setup Promise in chunk cache
/******/ 							var promise = new Promise(function(resolve, reject) { installedChunkData = installedChunks[chunkId] = [resolve, reject]; });
/******/ 							promises.push(installedChunkData[2] = promise);
/******/ 		
/******/ 							// start chunk loading
/******/ 							var url = __webpack_require__.p + __webpack_require__.u(chunkId);
/******/ 							// create error before stack unwound to get useful stacktrace later
/******/ 							var error = new Error();
/******/ 							var loadingEnded = function(event) {
/******/ 								if(__webpack_require__.o(installedChunks, chunkId)) {
/******/ 									installedChunkData = installedChunks[chunkId];
/******/ 									if(installedChunkData !== 0) installedChunks[chunkId] = undefined;
/******/ 									if(installedChunkData) {
/******/ 										var errorType = event && (event.type === 'load' ? 'missing' : event.type);
/******/ 										var realSrc = event && event.target && event.target.src;
/******/ 										error.message = 'Loading chunk ' + chunkId + ' failed.\n(' + errorType + ': ' + realSrc + ')';
/******/ 										error.name = 'ChunkLoadError';
/******/ 										error.type = errorType;
/******/ 										error.request = realSrc;
/******/ 										installedChunkData[1](error);
/******/ 									}
/******/ 								}
/******/ 							};
/******/ 							__webpack_require__.l(url, loadingEnded, "chunk-" + chunkId, chunkId);
/******/ 						} else installedChunks[chunkId] = 0;
/******/ 					}
/******/ 				}
/******/ 		};
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		// no on chunks loaded
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = function(parentChunkLoadingFunction, data) {
/******/ 			var chunkIds = data[0];
/******/ 			var moreModules = data[1];
/******/ 			var runtime = data[2];
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some(function(id) { return installedChunks[id] !== 0; })) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkIds[i]] = 0;
/******/ 			}
/******/ 		
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkpmcPiano_name_"] = self["webpackChunkpmcPiano_name_"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module is referenced by other modules so it can't be inlined
/******/ 	var __webpack_exports__ = __webpack_require__("./src/js/pmc-piano.js");
/******/ 	(pmcPiano = typeof pmcPiano === "undefined" ? {} : pmcPiano).piano = __webpack_exports__;
/******/ 	
/******/ })()
;
//# sourceMappingURL=pmc-piano.js.map