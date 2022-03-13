var pmcPiano;
/******/ (function() { // webpackBootstrap
var __webpack_exports__ = {};
/*!******************************!*\
  !*** ./src/js/pmc-cxense.js ***!
  \******************************/
/**
 * Cxense scripts to load CCE Js and dependency functions.
 */
// Global cxense Object initialization.
const cX = window.cX = window.cX || {};
cX.callQueue = cX.callQueue || [];
cX.options = cX.options || {};
cX.CCE = cX.CCE || {};
cX.CCE.callQueue = cX.CCE.callQueue || [];
const cxpmc = {
  // cxense site Id
  siteId: window.pmcCxenseData.siteId,
  // Persisted Query Id
  persistedQueryId: window.pmcCxenseData.persistedQueryId,
  // Piano application Id
  applicationId: window.pmcCxenseData.applicationId,

  /**
   * Entry point to define variable or run functions needed to execute cxense script.
   */
  init: function () {
    if (window.pmcCxenseData.compatMode === 'enable') {
      cX.options = {
        compat: {
          c1x: {
            wait: 3000
          }
        }
      };
    }

    this.loadJsDependencies();
  },

  /**
   * Loads all the javascript required for cxense script.
   */
  loadJsDependencies: function () {
    const self = this;
    const scripts = [{
      url: 'https://scdn.cxense.com/cx.cce.js',
      onloadCallback: () => self.runCxense()
    }];
    scripts.forEach(script => {
      self.addHeadJs(script);
    });
  },

  /**
   * Add JS scripts to head section.
   *
   * @param script
   */
  addHeadJs: function (script) {
    const scriptElement = document.createElement('script');
    scriptElement.type = 'text/javascript';
    scriptElement.async = 'async';
    scriptElement.src = script.url;
    scriptElement.onload = script.onloadCallback ? script.onloadCallback : '';
    scriptElement.onerror = script.onErrorCallback ? script.onErrorCallback : '';
    document.getElementsByTagName('head')[0].appendChild(scriptElement);
  },

  /**
   * Runs cxense functions.
   */
  runCxense: function () {
    const self = this;
    cX.callQueue.push(['setSiteId', `${self.siteId}`]);
    cX.CCE.callQueue.push(['sendPageViewEvent', 'pmc', `${self.persistedQueryId}`]);
  }
};

if (window.pmcCxenseData !== undefined) {
  cxpmc.init();
}
(pmcPiano = typeof pmcPiano === "undefined" ? {} : pmcPiano).cxense = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=pmc-cxense.js.map