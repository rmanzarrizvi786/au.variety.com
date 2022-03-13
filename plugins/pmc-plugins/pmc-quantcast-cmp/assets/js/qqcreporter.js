/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 9);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _require = __webpack_require__(1),
    ConsentString = _require.ConsentString;

var _require2 = __webpack_require__(8),
    decodeConsentString = _require2.decodeConsentString;

var _require3 = __webpack_require__(2),
    encodeConsentString = _require3.encodeConsentString;

module.exports = {
  ConsentString: ConsentString,
  decodeConsentString: decodeConsentString,
  encodeConsentString: encodeConsentString
};

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var _require = __webpack_require__(2),
    encodeConsentString = _require.encodeConsentString,
    _getMaxVendorId = _require.getMaxVendorId,
    encodeVendorIdsToBits = _require.encodeVendorIdsToBits,
    encodePurposeIdsToBits = _require.encodePurposeIdsToBits;

var _require2 = __webpack_require__(8),
    decodeConsentString = _require2.decodeConsentString;

var _require3 = __webpack_require__(7),
    vendorVersionMap = _require3.vendorVersionMap;
/**
 * Regular expression for validating
 */


var consentLanguageRegexp = /^[a-z]{2}$/;

var ConsentString = function () {
  /**
   * Initialize a new ConsentString object
   *
   * @param {string} baseString An existing consent string to parse and use for our initial values
   */
  function ConsentString() {
    var baseString = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

    _classCallCheck(this, ConsentString);

    this.created = new Date();
    this.lastUpdated = new Date();

    /**
     * Version number of consent string specification
     *
     * @type {integer}
     */
    this.version = 1;

    /**
     * Vendor list with format from https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/Draft_for_Public_Comment_Transparency%20%26%20Consent%20Framework%20-%20cookie%20and%20vendor%20list%20format%20specification%20v1.0a.pdf
     *
     * @type {object}
     */
    this.vendorList = null;

    /**
     * Version of the vendor list used for the purposes and vendors
     *
     * @type {integer}
     */
    this.vendorListVersion = null;

    /**
     * The unique ID of the CMP that last modified the consent string
     *
     * @type {integer}
     */
    this.cmpId = null;

    /**
     * Version of the code used by the CMP when collecting consent
     *
     * @type {integer}
     */
    this.cmpVersion = null;

    /**
     * ID of the screen used by CMP when collecting consent
     *
     * @type {integer}
     */
    this.consentScreen = null;

    /**
     * Two-letter ISO639-1 code (en, fr, de, etc.) of the language that the CMP asked consent in
     *
     * @type {string}
     */
    this.consentLanguage = null;

    /**
     * List of purpose IDs that the user has given consent to
     *
     * @type {integer[]}
     */
    this.allowedPurposeIds = [];

    /**
     * List of vendor IDs that the user has given consent to
     *
     * @type {integer[]}
     */
    this.allowedVendorIds = [];

    // Decode the base string
    if (baseString) {
      Object.assign(this, decodeConsentString(baseString));
    }
  }

  /**
   * Get the web-safe, base64-encoded consent string
   *
   * @return {string} Web-safe, base64-encoded consent string
   */


  _createClass(ConsentString, [{
    key: 'getConsentString',
    value: function getConsentString() {
      var updateDate = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

      if (!this.vendorList) {
        throw new Error('ConsentString - A vendor list is required to encode a consent string');
      }

      if (updateDate === true) {
        this.lastUpdated = new Date();
      }

      return encodeConsentString({
        version: this.getVersion(),
        vendorList: this.vendorList,
        allowedPurposeIds: this.allowedPurposeIds,
        allowedVendorIds: this.allowedVendorIds,
        created: this.created,
        lastUpdated: this.lastUpdated,
        cmpId: this.cmpId,
        cmpVersion: this.cmpVersion,
        consentScreen: this.consentScreen,
        consentLanguage: this.consentLanguage,
        vendorListVersion: this.vendorListVersion
      });
    }

    /**
     * Get the max vendorId
     *
     * @return {number} maxVendorId from the vendorList provided
     */

  }, {
    key: 'getMaxVendorId',
    value: function getMaxVendorId() {
      return _getMaxVendorId(this.vendorList.vendors);
    }

    /**
     * get the consents in a bit string.  This is to fulfill the in-app requirement
     *
     * @return {string} a bit string of all of the vendor consent data
     */

  }, {
    key: 'getParsedVendorConsents',
    value: function getParsedVendorConsents() {
      return encodeVendorIdsToBits(_getMaxVendorId(this.vendorList.vendors), this.allowedVendorIds);
    }

    /**
     * get the consents in a bit string.  This is to fulfill the in-app requirement
     *
     * @return {string} a bit string of all of the vendor consent data
     */

  }, {
    key: 'getParsedPurposeConsents',
    value: function getParsedPurposeConsents() {
      return encodePurposeIdsToBits(this.vendorList.purposes, this.allowedPurposeIds);
    }

    /**
     * Get the web-safe, base64-encoded metadata string
     *
     * @return {string} Web-safe, base64-encoded metadata string
     */

  }, {
    key: 'getMetadataString',
    value: function getMetadataString() {
      return encodeConsentString({
        version: this.getVersion(),
        created: this.created,
        lastUpdated: this.lastUpdated,
        cmpId: this.cmpId,
        cmpVersion: this.cmpVersion,
        consentScreen: this.consentScreen,
        vendorListVersion: this.vendorListVersion
      });
    }

    /**
     * Decode the web-safe, base64-encoded metadata string
     * @param {string} encodedMetadata Web-safe, base64-encoded metadata string
     * @return {object} decoded metadata
     */

  }, {
    key: 'getVersion',


    /**
     * Get the version number that this consent string specification adheres to
     *
     * @return {integer} Version number of consent string specification
     */
    value: function getVersion() {
      return this.version;
    }

    /**
     * Get the version of the vendor list
     *
     * @return {integer} Vendor list version
     */

  }, {
    key: 'getVendorListVersion',
    value: function getVendorListVersion() {
      return this.vendorListVersion;
    }

    /**
     * Set the vendors list to use when generating the consent string
     *
     * The expected format is the one from https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/Draft_for_Public_Comment_Transparency%20%26%20Consent%20Framework%20-%20cookie%20and%20vendor%20list%20format%20specification%20v1.0a.pdf
     *
     * @param {object} vendorList Vendor list with format from https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/Draft_for_Public_Comment_Transparency%20%26%20Consent%20Framework%20-%20cookie%20and%20vendor%20list%20format%20specification%20v1.0a.pdf
     */

  }, {
    key: 'setGlobalVendorList',
    value: function setGlobalVendorList(vendorList) {
      if ((typeof vendorList === 'undefined' ? 'undefined' : _typeof(vendorList)) !== 'object') {
        throw new Error('ConsentString - You must provide an object when setting the global vendor list');
      }

      if (!vendorList.vendorListVersion || !Array.isArray(vendorList.purposes) || !Array.isArray(vendorList.vendors)) {
        // The provided vendor list does not look valid
        throw new Error('ConsentString - The provided vendor list does not respect the schema from the IAB EU’s GDPR Consent and Transparency Framework');
      }

      // Cloning the GVL
      // It's important as we might transform it and don't want to modify objects that we do not own
      this.vendorList = {
        vendorListVersion: vendorList.vendorListVersion,
        lastUpdated: vendorList.lastUpdated,
        purposes: vendorList.purposes,
        features: vendorList.features,

        // Clone the list and sort the vendors by ID (it breaks our range generation algorithm if they are not sorted)
        vendors: vendorList.vendors.slice(0).sort(function (firstVendor, secondVendor) {
          return firstVendor.id < secondVendor.id ? -1 : 1;
        })
      };
      this.vendorListVersion = vendorList.vendorListVersion;
    }

    /**
     * Set the ID of the Consent Management Platform that last modified the consent string
     *
     * Every CMP is assigned a unique ID by the IAB EU that must be provided here before changing any other value in the consent string.
     *
     * @param {integer} id CMP ID
     */

  }, {
    key: 'setCmpId',
    value: function setCmpId(id) {
      this.cmpId = id;
    }

    /**
     * Get the ID of the Consent Management Platform from the consent string
     *
     * @return {integer}
     */

  }, {
    key: 'getCmpId',
    value: function getCmpId() {
      return this.cmpId;
    }

    /**
     * Set the version of the Consent Management Platform that last modified the consent string
     *
     * This version number references the CMP code running when collecting the user consent.
     *
     * @param {integer} version Version
     */

  }, {
    key: 'setCmpVersion',
    value: function setCmpVersion(version) {
      this.cmpVersion = version;
    }

    /**
     * Get the verison of the Consent Management Platform that last modified the consent string
     *
     * @return {integer}
     */

  }, {
    key: 'getCmpVersion',
    value: function getCmpVersion() {
      return this.cmpVersion;
    }

    /**
     * Set the Consent Management Platform screen ID that collected the user consent
     *
     * This screen ID references a unique view in the CMP that was displayed to the user to collect consent
     *
     * @param {*} screenId Screen ID
     */

  }, {
    key: 'setConsentScreen',
    value: function setConsentScreen(screenId) {
      this.consentScreen = screenId;
    }

    /**
     * Get the Consent Management Platform screen ID that collected the user consent
     *
     * @return {integer}
     */

  }, {
    key: 'getConsentScreen',
    value: function getConsentScreen() {
      return this.consentScreen;
    }

    /**
     * Set the language that the CMP asked the consent in
     *
     * @param {string} language Two-letter ISO639-1 code (en, fr, de, etc.)
     */

  }, {
    key: 'setConsentLanguage',
    value: function setConsentLanguage(language) {
      if (consentLanguageRegexp.test(language) === false) {
        throw new Error('ConsentString - The consent language must be a two-letter ISO639-1 code (en, fr, de, etc.)');
      }

      this.consentLanguage = language;
    }

    /**
     * Get the language that the CMP asked consent in
     *
     * @return {string} Two-letter ISO639-1 code (en, fr, de, etc.)
     */

  }, {
    key: 'getConsentLanguage',
    value: function getConsentLanguage() {
      return this.consentLanguage;
    }

    /**
     * Set the list of purpose IDs that the user has given consent to
     *
     * @param {integer[]} purposeIds An array of integers that map to the purposes defined in the vendor list. Purposes included in the array are purposes that the user has given consent to
     */

  }, {
    key: 'setPurposesAllowed',
    value: function setPurposesAllowed(purposeIds) {
      this.allowedPurposeIds = purposeIds;
    }

    /**
     * Get the list of purpose IDs that the user has given consent to
     *
     * @return {integer[]}
     */

  }, {
    key: 'getPurposesAllowed',
    value: function getPurposesAllowed() {
      return this.allowedPurposeIds;
    }

    /**
     * Set the consent status of a user for a given purpose
     *
     * @param {integer} purposeId The ID (from the vendor list) of the purpose to update
     * @param {boolean} value Consent status
     */

  }, {
    key: 'setPurposeAllowed',
    value: function setPurposeAllowed(purposeId, value) {
      var purposeIndex = this.allowedPurposeIds.indexOf(purposeId);

      if (value === true) {
        if (purposeIndex === -1) {
          this.allowedPurposeIds.push(purposeId);
        }
      } else if (value === false) {
        if (purposeIndex !== -1) {
          this.allowedPurposeIds.splice(purposeIndex, 1);
        }
      }
    }

    /**
     * Check if the user has given consent for a specific purpose
     *
     * @param {integer} purposeId
     *
     * @return {boolean}
     */

  }, {
    key: 'isPurposeAllowed',
    value: function isPurposeAllowed(purposeId) {
      return this.allowedPurposeIds.indexOf(purposeId) !== -1;
    }

    /**
     * Set the list of vendor IDs that the user has given consent to
     *
     * @param {integer[]} vendorIds An array of integers that map to the vendors defined in the vendor list. Vendors included in the array are vendors that the user has given consent to
     */

  }, {
    key: 'setVendorsAllowed',
    value: function setVendorsAllowed(vendorIds) {
      this.allowedVendorIds = vendorIds;
    }

    /**
     * Get the list of vendor IDs that the user has given consent to
     *
     * @return {integer[]}
     */

  }, {
    key: 'getVendorsAllowed',
    value: function getVendorsAllowed() {
      return this.allowedVendorIds;
    }

    /**
     * Set the consent status of a user for a given vendor
     *
     * @param {integer} vendorId The ID (from the vendor list) of the vendor to update
     * @param {boolean} value Consent status
     */

  }, {
    key: 'setVendorAllowed',
    value: function setVendorAllowed(vendorId, value) {
      var vendorIndex = this.allowedVendorIds.indexOf(vendorId);

      if (value === true) {
        if (vendorIndex === -1) {
          this.allowedVendorIds.push(vendorId);
        }
      } else if (value === false) {
        if (vendorIndex !== -1) {
          this.allowedVendorIds.splice(vendorIndex, 1);
        }
      }
    }

    /**
     * Check if the user has given consent for a specific vendor
     *
     * @param {integer} vendorId
     *
     * @return {boolean}
     */

  }, {
    key: 'isVendorAllowed',
    value: function isVendorAllowed(vendorId) {
      return this.allowedVendorIds.indexOf(vendorId) !== -1;
    }
  }], [{
    key: 'decodeMetadataString',
    value: function decodeMetadataString(encodedMetadata) {
      var decodedString = decodeConsentString(encodedMetadata);
      var metadata = {};
      vendorVersionMap[decodedString.version].metadataFields.forEach(function (field) {
        metadata[field] = decodedString[field];
      });
      return metadata;
    }
  }]);

  return ConsentString;
}();

module.exports = {
  ConsentString: ConsentString
};

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _require = __webpack_require__(3),
    encodeToBase64 = _require.encodeToBase64,
    padRight = _require.padRight;

/**
 * Encode a list of vendor IDs into bits
 *
 * @param {integer} maxVendorId Highest vendor ID in the vendor list
 * @param {integer[]} allowedVendorIds Vendors that the user has given consent to
 */


function encodeVendorIdsToBits(maxVendorId) {
  var allowedVendorIds = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];

  var vendorString = '';

  for (var id = 1; id <= maxVendorId; id += 1) {
    vendorString += allowedVendorIds.indexOf(id) !== -1 ? '1' : '0';
  }

  return padRight(vendorString, Math.max(0, maxVendorId - vendorString.length));
}

/**
 * Encode a list of purpose IDs into bits
 *
 * @param {*} purposes List of purposes from the vendor list
 * @param {*} allowedPurposeIds List of purpose IDs that the user has given consent to
 */
function encodePurposeIdsToBits(purposes) {
  var allowedPurposeIds = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : new Set();

  var maxPurposeId = 0;
  for (var i = 0; i < purposes.length; i += 1) {
    maxPurposeId = Math.max(maxPurposeId, purposes[i].id);
  }
  for (var _i = 0; _i < allowedPurposeIds.length; _i += 1) {
    maxPurposeId = Math.max(maxPurposeId, allowedPurposeIds[_i]);
  }

  var purposeString = '';
  for (var id = 1; id <= maxPurposeId; id += 1) {
    purposeString += allowedPurposeIds.indexOf(id) !== -1 ? '1' : '0';
  }

  return purposeString;
}

/**
 * Convert a list of vendor IDs to ranges
 *
 * @param {object[]} vendors List of vendors from the vendor list (important: this list must to be sorted by ID)
 * @param {integer[]} allowedVendorIds List of vendor IDs that the user has given consent to
 */
function convertVendorsToRanges(vendors, allowedVendorIds) {
  var range = [];
  var ranges = [];

  var idsInList = vendors.map(function (vendor) {
    return vendor.id;
  });

  for (var index = 0; index < vendors.length; index += 1) {
    var id = vendors[index].id;

    if (allowedVendorIds.indexOf(id) !== -1) {
      range.push(id);
    }

    // Do we need to close the current range?
    if ((allowedVendorIds.indexOf(id) === -1 // The vendor we are evaluating is not allowed
    || index === vendors.length - 1 // There is no more vendor to evaluate
    || idsInList.indexOf(id + 1) === -1 // There is no vendor after this one (ie there is a gap in the vendor IDs) ; we need to stop here to avoid including vendors that do not have consent
    ) && range.length) {
      var startVendorId = range.shift();
      var endVendorId = range.pop();

      range = [];

      ranges.push({
        isRange: typeof endVendorId === 'number',
        startVendorId: startVendorId,
        endVendorId: endVendorId
      });
    }
  }

  return ranges;
}

/**
 * Get maxVendorId from the list of vendors and return that id
 *
 * @param {object} vendors
 */
function getMaxVendorId(vendors) {
  // Find the max vendor ID from the vendor list
  var maxVendorId = 0;

  vendors.forEach(function (vendor) {
    if (vendor.id > maxVendorId) {
      maxVendorId = vendor.id;
    }
  });
  return maxVendorId;
}
/**
 * Encode consent data into a web-safe base64-encoded string
 *
 * @param {object} consentData Data to include in the string (see `utils/definitions.js` for the list of fields)
 */
function encodeConsentString(consentData) {
  var maxVendorId = consentData.maxVendorId;
  var _consentData$vendorLi = consentData.vendorList,
      vendorList = _consentData$vendorLi === undefined ? {} : _consentData$vendorLi,
      allowedPurposeIds = consentData.allowedPurposeIds,
      allowedVendorIds = consentData.allowedVendorIds;
  var _vendorList$vendors = vendorList.vendors,
      vendors = _vendorList$vendors === undefined ? [] : _vendorList$vendors,
      _vendorList$purposes = vendorList.purposes,
      purposes = _vendorList$purposes === undefined ? [] : _vendorList$purposes;

  // if no maxVendorId is in the ConsentData, get it

  if (!maxVendorId) {
    maxVendorId = getMaxVendorId(vendors);
  }

  // Encode the data with and without ranges and return the smallest encoded payload
  var noRangesData = encodeToBase64(_extends({}, consentData, {
    maxVendorId: maxVendorId,
    purposeIdBitString: encodePurposeIdsToBits(purposes, allowedPurposeIds),
    isRange: false,
    vendorIdBitString: encodeVendorIdsToBits(maxVendorId, allowedVendorIds)
  }));

  var vendorRangeList = convertVendorsToRanges(vendors, allowedVendorIds);

  var rangesData = encodeToBase64(_extends({}, consentData, {
    maxVendorId: maxVendorId,
    purposeIdBitString: encodePurposeIdsToBits(purposes, allowedPurposeIds),
    isRange: true,
    defaultConsent: false,
    numEntries: vendorRangeList.length,
    vendorRangeList: vendorRangeList
  }));

  return noRangesData.length < rangesData.length ? noRangesData : rangesData;
}

module.exports = {
  convertVendorsToRanges: convertVendorsToRanges,
  encodeConsentString: encodeConsentString,
  getMaxVendorId: getMaxVendorId,
  encodeVendorIdsToBits: encodeVendorIdsToBits,
  encodePurposeIdsToBits: encodePurposeIdsToBits
};

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/* eslint no-use-before-define: off */

var base64 = __webpack_require__(4);

var _require = __webpack_require__(7),
    versionNumBits = _require.versionNumBits,
    vendorVersionMap = _require.vendorVersionMap;

function repeat(count) {
  var string = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '0';

  var padString = '';

  for (var i = 0; i < count; i += 1) {
    padString += string;
  }

  return padString;
}

function padLeft(string, padding) {
  return repeat(Math.max(0, padding)) + string;
}

function padRight(string, padding) {
  return string + repeat(Math.max(0, padding));
}

function encodeIntToBits(number, numBits) {
  var bitString = '';

  if (typeof number === 'number' && !isNaN(number)) {
    bitString = parseInt(number, 10).toString(2);
  }

  // Pad the string if not filling all bits
  if (numBits >= bitString.length) {
    bitString = padLeft(bitString, numBits - bitString.length);
  }

  // Truncate the string if longer than the number of bits
  if (bitString.length > numBits) {
    bitString = bitString.substring(0, numBits);
  }

  return bitString;
}

function encodeBoolToBits(value) {
  return encodeIntToBits(value === true ? 1 : 0, 1);
}

function encodeDateToBits(date, numBits) {
  if (date instanceof Date) {
    return encodeIntToBits(date.getTime() / 100, numBits);
  }
  return encodeIntToBits(date, numBits);
}

function encodeLetterToBits(letter, numBits) {
  return encodeIntToBits(letter.toUpperCase().charCodeAt(0) - 65, numBits);
}

function encodeLanguageToBits(language) {
  var numBits = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 12;

  return encodeLetterToBits(language.slice(0, 1), numBits / 2) + encodeLetterToBits(language.slice(1), numBits / 2);
}

function decodeBitsToInt(bitString, start, length) {
  return parseInt(bitString.substr(start, length), 2);
}

function decodeBitsToDate(bitString, start, length) {
  return new Date(decodeBitsToInt(bitString, start, length) * 100);
}

function decodeBitsToBool(bitString, start) {
  return parseInt(bitString.substr(start, 1), 2) === 1;
}

function decodeBitsToLetter(bitString) {
  var letterCode = decodeBitsToInt(bitString);
  return String.fromCharCode(letterCode + 65).toLowerCase();
}

function decodeBitsToLanguage(bitString, start, length) {
  var languageBitString = bitString.substr(start, length);

  return decodeBitsToLetter(languageBitString.slice(0, length / 2)) + decodeBitsToLetter(languageBitString.slice(length / 2));
}

function encodeField(_ref) {
  var input = _ref.input,
      field = _ref.field;
  var name = field.name,
      type = field.type,
      numBits = field.numBits,
      encoder = field.encoder,
      validator = field.validator;


  if (typeof validator === 'function') {
    if (!validator(input)) {
      return '';
    }
  }
  if (typeof encoder === 'function') {
    return encoder(input);
  }

  var bitCount = typeof numBits === 'function' ? numBits(input) : numBits;

  var inputValue = input[name];
  var fieldValue = inputValue === null || inputValue === undefined ? '' : inputValue;

  switch (type) {
    case 'int':
      return encodeIntToBits(fieldValue, bitCount);
    case 'bool':
      return encodeBoolToBits(fieldValue);
    case 'date':
      return encodeDateToBits(fieldValue, bitCount);
    case 'bits':
      return padRight(fieldValue, bitCount - fieldValue.length).substring(0, bitCount);
    case 'list':
      return fieldValue.reduce(function (acc, listValue) {
        return acc + encodeFields({
          input: listValue,
          fields: field.fields
        });
      }, '');
    case 'language':
      return encodeLanguageToBits(fieldValue, bitCount);
    default:
      throw new Error('ConsentString - Unknown field type ' + type + ' for encoding');
  }
}

function encodeFields(_ref2) {
  var input = _ref2.input,
      fields = _ref2.fields;

  return fields.reduce(function (acc, field) {
    acc += encodeField({ input: input, field: field });

    return acc;
  }, '');
}

function decodeField(_ref3) {
  var input = _ref3.input,
      output = _ref3.output,
      startPosition = _ref3.startPosition,
      field = _ref3.field;
  var type = field.type,
      numBits = field.numBits,
      decoder = field.decoder,
      validator = field.validator,
      listCount = field.listCount;


  if (typeof validator === 'function') {
    if (!validator(output)) {
      // Not decoding this field so make sure we start parsing the next field at
      // the same point
      return { newPosition: startPosition };
    }
  }

  if (typeof decoder === 'function') {
    return decoder(input, output, startPosition);
  }

  var bitCount = typeof numBits === 'function' ? numBits(output) : numBits;

  switch (type) {
    case 'int':
      return { fieldValue: decodeBitsToInt(input, startPosition, bitCount) };
    case 'bool':
      return { fieldValue: decodeBitsToBool(input, startPosition) };
    case 'date':
      return { fieldValue: decodeBitsToDate(input, startPosition, bitCount) };
    case 'bits':
      return { fieldValue: input.substr(startPosition, bitCount) };
    case 'list':
      return decodeList(input, output, startPosition, field, listCount);
    case 'language':
      return { fieldValue: decodeBitsToLanguage(input, startPosition, bitCount) };
    default:
      throw new Error('ConsentString - Unknown field type ' + type + ' for decoding');
  }
}

function decodeList(input, output, startPosition, field, listCount) {
  var listEntryCount = 0;

  if (typeof listCount === 'function') {
    listEntryCount = listCount(output);
  } else if (typeof listCount === 'number') {
    listEntryCount = listCount;
  }

  var newPosition = startPosition;
  var fieldValue = [];

  for (var i = 0; i < listEntryCount; i += 1) {
    var decodedFields = decodeFields({
      input: input,
      fields: field.fields,
      startPosition: newPosition
    });

    newPosition = decodedFields.newPosition;
    fieldValue.push(decodedFields.decodedObject);
  }

  return { fieldValue: fieldValue, newPosition: newPosition };
}

function decodeFields(_ref4) {
  var input = _ref4.input,
      fields = _ref4.fields,
      _ref4$startPosition = _ref4.startPosition,
      startPosition = _ref4$startPosition === undefined ? 0 : _ref4$startPosition;

  var position = startPosition;

  var decodedObject = fields.reduce(function (acc, field) {
    var name = field.name,
        numBits = field.numBits;

    var _decodeField = decodeField({
      input: input,
      output: acc,
      startPosition: position,
      field: field
    }),
        fieldValue = _decodeField.fieldValue,
        newPosition = _decodeField.newPosition;

    if (fieldValue !== undefined) {
      acc[name] = fieldValue;
    }

    if (newPosition !== undefined) {
      position = newPosition;
    } else if (typeof numBits === 'number') {
      position += numBits;
    }

    return acc;
  }, {});

  return {
    decodedObject: decodedObject,
    newPosition: position
  };
}

/**
 * Encode the data properties to a bit string. Encoding will encode
 * either `selectedVendorIds` or the `vendorRangeList` depending on
 * the value of the `isRange` flag.
 */
function encodeDataToBits(data, definitionMap) {
  var version = data.version;


  if (typeof version !== 'number') {
    throw new Error('ConsentString - No version field to encode');
  } else if (!definitionMap[version]) {
    throw new Error('ConsentString - No definition for version ' + version);
  } else {
    var fields = definitionMap[version].fields;
    return encodeFields({ input: data, fields: fields });
  }
}

/**
 * Take all fields required to encode the consent string and produce the URL safe Base64 encoded value
 */
function encodeToBase64(data) {
  var definitionMap = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : vendorVersionMap;

  var binaryValue = encodeDataToBits(data, definitionMap);

  if (binaryValue) {
    // Pad length to multiple of 8
    var paddedBinaryValue = padRight(binaryValue, 7 - (binaryValue.length + 7) % 8);

    // Encode to bytes
    var bytes = '';
    for (var i = 0; i < paddedBinaryValue.length; i += 8) {
      bytes += String.fromCharCode(parseInt(paddedBinaryValue.substr(i, 8), 2));
    }

    // Make base64 string URL friendly
    return base64.encode(bytes).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
  }

  return null;
}

function decodeConsentStringBitValue(bitString) {
  var definitionMap = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : vendorVersionMap;

  var version = decodeBitsToInt(bitString, 0, versionNumBits);

  if (typeof version !== 'number') {
    throw new Error('ConsentString - Unknown version number in the string to decode');
  } else if (!vendorVersionMap[version]) {
    throw new Error('ConsentString - Unsupported version ' + version + ' in the string to decode');
  }

  var fields = definitionMap[version].fields;

  var _decodeFields = decodeFields({ input: bitString, fields: fields }),
      decodedObject = _decodeFields.decodedObject;

  return decodedObject;
}

/**
 * Decode the (URL safe Base64) value of a consent string into an object.
 */
function decodeFromBase64(consentString, definitionMap) {
  // Add padding
  var unsafe = consentString;
  while (unsafe.length % 4 !== 0) {
    unsafe += '=';
  }

  // Replace safe characters
  unsafe = unsafe.replace(/-/g, '+').replace(/_/g, '/');

  var bytes = base64.decode(unsafe);

  var inputBits = '';
  for (var i = 0; i < bytes.length; i += 1) {
    var bitString = bytes.charCodeAt(i).toString(2);
    inputBits += padLeft(bitString, 8 - bitString.length);
  }

  return decodeConsentStringBitValue(inputBits, definitionMap);
}

function decodeBitsToIds(bitString) {
  return bitString.split('').reduce(function (acc, bit, index) {
    if (bit === '1') {
      if (acc.indexOf(index + 1) === -1) {
        acc.push(index + 1);
      }
    }
    return acc;
  }, []);
}

module.exports = {
  padRight: padRight,
  padLeft: padLeft,
  encodeField: encodeField,
  encodeDataToBits: encodeDataToBits,
  encodeIntToBits: encodeIntToBits,
  encodeBoolToBits: encodeBoolToBits,
  encodeDateToBits: encodeDateToBits,
  encodeLanguageToBits: encodeLanguageToBits,
  encodeLetterToBits: encodeLetterToBits,
  encodeToBase64: encodeToBase64,
  decodeBitsToIds: decodeBitsToIds,
  decodeBitsToInt: decodeBitsToInt,
  decodeBitsToDate: decodeBitsToDate,
  decodeBitsToBool: decodeBitsToBool,
  decodeBitsToLanguage: decodeBitsToLanguage,
  decodeBitsToLetter: decodeBitsToLetter,
  decodeFromBase64: decodeFromBase64
};

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(module, global) {var __WEBPACK_AMD_DEFINE_RESULT__;/*! http://mths.be/base64 v0.1.0 by @mathias | MIT license */
;(function(root) {

	// Detect free variables `exports`.
	var freeExports =  true && exports;

	// Detect free variable `module`.
	var freeModule =  true && module &&
		module.exports == freeExports && module;

	// Detect free variable `global`, from Node.js or Browserified code, and use
	// it as `root`.
	var freeGlobal = typeof global == 'object' && global;
	if (freeGlobal.global === freeGlobal || freeGlobal.window === freeGlobal) {
		root = freeGlobal;
	}

	/*--------------------------------------------------------------------------*/

	var InvalidCharacterError = function(message) {
		this.message = message;
	};
	InvalidCharacterError.prototype = new Error;
	InvalidCharacterError.prototype.name = 'InvalidCharacterError';

	var error = function(message) {
		// Note: the error messages used throughout this file match those used by
		// the native `atob`/`btoa` implementation in Chromium.
		throw new InvalidCharacterError(message);
	};

	var TABLE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
	// http://whatwg.org/html/common-microsyntaxes.html#space-character
	var REGEX_SPACE_CHARACTERS = /[\t\n\f\r ]/g;

	// `decode` is designed to be fully compatible with `atob` as described in the
	// HTML Standard. http://whatwg.org/html/webappapis.html#dom-windowbase64-atob
	// The optimized base64-decoding algorithm used is based on @atk’s excellent
	// implementation. https://gist.github.com/atk/1020396
	var decode = function(input) {
		input = String(input)
			.replace(REGEX_SPACE_CHARACTERS, '');
		var length = input.length;
		if (length % 4 == 0) {
			input = input.replace(/==?$/, '');
			length = input.length;
		}
		if (
			length % 4 == 1 ||
			// http://whatwg.org/C#alphanumeric-ascii-characters
			/[^+a-zA-Z0-9/]/.test(input)
		) {
			error(
				'Invalid character: the string to be decoded is not correctly encoded.'
			);
		}
		var bitCounter = 0;
		var bitStorage;
		var buffer;
		var output = '';
		var position = -1;
		while (++position < length) {
			buffer = TABLE.indexOf(input.charAt(position));
			bitStorage = bitCounter % 4 ? bitStorage * 64 + buffer : buffer;
			// Unless this is the first of a group of 4 characters…
			if (bitCounter++ % 4) {
				// …convert the first 8 bits to a single ASCII character.
				output += String.fromCharCode(
					0xFF & bitStorage >> (-2 * bitCounter & 6)
				);
			}
		}
		return output;
	};

	// `encode` is designed to be fully compatible with `btoa` as described in the
	// HTML Standard: http://whatwg.org/html/webappapis.html#dom-windowbase64-btoa
	var encode = function(input) {
		input = String(input);
		if (/[^\0-\xFF]/.test(input)) {
			// Note: no need to special-case astral symbols here, as surrogates are
			// matched, and the input is supposed to only contain ASCII anyway.
			error(
				'The string to be encoded contains characters outside of the ' +
				'Latin1 range.'
			);
		}
		var padding = input.length % 3;
		var output = '';
		var position = -1;
		var a;
		var b;
		var c;
		var d;
		var buffer;
		// Make sure any padding is handled outside of the loop.
		var length = input.length - padding;

		while (++position < length) {
			// Read three bytes, i.e. 24 bits.
			a = input.charCodeAt(position) << 16;
			b = input.charCodeAt(++position) << 8;
			c = input.charCodeAt(++position);
			buffer = a + b + c;
			// Turn the 24 bits into four chunks of 6 bits each, and append the
			// matching character for each of them to the output.
			output += (
				TABLE.charAt(buffer >> 18 & 0x3F) +
				TABLE.charAt(buffer >> 12 & 0x3F) +
				TABLE.charAt(buffer >> 6 & 0x3F) +
				TABLE.charAt(buffer & 0x3F)
			);
		}

		if (padding == 2) {
			a = input.charCodeAt(position) << 8;
			b = input.charCodeAt(++position);
			buffer = a + b;
			output += (
				TABLE.charAt(buffer >> 10) +
				TABLE.charAt((buffer >> 4) & 0x3F) +
				TABLE.charAt((buffer << 2) & 0x3F) +
				'='
			);
		} else if (padding == 1) {
			buffer = input.charCodeAt(position);
			output += (
				TABLE.charAt(buffer >> 2) +
				TABLE.charAt((buffer << 4) & 0x3F) +
				'=='
			);
		}

		return output;
	};

	var base64 = {
		'encode': encode,
		'decode': decode,
		'version': '0.1.0'
	};

	// Some AMD build optimizers, like r.js, check for specific condition patterns
	// like the following:
	if (
		true
	) {
		!(__WEBPACK_AMD_DEFINE_RESULT__ = (function() {
			return base64;
		}).call(exports, __webpack_require__, exports, module),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
	}	else { var key; }

}(this));

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(5)(module), __webpack_require__(6)))

/***/ }),
/* 5 */
/***/ (function(module, exports) {

module.exports = function(module) {
	if (!module.webpackPolyfill) {
		module.deprecate = function() {};
		module.paths = [];
		// module.parent = undefined by default
		if (!module.children) module.children = [];
		Object.defineProperty(module, "loaded", {
			enumerable: true,
			get: function() {
				return module.l;
			}
		});
		Object.defineProperty(module, "id", {
			enumerable: true,
			get: function() {
				return module.i;
			}
		});
		module.webpackPolyfill = 1;
	}
	return module;
};


/***/ }),
/* 6 */
/***/ (function(module, exports) {

var g;

// This works in non-strict mode
g = (function() {
	return this;
})();

try {
	// This works if eval is allowed (see CSP)
	g = g || new Function("return this")();
} catch (e) {
	// This works if the window reference is available
	if (typeof window === "object") g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;


/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/**
 * Number of bits for encoding the version integer
 * Expected to be the same across versions
 */
var versionNumBits = 6;

/**
 * Definition of the consent string encoded format
 *
 * From https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/Draft_for_Public_Comment_Transparency%20%26%20Consent%20Framework%20-%20cookie%20and%20vendor%20list%20format%20specification%20v1.0a.pdf
 */
var vendorVersionMap = {
  /**
   * Version 1
   */
  1: {
    version: 1,
    metadataFields: ['version', 'created', 'lastUpdated', 'cmpId', 'cmpVersion', 'consentScreen', 'vendorListVersion'],
    fields: [{ name: 'version', type: 'int', numBits: 6 }, { name: 'created', type: 'date', numBits: 36 }, { name: 'lastUpdated', type: 'date', numBits: 36 }, { name: 'cmpId', type: 'int', numBits: 12 }, { name: 'cmpVersion', type: 'int', numBits: 12 }, { name: 'consentScreen', type: 'int', numBits: 6 }, { name: 'consentLanguage', type: 'language', numBits: 12 }, { name: 'vendorListVersion', type: 'int', numBits: 12 }, { name: 'purposeIdBitString', type: 'bits', numBits: 24 }, { name: 'maxVendorId', type: 'int', numBits: 16 }, { name: 'isRange', type: 'bool', numBits: 1 }, {
      name: 'vendorIdBitString',
      type: 'bits',
      numBits: function numBits(decodedObject) {
        return decodedObject.maxVendorId;
      },
      validator: function validator(decodedObject) {
        return !decodedObject.isRange;
      }
    }, {
      name: 'defaultConsent',
      type: 'bool',
      numBits: 1,
      validator: function validator(decodedObject) {
        return decodedObject.isRange;
      }
    }, {
      name: 'numEntries',
      numBits: 12,
      type: 'int',
      validator: function validator(decodedObject) {
        return decodedObject.isRange;
      }
    }, {
      name: 'vendorRangeList',
      type: 'list',
      listCount: function listCount(decodedObject) {
        return decodedObject.numEntries;
      },
      validator: function validator(decodedObject) {
        return decodedObject.isRange;
      },
      fields: [{
        name: 'isRange',
        type: 'bool',
        numBits: 1
      }, {
        name: 'startVendorId',
        type: 'int',
        numBits: 16
      }, {
        name: 'endVendorId',
        type: 'int',
        numBits: 16,
        validator: function validator(decodedObject) {
          return decodedObject.isRange;
        }
      }]
    }]
  }
};

module.exports = {
  versionNumBits: versionNumBits,
  vendorVersionMap: vendorVersionMap
};

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _require = __webpack_require__(3),
    decodeBitsToIds = _require.decodeBitsToIds,
    decodeFromBase64 = _require.decodeFromBase64;

/**
 * Decode consent data from a web-safe base64-encoded string
 *
 * @param {string} consentString
 */


function decodeConsentString(consentString) {
  var _decodeFromBase = decodeFromBase64(consentString),
      version = _decodeFromBase.version,
      cmpId = _decodeFromBase.cmpId,
      vendorListVersion = _decodeFromBase.vendorListVersion,
      purposeIdBitString = _decodeFromBase.purposeIdBitString,
      maxVendorId = _decodeFromBase.maxVendorId,
      created = _decodeFromBase.created,
      lastUpdated = _decodeFromBase.lastUpdated,
      isRange = _decodeFromBase.isRange,
      defaultConsent = _decodeFromBase.defaultConsent,
      vendorIdBitString = _decodeFromBase.vendorIdBitString,
      vendorRangeList = _decodeFromBase.vendorRangeList,
      cmpVersion = _decodeFromBase.cmpVersion,
      consentScreen = _decodeFromBase.consentScreen,
      consentLanguage = _decodeFromBase.consentLanguage;

  var consentStringData = {
    version: version,
    cmpId: cmpId,
    vendorListVersion: vendorListVersion,
    allowedPurposeIds: decodeBitsToIds(purposeIdBitString),
    maxVendorId: maxVendorId,
    created: created,
    lastUpdated: lastUpdated,
    cmpVersion: cmpVersion,
    consentScreen: consentScreen,
    consentLanguage: consentLanguage
  };

  if (isRange) {
    /* eslint no-shadow: off */
    var idMap = vendorRangeList.reduce(function (acc, _ref) {
      var isRange = _ref.isRange,
          startVendorId = _ref.startVendorId,
          endVendorId = _ref.endVendorId;

      var lastVendorId = isRange ? endVendorId : startVendorId;

      for (var i = startVendorId; i <= lastVendorId; i += 1) {
        acc[i] = true;
      }

      return acc;
    }, {});

    consentStringData.allowedVendorIds = [];

    for (var i = 1; i <= maxVendorId; i += 1) {
      if (defaultConsent && !idMap[i] || !defaultConsent && idMap[i]) {
        if (consentStringData.allowedVendorIds.indexOf(i) === -1) {
          consentStringData.allowedVendorIds.push(i);
        }
      }
    }
  } else {
    consentStringData.allowedVendorIds = decodeBitsToIds(vendorIdBitString);
  }

  return consentStringData;
}

module.exports = {
  decodeConsentString: decodeConsentString
};

/***/ }),
/* 9 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";

// CONCATENATED MODULE: ./pmc_gdpr_utils.js
var pmc_gdpr_utils = {
  // RFC4122 complaint UUID
  uuid: function uuid() {
    var uuid = '';
    var i;
    var random;

    for (i = 0; i < 32; i++) {
      random = Math.random() * 16 | 0;

      if (i === 8 || i === 12 || i === 16 || i === 20) {
        uuid += '-';
      }

      uuid += (i === 12 ? 4 : i === 16 ? random & 3 | 8 : random).toString(16);
    }

    return uuid;
  },
  attachHandler: function attachHandler(el, type, f) {
    if (el.addEventListener) {
      el.addEventListener(type, f, false);
    } else if (el.attachEvent) {
      el.attachEvent('on' + type, f);
    }
  },
  generateUUID: function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      // eslint-disable-next-line no-bitwise
      var r = Math.random() * 16 | 0;
      var v = c === 'x' ? r : r & 0x3 | 0x8;
      return v.toString(16);
    });
  },
  getNow: function getNow() {
    return Date.now ? Date.now() : new Date().getTime();
  },
  getXhrOptions: function getXhrOptions(options) {
    return {
      method: options.method || 'POST',
      url: options.url,
      headers: options.headers || {
        'Content-type': 'application/json; charset=utf-8'
      }
    };
  },
  getMeta: function getMeta(options) {
    return {
      id: this.uuid(),
      namespace: options.namespace
    };
  },
  getRequestData: function getRequestData(data, meta) {
    return JSON.stringify({
      payload: data,
      meta: meta
    });
  }
};
/* harmony default export */ var pmc_gdpr_utils_0 = (pmc_gdpr_utils);
// CONCATENATED MODULE: ./CacheManager.js

/**
 * Module that saves data to local storage but expires it afteer a certain
 * period of time, forming a local cache.
 */

var retrieved = {};
var w = window;
/**
 * Set data in the browser local storage.
 * @param {string} name
 * @param {object} data
 * @param {number} expires
 */

function setData(name, data, expires) {
  var cache = {
    'expires': expires,
    'data': data
  };
  retrieved[name] = cache;

  try {
    w.localStorage.setItem('pmc.cache.' + name, JSON.stringify(cache));
  } catch (e) {
    /* Do nothing. */
  }
}
/**
 * Get data from the browser local storage.
 * @param {string} name
 * @returns {object} data
 */


function getData(name) {
  var cached = retrieved[name];

  if (!cached) {
    try {
      cached = JSON.parse(w.localStorage.getItem('frisbee.cache.' + name));
      retrieved[name] = cached;
    } catch (e) {
      /* Do nothing. */
    }
  } // Expires == 0 or null means it never expires.


  if (cached && (!cached.expires || cached.expires > pmc_gdpr_utils_0.getNow())) {
    return cached.data;
  }

  return null;
}
/**
 * Delete data from the browser local storage.
 * @param {string} name
 */


function dropData(name) {
  delete retrieved[name];

  try {
    w.localStorage.removeItem('frisbee.cache.' + name);
  } catch (e) {
    /* Do nothing. */
  }
} // Watch for changes to the cache in other windows, and store the changes
// locally in this window as well so we don't go to the disk if we don't
// have to.


pmc_gdpr_utils_0.attachHandler(w, 'storage', function (e) {
  if (e.key.indexOf('frisbee.cache.') === 0) {
    delete retrieved[e.key];

    try {
      retrieved[e.key] = JSON.parse(e.newValue);
    } catch (e) {
      /* Do nothing. */
    }
  }
});
/* harmony default export */ var CacheManager = ({
  setData: setData,
  getData: getData,
  dropData: dropData
});
// CONCATENATED MODULE: ./QQCReporter.js
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



/**
 * Injects and manages Quantcast Choice.
 */

var QQCReporter_QQCReporter =
/*#__PURE__*/
function () {
  function QQCReporter() {
    _classCallCheck(this, QQCReporter);
  }

  _createClass(QQCReporter, null, [{
    key: "init",

    /**
       * Injects Choice into the page with a config object.
       *
       * @param {object} [cmpConfig]  Default is responsive to header.js
       */
    value: function init() {
      if (!window.__cmp) {
        return;
      }

      var daysBetweenDisplay = 7;
      /**
           * Handles signal that the UI has been clicked.
           */

      var uIClickedCallback = function uIClickedCallback() {
        /**
               *
               * @param {object} data - response from call to cmp getConsentData
               * @param {boolean} success - if true, we have a valid response
               */
        var consentTrackingCallback = function consentTrackingCallback(data, success) {
          if (success) {
            var newEuPubConsent = QQCReporter.getCookie('eupubconsent');
            var newEuConsent = data.consentData;
            var consentId = CacheManager.getData('choiceConsentID'); // If the new data does not match the previous data, the
            // user has changed consent.  Report it.

            if (!consentId || newEuPubConsent !== originalEuPubConsent || newEuConsent !== originalEuConsent) {
              var oneDay = 86400000; // Generate our own random ID for this consent record.

              if (!consentId) {
                consentId = pmc_gdpr_utils_0.generateUUID();
                CacheManager.setData('choiceConsentID', consentId, pmc_gdpr_utils_0.getNow() + oneDay * daysBetweenDisplay * 13);
              }

              CacheManager.setData('euconsent', data.consentData, pmc_gdpr_utils_0.getNow() + oneDay * daysBetweenDisplay);
              QQCReporter.sendConsentDataToFrisbee({
                choiceConsentID: consentId,
                euconsent: newEuConsent,
                eupubconsent: newEuPubConsent
              });
              CmpGate && CmpGate.handleAnalyticsConsent && CmpGate.handleAnalyticsConsent(newEuConsent);
              originalEuConsent = newEuConsent;
              originalEuPubConsent = newEuPubConsent;
            } // Add the

          } // Wait for the click again. The timeout is needed to escape the
          // current callback (otherwise we recurse).


          setTimeout(function () {
            window.__cmp('setConsentUiCallback', uIClickedCallback);
          }, 1);
        }; // Once we have a click signal, ask for the consent data.


        window.__cmp('getConsentData', null, consentTrackingCallback);
      }; // Set a callback for interaction with the UI so we can report to
      // Frisbee.


      window.__cmp('setConsentUiCallback', uIClickedCallback);
    }
    /**
       * Gets a specified cookie. At present first party cookies only.
       *
       * @param {string} cookieName
       * @returns {*}
       */

  }, {
    key: "getCookie",
    value: function getCookie(cookieName) {
      var pattern = RegExp('(?:^|; *)' + cookieName + '=(.[^;]*)');
      var matched = document.cookie.match(pattern);

      if (matched) {
        return matched[1];
      }

      return false;
    }
    /**
       * Record our audit traces in Frisbee.
       *
       * @param {object} quantcastConsentData
       */

  }, {
    key: "sendConsentDataToFrisbee",
    value: function sendConsentDataToFrisbee(quantcastConsentData) {
      var frisbee = new Frisbee({
        'namespace': 'prod-gdpr-stream',
        'url': 'https://collector.sheknows.com/event'
      });
      quantcastConsentData.origin = window.location.origin;
      frisbee.add(quantcastConsentData);
      frisbee.sendAll();
    }
  }]);

  return QQCReporter;
}();


// EXTERNAL MODULE: ./node_modules/consent-string/dist/index.js
var dist = __webpack_require__(0);

// CONCATENATED MODULE: ./export4web.js


/** The ^^^ ConsentString class comes from the IAB. It allows us to decode the consent string to determine
 * what degree of consent the user has granted for data collection
 * "description": "Encode and decode web-safe base64 consent information with the IAB EU's GDPR Transparency and Consent Framework",
 * "homepage": "https://github.com/InteractiveAdvertisingBureau/Consent-String-SDK-JS",
 */

var CmpProcess = {
  checkForNonEUCMP: function checkForNonEUCMP() {
    var euconsent = this.getCookie('eupubconsent');

    if (euconsent) {
      this.handleAnalyticsConsent(euconsent);
    } else {
      // No opt-outs
      this.initAnalytics(true);
    }
  },
  setConsentBodyClass: function setConsentBodyClass() {
    document.body.classList.remove('waitingForCmp');
    document.body.classList.add('hasCmp');
  },
  getCookie: function getCookie(cname) {
    var decodedCookie = decodeURIComponent(document.cookie);
    var cookieSplut = decodedCookie.split('; ');
    var consentValue = '';
    cookieSplut.forEach(function (ca) {
      if (ca.indexOf(cname) === 0) {
        consentValue = ca.split('=')[1];
      }
    });
    return consentValue;
  },
  // These ids are from the IAB vendors list: https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/reference/src/docs/assets/vendorlist.json
  // We need to get Storage consent for cookies.
  // We need to get Measurement consent for GA and omni and hotjar.
  handleAnalyticsConsent: function handleAnalyticsConsent(euconsent) {
    var consentData = new dist["ConsentString"](euconsent);

    if (!consentData) {
      console.error("Error with consent string: {euconsent}");
      return;
    }

    var storageConsent = consentData.isPurposeAllowed(1);
    var measurementConsent = consentData.isPurposeAllowed(5);

    if (measurementConsent) {
      this.initAnalytics(storageConsent);
    }
  },
  modifyConsent: function modifyConsent() {
    // This is called when the Consent UI is closed. This should only fire if
    // the user manually opens the preference screen. May or may not have changed any consent.
    window.__cmp('getConsentData', null, function (consentInfo, success) {
      if (consentInfo.consentData) {
        // we have a consent string. let's parse it.
        var consentData = new dist["ConsentString"](consentInfo.consentData);

        if (!consentData) {
          console.error("Error with consent string: {consentInfo.consentData}");
          return;
        }

        var storageConsent = consentData.isPurposeAllowed(1);
        var measurementConsent = consentData.isPurposeAllowed(5);

        if (!measurementConsent) {
          // try to stop collecting
          window.ga(function () {
            var trackers = window.ga.getAll();
            trackers.forEach(function (tracker) {
              var uid = tracker.b.data.values[':trackingId'];

              if (uid) {
                window['ga-disable-' + uid] = true;
              }
            });
          });
        } else {
          CmpProcess.initAnalytics(storageConsent);
        }
      }
    });
  },
  initAnalytics: function initAnalytics(storageConsent) {
    // One place to call all gated measurements
    window.loadGA && window.loadGA(storageConsent);
  }
}; // Collect for compliance

QQCReporter_QQCReporter.init();

window.__cmp('getConsentData', null, function (consentInfo, success) {
  // Until this callback fires, we are waiting on a consent decision. Therefore,
  // no other tracking/advertising in the browser should happen.
  // If we get consentInfo.consentData, we have a decision made, either this session or previously.
  // Now we process that  string and run with it. This implies we are in the EU
  //
  // If there isn't consentInfo.consentdata value, we're not in the EU. PMC has made a decision
  // to respect all privacy optons, and so should check for a cookie just in case.
  if (consentInfo.consentData) {
    // we have a consent string. let's parse it.
    CmpProcess.handleAnalyticsConsent(consentInfo.consentData);
  } else {
    CmpProcess.checkForNonEUCMP();
  }

  CmpProcess.setConsentBodyClass(); // This  next callback should only fire if the user manually opens the preference screen.
  // The timeout is needed to escape the current callback (otherwise we recurse).

  setTimeout(function () {
    window.__cmp('setConsentUiCallback', CmpProcess.modifyConsent);
  }, 1);
});

/***/ })
/******/ ]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2NvbnNlbnQtc3RyaW5nL2Rpc3QvaW5kZXguanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2NvbnNlbnQtc3RyaW5nL2Rpc3QvY29uc2VudC1zdHJpbmcuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2NvbnNlbnQtc3RyaW5nL2Rpc3QvZW5jb2RlLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9jb25zZW50LXN0cmluZy9kaXN0L3V0aWxzL2JpdHMuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2Jhc2UtNjQvYmFzZTY0LmpzIiwid2VicGFjazovLy8od2VicGFjaykvYnVpbGRpbi9tb2R1bGUuanMiLCJ3ZWJwYWNrOi8vLyh3ZWJwYWNrKS9idWlsZGluL2dsb2JhbC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvY29uc2VudC1zdHJpbmcvZGlzdC91dGlscy9kZWZpbml0aW9ucy5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvY29uc2VudC1zdHJpbmcvZGlzdC9kZWNvZGUuanMiLCJ3ZWJwYWNrOi8vLy4vcG1jX2dkcHJfdXRpbHMuanMiLCJ3ZWJwYWNrOi8vLy4vQ2FjaGVNYW5hZ2VyLmpzIiwid2VicGFjazovLy8uL1FRQ1JlcG9ydGVyLmpzIiwid2VicGFjazovLy8uL2V4cG9ydDR3ZWIuanMiXSwibmFtZXMiOlsicG1jX2dkcHJfdXRpbHMiLCJ1dWlkIiwiaSIsInJhbmRvbSIsIk1hdGgiLCJ0b1N0cmluZyIsImF0dGFjaEhhbmRsZXIiLCJlbCIsInR5cGUiLCJmIiwiYWRkRXZlbnRMaXN0ZW5lciIsImF0dGFjaEV2ZW50IiwiZ2VuZXJhdGVVVUlEIiwicmVwbGFjZSIsImMiLCJyIiwidiIsImdldE5vdyIsIkRhdGUiLCJub3ciLCJnZXRUaW1lIiwiZ2V0WGhyT3B0aW9ucyIsIm9wdGlvbnMiLCJtZXRob2QiLCJ1cmwiLCJoZWFkZXJzIiwiZ2V0TWV0YSIsImlkIiwibmFtZXNwYWNlIiwiZ2V0UmVxdWVzdERhdGEiLCJkYXRhIiwibWV0YSIsIkpTT04iLCJzdHJpbmdpZnkiLCJwYXlsb2FkIiwicmV0cmlldmVkIiwidyIsIndpbmRvdyIsInNldERhdGEiLCJuYW1lIiwiZXhwaXJlcyIsImNhY2hlIiwibG9jYWxTdG9yYWdlIiwic2V0SXRlbSIsImUiLCJnZXREYXRhIiwiY2FjaGVkIiwicGFyc2UiLCJnZXRJdGVtIiwiZHJvcERhdGEiLCJyZW1vdmVJdGVtIiwia2V5IiwiaW5kZXhPZiIsIm5ld1ZhbHVlIiwiUVFDUmVwb3J0ZXIiLCJfX2NtcCIsImRheXNCZXR3ZWVuRGlzcGxheSIsInVJQ2xpY2tlZENhbGxiYWNrIiwiY29uc2VudFRyYWNraW5nQ2FsbGJhY2siLCJzdWNjZXNzIiwibmV3RXVQdWJDb25zZW50IiwiZ2V0Q29va2llIiwibmV3RXVDb25zZW50IiwiY29uc2VudERhdGEiLCJjb25zZW50SWQiLCJDYWNoZU1hbmFnZXIiLCJvcmlnaW5hbEV1UHViQ29uc2VudCIsIm9yaWdpbmFsRXVDb25zZW50Iiwib25lRGF5Iiwic2VuZENvbnNlbnREYXRhVG9GcmlzYmVlIiwiY2hvaWNlQ29uc2VudElEIiwiZXVjb25zZW50IiwiZXVwdWJjb25zZW50IiwiQ21wR2F0ZSIsImhhbmRsZUFuYWx5dGljc0NvbnNlbnQiLCJzZXRUaW1lb3V0IiwiY29va2llTmFtZSIsInBhdHRlcm4iLCJSZWdFeHAiLCJtYXRjaGVkIiwiZG9jdW1lbnQiLCJjb29raWUiLCJtYXRjaCIsInF1YW50Y2FzdENvbnNlbnREYXRhIiwiZnJpc2JlZSIsIkZyaXNiZWUiLCJvcmlnaW4iLCJsb2NhdGlvbiIsImFkZCIsInNlbmRBbGwiLCJDbXBQcm9jZXNzIiwiY2hlY2tGb3JOb25FVUNNUCIsImluaXRBbmFseXRpY3MiLCJzZXRDb25zZW50Qm9keUNsYXNzIiwiYm9keSIsImNsYXNzTGlzdCIsInJlbW92ZSIsImNuYW1lIiwiZGVjb2RlZENvb2tpZSIsImRlY29kZVVSSUNvbXBvbmVudCIsImNvb2tpZVNwbHV0Iiwic3BsaXQiLCJjb25zZW50VmFsdWUiLCJmb3JFYWNoIiwiY2EiLCJDb25zZW50U3RyaW5nIiwiY29uc29sZSIsImVycm9yIiwic3RvcmFnZUNvbnNlbnQiLCJpc1B1cnBvc2VBbGxvd2VkIiwibWVhc3VyZW1lbnRDb25zZW50IiwibW9kaWZ5Q29uc2VudCIsImNvbnNlbnRJbmZvIiwiZ2EiLCJ0cmFja2VycyIsImdldEFsbCIsInRyYWNrZXIiLCJ1aWQiLCJiIiwidmFsdWVzIiwibG9hZEdBIiwiaW5pdCJdLCJtYXBwaW5ncyI6IjtBQUFBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBOzs7QUFHQTtBQUNBOztBQUVBO0FBQ0E7O0FBRUE7QUFDQTtBQUNBO0FBQ0Esa0RBQTBDLGdDQUFnQztBQUMxRTtBQUNBOztBQUVBO0FBQ0E7QUFDQTtBQUNBLGdFQUF3RCxrQkFBa0I7QUFDMUU7QUFDQSx5REFBaUQsY0FBYztBQUMvRDs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsaURBQXlDLGlDQUFpQztBQUMxRSx3SEFBZ0gsbUJBQW1CLEVBQUU7QUFDckk7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQSxtQ0FBMkIsMEJBQTBCLEVBQUU7QUFDdkQseUNBQWlDLGVBQWU7QUFDaEQ7QUFDQTtBQUNBOztBQUVBO0FBQ0EsOERBQXNELCtEQUErRDs7QUFFckg7QUFDQTs7O0FBR0E7QUFDQTs7Ozs7Ozs7QUNsRmE7O0FBRWIsZUFBZSxtQkFBTyxDQUFDLENBQWtCO0FBQ3pDOztBQUVBLGdCQUFnQixtQkFBTyxDQUFDLENBQVU7QUFDbEM7O0FBRUEsZ0JBQWdCLG1CQUFPLENBQUMsQ0FBVTtBQUNsQzs7QUFFQTtBQUNB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QUFNLENBQUNrQixLQUFQLENBQWEsZ0JBQWIsRUFBK0IsSUFBL0IsRUFBcUMsVUFBVXlELFdBQVYsRUFBdUJyRCxPQUF2QixFQUFnQztBQUNuRTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBLE1BQUlxRCxXQUFXLENBQUNqRCxXQUFoQixFQUE2QjtBQUMzQjtBQUNBMkIsY0FBVSxDQUFDaEIsc0JBQVgsQ0FBa0NzQyxXQUFXLENBQUNqRCxXQUE5QztBQUNELEdBSEQsTUFHTztBQUNMMkIsY0FBVSxDQUFDQyxnQkFBWDtBQUNEOztBQUNERCxZQUFVLENBQUNHLG1CQUFYLEdBZm1FLENBZ0JuRTtBQUNBOztBQUNBbEIsWUFBVSxDQUFDLFlBQVk7QUFBRXRDLFVBQU0sQ0FBQ2tCLEtBQVAsQ0FBYSxzQkFBYixFQUFxQ21DLFVBQVUsQ0FBQ3FCLGFBQWhEO0FBQWlFLEdBQWhGLEVBQWtGLENBQWxGLENBQVY7QUFDRCxDQW5CRCxFIiwiZmlsZSI6Ii4uLy4uLy4uL2Fzc2V0cy9qcy9xcWNyZXBvcnRlci5qcyIsInNvdXJjZXNDb250ZW50IjpbIiBcdC8vIFRoZSBtb2R1bGUgY2FjaGVcbiBcdHZhciBpbnN0YWxsZWRNb2R1bGVzID0ge307XG5cbiBcdC8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG4gXHRmdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cbiBcdFx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG4gXHRcdGlmKGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdKSB7XG4gXHRcdFx0cmV0dXJuIGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdLmV4cG9ydHM7XG4gXHRcdH1cbiBcdFx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcbiBcdFx0dmFyIG1vZHVsZSA9IGluc3RhbGxlZE1vZHVsZXNbbW9kdWxlSWRdID0ge1xuIFx0XHRcdGk6IG1vZHVsZUlkLFxuIFx0XHRcdGw6IGZhbHNlLFxuIFx0XHRcdGV4cG9ydHM6IHt9XG4gXHRcdH07XG5cbiBcdFx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG4gXHRcdG1vZHVsZXNbbW9kdWxlSWRdLmNhbGwobW9kdWxlLmV4cG9ydHMsIG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG4gXHRcdC8vIEZsYWcgdGhlIG1vZHVsZSBhcyBsb2FkZWRcbiBcdFx0bW9kdWxlLmwgPSB0cnVlO1xuXG4gXHRcdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG4gXHRcdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbiBcdH1cblxuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZXMgb2JqZWN0IChfX3dlYnBhY2tfbW9kdWxlc19fKVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5tID0gbW9kdWxlcztcblxuIFx0Ly8gZXhwb3NlIHRoZSBtb2R1bGUgY2FjaGVcbiBcdF9fd2VicGFja19yZXF1aXJlX18uYyA9IGluc3RhbGxlZE1vZHVsZXM7XG5cbiBcdC8vIGRlZmluZSBnZXR0ZXIgZnVuY3Rpb24gZm9yIGhhcm1vbnkgZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5kID0gZnVuY3Rpb24oZXhwb3J0cywgbmFtZSwgZ2V0dGVyKSB7XG4gXHRcdGlmKCFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywgbmFtZSkpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgbmFtZSwgeyBlbnVtZXJhYmxlOiB0cnVlLCBnZXQ6IGdldHRlciB9KTtcbiBcdFx0fVxuIFx0fTtcblxuIFx0Ly8gZGVmaW5lIF9fZXNNb2R1bGUgb24gZXhwb3J0c1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5yID0gZnVuY3Rpb24oZXhwb3J0cykge1xuIFx0XHRpZih0eXBlb2YgU3ltYm9sICE9PSAndW5kZWZpbmVkJyAmJiBTeW1ib2wudG9TdHJpbmdUYWcpIHtcbiBcdFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgU3ltYm9sLnRvU3RyaW5nVGFnLCB7IHZhbHVlOiAnTW9kdWxlJyB9KTtcbiBcdFx0fVxuIFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgJ19fZXNNb2R1bGUnLCB7IHZhbHVlOiB0cnVlIH0pO1xuIFx0fTtcblxuIFx0Ly8gY3JlYXRlIGEgZmFrZSBuYW1lc3BhY2Ugb2JqZWN0XG4gXHQvLyBtb2RlICYgMTogdmFsdWUgaXMgYSBtb2R1bGUgaWQsIHJlcXVpcmUgaXRcbiBcdC8vIG1vZGUgJiAyOiBtZXJnZSBhbGwgcHJvcGVydGllcyBvZiB2YWx1ZSBpbnRvIHRoZSBuc1xuIFx0Ly8gbW9kZSAmIDQ6IHJldHVybiB2YWx1ZSB3aGVuIGFscmVhZHkgbnMgb2JqZWN0XG4gXHQvLyBtb2RlICYgOHwxOiBiZWhhdmUgbGlrZSByZXF1aXJlXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnQgPSBmdW5jdGlvbih2YWx1ZSwgbW9kZSkge1xuIFx0XHRpZihtb2RlICYgMSkgdmFsdWUgPSBfX3dlYnBhY2tfcmVxdWlyZV9fKHZhbHVlKTtcbiBcdFx0aWYobW9kZSAmIDgpIHJldHVybiB2YWx1ZTtcbiBcdFx0aWYoKG1vZGUgJiA0KSAmJiB0eXBlb2YgdmFsdWUgPT09ICdvYmplY3QnICYmIHZhbHVlICYmIHZhbHVlLl9fZXNNb2R1bGUpIHJldHVybiB2YWx1ZTtcbiBcdFx0dmFyIG5zID0gT2JqZWN0LmNyZWF0ZShudWxsKTtcbiBcdFx0X193ZWJwYWNrX3JlcXVpcmVfXy5yKG5zKTtcbiBcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KG5zLCAnZGVmYXVsdCcsIHsgZW51bWVyYWJsZTogdHJ1ZSwgdmFsdWU6IHZhbHVlIH0pO1xuIFx0XHRpZihtb2RlICYgMiAmJiB0eXBlb2YgdmFsdWUgIT0gJ3N0cmluZycpIGZvcih2YXIga2V5IGluIHZhbHVlKSBfX3dlYnBhY2tfcmVxdWlyZV9fLmQobnMsIGtleSwgZnVuY3Rpb24oa2V5KSB7IHJldHVybiB2YWx1ZVtrZXldOyB9LmJpbmQobnVsbCwga2V5KSk7XG4gXHRcdHJldHVybiBucztcbiBcdH07XG5cbiBcdC8vIGdldERlZmF1bHRFeHBvcnQgZnVuY3Rpb24gZm9yIGNvbXBhdGliaWxpdHkgd2l0aCBub24taGFybW9ueSBtb2R1bGVzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm4gPSBmdW5jdGlvbihtb2R1bGUpIHtcbiBcdFx0dmFyIGdldHRlciA9IG1vZHVsZSAmJiBtb2R1bGUuX19lc01vZHVsZSA/XG4gXHRcdFx0ZnVuY3Rpb24gZ2V0RGVmYXVsdCgpIHsgcmV0dXJuIG1vZHVsZVsnZGVmYXVsdCddOyB9IDpcbiBcdFx0XHRmdW5jdGlvbiBnZXRNb2R1bGVFeHBvcnRzKCkgeyByZXR1cm4gbW9kdWxlOyB9O1xuIFx0XHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQoZ2V0dGVyLCAnYScsIGdldHRlcik7XG4gXHRcdHJldHVybiBnZXR0ZXI7XG4gXHR9O1xuXG4gXHQvLyBPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGxcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubyA9IGZ1bmN0aW9uKG9iamVjdCwgcHJvcGVydHkpIHsgcmV0dXJuIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmplY3QsIHByb3BlcnR5KTsgfTtcblxuIFx0Ly8gX193ZWJwYWNrX3B1YmxpY19wYXRoX19cbiBcdF9fd2VicGFja19yZXF1aXJlX18ucCA9IFwiXCI7XG5cblxuIFx0Ly8gTG9hZCBlbnRyeSBtb2R1bGUgYW5kIHJldHVybiBleHBvcnRzXG4gXHRyZXR1cm4gX193ZWJwYWNrX3JlcXVpcmVfXyhfX3dlYnBhY2tfcmVxdWlyZV9fLnMgPSA5KTtcbiIsIid1c2Ugc3RyaWN0JztcblxudmFyIF9yZXF1aXJlID0gcmVxdWlyZSgnLi9jb25zZW50LXN0cmluZycpLFxuICAgIENvbnNlbnRTdHJpbmcgPSBfcmVxdWlyZS5Db25zZW50U3RyaW5nO1xuXG52YXIgX3JlcXVpcmUyID0gcmVxdWlyZSgnLi9kZWNvZGUnKSxcbiAgICBkZWNvZGVDb25zZW50U3RyaW5nID0gX3JlcXVpcmUyLmRlY29kZUNvbnNlbnRTdHJpbmc7XG5cbnZhciBfcmVxdWlyZTMgPSByZXF1aXJlKCcuL2VuY29kZScpLFxuICAgIGVuY29kZUNvbnNlbnRTdHJpbmcgPSBfcmVxdWlyZTMuZW5jb2RlQ29uc2VudFN0cmluZztcblxubW9kdWxlLmV4cG9ydHMgPSB7XG4gIENvbnNlbnRTdHJpbmc6IENvbnNlbnRTdHJpbmcsXG4gIGRlY29kZUNvbnNlbnRTdHJpbmc6IGRlY29kZUNvbnNlbnRTdHJpbmcsXG4gIGVuY29kZUNvbnNlbnRTdHJpbmc6IGVuY29kZUNvbnNlbnRTdHJpbmdcbn07IiwiJ3VzZSBzdHJpY3QnO1xuXG52YXIgX3R5cGVvZiA9IHR5cGVvZiBTeW1ib2wgPT09IFwiZnVuY3Rpb25cIiAmJiB0eXBlb2YgU3ltYm9sLml0ZXJhdG9yID09PSBcInN5bWJvbFwiID8gZnVuY3Rpb24gKG9iaikgeyByZXR1cm4gdHlwZW9mIG9iajsgfSA6IGZ1bmN0aW9uIChvYmopIHsgcmV0dXJuIG9iaiAmJiB0eXBlb2YgU3ltYm9sID09PSBcImZ1bmN0aW9uXCIgJiYgb2JqLmNvbnN0cnVjdG9yID09PSBTeW1ib2wgJiYgb2JqICE9PSBTeW1ib2wucHJvdG90eXBlID8gXCJzeW1ib2xcIiA6IHR5cGVvZiBvYmo7IH07XG5cbnZhciBfY3JlYXRlQ2xhc3MgPSBmdW5jdGlvbiAoKSB7IGZ1bmN0aW9uIGRlZmluZVByb3BlcnRpZXModGFyZ2V0LCBwcm9wcykgeyBmb3IgKHZhciBpID0gMDsgaSA8IHByb3BzLmxlbmd0aDsgaSsrKSB7IHZhciBkZXNjcmlwdG9yID0gcHJvcHNbaV07IGRlc2NyaXB0b3IuZW51bWVyYWJsZSA9IGRlc2NyaXB0b3IuZW51bWVyYWJsZSB8fCBmYWxzZTsgZGVzY3JpcHRvci5jb25maWd1cmFibGUgPSB0cnVlOyBpZiAoXCJ2YWx1ZVwiIGluIGRlc2NyaXB0b3IpIGRlc2NyaXB0b3Iud3JpdGFibGUgPSB0cnVlOyBPYmplY3QuZGVmaW5lUHJvcGVydHkodGFyZ2V0LCBkZXNjcmlwdG9yLmtleSwgZGVzY3JpcHRvcik7IH0gfSByZXR1cm4gZnVuY3Rpb24gKENvbnN0cnVjdG9yLCBwcm90b1Byb3BzLCBzdGF0aWNQcm9wcykgeyBpZiAocHJvdG9Qcm9wcykgZGVmaW5lUHJvcGVydGllcyhDb25zdHJ1Y3Rvci5wcm90b3R5cGUsIHByb3RvUHJvcHMpOyBpZiAoc3RhdGljUHJvcHMpIGRlZmluZVByb3BlcnRpZXMoQ29uc3RydWN0b3IsIHN0YXRpY1Byb3BzKTsgcmV0dXJuIENvbnN0cnVjdG9yOyB9OyB9KCk7XG5cbmZ1bmN0aW9uIF9jbGFzc0NhbGxDaGVjayhpbnN0YW5jZSwgQ29uc3RydWN0b3IpIHsgaWYgKCEoaW5zdGFuY2UgaW5zdGFuY2VvZiBDb25zdHJ1Y3RvcikpIHsgdGhyb3cgbmV3IFR5cGVFcnJvcihcIkNhbm5vdCBjYWxsIGEgY2xhc3MgYXMgYSBmdW5jdGlvblwiKTsgfSB9XG5cbnZhciBfcmVxdWlyZSA9IHJlcXVpcmUoJy4vZW5jb2RlJyksXG4gICAgZW5jb2RlQ29uc2VudFN0cmluZyA9IF9yZXF1aXJlLmVuY29kZUNvbnNlbnRTdHJpbmcsXG4gICAgX2dldE1heFZlbmRvcklkID0gX3JlcXVpcmUuZ2V0TWF4VmVuZG9ySWQsXG4gICAgZW5jb2RlVmVuZG9ySWRzVG9CaXRzID0gX3JlcXVpcmUuZW5jb2RlVmVuZG9ySWRzVG9CaXRzLFxuICAgIGVuY29kZVB1cnBvc2VJZHNUb0JpdHMgPSBfcmVxdWlyZS5lbmNvZGVQdXJwb3NlSWRzVG9CaXRzO1xuXG52YXIgX3JlcXVpcmUyID0gcmVxdWlyZSgnLi9kZWNvZGUnKSxcbiAgICBkZWNvZGVDb25zZW50U3RyaW5nID0gX3JlcXVpcmUyLmRlY29kZUNvbnNlbnRTdHJpbmc7XG5cbnZhciBfcmVxdWlyZTMgPSByZXF1aXJlKCcuL3V0aWxzL2RlZmluaXRpb25zJyksXG4gICAgdmVuZG9yVmVyc2lvbk1hcCA9IF9yZXF1aXJlMy52ZW5kb3JWZXJzaW9uTWFwO1xuLyoqXG4gKiBSZWd1bGFyIGV4cHJlc3Npb24gZm9yIHZhbGlkYXRpbmdcbiAqL1xuXG5cbnZhciBjb25zZW50TGFuZ3VhZ2VSZWdleHAgPSAvXlthLXpdezJ9JC87XG5cbnZhciBDb25zZW50U3RyaW5nID0gZnVuY3Rpb24gKCkge1xuICAvKipcbiAgICogSW5pdGlhbGl6ZSBhIG5ldyBDb25zZW50U3RyaW5nIG9iamVjdFxuICAgKlxuICAgKiBAcGFyYW0ge3N0cmluZ30gYmFzZVN0cmluZyBBbiBleGlzdGluZyBjb25zZW50IHN0cmluZyB0byBwYXJzZSBhbmQgdXNlIGZvciBvdXIgaW5pdGlhbCB2YWx1ZXNcbiAgICovXG4gIGZ1bmN0aW9uIENvbnNlbnRTdHJpbmcoKSB7XG4gICAgdmFyIGJhc2VTdHJpbmcgPSBhcmd1bWVudHMubGVuZ3RoID4gMCAmJiBhcmd1bWVudHNbMF0gIT09IHVuZGVmaW5lZCA/IGFyZ3VtZW50c1swXSA6IG51bGw7XG5cbiAgICBfY2xhc3NDYWxsQ2hlY2sodGhpcywgQ29uc2VudFN0cmluZyk7XG5cbiAgICB0aGlzLmNyZWF0ZWQgPSBuZXcgRGF0ZSgpO1xuICAgIHRoaXMubGFzdFVwZGF0ZWQgPSBuZXcgRGF0ZSgpO1xuXG4gICAgLyoqXG4gICAgICogVmVyc2lvbiBudW1iZXIgb2YgY29uc2VudCBzdHJpbmcgc3BlY2lmaWNhdGlvblxuICAgICAqXG4gICAgICogQHR5cGUge2ludGVnZXJ9XG4gICAgICovXG4gICAgdGhpcy52ZXJzaW9uID0gMTtcblxuICAgIC8qKlxuICAgICAqIFZlbmRvciBsaXN0IHdpdGggZm9ybWF0IGZyb20gaHR0cHM6Ly9naXRodWIuY29tL0ludGVyYWN0aXZlQWR2ZXJ0aXNpbmdCdXJlYXUvR0RQUi1UcmFuc3BhcmVuY3ktYW5kLUNvbnNlbnQtRnJhbWV3b3JrL2Jsb2IvbWFzdGVyL0RyYWZ0X2Zvcl9QdWJsaWNfQ29tbWVudF9UcmFuc3BhcmVuY3klMjAlMjYlMjBDb25zZW50JTIwRnJhbWV3b3JrJTIwLSUyMGNvb2tpZSUyMGFuZCUyMHZlbmRvciUyMGxpc3QlMjBmb3JtYXQlMjBzcGVjaWZpY2F0aW9uJTIwdjEuMGEucGRmXG4gICAgICpcbiAgICAgKiBAdHlwZSB7b2JqZWN0fVxuICAgICAqL1xuICAgIHRoaXMudmVuZG9yTGlzdCA9IG51bGw7XG5cbiAgICAvKipcbiAgICAgKiBWZXJzaW9uIG9mIHRoZSB2ZW5kb3IgbGlzdCB1c2VkIGZvciB0aGUgcHVycG9zZXMgYW5kIHZlbmRvcnNcbiAgICAgKlxuICAgICAqIEB0eXBlIHtpbnRlZ2VyfVxuICAgICAqL1xuICAgIHRoaXMudmVuZG9yTGlzdFZlcnNpb24gPSBudWxsO1xuXG4gICAgLyoqXG4gICAgICogVGhlIHVuaXF1ZSBJRCBvZiB0aGUgQ01QIHRoYXQgbGFzdCBtb2RpZmllZCB0aGUgY29uc2VudCBzdHJpbmdcbiAgICAgKlxuICAgICAqIEB0eXBlIHtpbnRlZ2VyfVxuICAgICAqL1xuICAgIHRoaXMuY21wSWQgPSBudWxsO1xuXG4gICAgLyoqXG4gICAgICogVmVyc2lvbiBvZiB0aGUgY29kZSB1c2VkIGJ5IHRoZSBDTVAgd2hlbiBjb2xsZWN0aW5nIGNvbnNlbnRcbiAgICAgKlxuICAgICAqIEB0eXBlIHtpbnRlZ2VyfVxuICAgICAqL1xuICAgIHRoaXMuY21wVmVyc2lvbiA9IG51bGw7XG5cbiAgICAvKipcbiAgICAgKiBJRCBvZiB0aGUgc2NyZWVuIHVzZWQgYnkgQ01QIHdoZW4gY29sbGVjdGluZyBjb25zZW50XG4gICAgICpcbiAgICAgKiBAdHlwZSB7aW50ZWdlcn1cbiAgICAgKi9cbiAgICB0aGlzLmNvbnNlbnRTY3JlZW4gPSBudWxsO1xuXG4gICAgLyoqXG4gICAgICogVHdvLWxldHRlciBJU082MzktMSBjb2RlIChlbiwgZnIsIGRlLCBldGMuKSBvZiB0aGUgbGFuZ3VhZ2UgdGhhdCB0aGUgQ01QIGFza2VkIGNvbnNlbnQgaW5cbiAgICAgKlxuICAgICAqIEB0eXBlIHtzdHJpbmd9XG4gICAgICovXG4gICAgdGhpcy5jb25zZW50TGFuZ3VhZ2UgPSBudWxsO1xuXG4gICAgLyoqXG4gICAgICogTGlzdCBvZiBwdXJwb3NlIElEcyB0aGF0IHRoZSB1c2VyIGhhcyBnaXZlbiBjb25zZW50IHRvXG4gICAgICpcbiAgICAgKiBAdHlwZSB7aW50ZWdlcltdfVxuICAgICAqL1xuICAgIHRoaXMuYWxsb3dlZFB1cnBvc2VJZHMgPSBbXTtcblxuICAgIC8qKlxuICAgICAqIExpc3Qgb2YgdmVuZG9yIElEcyB0aGF0IHRoZSB1c2VyIGhhcyBnaXZlbiBjb25zZW50IHRvXG4gICAgICpcbiAgICAgKiBAdHlwZSB7aW50ZWdlcltdfVxuICAgICAqL1xuICAgIHRoaXMuYWxsb3dlZFZlbmRvcklkcyA9IFtdO1xuXG4gICAgLy8gRGVjb2RlIHRoZSBiYXNlIHN0cmluZ1xuICAgIGlmIChiYXNlU3RyaW5nKSB7XG4gICAgICBPYmplY3QuYXNzaWduKHRoaXMsIGRlY29kZUNvbnNlbnRTdHJpbmcoYmFzZVN0cmluZykpO1xuICAgIH1cbiAgfVxuXG4gIC8qKlxuICAgKiBHZXQgdGhlIHdlYi1zYWZlLCBiYXNlNjQtZW5jb2RlZCBjb25zZW50IHN0cmluZ1xuICAgKlxuICAgKiBAcmV0dXJuIHtzdHJpbmd9IFdlYi1zYWZlLCBiYXNlNjQtZW5jb2RlZCBjb25zZW50IHN0cmluZ1xuICAgKi9cblxuXG4gIF9jcmVhdGVDbGFzcyhDb25zZW50U3RyaW5nLCBbe1xuICAgIGtleTogJ2dldENvbnNlbnRTdHJpbmcnLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBnZXRDb25zZW50U3RyaW5nKCkge1xuICAgICAgdmFyIHVwZGF0ZURhdGUgPSBhcmd1bWVudHMubGVuZ3RoID4gMCAmJiBhcmd1bWVudHNbMF0gIT09IHVuZGVmaW5lZCA/IGFyZ3VtZW50c1swXSA6IHRydWU7XG5cbiAgICAgIGlmICghdGhpcy52ZW5kb3JMaXN0KSB7XG4gICAgICAgIHRocm93IG5ldyBFcnJvcignQ29uc2VudFN0cmluZyAtIEEgdmVuZG9yIGxpc3QgaXMgcmVxdWlyZWQgdG8gZW5jb2RlIGEgY29uc2VudCBzdHJpbmcnKTtcbiAgICAgIH1cblxuICAgICAgaWYgKHVwZGF0ZURhdGUgPT09IHRydWUpIHtcbiAgICAgICAgdGhpcy5sYXN0VXBkYXRlZCA9IG5ldyBEYXRlKCk7XG4gICAgICB9XG5cbiAgICAgIHJldHVybiBlbmNvZGVDb25zZW50U3RyaW5nKHtcbiAgICAgICAgdmVyc2lvbjogdGhpcy5nZXRWZXJzaW9uKCksXG4gICAgICAgIHZlbmRvckxpc3Q6IHRoaXMudmVuZG9yTGlzdCxcbiAgICAgICAgYWxsb3dlZFB1cnBvc2VJZHM6IHRoaXMuYWxsb3dlZFB1cnBvc2VJZHMsXG4gICAgICAgIGFsbG93ZWRWZW5kb3JJZHM6IHRoaXMuYWxsb3dlZFZlbmRvcklkcyxcbiAgICAgICAgY3JlYXRlZDogdGhpcy5jcmVhdGVkLFxuICAgICAgICBsYXN0VXBkYXRlZDogdGhpcy5sYXN0VXBkYXRlZCxcbiAgICAgICAgY21wSWQ6IHRoaXMuY21wSWQsXG4gICAgICAgIGNtcFZlcnNpb246IHRoaXMuY21wVmVyc2lvbixcbiAgICAgICAgY29uc2VudFNjcmVlbjogdGhpcy5jb25zZW50U2NyZWVuLFxuICAgICAgICBjb25zZW50TGFuZ3VhZ2U6IHRoaXMuY29uc2VudExhbmd1YWdlLFxuICAgICAgICB2ZW5kb3JMaXN0VmVyc2lvbjogdGhpcy52ZW5kb3JMaXN0VmVyc2lvblxuICAgICAgfSk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogR2V0IHRoZSBtYXggdmVuZG9ySWRcbiAgICAgKlxuICAgICAqIEByZXR1cm4ge251bWJlcn0gbWF4VmVuZG9ySWQgZnJvbSB0aGUgdmVuZG9yTGlzdCBwcm92aWRlZFxuICAgICAqL1xuXG4gIH0sIHtcbiAgICBrZXk6ICdnZXRNYXhWZW5kb3JJZCcsXG4gICAgdmFsdWU6IGZ1bmN0aW9uIGdldE1heFZlbmRvcklkKCkge1xuICAgICAgcmV0dXJuIF9nZXRNYXhWZW5kb3JJZCh0aGlzLnZlbmRvckxpc3QudmVuZG9ycyk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogZ2V0IHRoZSBjb25zZW50cyBpbiBhIGJpdCBzdHJpbmcuICBUaGlzIGlzIHRvIGZ1bGZpbGwgdGhlIGluLWFwcCByZXF1aXJlbWVudFxuICAgICAqXG4gICAgICogQHJldHVybiB7c3RyaW5nfSBhIGJpdCBzdHJpbmcgb2YgYWxsIG9mIHRoZSB2ZW5kb3IgY29uc2VudCBkYXRhXG4gICAgICovXG5cbiAgfSwge1xuICAgIGtleTogJ2dldFBhcnNlZFZlbmRvckNvbnNlbnRzJyxcbiAgICB2YWx1ZTogZnVuY3Rpb24gZ2V0UGFyc2VkVmVuZG9yQ29uc2VudHMoKSB7XG4gICAgICByZXR1cm4gZW5jb2RlVmVuZG9ySWRzVG9CaXRzKF9nZXRNYXhWZW5kb3JJZCh0aGlzLnZlbmRvckxpc3QudmVuZG9ycyksIHRoaXMuYWxsb3dlZFZlbmRvcklkcyk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogZ2V0IHRoZSBjb25zZW50cyBpbiBhIGJpdCBzdHJpbmcuICBUaGlzIGlzIHRvIGZ1bGZpbGwgdGhlIGluLWFwcCByZXF1aXJlbWVudFxuICAgICAqXG4gICAgICogQHJldHVybiB7c3RyaW5nfSBhIGJpdCBzdHJpbmcgb2YgYWxsIG9mIHRoZSB2ZW5kb3IgY29uc2VudCBkYXRhXG4gICAgICovXG5cbiAgfSwge1xuICAgIGtleTogJ2dldFBhcnNlZFB1cnBvc2VDb25zZW50cycsXG4gICAgdmFsdWU6IGZ1bmN0aW9uIGdldFBhcnNlZFB1cnBvc2VDb25zZW50cygpIHtcbiAgICAgIHJldHVybiBlbmNvZGVQdXJwb3NlSWRzVG9CaXRzKHRoaXMudmVuZG9yTGlzdC5wdXJwb3NlcywgdGhpcy5hbGxvd2VkUHVycG9zZUlkcyk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogR2V0IHRoZSB3ZWItc2FmZSwgYmFzZTY0LWVuY29kZWQgbWV0YWRhdGEgc3RyaW5nXG4gICAgICpcbiAgICAgKiBAcmV0dXJuIHtzdHJpbmd9IFdlYi1zYWZlLCBiYXNlNjQtZW5jb2RlZCBtZXRhZGF0YSBzdHJpbmdcbiAgICAgKi9cblxuICB9LCB7XG4gICAga2V5OiAnZ2V0TWV0YWRhdGFTdHJpbmcnLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBnZXRNZXRhZGF0YVN0cmluZygpIHtcbiAgICAgIHJldHVybiBlbmNvZGVDb25zZW50U3RyaW5nKHtcbiAgICAgICAgdmVyc2lvbjogdGhpcy5nZXRWZXJzaW9uKCksXG4gICAgICAgIGNyZWF0ZWQ6IHRoaXMuY3JlYXRlZCxcbiAgICAgICAgbGFzdFVwZGF0ZWQ6IHRoaXMubGFzdFVwZGF0ZWQsXG4gICAgICAgIGNtcElkOiB0aGlzLmNtcElkLFxuICAgICAgICBjbXBWZXJzaW9uOiB0aGlzLmNtcFZlcnNpb24sXG4gICAgICAgIGNvbnNlbnRTY3JlZW46IHRoaXMuY29uc2VudFNjcmVlbixcbiAgICAgICAgdmVuZG9yTGlzdFZlcnNpb246IHRoaXMudmVuZG9yTGlzdFZlcnNpb25cbiAgICAgIH0pO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIERlY29kZSB0aGUgd2ViLXNhZmUsIGJhc2U2NC1lbmNvZGVkIG1ldGFkYXRhIHN0cmluZ1xuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBlbmNvZGVkTWV0YWRhdGEgV2ViLXNhZmUsIGJhc2U2NC1lbmNvZGVkIG1ldGFkYXRhIHN0cmluZ1xuICAgICAqIEByZXR1cm4ge29iamVjdH0gZGVjb2RlZCBtZXRhZGF0YVxuICAgICAqL1xuXG4gIH0sIHtcbiAgICBrZXk6ICdnZXRWZXJzaW9uJyxcblxuXG4gICAgLyoqXG4gICAgICogR2V0IHRoZSB2ZXJzaW9uIG51bWJlciB0aGF0IHRoaXMgY29uc2VudCBzdHJpbmcgc3BlY2lmaWNhdGlvbiBhZGhlcmVzIHRvXG4gICAgICpcbiAgICAgKiBAcmV0dXJuIHtpbnRlZ2VyfSBWZXJzaW9uIG51bWJlciBvZiBjb25zZW50IHN0cmluZyBzcGVjaWZpY2F0aW9uXG4gICAgICovXG4gICAgdmFsdWU6IGZ1bmN0aW9uIGdldFZlcnNpb24oKSB7XG4gICAgICByZXR1cm4gdGhpcy52ZXJzaW9uO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEdldCB0aGUgdmVyc2lvbiBvZiB0aGUgdmVuZG9yIGxpc3RcbiAgICAgKlxuICAgICAqIEByZXR1cm4ge2ludGVnZXJ9IFZlbmRvciBsaXN0IHZlcnNpb25cbiAgICAgKi9cblxuICB9LCB7XG4gICAga2V5OiAnZ2V0VmVuZG9yTGlzdFZlcnNpb24nLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBnZXRWZW5kb3JMaXN0VmVyc2lvbigpIHtcbiAgICAgIHJldHVybiB0aGlzLnZlbmRvckxpc3RWZXJzaW9uO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIFNldCB0aGUgdmVuZG9ycyBsaXN0IHRvIHVzZSB3aGVuIGdlbmVyYXRpbmcgdGhlIGNvbnNlbnQgc3RyaW5nXG4gICAgICpcbiAgICAgKiBUaGUgZXhwZWN0ZWQgZm9ybWF0IGlzIHRoZSBvbmUgZnJvbSBodHRwczovL2dpdGh1Yi5jb20vSW50ZXJhY3RpdmVBZHZlcnRpc2luZ0J1cmVhdS9HRFBSLVRyYW5zcGFyZW5jeS1hbmQtQ29uc2VudC1GcmFtZXdvcmsvYmxvYi9tYXN0ZXIvRHJhZnRfZm9yX1B1YmxpY19Db21tZW50X1RyYW5zcGFyZW5jeSUyMCUyNiUyMENvbnNlbnQlMjBGcmFtZXdvcmslMjAtJTIwY29va2llJTIwYW5kJTIwdmVuZG9yJTIwbGlzdCUyMGZvcm1hdCUyMHNwZWNpZmljYXRpb24lMjB2MS4wYS5wZGZcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSB2ZW5kb3JMaXN0IFZlbmRvciBsaXN0IHdpdGggZm9ybWF0IGZyb20gaHR0cHM6Ly9naXRodWIuY29tL0ludGVyYWN0aXZlQWR2ZXJ0aXNpbmdCdXJlYXUvR0RQUi1UcmFuc3BhcmVuY3ktYW5kLUNvbnNlbnQtRnJhbWV3b3JrL2Jsb2IvbWFzdGVyL0RyYWZ0X2Zvcl9QdWJsaWNfQ29tbWVudF9UcmFuc3BhcmVuY3klMjAlMjYlMjBDb25zZW50JTIwRnJhbWV3b3JrJTIwLSUyMGNvb2tpZSUyMGFuZCUyMHZlbmRvciUyMGxpc3QlMjBmb3JtYXQlMjBzcGVjaWZpY2F0aW9uJTIwdjEuMGEucGRmXG4gICAgICovXG5cbiAgfSwge1xuICAgIGtleTogJ3NldEdsb2JhbFZlbmRvckxpc3QnLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBzZXRHbG9iYWxWZW5kb3JMaXN0KHZlbmRvckxpc3QpIHtcbiAgICAgIGlmICgodHlwZW9mIHZlbmRvckxpc3QgPT09ICd1bmRlZmluZWQnID8gJ3VuZGVmaW5lZCcgOiBfdHlwZW9mKHZlbmRvckxpc3QpKSAhPT0gJ29iamVjdCcpIHtcbiAgICAgICAgdGhyb3cgbmV3IEVycm9yKCdDb25zZW50U3RyaW5nIC0gWW91IG11c3QgcHJvdmlkZSBhbiBvYmplY3Qgd2hlbiBzZXR0aW5nIHRoZSBnbG9iYWwgdmVuZG9yIGxpc3QnKTtcbiAgICAgIH1cblxuICAgICAgaWYgKCF2ZW5kb3JMaXN0LnZlbmRvckxpc3RWZXJzaW9uIHx8ICFBcnJheS5pc0FycmF5KHZlbmRvckxpc3QucHVycG9zZXMpIHx8ICFBcnJheS5pc0FycmF5KHZlbmRvckxpc3QudmVuZG9ycykpIHtcbiAgICAgICAgLy8gVGhlIHByb3ZpZGVkIHZlbmRvciBsaXN0IGRvZXMgbm90IGxvb2sgdmFsaWRcbiAgICAgICAgdGhyb3cgbmV3IEVycm9yKCdDb25zZW50U3RyaW5nIC0gVGhlIHByb3ZpZGVkIHZlbmRvciBsaXN0IGRvZXMgbm90IHJlc3BlY3QgdGhlIHNjaGVtYSBmcm9tIHRoZSBJQUIgRVXigJlzIEdEUFIgQ29uc2VudCBhbmQgVHJhbnNwYXJlbmN5IEZyYW1ld29yaycpO1xuICAgICAgfVxuXG4gICAgICAvLyBDbG9uaW5nIHRoZSBHVkxcbiAgICAgIC8vIEl0J3MgaW1wb3J0YW50IGFzIHdlIG1pZ2h0IHRyYW5zZm9ybSBpdCBhbmQgZG9uJ3Qgd2FudCB0byBtb2RpZnkgb2JqZWN0cyB0aGF0IHdlIGRvIG5vdCBvd25cbiAgICAgIHRoaXMudmVuZG9yTGlzdCA9IHtcbiAgICAgICAgdmVuZG9yTGlzdFZlcnNpb246IHZlbmRvckxpc3QudmVuZG9yTGlzdFZlcnNpb24sXG4gICAgICAgIGxhc3RVcGRhdGVkOiB2ZW5kb3JMaXN0Lmxhc3RVcGRhdGVkLFxuICAgICAgICBwdXJwb3NlczogdmVuZG9yTGlzdC5wdXJwb3NlcyxcbiAgICAgICAgZmVhdHVyZXM6IHZlbmRvckxpc3QuZmVhdHVyZXMsXG5cbiAgICAgICAgLy8gQ2xvbmUgdGhlIGxpc3QgYW5kIHNvcnQgdGhlIHZlbmRvcnMgYnkgSUQgKGl0IGJyZWFrcyBvdXIgcmFuZ2UgZ2VuZXJhdGlvbiBhbGdvcml0aG0gaWYgdGhleSBhcmUgbm90IHNvcnRlZClcbiAgICAgICAgdmVuZG9yczogdmVuZG9yTGlzdC52ZW5kb3JzLnNsaWNlKDApLnNvcnQoZnVuY3Rpb24gKGZpcnN0VmVuZG9yLCBzZWNvbmRWZW5kb3IpIHtcbiAgICAgICAgICByZXR1cm4gZmlyc3RWZW5kb3IuaWQgPCBzZWNvbmRWZW5kb3IuaWQgPyAtMSA6IDE7XG4gICAgICAgIH0pXG4gICAgICB9O1xuICAgICAgdGhpcy52ZW5kb3JMaXN0VmVyc2lvbiA9IHZlbmRvckxpc3QudmVuZG9yTGlzdFZlcnNpb247XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogU2V0IHRoZSBJRCBvZiB0aGUgQ29uc2VudCBNYW5hZ2VtZW50IFBsYXRmb3JtIHRoYXQgbGFzdCBtb2RpZmllZCB0aGUgY29uc2VudCBzdHJpbmdcbiAgICAgKlxuICAgICAqIEV2ZXJ5IENNUCBpcyBhc3NpZ25lZCBhIHVuaXF1ZSBJRCBieSB0aGUgSUFCIEVVIHRoYXQgbXVzdCBiZSBwcm92aWRlZCBoZXJlIGJlZm9yZSBjaGFuZ2luZyBhbnkgb3RoZXIgdmFsdWUgaW4gdGhlIGNvbnNlbnQgc3RyaW5nLlxuICAgICAqXG4gICAgICogQHBhcmFtIHtpbnRlZ2VyfSBpZCBDTVAgSURcbiAgICAgKi9cblxuICB9LCB7XG4gICAga2V5OiAnc2V0Q21wSWQnLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBzZXRDbXBJZChpZCkge1xuICAgICAgdGhpcy5jbXBJZCA9IGlkO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEdldCB0aGUgSUQgb2YgdGhlIENvbnNlbnQgTWFuYWdlbWVudCBQbGF0Zm9ybSBmcm9tIHRoZSBjb25zZW50IHN0cmluZ1xuICAgICAqXG4gICAgICogQHJldHVybiB7aW50ZWdlcn1cbiAgICAgKi9cblxuICB9LCB7XG4gICAga2V5OiAnZ2V0Q21wSWQnLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBnZXRDbXBJZCgpIHtcbiAgICAgIHJldHVybiB0aGlzLmNtcElkO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIFNldCB0aGUgdmVyc2lvbiBvZiB0aGUgQ29uc2VudCBNYW5hZ2VtZW50IFBsYXRmb3JtIHRoYXQgbGFzdCBtb2RpZmllZCB0aGUgY29uc2VudCBzdHJpbmdcbiAgICAgKlxuICAgICAqIFRoaXMgdmVyc2lvbiBudW1iZXIgcmVmZXJlbmNlcyB0aGUgQ01QIGNvZGUgcnVubmluZyB3aGVuIGNvbGxlY3RpbmcgdGhlIHVzZXIgY29uc2VudC5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7aW50ZWdlcn0gdmVyc2lvbiBWZXJzaW9uXG4gICAgICovXG5cbiAgfSwge1xuICAgIGtleTogJ3NldENtcFZlcnNpb24nLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBzZXRDbXBWZXJzaW9uKHZlcnNpb24pIHtcbiAgICAgIHRoaXMuY21wVmVyc2lvbiA9IHZlcnNpb247XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogR2V0IHRoZSB2ZXJpc29uIG9mIHRoZSBDb25zZW50IE1hbmFnZW1lbnQgUGxhdGZvcm0gdGhhdCBsYXN0IG1vZGlmaWVkIHRoZSBjb25zZW50IHN0cmluZ1xuICAgICAqXG4gICAgICogQHJldHVybiB7aW50ZWdlcn1cbiAgICAgKi9cblxuICB9LCB7XG4gICAga2V5OiAnZ2V0Q21wVmVyc2lvbicsXG4gICAgdmFsdWU6IGZ1bmN0aW9uIGdldENtcFZlcnNpb24oKSB7XG4gICAgICByZXR1cm4gdGhpcy5jbXBWZXJzaW9uO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIFNldCB0aGUgQ29uc2VudCBNYW5hZ2VtZW50IFBsYXRmb3JtIHNjcmVlbiBJRCB0aGF0IGNvbGxlY3RlZCB0aGUgdXNlciBjb25zZW50XG4gICAgICpcbiAgICAgKiBUaGlzIHNjcmVlbiBJRCByZWZlcmVuY2VzIGEgdW5pcXVlIHZpZXcgaW4gdGhlIENNUCB0aGF0IHdhcyBkaXNwbGF5ZWQgdG8gdGhlIHVzZXIgdG8gY29sbGVjdCBjb25zZW50XG4gICAgICpcbiAgICAgKiBAcGFyYW0geyp9IHNjcmVlbklkIFNjcmVlbiBJRFxuICAgICAqL1xuXG4gIH0sIHtcbiAgICBrZXk6ICdzZXRDb25zZW50U2NyZWVuJyxcbiAgICB2YWx1ZTogZnVuY3Rpb24gc2V0Q29uc2VudFNjcmVlbihzY3JlZW5JZCkge1xuICAgICAgdGhpcy5jb25zZW50U2NyZWVuID0gc2NyZWVuSWQ7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogR2V0IHRoZSBDb25zZW50IE1hbmFnZW1lbnQgUGxhdGZvcm0gc2NyZWVuIElEIHRoYXQgY29sbGVjdGVkIHRoZSB1c2VyIGNvbnNlbnRcbiAgICAgKlxuICAgICAqIEByZXR1cm4ge2ludGVnZXJ9XG4gICAgICovXG5cbiAgfSwge1xuICAgIGtleTogJ2dldENvbnNlbnRTY3JlZW4nLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBnZXRDb25zZW50U2NyZWVuKCkge1xuICAgICAgcmV0dXJuIHRoaXMuY29uc2VudFNjcmVlbjtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBTZXQgdGhlIGxhbmd1YWdlIHRoYXQgdGhlIENNUCBhc2tlZCB0aGUgY29uc2VudCBpblxuICAgICAqXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IGxhbmd1YWdlIFR3by1sZXR0ZXIgSVNPNjM5LTEgY29kZSAoZW4sIGZyLCBkZSwgZXRjLilcbiAgICAgKi9cblxuICB9LCB7XG4gICAga2V5OiAnc2V0Q29uc2VudExhbmd1YWdlJyxcbiAgICB2YWx1ZTogZnVuY3Rpb24gc2V0Q29uc2VudExhbmd1YWdlKGxhbmd1YWdlKSB7XG4gICAgICBpZiAoY29uc2VudExhbmd1YWdlUmVnZXhwLnRlc3QobGFuZ3VhZ2UpID09PSBmYWxzZSkge1xuICAgICAgICB0aHJvdyBuZXcgRXJyb3IoJ0NvbnNlbnRTdHJpbmcgLSBUaGUgY29uc2VudCBsYW5ndWFnZSBtdXN0IGJlIGEgdHdvLWxldHRlciBJU082MzktMSBjb2RlIChlbiwgZnIsIGRlLCBldGMuKScpO1xuICAgICAgfVxuXG4gICAgICB0aGlzLmNvbnNlbnRMYW5ndWFnZSA9IGxhbmd1YWdlO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEdldCB0aGUgbGFuZ3VhZ2UgdGhhdCB0aGUgQ01QIGFza2VkIGNvbnNlbnQgaW5cbiAgICAgKlxuICAgICAqIEByZXR1cm4ge3N0cmluZ30gVHdvLWxldHRlciBJU082MzktMSBjb2RlIChlbiwgZnIsIGRlLCBldGMuKVxuICAgICAqL1xuXG4gIH0sIHtcbiAgICBrZXk6ICdnZXRDb25zZW50TGFuZ3VhZ2UnLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBnZXRDb25zZW50TGFuZ3VhZ2UoKSB7XG4gICAgICByZXR1cm4gdGhpcy5jb25zZW50TGFuZ3VhZ2U7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogU2V0IHRoZSBsaXN0IG9mIHB1cnBvc2UgSURzIHRoYXQgdGhlIHVzZXIgaGFzIGdpdmVuIGNvbnNlbnQgdG9cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7aW50ZWdlcltdfSBwdXJwb3NlSWRzIEFuIGFycmF5IG9mIGludGVnZXJzIHRoYXQgbWFwIHRvIHRoZSBwdXJwb3NlcyBkZWZpbmVkIGluIHRoZSB2ZW5kb3IgbGlzdC4gUHVycG9zZXMgaW5jbHVkZWQgaW4gdGhlIGFycmF5IGFyZSBwdXJwb3NlcyB0aGF0IHRoZSB1c2VyIGhhcyBnaXZlbiBjb25zZW50IHRvXG4gICAgICovXG5cbiAgfSwge1xuICAgIGtleTogJ3NldFB1cnBvc2VzQWxsb3dlZCcsXG4gICAgdmFsdWU6IGZ1bmN0aW9uIHNldFB1cnBvc2VzQWxsb3dlZChwdXJwb3NlSWRzKSB7XG4gICAgICB0aGlzLmFsbG93ZWRQdXJwb3NlSWRzID0gcHVycG9zZUlkcztcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBHZXQgdGhlIGxpc3Qgb2YgcHVycG9zZSBJRHMgdGhhdCB0aGUgdXNlciBoYXMgZ2l2ZW4gY29uc2VudCB0b1xuICAgICAqXG4gICAgICogQHJldHVybiB7aW50ZWdlcltdfVxuICAgICAqL1xuXG4gIH0sIHtcbiAgICBrZXk6ICdnZXRQdXJwb3Nlc0FsbG93ZWQnLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBnZXRQdXJwb3Nlc0FsbG93ZWQoKSB7XG4gICAgICByZXR1cm4gdGhpcy5hbGxvd2VkUHVycG9zZUlkcztcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBTZXQgdGhlIGNvbnNlbnQgc3RhdHVzIG9mIGEgdXNlciBmb3IgYSBnaXZlbiBwdXJwb3NlXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge2ludGVnZXJ9IHB1cnBvc2VJZCBUaGUgSUQgKGZyb20gdGhlIHZlbmRvciBsaXN0KSBvZiB0aGUgcHVycG9zZSB0byB1cGRhdGVcbiAgICAgKiBAcGFyYW0ge2Jvb2xlYW59IHZhbHVlIENvbnNlbnQgc3RhdHVzXG4gICAgICovXG5cbiAgfSwge1xuICAgIGtleTogJ3NldFB1cnBvc2VBbGxvd2VkJyxcbiAgICB2YWx1ZTogZnVuY3Rpb24gc2V0UHVycG9zZUFsbG93ZWQocHVycG9zZUlkLCB2YWx1ZSkge1xuICAgICAgdmFyIHB1cnBvc2VJbmRleCA9IHRoaXMuYWxsb3dlZFB1cnBvc2VJZHMuaW5kZXhPZihwdXJwb3NlSWQpO1xuXG4gICAgICBpZiAodmFsdWUgPT09IHRydWUpIHtcbiAgICAgICAgaWYgKHB1cnBvc2VJbmRleCA9PT0gLTEpIHtcbiAgICAgICAgICB0aGlzLmFsbG93ZWRQdXJwb3NlSWRzLnB1c2gocHVycG9zZUlkKTtcbiAgICAgICAgfVxuICAgICAgfSBlbHNlIGlmICh2YWx1ZSA9PT0gZmFsc2UpIHtcbiAgICAgICAgaWYgKHB1cnBvc2VJbmRleCAhPT0gLTEpIHtcbiAgICAgICAgICB0aGlzLmFsbG93ZWRQdXJwb3NlSWRzLnNwbGljZShwdXJwb3NlSW5kZXgsIDEpO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQ2hlY2sgaWYgdGhlIHVzZXIgaGFzIGdpdmVuIGNvbnNlbnQgZm9yIGEgc3BlY2lmaWMgcHVycG9zZVxuICAgICAqXG4gICAgICogQHBhcmFtIHtpbnRlZ2VyfSBwdXJwb3NlSWRcbiAgICAgKlxuICAgICAqIEByZXR1cm4ge2Jvb2xlYW59XG4gICAgICovXG5cbiAgfSwge1xuICAgIGtleTogJ2lzUHVycG9zZUFsbG93ZWQnLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBpc1B1cnBvc2VBbGxvd2VkKHB1cnBvc2VJZCkge1xuICAgICAgcmV0dXJuIHRoaXMuYWxsb3dlZFB1cnBvc2VJZHMuaW5kZXhPZihwdXJwb3NlSWQpICE9PSAtMTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBTZXQgdGhlIGxpc3Qgb2YgdmVuZG9yIElEcyB0aGF0IHRoZSB1c2VyIGhhcyBnaXZlbiBjb25zZW50IHRvXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge2ludGVnZXJbXX0gdmVuZG9ySWRzIEFuIGFycmF5IG9mIGludGVnZXJzIHRoYXQgbWFwIHRvIHRoZSB2ZW5kb3JzIGRlZmluZWQgaW4gdGhlIHZlbmRvciBsaXN0LiBWZW5kb3JzIGluY2x1ZGVkIGluIHRoZSBhcnJheSBhcmUgdmVuZG9ycyB0aGF0IHRoZSB1c2VyIGhhcyBnaXZlbiBjb25zZW50IHRvXG4gICAgICovXG5cbiAgfSwge1xuICAgIGtleTogJ3NldFZlbmRvcnNBbGxvd2VkJyxcbiAgICB2YWx1ZTogZnVuY3Rpb24gc2V0VmVuZG9yc0FsbG93ZWQodmVuZG9ySWRzKSB7XG4gICAgICB0aGlzLmFsbG93ZWRWZW5kb3JJZHMgPSB2ZW5kb3JJZHM7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogR2V0IHRoZSBsaXN0IG9mIHZlbmRvciBJRHMgdGhhdCB0aGUgdXNlciBoYXMgZ2l2ZW4gY29uc2VudCB0b1xuICAgICAqXG4gICAgICogQHJldHVybiB7aW50ZWdlcltdfVxuICAgICAqL1xuXG4gIH0sIHtcbiAgICBrZXk6ICdnZXRWZW5kb3JzQWxsb3dlZCcsXG4gICAgdmFsdWU6IGZ1bmN0aW9uIGdldFZlbmRvcnNBbGxvd2VkKCkge1xuICAgICAgcmV0dXJuIHRoaXMuYWxsb3dlZFZlbmRvcklkcztcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBTZXQgdGhlIGNvbnNlbnQgc3RhdHVzIG9mIGEgdXNlciBmb3IgYSBnaXZlbiB2ZW5kb3JcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7aW50ZWdlcn0gdmVuZG9ySWQgVGhlIElEIChmcm9tIHRoZSB2ZW5kb3IgbGlzdCkgb2YgdGhlIHZlbmRvciB0byB1cGRhdGVcbiAgICAgKiBAcGFyYW0ge2Jvb2xlYW59IHZhbHVlIENvbnNlbnQgc3RhdHVzXG4gICAgICovXG5cbiAgfSwge1xuICAgIGtleTogJ3NldFZlbmRvckFsbG93ZWQnLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBzZXRWZW5kb3JBbGxvd2VkKHZlbmRvcklkLCB2YWx1ZSkge1xuICAgICAgdmFyIHZlbmRvckluZGV4ID0gdGhpcy5hbGxvd2VkVmVuZG9ySWRzLmluZGV4T2YodmVuZG9ySWQpO1xuXG4gICAgICBpZiAodmFsdWUgPT09IHRydWUpIHtcbiAgICAgICAgaWYgKHZlbmRvckluZGV4ID09PSAtMSkge1xuICAgICAgICAgIHRoaXMuYWxsb3dlZFZlbmRvcklkcy5wdXNoKHZlbmRvcklkKTtcbiAgICAgICAgfVxuICAgICAgfSBlbHNlIGlmICh2YWx1ZSA9PT0gZmFsc2UpIHtcbiAgICAgICAgaWYgKHZlbmRvckluZGV4ICE9PSAtMSkge1xuICAgICAgICAgIHRoaXMuYWxsb3dlZFZlbmRvcklkcy5zcGxpY2UodmVuZG9ySW5kZXgsIDEpO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQ2hlY2sgaWYgdGhlIHVzZXIgaGFzIGdpdmVuIGNvbnNlbnQgZm9yIGEgc3BlY2lmaWMgdmVuZG9yXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge2ludGVnZXJ9IHZlbmRvcklkXG4gICAgICpcbiAgICAgKiBAcmV0dXJuIHtib29sZWFufVxuICAgICAqL1xuXG4gIH0sIHtcbiAgICBrZXk6ICdpc1ZlbmRvckFsbG93ZWQnLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBpc1ZlbmRvckFsbG93ZWQodmVuZG9ySWQpIHtcbiAgICAgIHJldHVybiB0aGlzLmFsbG93ZWRWZW5kb3JJZHMuaW5kZXhPZih2ZW5kb3JJZCkgIT09IC0xO1xuICAgIH1cbiAgfV0sIFt7XG4gICAga2V5OiAnZGVjb2RlTWV0YWRhdGFTdHJpbmcnLFxuICAgIHZhbHVlOiBmdW5jdGlvbiBkZWNvZGVNZXRhZGF0YVN0cmluZyhlbmNvZGVkTWV0YWRhdGEpIHtcbiAgICAgIHZhciBkZWNvZGVkU3RyaW5nID0gZGVjb2RlQ29uc2VudFN0cmluZyhlbmNvZGVkTWV0YWRhdGEpO1xuICAgICAgdmFyIG1ldGFkYXRhID0ge307XG4gICAgICB2ZW5kb3JWZXJzaW9uTWFwW2RlY29kZWRTdHJpbmcudmVyc2lvbl0ubWV0YWRhdGFGaWVsZHMuZm9yRWFjaChmdW5jdGlvbiAoZmllbGQpIHtcbiAgICAgICAgbWV0YWRhdGFbZmllbGRdID0gZGVjb2RlZFN0cmluZ1tmaWVsZF07XG4gICAgICB9KTtcbiAgICAgIHJldHVybiBtZXRhZGF0YTtcbiAgICB9XG4gIH1dKTtcblxuICByZXR1cm4gQ29uc2VudFN0cmluZztcbn0oKTtcblxubW9kdWxlLmV4cG9ydHMgPSB7XG4gIENvbnNlbnRTdHJpbmc6IENvbnNlbnRTdHJpbmdcbn07IiwiJ3VzZSBzdHJpY3QnO1xuXG52YXIgX2V4dGVuZHMgPSBPYmplY3QuYXNzaWduIHx8IGZ1bmN0aW9uICh0YXJnZXQpIHsgZm9yICh2YXIgaSA9IDE7IGkgPCBhcmd1bWVudHMubGVuZ3RoOyBpKyspIHsgdmFyIHNvdXJjZSA9IGFyZ3VtZW50c1tpXTsgZm9yICh2YXIga2V5IGluIHNvdXJjZSkgeyBpZiAoT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKHNvdXJjZSwga2V5KSkgeyB0YXJnZXRba2V5XSA9IHNvdXJjZVtrZXldOyB9IH0gfSByZXR1cm4gdGFyZ2V0OyB9O1xuXG52YXIgX3JlcXVpcmUgPSByZXF1aXJlKCcuL3V0aWxzL2JpdHMnKSxcbiAgICBlbmNvZGVUb0Jhc2U2NCA9IF9yZXF1aXJlLmVuY29kZVRvQmFzZTY0LFxuICAgIHBhZFJpZ2h0ID0gX3JlcXVpcmUucGFkUmlnaHQ7XG5cbi8qKlxuICogRW5jb2RlIGEgbGlzdCBvZiB2ZW5kb3IgSURzIGludG8gYml0c1xuICpcbiAqIEBwYXJhbSB7aW50ZWdlcn0gbWF4VmVuZG9ySWQgSGlnaGVzdCB2ZW5kb3IgSUQgaW4gdGhlIHZlbmRvciBsaXN0XG4gKiBAcGFyYW0ge2ludGVnZXJbXX0gYWxsb3dlZFZlbmRvcklkcyBWZW5kb3JzIHRoYXQgdGhlIHVzZXIgaGFzIGdpdmVuIGNvbnNlbnQgdG9cbiAqL1xuXG5cbmZ1bmN0aW9uIGVuY29kZVZlbmRvcklkc1RvQml0cyhtYXhWZW5kb3JJZCkge1xuICB2YXIgYWxsb3dlZFZlbmRvcklkcyA9IGFyZ3VtZW50cy5sZW5ndGggPiAxICYmIGFyZ3VtZW50c1sxXSAhPT0gdW5kZWZpbmVkID8gYXJndW1lbnRzWzFdIDogW107XG5cbiAgdmFyIHZlbmRvclN0cmluZyA9ICcnO1xuXG4gIGZvciAodmFyIGlkID0gMTsgaWQgPD0gbWF4VmVuZG9ySWQ7IGlkICs9IDEpIHtcbiAgICB2ZW5kb3JTdHJpbmcgKz0gYWxsb3dlZFZlbmRvcklkcy5pbmRleE9mKGlkKSAhPT0gLTEgPyAnMScgOiAnMCc7XG4gIH1cblxuICByZXR1cm4gcGFkUmlnaHQodmVuZG9yU3RyaW5nLCBNYXRoLm1heCgwLCBtYXhWZW5kb3JJZCAtIHZlbmRvclN0cmluZy5sZW5ndGgpKTtcbn1cblxuLyoqXG4gKiBFbmNvZGUgYSBsaXN0IG9mIHB1cnBvc2UgSURzIGludG8gYml0c1xuICpcbiAqIEBwYXJhbSB7Kn0gcHVycG9zZXMgTGlzdCBvZiBwdXJwb3NlcyBmcm9tIHRoZSB2ZW5kb3IgbGlzdFxuICogQHBhcmFtIHsqfSBhbGxvd2VkUHVycG9zZUlkcyBMaXN0IG9mIHB1cnBvc2UgSURzIHRoYXQgdGhlIHVzZXIgaGFzIGdpdmVuIGNvbnNlbnQgdG9cbiAqL1xuZnVuY3Rpb24gZW5jb2RlUHVycG9zZUlkc1RvQml0cyhwdXJwb3Nlcykge1xuICB2YXIgYWxsb3dlZFB1cnBvc2VJZHMgPSBhcmd1bWVudHMubGVuZ3RoID4gMSAmJiBhcmd1bWVudHNbMV0gIT09IHVuZGVmaW5lZCA/IGFyZ3VtZW50c1sxXSA6IG5ldyBTZXQoKTtcblxuICB2YXIgbWF4UHVycG9zZUlkID0gMDtcbiAgZm9yICh2YXIgaSA9IDA7IGkgPCBwdXJwb3Nlcy5sZW5ndGg7IGkgKz0gMSkge1xuICAgIG1heFB1cnBvc2VJZCA9IE1hdGgubWF4KG1heFB1cnBvc2VJZCwgcHVycG9zZXNbaV0uaWQpO1xuICB9XG4gIGZvciAodmFyIF9pID0gMDsgX2kgPCBhbGxvd2VkUHVycG9zZUlkcy5sZW5ndGg7IF9pICs9IDEpIHtcbiAgICBtYXhQdXJwb3NlSWQgPSBNYXRoLm1heChtYXhQdXJwb3NlSWQsIGFsbG93ZWRQdXJwb3NlSWRzW19pXSk7XG4gIH1cblxuICB2YXIgcHVycG9zZVN0cmluZyA9ICcnO1xuICBmb3IgKHZhciBpZCA9IDE7IGlkIDw9IG1heFB1cnBvc2VJZDsgaWQgKz0gMSkge1xuICAgIHB1cnBvc2VTdHJpbmcgKz0gYWxsb3dlZFB1cnBvc2VJZHMuaW5kZXhPZihpZCkgIT09IC0xID8gJzEnIDogJzAnO1xuICB9XG5cbiAgcmV0dXJuIHB1cnBvc2VTdHJpbmc7XG59XG5cbi8qKlxuICogQ29udmVydCBhIGxpc3Qgb2YgdmVuZG9yIElEcyB0byByYW5nZXNcbiAqXG4gKiBAcGFyYW0ge29iamVjdFtdfSB2ZW5kb3JzIExpc3Qgb2YgdmVuZG9ycyBmcm9tIHRoZSB2ZW5kb3IgbGlzdCAoaW1wb3J0YW50OiB0aGlzIGxpc3QgbXVzdCB0byBiZSBzb3J0ZWQgYnkgSUQpXG4gKiBAcGFyYW0ge2ludGVnZXJbXX0gYWxsb3dlZFZlbmRvcklkcyBMaXN0IG9mIHZlbmRvciBJRHMgdGhhdCB0aGUgdXNlciBoYXMgZ2l2ZW4gY29uc2VudCB0b1xuICovXG5mdW5jdGlvbiBjb252ZXJ0VmVuZG9yc1RvUmFuZ2VzKHZlbmRvcnMsIGFsbG93ZWRWZW5kb3JJZHMpIHtcbiAgdmFyIHJhbmdlID0gW107XG4gIHZhciByYW5nZXMgPSBbXTtcblxuICB2YXIgaWRzSW5MaXN0ID0gdmVuZG9ycy5tYXAoZnVuY3Rpb24gKHZlbmRvcikge1xuICAgIHJldHVybiB2ZW5kb3IuaWQ7XG4gIH0pO1xuXG4gIGZvciAodmFyIGluZGV4ID0gMDsgaW5kZXggPCB2ZW5kb3JzLmxlbmd0aDsgaW5kZXggKz0gMSkge1xuICAgIHZhciBpZCA9IHZlbmRvcnNbaW5kZXhdLmlkO1xuXG4gICAgaWYgKGFsbG93ZWRWZW5kb3JJZHMuaW5kZXhPZihpZCkgIT09IC0xKSB7XG4gICAgICByYW5nZS5wdXNoKGlkKTtcbiAgICB9XG5cbiAgICAvLyBEbyB3ZSBuZWVkIHRvIGNsb3NlIHRoZSBjdXJyZW50IHJhbmdlP1xuICAgIGlmICgoYWxsb3dlZFZlbmRvcklkcy5pbmRleE9mKGlkKSA9PT0gLTEgLy8gVGhlIHZlbmRvciB3ZSBhcmUgZXZhbHVhdGluZyBpcyBub3QgYWxsb3dlZFxuICAgIHx8IGluZGV4ID09PSB2ZW5kb3JzLmxlbmd0aCAtIDEgLy8gVGhlcmUgaXMgbm8gbW9yZSB2ZW5kb3IgdG8gZXZhbHVhdGVcbiAgICB8fCBpZHNJbkxpc3QuaW5kZXhPZihpZCArIDEpID09PSAtMSAvLyBUaGVyZSBpcyBubyB2ZW5kb3IgYWZ0ZXIgdGhpcyBvbmUgKGllIHRoZXJlIGlzIGEgZ2FwIGluIHRoZSB2ZW5kb3IgSURzKSA7IHdlIG5lZWQgdG8gc3RvcCBoZXJlIHRvIGF2b2lkIGluY2x1ZGluZyB2ZW5kb3JzIHRoYXQgZG8gbm90IGhhdmUgY29uc2VudFxuICAgICkgJiYgcmFuZ2UubGVuZ3RoKSB7XG4gICAgICB2YXIgc3RhcnRWZW5kb3JJZCA9IHJhbmdlLnNoaWZ0KCk7XG4gICAgICB2YXIgZW5kVmVuZG9ySWQgPSByYW5nZS5wb3AoKTtcblxuICAgICAgcmFuZ2UgPSBbXTtcblxuICAgICAgcmFuZ2VzLnB1c2goe1xuICAgICAgICBpc1JhbmdlOiB0eXBlb2YgZW5kVmVuZG9ySWQgPT09ICdudW1iZXInLFxuICAgICAgICBzdGFydFZlbmRvcklkOiBzdGFydFZlbmRvcklkLFxuICAgICAgICBlbmRWZW5kb3JJZDogZW5kVmVuZG9ySWRcbiAgICAgIH0pO1xuICAgIH1cbiAgfVxuXG4gIHJldHVybiByYW5nZXM7XG59XG5cbi8qKlxuICogR2V0IG1heFZlbmRvcklkIGZyb20gdGhlIGxpc3Qgb2YgdmVuZG9ycyBhbmQgcmV0dXJuIHRoYXQgaWRcbiAqXG4gKiBAcGFyYW0ge29iamVjdH0gdmVuZG9yc1xuICovXG5mdW5jdGlvbiBnZXRNYXhWZW5kb3JJZCh2ZW5kb3JzKSB7XG4gIC8vIEZpbmQgdGhlIG1heCB2ZW5kb3IgSUQgZnJvbSB0aGUgdmVuZG9yIGxpc3RcbiAgdmFyIG1heFZlbmRvcklkID0gMDtcblxuICB2ZW5kb3JzLmZvckVhY2goZnVuY3Rpb24gKHZlbmRvcikge1xuICAgIGlmICh2ZW5kb3IuaWQgPiBtYXhWZW5kb3JJZCkge1xuICAgICAgbWF4VmVuZG9ySWQgPSB2ZW5kb3IuaWQ7XG4gICAgfVxuICB9KTtcbiAgcmV0dXJuIG1heFZlbmRvcklkO1xufVxuLyoqXG4gKiBFbmNvZGUgY29uc2VudCBkYXRhIGludG8gYSB3ZWItc2FmZSBiYXNlNjQtZW5jb2RlZCBzdHJpbmdcbiAqXG4gKiBAcGFyYW0ge29iamVjdH0gY29uc2VudERhdGEgRGF0YSB0byBpbmNsdWRlIGluIHRoZSBzdHJpbmcgKHNlZSBgdXRpbHMvZGVmaW5pdGlvbnMuanNgIGZvciB0aGUgbGlzdCBvZiBmaWVsZHMpXG4gKi9cbmZ1bmN0aW9uIGVuY29kZUNvbnNlbnRTdHJpbmcoY29uc2VudERhdGEpIHtcbiAgdmFyIG1heFZlbmRvcklkID0gY29uc2VudERhdGEubWF4VmVuZG9ySWQ7XG4gIHZhciBfY29uc2VudERhdGEkdmVuZG9yTGkgPSBjb25zZW50RGF0YS52ZW5kb3JMaXN0LFxuICAgICAgdmVuZG9yTGlzdCA9IF9jb25zZW50RGF0YSR2ZW5kb3JMaSA9PT0gdW5kZWZpbmVkID8ge30gOiBfY29uc2VudERhdGEkdmVuZG9yTGksXG4gICAgICBhbGxvd2VkUHVycG9zZUlkcyA9IGNvbnNlbnREYXRhLmFsbG93ZWRQdXJwb3NlSWRzLFxuICAgICAgYWxsb3dlZFZlbmRvcklkcyA9IGNvbnNlbnREYXRhLmFsbG93ZWRWZW5kb3JJZHM7XG4gIHZhciBfdmVuZG9yTGlzdCR2ZW5kb3JzID0gdmVuZG9yTGlzdC52ZW5kb3JzLFxuICAgICAgdmVuZG9ycyA9IF92ZW5kb3JMaXN0JHZlbmRvcnMgPT09IHVuZGVmaW5lZCA/IFtdIDogX3ZlbmRvckxpc3QkdmVuZG9ycyxcbiAgICAgIF92ZW5kb3JMaXN0JHB1cnBvc2VzID0gdmVuZG9yTGlzdC5wdXJwb3NlcyxcbiAgICAgIHB1cnBvc2VzID0gX3ZlbmRvckxpc3QkcHVycG9zZXMgPT09IHVuZGVmaW5lZCA/IFtdIDogX3ZlbmRvckxpc3QkcHVycG9zZXM7XG5cbiAgLy8gaWYgbm8gbWF4VmVuZG9ySWQgaXMgaW4gdGhlIENvbnNlbnREYXRhLCBnZXQgaXRcblxuICBpZiAoIW1heFZlbmRvcklkKSB7XG4gICAgbWF4VmVuZG9ySWQgPSBnZXRNYXhWZW5kb3JJZCh2ZW5kb3JzKTtcbiAgfVxuXG4gIC8vIEVuY29kZSB0aGUgZGF0YSB3aXRoIGFuZCB3aXRob3V0IHJhbmdlcyBhbmQgcmV0dXJuIHRoZSBzbWFsbGVzdCBlbmNvZGVkIHBheWxvYWRcbiAgdmFyIG5vUmFuZ2VzRGF0YSA9IGVuY29kZVRvQmFzZTY0KF9leHRlbmRzKHt9LCBjb25zZW50RGF0YSwge1xuICAgIG1heFZlbmRvcklkOiBtYXhWZW5kb3JJZCxcbiAgICBwdXJwb3NlSWRCaXRTdHJpbmc6IGVuY29kZVB1cnBvc2VJZHNUb0JpdHMocHVycG9zZXMsIGFsbG93ZWRQdXJwb3NlSWRzKSxcbiAgICBpc1JhbmdlOiBmYWxzZSxcbiAgICB2ZW5kb3JJZEJpdFN0cmluZzogZW5jb2RlVmVuZG9ySWRzVG9CaXRzKG1heFZlbmRvcklkLCBhbGxvd2VkVmVuZG9ySWRzKVxuICB9KSk7XG5cbiAgdmFyIHZlbmRvclJhbmdlTGlzdCA9IGNvbnZlcnRWZW5kb3JzVG9SYW5nZXModmVuZG9ycywgYWxsb3dlZFZlbmRvcklkcyk7XG5cbiAgdmFyIHJhbmdlc0RhdGEgPSBlbmNvZGVUb0Jhc2U2NChfZXh0ZW5kcyh7fSwgY29uc2VudERhdGEsIHtcbiAgICBtYXhWZW5kb3JJZDogbWF4VmVuZG9ySWQsXG4gICAgcHVycG9zZUlkQml0U3RyaW5nOiBlbmNvZGVQdXJwb3NlSWRzVG9CaXRzKHB1cnBvc2VzLCBhbGxvd2VkUHVycG9zZUlkcyksXG4gICAgaXNSYW5nZTogdHJ1ZSxcbiAgICBkZWZhdWx0Q29uc2VudDogZmFsc2UsXG4gICAgbnVtRW50cmllczogdmVuZG9yUmFuZ2VMaXN0Lmxlbmd0aCxcbiAgICB2ZW5kb3JSYW5nZUxpc3Q6IHZlbmRvclJhbmdlTGlzdFxuICB9KSk7XG5cbiAgcmV0dXJuIG5vUmFuZ2VzRGF0YS5sZW5ndGggPCByYW5nZXNEYXRhLmxlbmd0aCA/IG5vUmFuZ2VzRGF0YSA6IHJhbmdlc0RhdGE7XG59XG5cbm1vZHVsZS5leHBvcnRzID0ge1xuICBjb252ZXJ0VmVuZG9yc1RvUmFuZ2VzOiBjb252ZXJ0VmVuZG9yc1RvUmFuZ2VzLFxuICBlbmNvZGVDb25zZW50U3RyaW5nOiBlbmNvZGVDb25zZW50U3RyaW5nLFxuICBnZXRNYXhWZW5kb3JJZDogZ2V0TWF4VmVuZG9ySWQsXG4gIGVuY29kZVZlbmRvcklkc1RvQml0czogZW5jb2RlVmVuZG9ySWRzVG9CaXRzLFxuICBlbmNvZGVQdXJwb3NlSWRzVG9CaXRzOiBlbmNvZGVQdXJwb3NlSWRzVG9CaXRzXG59OyIsIid1c2Ugc3RyaWN0JztcblxuLyogZXNsaW50IG5vLXVzZS1iZWZvcmUtZGVmaW5lOiBvZmYgKi9cblxudmFyIGJhc2U2NCA9IHJlcXVpcmUoJ2Jhc2UtNjQnKTtcblxudmFyIF9yZXF1aXJlID0gcmVxdWlyZSgnLi9kZWZpbml0aW9ucycpLFxuICAgIHZlcnNpb25OdW1CaXRzID0gX3JlcXVpcmUudmVyc2lvbk51bUJpdHMsXG4gICAgdmVuZG9yVmVyc2lvbk1hcCA9IF9yZXF1aXJlLnZlbmRvclZlcnNpb25NYXA7XG5cbmZ1bmN0aW9uIHJlcGVhdChjb3VudCkge1xuICB2YXIgc3RyaW5nID0gYXJndW1lbnRzLmxlbmd0aCA+IDEgJiYgYXJndW1lbnRzWzFdICE9PSB1bmRlZmluZWQgPyBhcmd1bWVudHNbMV0gOiAnMCc7XG5cbiAgdmFyIHBhZFN0cmluZyA9ICcnO1xuXG4gIGZvciAodmFyIGkgPSAwOyBpIDwgY291bnQ7IGkgKz0gMSkge1xuICAgIHBhZFN0cmluZyArPSBzdHJpbmc7XG4gIH1cblxuICByZXR1cm4gcGFkU3RyaW5nO1xufVxuXG5mdW5jdGlvbiBwYWRMZWZ0KHN0cmluZywgcGFkZGluZykge1xuICByZXR1cm4gcmVwZWF0KE1hdGgubWF4KDAsIHBhZGRpbmcpKSArIHN0cmluZztcbn1cblxuZnVuY3Rpb24gcGFkUmlnaHQoc3RyaW5nLCBwYWRkaW5nKSB7XG4gIHJldHVybiBzdHJpbmcgKyByZXBlYXQoTWF0aC5tYXgoMCwgcGFkZGluZykpO1xufVxuXG5mdW5jdGlvbiBlbmNvZGVJbnRUb0JpdHMobnVtYmVyLCBudW1CaXRzKSB7XG4gIHZhciBiaXRTdHJpbmcgPSAnJztcblxuICBpZiAodHlwZW9mIG51bWJlciA9PT0gJ251bWJlcicgJiYgIWlzTmFOKG51bWJlcikpIHtcbiAgICBiaXRTdHJpbmcgPSBwYXJzZUludChudW1iZXIsIDEwKS50b1N0cmluZygyKTtcbiAgfVxuXG4gIC8vIFBhZCB0aGUgc3RyaW5nIGlmIG5vdCBmaWxsaW5nIGFsbCBiaXRzXG4gIGlmIChudW1CaXRzID49IGJpdFN0cmluZy5sZW5ndGgpIHtcbiAgICBiaXRTdHJpbmcgPSBwYWRMZWZ0KGJpdFN0cmluZywgbnVtQml0cyAtIGJpdFN0cmluZy5sZW5ndGgpO1xuICB9XG5cbiAgLy8gVHJ1bmNhdGUgdGhlIHN0cmluZyBpZiBsb25nZXIgdGhhbiB0aGUgbnVtYmVyIG9mIGJpdHNcbiAgaWYgKGJpdFN0cmluZy5sZW5ndGggPiBudW1CaXRzKSB7XG4gICAgYml0U3RyaW5nID0gYml0U3RyaW5nLnN1YnN0cmluZygwLCBudW1CaXRzKTtcbiAgfVxuXG4gIHJldHVybiBiaXRTdHJpbmc7XG59XG5cbmZ1bmN0aW9uIGVuY29kZUJvb2xUb0JpdHModmFsdWUpIHtcbiAgcmV0dXJuIGVuY29kZUludFRvQml0cyh2YWx1ZSA9PT0gdHJ1ZSA/IDEgOiAwLCAxKTtcbn1cblxuZnVuY3Rpb24gZW5jb2RlRGF0ZVRvQml0cyhkYXRlLCBudW1CaXRzKSB7XG4gIGlmIChkYXRlIGluc3RhbmNlb2YgRGF0ZSkge1xuICAgIHJldHVybiBlbmNvZGVJbnRUb0JpdHMoZGF0ZS5nZXRUaW1lKCkgLyAxMDAsIG51bUJpdHMpO1xuICB9XG4gIHJldHVybiBlbmNvZGVJbnRUb0JpdHMoZGF0ZSwgbnVtQml0cyk7XG59XG5cbmZ1bmN0aW9uIGVuY29kZUxldHRlclRvQml0cyhsZXR0ZXIsIG51bUJpdHMpIHtcbiAgcmV0dXJuIGVuY29kZUludFRvQml0cyhsZXR0ZXIudG9VcHBlckNhc2UoKS5jaGFyQ29kZUF0KDApIC0gNjUsIG51bUJpdHMpO1xufVxuXG5mdW5jdGlvbiBlbmNvZGVMYW5ndWFnZVRvQml0cyhsYW5ndWFnZSkge1xuICB2YXIgbnVtQml0cyA9IGFyZ3VtZW50cy5sZW5ndGggPiAxICYmIGFyZ3VtZW50c1sxXSAhPT0gdW5kZWZpbmVkID8gYXJndW1lbnRzWzFdIDogMTI7XG5cbiAgcmV0dXJuIGVuY29kZUxldHRlclRvQml0cyhsYW5ndWFnZS5zbGljZSgwLCAxKSwgbnVtQml0cyAvIDIpICsgZW5jb2RlTGV0dGVyVG9CaXRzKGxhbmd1YWdlLnNsaWNlKDEpLCBudW1CaXRzIC8gMik7XG59XG5cbmZ1bmN0aW9uIGRlY29kZUJpdHNUb0ludChiaXRTdHJpbmcsIHN0YXJ0LCBsZW5ndGgpIHtcbiAgcmV0dXJuIHBhcnNlSW50KGJpdFN0cmluZy5zdWJzdHIoc3RhcnQsIGxlbmd0aCksIDIpO1xufVxuXG5mdW5jdGlvbiBkZWNvZGVCaXRzVG9EYXRlKGJpdFN0cmluZywgc3RhcnQsIGxlbmd0aCkge1xuICByZXR1cm4gbmV3IERhdGUoZGVjb2RlQml0c1RvSW50KGJpdFN0cmluZywgc3RhcnQsIGxlbmd0aCkgKiAxMDApO1xufVxuXG5mdW5jdGlvbiBkZWNvZGVCaXRzVG9Cb29sKGJpdFN0cmluZywgc3RhcnQpIHtcbiAgcmV0dXJuIHBhcnNlSW50KGJpdFN0cmluZy5zdWJzdHIoc3RhcnQsIDEpLCAyKSA9PT0gMTtcbn1cblxuZnVuY3Rpb24gZGVjb2RlQml0c1RvTGV0dGVyKGJpdFN0cmluZykge1xuICB2YXIgbGV0dGVyQ29kZSA9IGRlY29kZUJpdHNUb0ludChiaXRTdHJpbmcpO1xuICByZXR1cm4gU3RyaW5nLmZyb21DaGFyQ29kZShsZXR0ZXJDb2RlICsgNjUpLnRvTG93ZXJDYXNlKCk7XG59XG5cbmZ1bmN0aW9uIGRlY29kZUJpdHNUb0xhbmd1YWdlKGJpdFN0cmluZywgc3RhcnQsIGxlbmd0aCkge1xuICB2YXIgbGFuZ3VhZ2VCaXRTdHJpbmcgPSBiaXRTdHJpbmcuc3Vic3RyKHN0YXJ0LCBsZW5ndGgpO1xuXG4gIHJldHVybiBkZWNvZGVCaXRzVG9MZXR0ZXIobGFuZ3VhZ2VCaXRTdHJpbmcuc2xpY2UoMCwgbGVuZ3RoIC8gMikpICsgZGVjb2RlQml0c1RvTGV0dGVyKGxhbmd1YWdlQml0U3RyaW5nLnNsaWNlKGxlbmd0aCAvIDIpKTtcbn1cblxuZnVuY3Rpb24gZW5jb2RlRmllbGQoX3JlZikge1xuICB2YXIgaW5wdXQgPSBfcmVmLmlucHV0LFxuICAgICAgZmllbGQgPSBfcmVmLmZpZWxkO1xuICB2YXIgbmFtZSA9IGZpZWxkLm5hbWUsXG4gICAgICB0eXBlID0gZmllbGQudHlwZSxcbiAgICAgIG51bUJpdHMgPSBmaWVsZC5udW1CaXRzLFxuICAgICAgZW5jb2RlciA9IGZpZWxkLmVuY29kZXIsXG4gICAgICB2YWxpZGF0b3IgPSBmaWVsZC52YWxpZGF0b3I7XG5cblxuICBpZiAodHlwZW9mIHZhbGlkYXRvciA9PT0gJ2Z1bmN0aW9uJykge1xuICAgIGlmICghdmFsaWRhdG9yKGlucHV0KSkge1xuICAgICAgcmV0dXJuICcnO1xuICAgIH1cbiAgfVxuICBpZiAodHlwZW9mIGVuY29kZXIgPT09ICdmdW5jdGlvbicpIHtcbiAgICByZXR1cm4gZW5jb2RlcihpbnB1dCk7XG4gIH1cblxuICB2YXIgYml0Q291bnQgPSB0eXBlb2YgbnVtQml0cyA9PT0gJ2Z1bmN0aW9uJyA/IG51bUJpdHMoaW5wdXQpIDogbnVtQml0cztcblxuICB2YXIgaW5wdXRWYWx1ZSA9IGlucHV0W25hbWVdO1xuICB2YXIgZmllbGRWYWx1ZSA9IGlucHV0VmFsdWUgPT09IG51bGwgfHwgaW5wdXRWYWx1ZSA9PT0gdW5kZWZpbmVkID8gJycgOiBpbnB1dFZhbHVlO1xuXG4gIHN3aXRjaCAodHlwZSkge1xuICAgIGNhc2UgJ2ludCc6XG4gICAgICByZXR1cm4gZW5jb2RlSW50VG9CaXRzKGZpZWxkVmFsdWUsIGJpdENvdW50KTtcbiAgICBjYXNlICdib29sJzpcbiAgICAgIHJldHVybiBlbmNvZGVCb29sVG9CaXRzKGZpZWxkVmFsdWUpO1xuICAgIGNhc2UgJ2RhdGUnOlxuICAgICAgcmV0dXJuIGVuY29kZURhdGVUb0JpdHMoZmllbGRWYWx1ZSwgYml0Q291bnQpO1xuICAgIGNhc2UgJ2JpdHMnOlxuICAgICAgcmV0dXJuIHBhZFJpZ2h0KGZpZWxkVmFsdWUsIGJpdENvdW50IC0gZmllbGRWYWx1ZS5sZW5ndGgpLnN1YnN0cmluZygwLCBiaXRDb3VudCk7XG4gICAgY2FzZSAnbGlzdCc6XG4gICAgICByZXR1cm4gZmllbGRWYWx1ZS5yZWR1Y2UoZnVuY3Rpb24gKGFjYywgbGlzdFZhbHVlKSB7XG4gICAgICAgIHJldHVybiBhY2MgKyBlbmNvZGVGaWVsZHMoe1xuICAgICAgICAgIGlucHV0OiBsaXN0VmFsdWUsXG4gICAgICAgICAgZmllbGRzOiBmaWVsZC5maWVsZHNcbiAgICAgICAgfSk7XG4gICAgICB9LCAnJyk7XG4gICAgY2FzZSAnbGFuZ3VhZ2UnOlxuICAgICAgcmV0dXJuIGVuY29kZUxhbmd1YWdlVG9CaXRzKGZpZWxkVmFsdWUsIGJpdENvdW50KTtcbiAgICBkZWZhdWx0OlxuICAgICAgdGhyb3cgbmV3IEVycm9yKCdDb25zZW50U3RyaW5nIC0gVW5rbm93biBmaWVsZCB0eXBlICcgKyB0eXBlICsgJyBmb3IgZW5jb2RpbmcnKTtcbiAgfVxufVxuXG5mdW5jdGlvbiBlbmNvZGVGaWVsZHMoX3JlZjIpIHtcbiAgdmFyIGlucHV0ID0gX3JlZjIuaW5wdXQsXG4gICAgICBmaWVsZHMgPSBfcmVmMi5maWVsZHM7XG5cbiAgcmV0dXJuIGZpZWxkcy5yZWR1Y2UoZnVuY3Rpb24gKGFjYywgZmllbGQpIHtcbiAgICBhY2MgKz0gZW5jb2RlRmllbGQoeyBpbnB1dDogaW5wdXQsIGZpZWxkOiBmaWVsZCB9KTtcblxuICAgIHJldHVybiBhY2M7XG4gIH0sICcnKTtcbn1cblxuZnVuY3Rpb24gZGVjb2RlRmllbGQoX3JlZjMpIHtcbiAgdmFyIGlucHV0ID0gX3JlZjMuaW5wdXQsXG4gICAgICBvdXRwdXQgPSBfcmVmMy5vdXRwdXQsXG4gICAgICBzdGFydFBvc2l0aW9uID0gX3JlZjMuc3RhcnRQb3NpdGlvbixcbiAgICAgIGZpZWxkID0gX3JlZjMuZmllbGQ7XG4gIHZhciB0eXBlID0gZmllbGQudHlwZSxcbiAgICAgIG51bUJpdHMgPSBmaWVsZC5udW1CaXRzLFxuICAgICAgZGVjb2RlciA9IGZpZWxkLmRlY29kZXIsXG4gICAgICB2YWxpZGF0b3IgPSBmaWVsZC52YWxpZGF0b3IsXG4gICAgICBsaXN0Q291bnQgPSBmaWVsZC5saXN0Q291bnQ7XG5cblxuICBpZiAodHlwZW9mIHZhbGlkYXRvciA9PT0gJ2Z1bmN0aW9uJykge1xuICAgIGlmICghdmFsaWRhdG9yKG91dHB1dCkpIHtcbiAgICAgIC8vIE5vdCBkZWNvZGluZyB0aGlzIGZpZWxkIHNvIG1ha2Ugc3VyZSB3ZSBzdGFydCBwYXJzaW5nIHRoZSBuZXh0IGZpZWxkIGF0XG4gICAgICAvLyB0aGUgc2FtZSBwb2ludFxuICAgICAgcmV0dXJuIHsgbmV3UG9zaXRpb246IHN0YXJ0UG9zaXRpb24gfTtcbiAgICB9XG4gIH1cblxuICBpZiAodHlwZW9mIGRlY29kZXIgPT09ICdmdW5jdGlvbicpIHtcbiAgICByZXR1cm4gZGVjb2RlcihpbnB1dCwgb3V0cHV0LCBzdGFydFBvc2l0aW9uKTtcbiAgfVxuXG4gIHZhciBiaXRDb3VudCA9IHR5cGVvZiBudW1CaXRzID09PSAnZnVuY3Rpb24nID8gbnVtQml0cyhvdXRwdXQpIDogbnVtQml0cztcblxuICBzd2l0Y2ggKHR5cGUpIHtcbiAgICBjYXNlICdpbnQnOlxuICAgICAgcmV0dXJuIHsgZmllbGRWYWx1ZTogZGVjb2RlQml0c1RvSW50KGlucHV0LCBzdGFydFBvc2l0aW9uLCBiaXRDb3VudCkgfTtcbiAgICBjYXNlICdib29sJzpcbiAgICAgIHJldHVybiB7IGZpZWxkVmFsdWU6IGRlY29kZUJpdHNUb0Jvb2woaW5wdXQsIHN0YXJ0UG9zaXRpb24pIH07XG4gICAgY2FzZSAnZGF0ZSc6XG4gICAgICByZXR1cm4geyBmaWVsZFZhbHVlOiBkZWNvZGVCaXRzVG9EYXRlKGlucHV0LCBzdGFydFBvc2l0aW9uLCBiaXRDb3VudCkgfTtcbiAgICBjYXNlICdiaXRzJzpcbiAgICAgIHJldHVybiB7IGZpZWxkVmFsdWU6IGlucHV0LnN1YnN0cihzdGFydFBvc2l0aW9uLCBiaXRDb3VudCkgfTtcbiAgICBjYXNlICdsaXN0JzpcbiAgICAgIHJldHVybiBkZWNvZGVMaXN0KGlucHV0LCBvdXRwdXQsIHN0YXJ0UG9zaXRpb24sIGZpZWxkLCBsaXN0Q291bnQpO1xuICAgIGNhc2UgJ2xhbmd1YWdlJzpcbiAgICAgIHJldHVybiB7IGZpZWxkVmFsdWU6IGRlY29kZUJpdHNUb0xhbmd1YWdlKGlucHV0LCBzdGFydFBvc2l0aW9uLCBiaXRDb3VudCkgfTtcbiAgICBkZWZhdWx0OlxuICAgICAgdGhyb3cgbmV3IEVycm9yKCdDb25zZW50U3RyaW5nIC0gVW5rbm93biBmaWVsZCB0eXBlICcgKyB0eXBlICsgJyBmb3IgZGVjb2RpbmcnKTtcbiAgfVxufVxuXG5mdW5jdGlvbiBkZWNvZGVMaXN0KGlucHV0LCBvdXRwdXQsIHN0YXJ0UG9zaXRpb24sIGZpZWxkLCBsaXN0Q291bnQpIHtcbiAgdmFyIGxpc3RFbnRyeUNvdW50ID0gMDtcblxuICBpZiAodHlwZW9mIGxpc3RDb3VudCA9PT0gJ2Z1bmN0aW9uJykge1xuICAgIGxpc3RFbnRyeUNvdW50ID0gbGlzdENvdW50KG91dHB1dCk7XG4gIH0gZWxzZSBpZiAodHlwZW9mIGxpc3RDb3VudCA9PT0gJ251bWJlcicpIHtcbiAgICBsaXN0RW50cnlDb3VudCA9IGxpc3RDb3VudDtcbiAgfVxuXG4gIHZhciBuZXdQb3NpdGlvbiA9IHN0YXJ0UG9zaXRpb247XG4gIHZhciBmaWVsZFZhbHVlID0gW107XG5cbiAgZm9yICh2YXIgaSA9IDA7IGkgPCBsaXN0RW50cnlDb3VudDsgaSArPSAxKSB7XG4gICAgdmFyIGRlY29kZWRGaWVsZHMgPSBkZWNvZGVGaWVsZHMoe1xuICAgICAgaW5wdXQ6IGlucHV0LFxuICAgICAgZmllbGRzOiBmaWVsZC5maWVsZHMsXG4gICAgICBzdGFydFBvc2l0aW9uOiBuZXdQb3NpdGlvblxuICAgIH0pO1xuXG4gICAgbmV3UG9zaXRpb24gPSBkZWNvZGVkRmllbGRzLm5ld1Bvc2l0aW9uO1xuICAgIGZpZWxkVmFsdWUucHVzaChkZWNvZGVkRmllbGRzLmRlY29kZWRPYmplY3QpO1xuICB9XG5cbiAgcmV0dXJuIHsgZmllbGRWYWx1ZTogZmllbGRWYWx1ZSwgbmV3UG9zaXRpb246IG5ld1Bvc2l0aW9uIH07XG59XG5cbmZ1bmN0aW9uIGRlY29kZUZpZWxkcyhfcmVmNCkge1xuICB2YXIgaW5wdXQgPSBfcmVmNC5pbnB1dCxcbiAgICAgIGZpZWxkcyA9IF9yZWY0LmZpZWxkcyxcbiAgICAgIF9yZWY0JHN0YXJ0UG9zaXRpb24gPSBfcmVmNC5zdGFydFBvc2l0aW9uLFxuICAgICAgc3RhcnRQb3NpdGlvbiA9IF9yZWY0JHN0YXJ0UG9zaXRpb24gPT09IHVuZGVmaW5lZCA/IDAgOiBfcmVmNCRzdGFydFBvc2l0aW9uO1xuXG4gIHZhciBwb3NpdGlvbiA9IHN0YXJ0UG9zaXRpb247XG5cbiAgdmFyIGRlY29kZWRPYmplY3QgPSBmaWVsZHMucmVkdWNlKGZ1bmN0aW9uIChhY2MsIGZpZWxkKSB7XG4gICAgdmFyIG5hbWUgPSBmaWVsZC5uYW1lLFxuICAgICAgICBudW1CaXRzID0gZmllbGQubnVtQml0cztcblxuICAgIHZhciBfZGVjb2RlRmllbGQgPSBkZWNvZGVGaWVsZCh7XG4gICAgICBpbnB1dDogaW5wdXQsXG4gICAgICBvdXRwdXQ6IGFjYyxcbiAgICAgIHN0YXJ0UG9zaXRpb246IHBvc2l0aW9uLFxuICAgICAgZmllbGQ6IGZpZWxkXG4gICAgfSksXG4gICAgICAgIGZpZWxkVmFsdWUgPSBfZGVjb2RlRmllbGQuZmllbGRWYWx1ZSxcbiAgICAgICAgbmV3UG9zaXRpb24gPSBfZGVjb2RlRmllbGQubmV3UG9zaXRpb247XG5cbiAgICBpZiAoZmllbGRWYWx1ZSAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICBhY2NbbmFtZV0gPSBmaWVsZFZhbHVlO1xuICAgIH1cblxuICAgIGlmIChuZXdQb3NpdGlvbiAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICBwb3NpdGlvbiA9IG5ld1Bvc2l0aW9uO1xuICAgIH0gZWxzZSBpZiAodHlwZW9mIG51bUJpdHMgPT09ICdudW1iZXInKSB7XG4gICAgICBwb3NpdGlvbiArPSBudW1CaXRzO1xuICAgIH1cblxuICAgIHJldHVybiBhY2M7XG4gIH0sIHt9KTtcblxuICByZXR1cm4ge1xuICAgIGRlY29kZWRPYmplY3Q6IGRlY29kZWRPYmplY3QsXG4gICAgbmV3UG9zaXRpb246IHBvc2l0aW9uXG4gIH07XG59XG5cbi8qKlxuICogRW5jb2RlIHRoZSBkYXRhIHByb3BlcnRpZXMgdG8gYSBiaXQgc3RyaW5nLiBFbmNvZGluZyB3aWxsIGVuY29kZVxuICogZWl0aGVyIGBzZWxlY3RlZFZlbmRvcklkc2Agb3IgdGhlIGB2ZW5kb3JSYW5nZUxpc3RgIGRlcGVuZGluZyBvblxuICogdGhlIHZhbHVlIG9mIHRoZSBgaXNSYW5nZWAgZmxhZy5cbiAqL1xuZnVuY3Rpb24gZW5jb2RlRGF0YVRvQml0cyhkYXRhLCBkZWZpbml0aW9uTWFwKSB7XG4gIHZhciB2ZXJzaW9uID0gZGF0YS52ZXJzaW9uO1xuXG5cbiAgaWYgKHR5cGVvZiB2ZXJzaW9uICE9PSAnbnVtYmVyJykge1xuICAgIHRocm93IG5ldyBFcnJvcignQ29uc2VudFN0cmluZyAtIE5vIHZlcnNpb24gZmllbGQgdG8gZW5jb2RlJyk7XG4gIH0gZWxzZSBpZiAoIWRlZmluaXRpb25NYXBbdmVyc2lvbl0pIHtcbiAgICB0aHJvdyBuZXcgRXJyb3IoJ0NvbnNlbnRTdHJpbmcgLSBObyBkZWZpbml0aW9uIGZvciB2ZXJzaW9uICcgKyB2ZXJzaW9uKTtcbiAgfSBlbHNlIHtcbiAgICB2YXIgZmllbGRzID0gZGVmaW5pdGlvbk1hcFt2ZXJzaW9uXS5maWVsZHM7XG4gICAgcmV0dXJuIGVuY29kZUZpZWxkcyh7IGlucHV0OiBkYXRhLCBmaWVsZHM6IGZpZWxkcyB9KTtcbiAgfVxufVxuXG4vKipcbiAqIFRha2UgYWxsIGZpZWxkcyByZXF1aXJlZCB0byBlbmNvZGUgdGhlIGNvbnNlbnQgc3RyaW5nIGFuZCBwcm9kdWNlIHRoZSBVUkwgc2FmZSBCYXNlNjQgZW5jb2RlZCB2YWx1ZVxuICovXG5mdW5jdGlvbiBlbmNvZGVUb0Jhc2U2NChkYXRhKSB7XG4gIHZhciBkZWZpbml0aW9uTWFwID0gYXJndW1lbnRzLmxlbmd0aCA+IDEgJiYgYXJndW1lbnRzWzFdICE9PSB1bmRlZmluZWQgPyBhcmd1bWVudHNbMV0gOiB2ZW5kb3JWZXJzaW9uTWFwO1xuXG4gIHZhciBiaW5hcnlWYWx1ZSA9IGVuY29kZURhdGFUb0JpdHMoZGF0YSwgZGVmaW5pdGlvbk1hcCk7XG5cbiAgaWYgKGJpbmFyeVZhbHVlKSB7XG4gICAgLy8gUGFkIGxlbmd0aCB0byBtdWx0aXBsZSBvZiA4XG4gICAgdmFyIHBhZGRlZEJpbmFyeVZhbHVlID0gcGFkUmlnaHQoYmluYXJ5VmFsdWUsIDcgLSAoYmluYXJ5VmFsdWUubGVuZ3RoICsgNykgJSA4KTtcblxuICAgIC8vIEVuY29kZSB0byBieXRlc1xuICAgIHZhciBieXRlcyA9ICcnO1xuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgcGFkZGVkQmluYXJ5VmFsdWUubGVuZ3RoOyBpICs9IDgpIHtcbiAgICAgIGJ5dGVzICs9IFN0cmluZy5mcm9tQ2hhckNvZGUocGFyc2VJbnQocGFkZGVkQmluYXJ5VmFsdWUuc3Vic3RyKGksIDgpLCAyKSk7XG4gICAgfVxuXG4gICAgLy8gTWFrZSBiYXNlNjQgc3RyaW5nIFVSTCBmcmllbmRseVxuICAgIHJldHVybiBiYXNlNjQuZW5jb2RlKGJ5dGVzKS5yZXBsYWNlKC9cXCsvZywgJy0nKS5yZXBsYWNlKC9cXC8vZywgJ18nKS5yZXBsYWNlKC89KyQvLCAnJyk7XG4gIH1cblxuICByZXR1cm4gbnVsbDtcbn1cblxuZnVuY3Rpb24gZGVjb2RlQ29uc2VudFN0cmluZ0JpdFZhbHVlKGJpdFN0cmluZykge1xuICB2YXIgZGVmaW5pdGlvbk1hcCA9IGFyZ3VtZW50cy5sZW5ndGggPiAxICYmIGFyZ3VtZW50c1sxXSAhPT0gdW5kZWZpbmVkID8gYXJndW1lbnRzWzFdIDogdmVuZG9yVmVyc2lvbk1hcDtcblxuICB2YXIgdmVyc2lvbiA9IGRlY29kZUJpdHNUb0ludChiaXRTdHJpbmcsIDAsIHZlcnNpb25OdW1CaXRzKTtcblxuICBpZiAodHlwZW9mIHZlcnNpb24gIT09ICdudW1iZXInKSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKCdDb25zZW50U3RyaW5nIC0gVW5rbm93biB2ZXJzaW9uIG51bWJlciBpbiB0aGUgc3RyaW5nIHRvIGRlY29kZScpO1xuICB9IGVsc2UgaWYgKCF2ZW5kb3JWZXJzaW9uTWFwW3ZlcnNpb25dKSB7XG4gICAgdGhyb3cgbmV3IEVycm9yKCdDb25zZW50U3RyaW5nIC0gVW5zdXBwb3J0ZWQgdmVyc2lvbiAnICsgdmVyc2lvbiArICcgaW4gdGhlIHN0cmluZyB0byBkZWNvZGUnKTtcbiAgfVxuXG4gIHZhciBmaWVsZHMgPSBkZWZpbml0aW9uTWFwW3ZlcnNpb25dLmZpZWxkcztcblxuICB2YXIgX2RlY29kZUZpZWxkcyA9IGRlY29kZUZpZWxkcyh7IGlucHV0OiBiaXRTdHJpbmcsIGZpZWxkczogZmllbGRzIH0pLFxuICAgICAgZGVjb2RlZE9iamVjdCA9IF9kZWNvZGVGaWVsZHMuZGVjb2RlZE9iamVjdDtcblxuICByZXR1cm4gZGVjb2RlZE9iamVjdDtcbn1cblxuLyoqXG4gKiBEZWNvZGUgdGhlIChVUkwgc2FmZSBCYXNlNjQpIHZhbHVlIG9mIGEgY29uc2VudCBzdHJpbmcgaW50byBhbiBvYmplY3QuXG4gKi9cbmZ1bmN0aW9uIGRlY29kZUZyb21CYXNlNjQoY29uc2VudFN0cmluZywgZGVmaW5pdGlvbk1hcCkge1xuICAvLyBBZGQgcGFkZGluZ1xuICB2YXIgdW5zYWZlID0gY29uc2VudFN0cmluZztcbiAgd2hpbGUgKHVuc2FmZS5sZW5ndGggJSA0ICE9PSAwKSB7XG4gICAgdW5zYWZlICs9ICc9JztcbiAgfVxuXG4gIC8vIFJlcGxhY2Ugc2FmZSBjaGFyYWN0ZXJzXG4gIHVuc2FmZSA9IHVuc2FmZS5yZXBsYWNlKC8tL2csICcrJykucmVwbGFjZSgvXy9nLCAnLycpO1xuXG4gIHZhciBieXRlcyA9IGJhc2U2NC5kZWNvZGUodW5zYWZlKTtcblxuICB2YXIgaW5wdXRCaXRzID0gJyc7XG4gIGZvciAodmFyIGkgPSAwOyBpIDwgYnl0ZXMubGVuZ3RoOyBpICs9IDEpIHtcbiAgICB2YXIgYml0U3RyaW5nID0gYnl0ZXMuY2hhckNvZGVBdChpKS50b1N0cmluZygyKTtcbiAgICBpbnB1dEJpdHMgKz0gcGFkTGVmdChiaXRTdHJpbmcsIDggLSBiaXRTdHJpbmcubGVuZ3RoKTtcbiAgfVxuXG4gIHJldHVybiBkZWNvZGVDb25zZW50U3RyaW5nQml0VmFsdWUoaW5wdXRCaXRzLCBkZWZpbml0aW9uTWFwKTtcbn1cblxuZnVuY3Rpb24gZGVjb2RlQml0c1RvSWRzKGJpdFN0cmluZykge1xuICByZXR1cm4gYml0U3RyaW5nLnNwbGl0KCcnKS5yZWR1Y2UoZnVuY3Rpb24gKGFjYywgYml0LCBpbmRleCkge1xuICAgIGlmIChiaXQgPT09ICcxJykge1xuICAgICAgaWYgKGFjYy5pbmRleE9mKGluZGV4ICsgMSkgPT09IC0xKSB7XG4gICAgICAgIGFjYy5wdXNoKGluZGV4ICsgMSk7XG4gICAgICB9XG4gICAgfVxuICAgIHJldHVybiBhY2M7XG4gIH0sIFtdKTtcbn1cblxubW9kdWxlLmV4cG9ydHMgPSB7XG4gIHBhZFJpZ2h0OiBwYWRSaWdodCxcbiAgcGFkTGVmdDogcGFkTGVmdCxcbiAgZW5jb2RlRmllbGQ6IGVuY29kZUZpZWxkLFxuICBlbmNvZGVEYXRhVG9CaXRzOiBlbmNvZGVEYXRhVG9CaXRzLFxuICBlbmNvZGVJbnRUb0JpdHM6IGVuY29kZUludFRvQml0cyxcbiAgZW5jb2RlQm9vbFRvQml0czogZW5jb2RlQm9vbFRvQml0cyxcbiAgZW5jb2RlRGF0ZVRvQml0czogZW5jb2RlRGF0ZVRvQml0cyxcbiAgZW5jb2RlTGFuZ3VhZ2VUb0JpdHM6IGVuY29kZUxhbmd1YWdlVG9CaXRzLFxuICBlbmNvZGVMZXR0ZXJUb0JpdHM6IGVuY29kZUxldHRlclRvQml0cyxcbiAgZW5jb2RlVG9CYXNlNjQ6IGVuY29kZVRvQmFzZTY0LFxuICBkZWNvZGVCaXRzVG9JZHM6IGRlY29kZUJpdHNUb0lkcyxcbiAgZGVjb2RlQml0c1RvSW50OiBkZWNvZGVCaXRzVG9JbnQsXG4gIGRlY29kZUJpdHNUb0RhdGU6IGRlY29kZUJpdHNUb0RhdGUsXG4gIGRlY29kZUJpdHNUb0Jvb2w6IGRlY29kZUJpdHNUb0Jvb2wsXG4gIGRlY29kZUJpdHNUb0xhbmd1YWdlOiBkZWNvZGVCaXRzVG9MYW5ndWFnZSxcbiAgZGVjb2RlQml0c1RvTGV0dGVyOiBkZWNvZGVCaXRzVG9MZXR0ZXIsXG4gIGRlY29kZUZyb21CYXNlNjQ6IGRlY29kZUZyb21CYXNlNjRcbn07IiwiLyohIGh0dHA6Ly9tdGhzLmJlL2Jhc2U2NCB2MC4xLjAgYnkgQG1hdGhpYXMgfCBNSVQgbGljZW5zZSAqL1xuOyhmdW5jdGlvbihyb290KSB7XG5cblx0Ly8gRGV0ZWN0IGZyZWUgdmFyaWFibGVzIGBleHBvcnRzYC5cblx0dmFyIGZyZWVFeHBvcnRzID0gdHlwZW9mIGV4cG9ydHMgPT0gJ29iamVjdCcgJiYgZXhwb3J0cztcblxuXHQvLyBEZXRlY3QgZnJlZSB2YXJpYWJsZSBgbW9kdWxlYC5cblx0dmFyIGZyZWVNb2R1bGUgPSB0eXBlb2YgbW9kdWxlID09ICdvYmplY3QnICYmIG1vZHVsZSAmJlxuXHRcdG1vZHVsZS5leHBvcnRzID09IGZyZWVFeHBvcnRzICYmIG1vZHVsZTtcblxuXHQvLyBEZXRlY3QgZnJlZSB2YXJpYWJsZSBgZ2xvYmFsYCwgZnJvbSBOb2RlLmpzIG9yIEJyb3dzZXJpZmllZCBjb2RlLCBhbmQgdXNlXG5cdC8vIGl0IGFzIGByb290YC5cblx0dmFyIGZyZWVHbG9iYWwgPSB0eXBlb2YgZ2xvYmFsID09ICdvYmplY3QnICYmIGdsb2JhbDtcblx0aWYgKGZyZWVHbG9iYWwuZ2xvYmFsID09PSBmcmVlR2xvYmFsIHx8IGZyZWVHbG9iYWwud2luZG93ID09PSBmcmVlR2xvYmFsKSB7XG5cdFx0cm9vdCA9IGZyZWVHbG9iYWw7XG5cdH1cblxuXHQvKi0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tKi9cblxuXHR2YXIgSW52YWxpZENoYXJhY3RlckVycm9yID0gZnVuY3Rpb24obWVzc2FnZSkge1xuXHRcdHRoaXMubWVzc2FnZSA9IG1lc3NhZ2U7XG5cdH07XG5cdEludmFsaWRDaGFyYWN0ZXJFcnJvci5wcm90b3R5cGUgPSBuZXcgRXJyb3I7XG5cdEludmFsaWRDaGFyYWN0ZXJFcnJvci5wcm90b3R5cGUubmFtZSA9ICdJbnZhbGlkQ2hhcmFjdGVyRXJyb3InO1xuXG5cdHZhciBlcnJvciA9IGZ1bmN0aW9uKG1lc3NhZ2UpIHtcblx0XHQvLyBOb3RlOiB0aGUgZXJyb3IgbWVzc2FnZXMgdXNlZCB0aHJvdWdob3V0IHRoaXMgZmlsZSBtYXRjaCB0aG9zZSB1c2VkIGJ5XG5cdFx0Ly8gdGhlIG5hdGl2ZSBgYXRvYmAvYGJ0b2FgIGltcGxlbWVudGF0aW9uIGluIENocm9taXVtLlxuXHRcdHRocm93IG5ldyBJbnZhbGlkQ2hhcmFjdGVyRXJyb3IobWVzc2FnZSk7XG5cdH07XG5cblx0dmFyIFRBQkxFID0gJ0FCQ0RFRkdISUpLTE1OT1BRUlNUVVZXWFlaYWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXowMTIzNDU2Nzg5Ky8nO1xuXHQvLyBodHRwOi8vd2hhdHdnLm9yZy9odG1sL2NvbW1vbi1taWNyb3N5bnRheGVzLmh0bWwjc3BhY2UtY2hhcmFjdGVyXG5cdHZhciBSRUdFWF9TUEFDRV9DSEFSQUNURVJTID0gL1tcXHRcXG5cXGZcXHIgXS9nO1xuXG5cdC8vIGBkZWNvZGVgIGlzIGRlc2lnbmVkIHRvIGJlIGZ1bGx5IGNvbXBhdGlibGUgd2l0aCBgYXRvYmAgYXMgZGVzY3JpYmVkIGluIHRoZVxuXHQvLyBIVE1MIFN0YW5kYXJkLiBodHRwOi8vd2hhdHdnLm9yZy9odG1sL3dlYmFwcGFwaXMuaHRtbCNkb20td2luZG93YmFzZTY0LWF0b2Jcblx0Ly8gVGhlIG9wdGltaXplZCBiYXNlNjQtZGVjb2RpbmcgYWxnb3JpdGhtIHVzZWQgaXMgYmFzZWQgb24gQGF0a+KAmXMgZXhjZWxsZW50XG5cdC8vIGltcGxlbWVudGF0aW9uLiBodHRwczovL2dpc3QuZ2l0aHViLmNvbS9hdGsvMTAyMDM5NlxuXHR2YXIgZGVjb2RlID0gZnVuY3Rpb24oaW5wdXQpIHtcblx0XHRpbnB1dCA9IFN0cmluZyhpbnB1dClcblx0XHRcdC5yZXBsYWNlKFJFR0VYX1NQQUNFX0NIQVJBQ1RFUlMsICcnKTtcblx0XHR2YXIgbGVuZ3RoID0gaW5wdXQubGVuZ3RoO1xuXHRcdGlmIChsZW5ndGggJSA0ID09IDApIHtcblx0XHRcdGlucHV0ID0gaW5wdXQucmVwbGFjZSgvPT0/JC8sICcnKTtcblx0XHRcdGxlbmd0aCA9IGlucHV0Lmxlbmd0aDtcblx0XHR9XG5cdFx0aWYgKFxuXHRcdFx0bGVuZ3RoICUgNCA9PSAxIHx8XG5cdFx0XHQvLyBodHRwOi8vd2hhdHdnLm9yZy9DI2FscGhhbnVtZXJpYy1hc2NpaS1jaGFyYWN0ZXJzXG5cdFx0XHQvW14rYS16QS1aMC05L10vLnRlc3QoaW5wdXQpXG5cdFx0KSB7XG5cdFx0XHRlcnJvcihcblx0XHRcdFx0J0ludmFsaWQgY2hhcmFjdGVyOiB0aGUgc3RyaW5nIHRvIGJlIGRlY29kZWQgaXMgbm90IGNvcnJlY3RseSBlbmNvZGVkLidcblx0XHRcdCk7XG5cdFx0fVxuXHRcdHZhciBiaXRDb3VudGVyID0gMDtcblx0XHR2YXIgYml0U3RvcmFnZTtcblx0XHR2YXIgYnVmZmVyO1xuXHRcdHZhciBvdXRwdXQgPSAnJztcblx0XHR2YXIgcG9zaXRpb24gPSAtMTtcblx0XHR3aGlsZSAoKytwb3NpdGlvbiA8IGxlbmd0aCkge1xuXHRcdFx0YnVmZmVyID0gVEFCTEUuaW5kZXhPZihpbnB1dC5jaGFyQXQocG9zaXRpb24pKTtcblx0XHRcdGJpdFN0b3JhZ2UgPSBiaXRDb3VudGVyICUgNCA/IGJpdFN0b3JhZ2UgKiA2NCArIGJ1ZmZlciA6IGJ1ZmZlcjtcblx0XHRcdC8vIFVubGVzcyB0aGlzIGlzIHRoZSBmaXJzdCBvZiBhIGdyb3VwIG9mIDQgY2hhcmFjdGVyc+KAplxuXHRcdFx0aWYgKGJpdENvdW50ZXIrKyAlIDQpIHtcblx0XHRcdFx0Ly8g4oCmY29udmVydCB0aGUgZmlyc3QgOCBiaXRzIHRvIGEgc2luZ2xlIEFTQ0lJIGNoYXJhY3Rlci5cblx0XHRcdFx0b3V0cHV0ICs9IFN0cmluZy5mcm9tQ2hhckNvZGUoXG5cdFx0XHRcdFx0MHhGRiAmIGJpdFN0b3JhZ2UgPj4gKC0yICogYml0Q291bnRlciAmIDYpXG5cdFx0XHRcdCk7XG5cdFx0XHR9XG5cdFx0fVxuXHRcdHJldHVybiBvdXRwdXQ7XG5cdH07XG5cblx0Ly8gYGVuY29kZWAgaXMgZGVzaWduZWQgdG8gYmUgZnVsbHkgY29tcGF0aWJsZSB3aXRoIGBidG9hYCBhcyBkZXNjcmliZWQgaW4gdGhlXG5cdC8vIEhUTUwgU3RhbmRhcmQ6IGh0dHA6Ly93aGF0d2cub3JnL2h0bWwvd2ViYXBwYXBpcy5odG1sI2RvbS13aW5kb3diYXNlNjQtYnRvYVxuXHR2YXIgZW5jb2RlID0gZnVuY3Rpb24oaW5wdXQpIHtcblx0XHRpbnB1dCA9IFN0cmluZyhpbnB1dCk7XG5cdFx0aWYgKC9bXlxcMC1cXHhGRl0vLnRlc3QoaW5wdXQpKSB7XG5cdFx0XHQvLyBOb3RlOiBubyBuZWVkIHRvIHNwZWNpYWwtY2FzZSBhc3RyYWwgc3ltYm9scyBoZXJlLCBhcyBzdXJyb2dhdGVzIGFyZVxuXHRcdFx0Ly8gbWF0Y2hlZCwgYW5kIHRoZSBpbnB1dCBpcyBzdXBwb3NlZCB0byBvbmx5IGNvbnRhaW4gQVNDSUkgYW55d2F5LlxuXHRcdFx0ZXJyb3IoXG5cdFx0XHRcdCdUaGUgc3RyaW5nIHRvIGJlIGVuY29kZWQgY29udGFpbnMgY2hhcmFjdGVycyBvdXRzaWRlIG9mIHRoZSAnICtcblx0XHRcdFx0J0xhdGluMSByYW5nZS4nXG5cdFx0XHQpO1xuXHRcdH1cblx0XHR2YXIgcGFkZGluZyA9IGlucHV0Lmxlbmd0aCAlIDM7XG5cdFx0dmFyIG91dHB1dCA9ICcnO1xuXHRcdHZhciBwb3NpdGlvbiA9IC0xO1xuXHRcdHZhciBhO1xuXHRcdHZhciBiO1xuXHRcdHZhciBjO1xuXHRcdHZhciBkO1xuXHRcdHZhciBidWZmZXI7XG5cdFx0Ly8gTWFrZSBzdXJlIGFueSBwYWRkaW5nIGlzIGhhbmRsZWQgb3V0c2lkZSBvZiB0aGUgbG9vcC5cblx0XHR2YXIgbGVuZ3RoID0gaW5wdXQubGVuZ3RoIC0gcGFkZGluZztcblxuXHRcdHdoaWxlICgrK3Bvc2l0aW9uIDwgbGVuZ3RoKSB7XG5cdFx0XHQvLyBSZWFkIHRocmVlIGJ5dGVzLCBpLmUuIDI0IGJpdHMuXG5cdFx0XHRhID0gaW5wdXQuY2hhckNvZGVBdChwb3NpdGlvbikgPDwgMTY7XG5cdFx0XHRiID0gaW5wdXQuY2hhckNvZGVBdCgrK3Bvc2l0aW9uKSA8PCA4O1xuXHRcdFx0YyA9IGlucHV0LmNoYXJDb2RlQXQoKytwb3NpdGlvbik7XG5cdFx0XHRidWZmZXIgPSBhICsgYiArIGM7XG5cdFx0XHQvLyBUdXJuIHRoZSAyNCBiaXRzIGludG8gZm91ciBjaHVua3Mgb2YgNiBiaXRzIGVhY2gsIGFuZCBhcHBlbmQgdGhlXG5cdFx0XHQvLyBtYXRjaGluZyBjaGFyYWN0ZXIgZm9yIGVhY2ggb2YgdGhlbSB0byB0aGUgb3V0cHV0LlxuXHRcdFx0b3V0cHV0ICs9IChcblx0XHRcdFx0VEFCTEUuY2hhckF0KGJ1ZmZlciA+PiAxOCAmIDB4M0YpICtcblx0XHRcdFx0VEFCTEUuY2hhckF0KGJ1ZmZlciA+PiAxMiAmIDB4M0YpICtcblx0XHRcdFx0VEFCTEUuY2hhckF0KGJ1ZmZlciA+PiA2ICYgMHgzRikgK1xuXHRcdFx0XHRUQUJMRS5jaGFyQXQoYnVmZmVyICYgMHgzRilcblx0XHRcdCk7XG5cdFx0fVxuXG5cdFx0aWYgKHBhZGRpbmcgPT0gMikge1xuXHRcdFx0YSA9IGlucHV0LmNoYXJDb2RlQXQocG9zaXRpb24pIDw8IDg7XG5cdFx0XHRiID0gaW5wdXQuY2hhckNvZGVBdCgrK3Bvc2l0aW9uKTtcblx0XHRcdGJ1ZmZlciA9IGEgKyBiO1xuXHRcdFx0b3V0cHV0ICs9IChcblx0XHRcdFx0VEFCTEUuY2hhckF0KGJ1ZmZlciA+PiAxMCkgK1xuXHRcdFx0XHRUQUJMRS5jaGFyQXQoKGJ1ZmZlciA+PiA0KSAmIDB4M0YpICtcblx0XHRcdFx0VEFCTEUuY2hhckF0KChidWZmZXIgPDwgMikgJiAweDNGKSArXG5cdFx0XHRcdCc9J1xuXHRcdFx0KTtcblx0XHR9IGVsc2UgaWYgKHBhZGRpbmcgPT0gMSkge1xuXHRcdFx0YnVmZmVyID0gaW5wdXQuY2hhckNvZGVBdChwb3NpdGlvbik7XG5cdFx0XHRvdXRwdXQgKz0gKFxuXHRcdFx0XHRUQUJMRS5jaGFyQXQoYnVmZmVyID4+IDIpICtcblx0XHRcdFx0VEFCTEUuY2hhckF0KChidWZmZXIgPDwgNCkgJiAweDNGKSArXG5cdFx0XHRcdCc9PSdcblx0XHRcdCk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIG91dHB1dDtcblx0fTtcblxuXHR2YXIgYmFzZTY0ID0ge1xuXHRcdCdlbmNvZGUnOiBlbmNvZGUsXG5cdFx0J2RlY29kZSc6IGRlY29kZSxcblx0XHQndmVyc2lvbic6ICcwLjEuMCdcblx0fTtcblxuXHQvLyBTb21lIEFNRCBidWlsZCBvcHRpbWl6ZXJzLCBsaWtlIHIuanMsIGNoZWNrIGZvciBzcGVjaWZpYyBjb25kaXRpb24gcGF0dGVybnNcblx0Ly8gbGlrZSB0aGUgZm9sbG93aW5nOlxuXHRpZiAoXG5cdFx0dHlwZW9mIGRlZmluZSA9PSAnZnVuY3Rpb24nICYmXG5cdFx0dHlwZW9mIGRlZmluZS5hbWQgPT0gJ29iamVjdCcgJiZcblx0XHRkZWZpbmUuYW1kXG5cdCkge1xuXHRcdGRlZmluZShmdW5jdGlvbigpIHtcblx0XHRcdHJldHVybiBiYXNlNjQ7XG5cdFx0fSk7XG5cdH1cdGVsc2UgaWYgKGZyZWVFeHBvcnRzICYmICFmcmVlRXhwb3J0cy5ub2RlVHlwZSkge1xuXHRcdGlmIChmcmVlTW9kdWxlKSB7IC8vIGluIE5vZGUuanMgb3IgUmluZ29KUyB2MC44LjArXG5cdFx0XHRmcmVlTW9kdWxlLmV4cG9ydHMgPSBiYXNlNjQ7XG5cdFx0fSBlbHNlIHsgLy8gaW4gTmFyd2hhbCBvciBSaW5nb0pTIHYwLjcuMC1cblx0XHRcdGZvciAodmFyIGtleSBpbiBiYXNlNjQpIHtcblx0XHRcdFx0YmFzZTY0Lmhhc093blByb3BlcnR5KGtleSkgJiYgKGZyZWVFeHBvcnRzW2tleV0gPSBiYXNlNjRba2V5XSk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9IGVsc2UgeyAvLyBpbiBSaGlubyBvciBhIHdlYiBicm93c2VyXG5cdFx0cm9vdC5iYXNlNjQgPSBiYXNlNjQ7XG5cdH1cblxufSh0aGlzKSk7XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKG1vZHVsZSkge1xuXHRpZiAoIW1vZHVsZS53ZWJwYWNrUG9seWZpbGwpIHtcblx0XHRtb2R1bGUuZGVwcmVjYXRlID0gZnVuY3Rpb24oKSB7fTtcblx0XHRtb2R1bGUucGF0aHMgPSBbXTtcblx0XHQvLyBtb2R1bGUucGFyZW50ID0gdW5kZWZpbmVkIGJ5IGRlZmF1bHRcblx0XHRpZiAoIW1vZHVsZS5jaGlsZHJlbikgbW9kdWxlLmNoaWxkcmVuID0gW107XG5cdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KG1vZHVsZSwgXCJsb2FkZWRcIiwge1xuXHRcdFx0ZW51bWVyYWJsZTogdHJ1ZSxcblx0XHRcdGdldDogZnVuY3Rpb24oKSB7XG5cdFx0XHRcdHJldHVybiBtb2R1bGUubDtcblx0XHRcdH1cblx0XHR9KTtcblx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkobW9kdWxlLCBcImlkXCIsIHtcblx0XHRcdGVudW1lcmFibGU6IHRydWUsXG5cdFx0XHRnZXQ6IGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRyZXR1cm4gbW9kdWxlLmk7XG5cdFx0XHR9XG5cdFx0fSk7XG5cdFx0bW9kdWxlLndlYnBhY2tQb2x5ZmlsbCA9IDE7XG5cdH1cblx0cmV0dXJuIG1vZHVsZTtcbn07XG4iLCJ2YXIgZztcblxuLy8gVGhpcyB3b3JrcyBpbiBub24tc3RyaWN0IG1vZGVcbmcgPSAoZnVuY3Rpb24oKSB7XG5cdHJldHVybiB0aGlzO1xufSkoKTtcblxudHJ5IHtcblx0Ly8gVGhpcyB3b3JrcyBpZiBldmFsIGlzIGFsbG93ZWQgKHNlZSBDU1ApXG5cdGcgPSBnIHx8IG5ldyBGdW5jdGlvbihcInJldHVybiB0aGlzXCIpKCk7XG59IGNhdGNoIChlKSB7XG5cdC8vIFRoaXMgd29ya3MgaWYgdGhlIHdpbmRvdyByZWZlcmVuY2UgaXMgYXZhaWxhYmxlXG5cdGlmICh0eXBlb2Ygd2luZG93ID09PSBcIm9iamVjdFwiKSBnID0gd2luZG93O1xufVxuXG4vLyBnIGNhbiBzdGlsbCBiZSB1bmRlZmluZWQsIGJ1dCBub3RoaW5nIHRvIGRvIGFib3V0IGl0Li4uXG4vLyBXZSByZXR1cm4gdW5kZWZpbmVkLCBpbnN0ZWFkIG9mIG5vdGhpbmcgaGVyZSwgc28gaXQnc1xuLy8gZWFzaWVyIHRvIGhhbmRsZSB0aGlzIGNhc2UuIGlmKCFnbG9iYWwpIHsgLi4ufVxuXG5tb2R1bGUuZXhwb3J0cyA9IGc7XG4iLCIndXNlIHN0cmljdCc7XG5cbi8qKlxuICogTnVtYmVyIG9mIGJpdHMgZm9yIGVuY29kaW5nIHRoZSB2ZXJzaW9uIGludGVnZXJcbiAqIEV4cGVjdGVkIHRvIGJlIHRoZSBzYW1lIGFjcm9zcyB2ZXJzaW9uc1xuICovXG52YXIgdmVyc2lvbk51bUJpdHMgPSA2O1xuXG4vKipcbiAqIERlZmluaXRpb24gb2YgdGhlIGNvbnNlbnQgc3RyaW5nIGVuY29kZWQgZm9ybWF0XG4gKlxuICogRnJvbSBodHRwczovL2dpdGh1Yi5jb20vSW50ZXJhY3RpdmVBZHZlcnRpc2luZ0J1cmVhdS9HRFBSLVRyYW5zcGFyZW5jeS1hbmQtQ29uc2VudC1GcmFtZXdvcmsvYmxvYi9tYXN0ZXIvRHJhZnRfZm9yX1B1YmxpY19Db21tZW50X1RyYW5zcGFyZW5jeSUyMCUyNiUyMENvbnNlbnQlMjBGcmFtZXdvcmslMjAtJTIwY29va2llJTIwYW5kJTIwdmVuZG9yJTIwbGlzdCUyMGZvcm1hdCUyMHNwZWNpZmljYXRpb24lMjB2MS4wYS5wZGZcbiAqL1xudmFyIHZlbmRvclZlcnNpb25NYXAgPSB7XG4gIC8qKlxuICAgKiBWZXJzaW9uIDFcbiAgICovXG4gIDE6IHtcbiAgICB2ZXJzaW9uOiAxLFxuICAgIG1ldGFkYXRhRmllbGRzOiBbJ3ZlcnNpb24nLCAnY3JlYXRlZCcsICdsYXN0VXBkYXRlZCcsICdjbXBJZCcsICdjbXBWZXJzaW9uJywgJ2NvbnNlbnRTY3JlZW4nLCAndmVuZG9yTGlzdFZlcnNpb24nXSxcbiAgICBmaWVsZHM6IFt7IG5hbWU6ICd2ZXJzaW9uJywgdHlwZTogJ2ludCcsIG51bUJpdHM6IDYgfSwgeyBuYW1lOiAnY3JlYXRlZCcsIHR5cGU6ICdkYXRlJywgbnVtQml0czogMzYgfSwgeyBuYW1lOiAnbGFzdFVwZGF0ZWQnLCB0eXBlOiAnZGF0ZScsIG51bUJpdHM6IDM2IH0sIHsgbmFtZTogJ2NtcElkJywgdHlwZTogJ2ludCcsIG51bUJpdHM6IDEyIH0sIHsgbmFtZTogJ2NtcFZlcnNpb24nLCB0eXBlOiAnaW50JywgbnVtQml0czogMTIgfSwgeyBuYW1lOiAnY29uc2VudFNjcmVlbicsIHR5cGU6ICdpbnQnLCBudW1CaXRzOiA2IH0sIHsgbmFtZTogJ2NvbnNlbnRMYW5ndWFnZScsIHR5cGU6ICdsYW5ndWFnZScsIG51bUJpdHM6IDEyIH0sIHsgbmFtZTogJ3ZlbmRvckxpc3RWZXJzaW9uJywgdHlwZTogJ2ludCcsIG51bUJpdHM6IDEyIH0sIHsgbmFtZTogJ3B1cnBvc2VJZEJpdFN0cmluZycsIHR5cGU6ICdiaXRzJywgbnVtQml0czogMjQgfSwgeyBuYW1lOiAnbWF4VmVuZG9ySWQnLCB0eXBlOiAnaW50JywgbnVtQml0czogMTYgfSwgeyBuYW1lOiAnaXNSYW5nZScsIHR5cGU6ICdib29sJywgbnVtQml0czogMSB9LCB7XG4gICAgICBuYW1lOiAndmVuZG9ySWRCaXRTdHJpbmcnLFxuICAgICAgdHlwZTogJ2JpdHMnLFxuICAgICAgbnVtQml0czogZnVuY3Rpb24gbnVtQml0cyhkZWNvZGVkT2JqZWN0KSB7XG4gICAgICAgIHJldHVybiBkZWNvZGVkT2JqZWN0Lm1heFZlbmRvcklkO1xuICAgICAgfSxcbiAgICAgIHZhbGlkYXRvcjogZnVuY3Rpb24gdmFsaWRhdG9yKGRlY29kZWRPYmplY3QpIHtcbiAgICAgICAgcmV0dXJuICFkZWNvZGVkT2JqZWN0LmlzUmFuZ2U7XG4gICAgICB9XG4gICAgfSwge1xuICAgICAgbmFtZTogJ2RlZmF1bHRDb25zZW50JyxcbiAgICAgIHR5cGU6ICdib29sJyxcbiAgICAgIG51bUJpdHM6IDEsXG4gICAgICB2YWxpZGF0b3I6IGZ1bmN0aW9uIHZhbGlkYXRvcihkZWNvZGVkT2JqZWN0KSB7XG4gICAgICAgIHJldHVybiBkZWNvZGVkT2JqZWN0LmlzUmFuZ2U7XG4gICAgICB9XG4gICAgfSwge1xuICAgICAgbmFtZTogJ251bUVudHJpZXMnLFxuICAgICAgbnVtQml0czogMTIsXG4gICAgICB0eXBlOiAnaW50JyxcbiAgICAgIHZhbGlkYXRvcjogZnVuY3Rpb24gdmFsaWRhdG9yKGRlY29kZWRPYmplY3QpIHtcbiAgICAgICAgcmV0dXJuIGRlY29kZWRPYmplY3QuaXNSYW5nZTtcbiAgICAgIH1cbiAgICB9LCB7XG4gICAgICBuYW1lOiAndmVuZG9yUmFuZ2VMaXN0JyxcbiAgICAgIHR5cGU6ICdsaXN0JyxcbiAgICAgIGxpc3RDb3VudDogZnVuY3Rpb24gbGlzdENvdW50KGRlY29kZWRPYmplY3QpIHtcbiAgICAgICAgcmV0dXJuIGRlY29kZWRPYmplY3QubnVtRW50cmllcztcbiAgICAgIH0sXG4gICAgICB2YWxpZGF0b3I6IGZ1bmN0aW9uIHZhbGlkYXRvcihkZWNvZGVkT2JqZWN0KSB7XG4gICAgICAgIHJldHVybiBkZWNvZGVkT2JqZWN0LmlzUmFuZ2U7XG4gICAgICB9LFxuICAgICAgZmllbGRzOiBbe1xuICAgICAgICBuYW1lOiAnaXNSYW5nZScsXG4gICAgICAgIHR5cGU6ICdib29sJyxcbiAgICAgICAgbnVtQml0czogMVxuICAgICAgfSwge1xuICAgICAgICBuYW1lOiAnc3RhcnRWZW5kb3JJZCcsXG4gICAgICAgIHR5cGU6ICdpbnQnLFxuICAgICAgICBudW1CaXRzOiAxNlxuICAgICAgfSwge1xuICAgICAgICBuYW1lOiAnZW5kVmVuZG9ySWQnLFxuICAgICAgICB0eXBlOiAnaW50JyxcbiAgICAgICAgbnVtQml0czogMTYsXG4gICAgICAgIHZhbGlkYXRvcjogZnVuY3Rpb24gdmFsaWRhdG9yKGRlY29kZWRPYmplY3QpIHtcbiAgICAgICAgICByZXR1cm4gZGVjb2RlZE9iamVjdC5pc1JhbmdlO1xuICAgICAgICB9XG4gICAgICB9XVxuICAgIH1dXG4gIH1cbn07XG5cbm1vZHVsZS5leHBvcnRzID0ge1xuICB2ZXJzaW9uTnVtQml0czogdmVyc2lvbk51bUJpdHMsXG4gIHZlbmRvclZlcnNpb25NYXA6IHZlbmRvclZlcnNpb25NYXBcbn07IiwiJ3VzZSBzdHJpY3QnO1xuXG52YXIgX3JlcXVpcmUgPSByZXF1aXJlKCcuL3V0aWxzL2JpdHMnKSxcbiAgICBkZWNvZGVCaXRzVG9JZHMgPSBfcmVxdWlyZS5kZWNvZGVCaXRzVG9JZHMsXG4gICAgZGVjb2RlRnJvbUJhc2U2NCA9IF9yZXF1aXJlLmRlY29kZUZyb21CYXNlNjQ7XG5cbi8qKlxuICogRGVjb2RlIGNvbnNlbnQgZGF0YSBmcm9tIGEgd2ViLXNhZmUgYmFzZTY0LWVuY29kZWQgc3RyaW5nXG4gKlxuICogQHBhcmFtIHtzdHJpbmd9IGNvbnNlbnRTdHJpbmdcbiAqL1xuXG5cbmZ1bmN0aW9uIGRlY29kZUNvbnNlbnRTdHJpbmcoY29uc2VudFN0cmluZykge1xuICB2YXIgX2RlY29kZUZyb21CYXNlID0gZGVjb2RlRnJvbUJhc2U2NChjb25zZW50U3RyaW5nKSxcbiAgICAgIHZlcnNpb24gPSBfZGVjb2RlRnJvbUJhc2UudmVyc2lvbixcbiAgICAgIGNtcElkID0gX2RlY29kZUZyb21CYXNlLmNtcElkLFxuICAgICAgdmVuZG9yTGlzdFZlcnNpb24gPSBfZGVjb2RlRnJvbUJhc2UudmVuZG9yTGlzdFZlcnNpb24sXG4gICAgICBwdXJwb3NlSWRCaXRTdHJpbmcgPSBfZGVjb2RlRnJvbUJhc2UucHVycG9zZUlkQml0U3RyaW5nLFxuICAgICAgbWF4VmVuZG9ySWQgPSBfZGVjb2RlRnJvbUJhc2UubWF4VmVuZG9ySWQsXG4gICAgICBjcmVhdGVkID0gX2RlY29kZUZyb21CYXNlLmNyZWF0ZWQsXG4gICAgICBsYXN0VXBkYXRlZCA9IF9kZWNvZGVGcm9tQmFzZS5sYXN0VXBkYXRlZCxcbiAgICAgIGlzUmFuZ2UgPSBfZGVjb2RlRnJvbUJhc2UuaXNSYW5nZSxcbiAgICAgIGRlZmF1bHRDb25zZW50ID0gX2RlY29kZUZyb21CYXNlLmRlZmF1bHRDb25zZW50LFxuICAgICAgdmVuZG9ySWRCaXRTdHJpbmcgPSBfZGVjb2RlRnJvbUJhc2UudmVuZG9ySWRCaXRTdHJpbmcsXG4gICAgICB2ZW5kb3JSYW5nZUxpc3QgPSBfZGVjb2RlRnJvbUJhc2UudmVuZG9yUmFuZ2VMaXN0LFxuICAgICAgY21wVmVyc2lvbiA9IF9kZWNvZGVGcm9tQmFzZS5jbXBWZXJzaW9uLFxuICAgICAgY29uc2VudFNjcmVlbiA9IF9kZWNvZGVGcm9tQmFzZS5jb25zZW50U2NyZWVuLFxuICAgICAgY29uc2VudExhbmd1YWdlID0gX2RlY29kZUZyb21CYXNlLmNvbnNlbnRMYW5ndWFnZTtcblxuICB2YXIgY29uc2VudFN0cmluZ0RhdGEgPSB7XG4gICAgdmVyc2lvbjogdmVyc2lvbixcbiAgICBjbXBJZDogY21wSWQsXG4gICAgdmVuZG9yTGlzdFZlcnNpb246IHZlbmRvckxpc3RWZXJzaW9uLFxuICAgIGFsbG93ZWRQdXJwb3NlSWRzOiBkZWNvZGVCaXRzVG9JZHMocHVycG9zZUlkQml0U3RyaW5nKSxcbiAgICBtYXhWZW5kb3JJZDogbWF4VmVuZG9ySWQsXG4gICAgY3JlYXRlZDogY3JlYXRlZCxcbiAgICBsYXN0VXBkYXRlZDogbGFzdFVwZGF0ZWQsXG4gICAgY21wVmVyc2lvbjogY21wVmVyc2lvbixcbiAgICBjb25zZW50U2NyZWVuOiBjb25zZW50U2NyZWVuLFxuICAgIGNvbnNlbnRMYW5ndWFnZTogY29uc2VudExhbmd1YWdlXG4gIH07XG5cbiAgaWYgKGlzUmFuZ2UpIHtcbiAgICAvKiBlc2xpbnQgbm8tc2hhZG93OiBvZmYgKi9cbiAgICB2YXIgaWRNYXAgPSB2ZW5kb3JSYW5nZUxpc3QucmVkdWNlKGZ1bmN0aW9uIChhY2MsIF9yZWYpIHtcbiAgICAgIHZhciBpc1JhbmdlID0gX3JlZi5pc1JhbmdlLFxuICAgICAgICAgIHN0YXJ0VmVuZG9ySWQgPSBfcmVmLnN0YXJ0VmVuZG9ySWQsXG4gICAgICAgICAgZW5kVmVuZG9ySWQgPSBfcmVmLmVuZFZlbmRvcklkO1xuXG4gICAgICB2YXIgbGFzdFZlbmRvcklkID0gaXNSYW5nZSA/IGVuZFZlbmRvcklkIDogc3RhcnRWZW5kb3JJZDtcblxuICAgICAgZm9yICh2YXIgaSA9IHN0YXJ0VmVuZG9ySWQ7IGkgPD0gbGFzdFZlbmRvcklkOyBpICs9IDEpIHtcbiAgICAgICAgYWNjW2ldID0gdHJ1ZTtcbiAgICAgIH1cblxuICAgICAgcmV0dXJuIGFjYztcbiAgICB9LCB7fSk7XG5cbiAgICBjb25zZW50U3RyaW5nRGF0YS5hbGxvd2VkVmVuZG9ySWRzID0gW107XG5cbiAgICBmb3IgKHZhciBpID0gMTsgaSA8PSBtYXhWZW5kb3JJZDsgaSArPSAxKSB7XG4gICAgICBpZiAoZGVmYXVsdENvbnNlbnQgJiYgIWlkTWFwW2ldIHx8ICFkZWZhdWx0Q29uc2VudCAmJiBpZE1hcFtpXSkge1xuICAgICAgICBpZiAoY29uc2VudFN0cmluZ0RhdGEuYWxsb3dlZFZlbmRvcklkcy5pbmRleE9mKGkpID09PSAtMSkge1xuICAgICAgICAgIGNvbnNlbnRTdHJpbmdEYXRhLmFsbG93ZWRWZW5kb3JJZHMucHVzaChpKTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH1cbiAgfSBlbHNlIHtcbiAgICBjb25zZW50U3RyaW5nRGF0YS5hbGxvd2VkVmVuZG9ySWRzID0gZGVjb2RlQml0c1RvSWRzKHZlbmRvcklkQml0U3RyaW5nKTtcbiAgfVxuXG4gIHJldHVybiBjb25zZW50U3RyaW5nRGF0YTtcbn1cblxubW9kdWxlLmV4cG9ydHMgPSB7XG4gIGRlY29kZUNvbnNlbnRTdHJpbmc6IGRlY29kZUNvbnNlbnRTdHJpbmdcbn07IiwidmFyIHBtY19nZHByX3V0aWxzID0ge1xuICAvLyBSRkM0MTIyIGNvbXBsYWludCBVVUlEXG4gIHV1aWQ6IGZ1bmN0aW9uICgpIHtcbiAgICB2YXIgdXVpZCA9ICcnO1xuICAgIHZhciBpO1xuICAgIHZhciByYW5kb207XG5cbiAgICBmb3IgKGkgPSAwOyBpIDwgMzI7IGkrKykge1xuICAgICAgcmFuZG9tID0gTWF0aC5yYW5kb20oKSAqIDE2IHwgMDtcblxuICAgICAgaWYgKGkgPT09IDggfHwgaSA9PT0gMTIgfHwgaSA9PT0gMTYgfHwgaSA9PT0gMjApIHtcbiAgICAgICAgdXVpZCArPSAnLSc7XG4gICAgICB9XG5cbiAgICAgIHV1aWQgKz0gKGkgPT09IDEyXG4gICAgICAgID8gNFxuICAgICAgICA6IChpID09PSAxNlxuICAgICAgICAgID8gKHJhbmRvbSAmIDMgfCA4KVxuICAgICAgICAgIDogcmFuZG9tXG4gICAgICAgIClcbiAgICAgICkudG9TdHJpbmcoMTYpO1xuICAgIH1cblxuICAgIHJldHVybiB1dWlkO1xuICB9LFxuXG4gIGF0dGFjaEhhbmRsZXI6IGZ1bmN0aW9uIChlbCwgdHlwZSwgZikge1xuICAgIGlmIChlbC5hZGRFdmVudExpc3RlbmVyKSB7XG4gICAgICBlbC5hZGRFdmVudExpc3RlbmVyKHR5cGUsIGYsIGZhbHNlKTtcbiAgICB9IGVsc2UgaWYgKGVsLmF0dGFjaEV2ZW50KSB7XG4gICAgICBlbC5hdHRhY2hFdmVudCgnb24nICsgdHlwZSwgZik7XG4gICAgfVxuICB9LFxuXG4gIGdlbmVyYXRlVVVJRDogZnVuY3Rpb24gKCkge1xuICAgIHJldHVybiAneHh4eHh4eHgteHh4eC00eHh4LXl4eHgteHh4eHh4eHh4eHh4Jy5yZXBsYWNlKC9beHldL2csIGZ1bmN0aW9uIChjKSB7XG4gICAgICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tYml0d2lzZVxuICAgICAgY29uc3QgciA9IE1hdGgucmFuZG9tKCkgKiAxNiB8IDA7XG5cbiAgICAgIGNvbnN0IHYgPSBjID09PSAneCcgPyByIDogKChyICYgMHgzKSB8IDB4OCk7XG5cbiAgICAgIHJldHVybiB2LnRvU3RyaW5nKDE2KTtcbiAgICB9KTtcbiAgfSxcblxuICBnZXROb3c6IGZ1bmN0aW9uICgpIHtcbiAgICByZXR1cm4gRGF0ZS5ub3cgPyBEYXRlLm5vdygpIDogKG5ldyBEYXRlKCkpLmdldFRpbWUoKTtcbiAgfSxcblxuICBnZXRYaHJPcHRpb25zOiBmdW5jdGlvbiAob3B0aW9ucykge1xuICAgIHJldHVybiB7XG4gICAgICBtZXRob2Q6IG9wdGlvbnMubWV0aG9kIHx8ICdQT1NUJyxcbiAgICAgIHVybDogb3B0aW9ucy51cmwsXG4gICAgICBoZWFkZXJzOiBvcHRpb25zLmhlYWRlcnMgfHwge1xuICAgICAgICAnQ29udGVudC10eXBlJzogJ2FwcGxpY2F0aW9uL2pzb247IGNoYXJzZXQ9dXRmLTgnXG4gICAgICB9XG4gICAgfTtcbiAgfSxcblxuICBnZXRNZXRhOiBmdW5jdGlvbiAob3B0aW9ucykge1xuICAgIHJldHVybiB7XG4gICAgICBpZDogdGhpcy51dWlkKCksXG4gICAgICBuYW1lc3BhY2U6IG9wdGlvbnMubmFtZXNwYWNlXG4gICAgfTtcbiAgfSxcblxuICBnZXRSZXF1ZXN0RGF0YTogZnVuY3Rpb24gKGRhdGEsIG1ldGEpIHtcbiAgICByZXR1cm4gSlNPTi5zdHJpbmdpZnkoe1xuICAgICAgcGF5bG9hZDogZGF0YSxcbiAgICAgIG1ldGE6IG1ldGFcbiAgICB9KTtcbiAgfVxufTtcblxuZXhwb3J0IGRlZmF1bHQgcG1jX2dkcHJfdXRpbHM7XG4iLCJpbXBvcnQgcG1jX2dkcHJfdXRpbHMgZnJvbSAnLi9wbWNfZ2Rwcl91dGlscyc7XG5cbi8qKlxuICogTW9kdWxlIHRoYXQgc2F2ZXMgZGF0YSB0byBsb2NhbCBzdG9yYWdlIGJ1dCBleHBpcmVzIGl0IGFmdGVlciBhIGNlcnRhaW5cbiAqIHBlcmlvZCBvZiB0aW1lLCBmb3JtaW5nIGEgbG9jYWwgY2FjaGUuXG4gKi9cbmNvbnN0IHJldHJpZXZlZCA9IHt9O1xuXG5jb25zdCB3ID0gd2luZG93O1xuXG4vKipcbiAqIFNldCBkYXRhIGluIHRoZSBicm93c2VyIGxvY2FsIHN0b3JhZ2UuXG4gKiBAcGFyYW0ge3N0cmluZ30gbmFtZVxuICogQHBhcmFtIHtvYmplY3R9IGRhdGFcbiAqIEBwYXJhbSB7bnVtYmVyfSBleHBpcmVzXG4gKi9cbmZ1bmN0aW9uIHNldERhdGEgKG5hbWUsIGRhdGEsIGV4cGlyZXMpIHtcbiAgY29uc3QgY2FjaGUgPSB7ICdleHBpcmVzJzogZXhwaXJlcywgJ2RhdGEnOiBkYXRhIH07XG5cbiAgcmV0cmlldmVkW25hbWVdID0gY2FjaGU7XG4gIHRyeSB7XG4gICAgdy5sb2NhbFN0b3JhZ2Uuc2V0SXRlbSgncG1jLmNhY2hlLicgKyBuYW1lLCBKU09OLnN0cmluZ2lmeShjYWNoZSkpO1xuICB9IGNhdGNoIChlKSB7IC8qIERvIG5vdGhpbmcuICovIH1cbn1cblxuLyoqXG4gKiBHZXQgZGF0YSBmcm9tIHRoZSBicm93c2VyIGxvY2FsIHN0b3JhZ2UuXG4gKiBAcGFyYW0ge3N0cmluZ30gbmFtZVxuICogQHJldHVybnMge29iamVjdH0gZGF0YVxuICovXG5mdW5jdGlvbiBnZXREYXRhIChuYW1lKSB7XG4gIGxldCBjYWNoZWQgPSByZXRyaWV2ZWRbbmFtZV07XG5cbiAgaWYgKCFjYWNoZWQpIHtcbiAgICB0cnkge1xuICAgICAgY2FjaGVkID0gSlNPTi5wYXJzZSh3LmxvY2FsU3RvcmFnZS5nZXRJdGVtKCdmcmlzYmVlLmNhY2hlLicgKyBuYW1lKSk7XG4gICAgICByZXRyaWV2ZWRbbmFtZV0gPSBjYWNoZWQ7XG4gICAgfSBjYXRjaCAoZSkgeyAvKiBEbyBub3RoaW5nLiAqLyB9XG4gIH1cblxuICAvLyBFeHBpcmVzID09IDAgb3IgbnVsbCBtZWFucyBpdCBuZXZlciBleHBpcmVzLlxuICBpZiAoY2FjaGVkICYmICghY2FjaGVkLmV4cGlyZXMgfHwgY2FjaGVkLmV4cGlyZXMgPiBwbWNfZ2Rwcl91dGlscy5nZXROb3coKSkpIHtcbiAgICByZXR1cm4gY2FjaGVkLmRhdGE7XG4gIH1cblxuICByZXR1cm4gbnVsbDtcbn1cblxuLyoqXG4gKiBEZWxldGUgZGF0YSBmcm9tIHRoZSBicm93c2VyIGxvY2FsIHN0b3JhZ2UuXG4gKiBAcGFyYW0ge3N0cmluZ30gbmFtZVxuICovXG5mdW5jdGlvbiBkcm9wRGF0YSAobmFtZSkge1xuICBkZWxldGUgcmV0cmlldmVkW25hbWVdO1xuXG4gIHRyeSB7XG4gICAgdy5sb2NhbFN0b3JhZ2UucmVtb3ZlSXRlbSgnZnJpc2JlZS5jYWNoZS4nICsgbmFtZSk7XG4gIH0gY2F0Y2ggKGUpIHsgLyogRG8gbm90aGluZy4gKi8gfVxufVxuXG4vLyBXYXRjaCBmb3IgY2hhbmdlcyB0byB0aGUgY2FjaGUgaW4gb3RoZXIgd2luZG93cywgYW5kIHN0b3JlIHRoZSBjaGFuZ2VzXG4vLyBsb2NhbGx5IGluIHRoaXMgd2luZG93IGFzIHdlbGwgc28gd2UgZG9uJ3QgZ28gdG8gdGhlIGRpc2sgaWYgd2UgZG9uJ3Rcbi8vIGhhdmUgdG8uXG5wbWNfZ2Rwcl91dGlscy5hdHRhY2hIYW5kbGVyKHcsICdzdG9yYWdlJywgZnVuY3Rpb24gKGUpIHtcbiAgaWYgKGUua2V5LmluZGV4T2YoJ2ZyaXNiZWUuY2FjaGUuJykgPT09IDApIHtcbiAgICBkZWxldGUgcmV0cmlldmVkW2Uua2V5XTtcblxuICAgIHRyeSB7XG4gICAgICByZXRyaWV2ZWRbZS5rZXldID0gSlNPTi5wYXJzZShlLm5ld1ZhbHVlKTtcbiAgICB9IGNhdGNoIChlKSB7IC8qIERvIG5vdGhpbmcuICovIH1cbiAgfVxufSk7XG5cbmV4cG9ydCBkZWZhdWx0IHtcbiAgc2V0RGF0YTogc2V0RGF0YSxcbiAgZ2V0RGF0YTogZ2V0RGF0YSxcbiAgZHJvcERhdGE6IGRyb3BEYXRhXG59O1xuIiwiaW1wb3J0IENhY2hlTWFuYWdlciBmcm9tICcuL0NhY2hlTWFuYWdlcic7XG5pbXBvcnQgcG1jX2dkcHJfdXRpbHMgZnJvbSAnLi9wbWNfZ2Rwcl91dGlscyc7XG5cbi8qKlxuICogSW5qZWN0cyBhbmQgbWFuYWdlcyBRdWFudGNhc3QgQ2hvaWNlLlxuICovXG5leHBvcnQgZGVmYXVsdCBjbGFzcyBRUUNSZXBvcnRlciB7XG4gIC8qKlxuICAgICAqIEluamVjdHMgQ2hvaWNlIGludG8gdGhlIHBhZ2Ugd2l0aCBhIGNvbmZpZyBvYmplY3QuXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gW2NtcENvbmZpZ10gIERlZmF1bHQgaXMgcmVzcG9uc2l2ZSB0byBoZWFkZXIuanNcbiAgICAgKi9cbiAgc3RhdGljIGluaXQgKCkge1xuICAgIGlmICghd2luZG93Ll9fY21wKSB7XG4gICAgICByZXR1cm47XG4gICAgfVxuXG4gICAgY29uc3QgZGF5c0JldHdlZW5EaXNwbGF5ID0gNztcblxuICAgIC8qKlxuICAgICAgICAgKiBIYW5kbGVzIHNpZ25hbCB0aGF0IHRoZSBVSSBoYXMgYmVlbiBjbGlja2VkLlxuICAgICAgICAgKi9cbiAgICBjb25zdCB1SUNsaWNrZWRDYWxsYmFjayA9IGZ1bmN0aW9uICgpIHtcbiAgICAgIC8qKlxuICAgICAgICAgICAgICpcbiAgICAgICAgICAgICAqIEBwYXJhbSB7b2JqZWN0fSBkYXRhIC0gcmVzcG9uc2UgZnJvbSBjYWxsIHRvIGNtcCBnZXRDb25zZW50RGF0YVxuICAgICAgICAgICAgICogQHBhcmFtIHtib29sZWFufSBzdWNjZXNzIC0gaWYgdHJ1ZSwgd2UgaGF2ZSBhIHZhbGlkIHJlc3BvbnNlXG4gICAgICAgICAgICAgKi9cbiAgICAgIGNvbnN0IGNvbnNlbnRUcmFja2luZ0NhbGxiYWNrID0gZnVuY3Rpb24gKGRhdGEsIHN1Y2Nlc3MpIHtcbiAgICAgICAgaWYgKHN1Y2Nlc3MpIHtcbiAgICAgICAgICBjb25zdCBuZXdFdVB1YkNvbnNlbnQgPSBRUUNSZXBvcnRlci5nZXRDb29raWUoJ2V1cHViY29uc2VudCcpO1xuXG4gICAgICAgICAgY29uc3QgbmV3RXVDb25zZW50ID0gZGF0YS5jb25zZW50RGF0YTtcblxuICAgICAgICAgIGxldCBjb25zZW50SWQgPSBDYWNoZU1hbmFnZXIuZ2V0RGF0YSgnY2hvaWNlQ29uc2VudElEJyk7XG5cbiAgICAgICAgICAvLyBJZiB0aGUgbmV3IGRhdGEgZG9lcyBub3QgbWF0Y2ggdGhlIHByZXZpb3VzIGRhdGEsIHRoZVxuICAgICAgICAgIC8vIHVzZXIgaGFzIGNoYW5nZWQgY29uc2VudC4gIFJlcG9ydCBpdC5cbiAgICAgICAgICBpZiAoIWNvbnNlbnRJZCB8fCBuZXdFdVB1YkNvbnNlbnQgIT09IG9yaWdpbmFsRXVQdWJDb25zZW50IHx8IG5ld0V1Q29uc2VudCAhPT0gb3JpZ2luYWxFdUNvbnNlbnQpIHtcbiAgICAgICAgICAgIGNvbnN0IG9uZURheSA9IDg2NDAwMDAwO1xuXG4gICAgICAgICAgICAvLyBHZW5lcmF0ZSBvdXIgb3duIHJhbmRvbSBJRCBmb3IgdGhpcyBjb25zZW50IHJlY29yZC5cbiAgICAgICAgICAgIGlmICghY29uc2VudElkKSB7XG4gICAgICAgICAgICAgIGNvbnNlbnRJZCA9IHBtY19nZHByX3V0aWxzLmdlbmVyYXRlVVVJRCgpO1xuICAgICAgICAgICAgICBDYWNoZU1hbmFnZXIuc2V0RGF0YSgnY2hvaWNlQ29uc2VudElEJywgY29uc2VudElkLCAocG1jX2dkcHJfdXRpbHMuZ2V0Tm93KCkgKyAob25lRGF5ICogZGF5c0JldHdlZW5EaXNwbGF5ICogMTMpKSk7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIENhY2hlTWFuYWdlci5zZXREYXRhKCdldWNvbnNlbnQnLCBkYXRhLmNvbnNlbnREYXRhLCAocG1jX2dkcHJfdXRpbHMuZ2V0Tm93KCkgKyAob25lRGF5ICogZGF5c0JldHdlZW5EaXNwbGF5KSkpO1xuXG4gICAgICAgICAgICBRUUNSZXBvcnRlci5zZW5kQ29uc2VudERhdGFUb0ZyaXNiZWUoe1xuICAgICAgICAgICAgICBjaG9pY2VDb25zZW50SUQ6IGNvbnNlbnRJZCxcbiAgICAgICAgICAgICAgZXVjb25zZW50OiBuZXdFdUNvbnNlbnQsXG4gICAgICAgICAgICAgIGV1cHViY29uc2VudDogbmV3RXVQdWJDb25zZW50XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIENtcEdhdGUgJiYgQ21wR2F0ZS5oYW5kbGVBbmFseXRpY3NDb25zZW50ICYmIENtcEdhdGUuaGFuZGxlQW5hbHl0aWNzQ29uc2VudChuZXdFdUNvbnNlbnQpO1xuICAgICAgICAgICAgb3JpZ2luYWxFdUNvbnNlbnQgPSBuZXdFdUNvbnNlbnQ7XG4gICAgICAgICAgICBvcmlnaW5hbEV1UHViQ29uc2VudCA9IG5ld0V1UHViQ29uc2VudDtcbiAgICAgICAgICB9XG4gICAgICAgICAgLy8gQWRkIHRoZVxuICAgICAgICB9XG5cbiAgICAgICAgLy8gV2FpdCBmb3IgdGhlIGNsaWNrIGFnYWluLiBUaGUgdGltZW91dCBpcyBuZWVkZWQgdG8gZXNjYXBlIHRoZVxuICAgICAgICAvLyBjdXJyZW50IGNhbGxiYWNrIChvdGhlcndpc2Ugd2UgcmVjdXJzZSkuXG4gICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24gKCkge1xuICAgICAgICAgIHdpbmRvdy5fX2NtcCgnc2V0Q29uc2VudFVpQ2FsbGJhY2snLCB1SUNsaWNrZWRDYWxsYmFjayk7XG4gICAgICAgIH0sIDEpO1xuICAgICAgfTtcblxuICAgICAgLy8gT25jZSB3ZSBoYXZlIGEgY2xpY2sgc2lnbmFsLCBhc2sgZm9yIHRoZSBjb25zZW50IGRhdGEuXG4gICAgICB3aW5kb3cuX19jbXAoJ2dldENvbnNlbnREYXRhJywgbnVsbCwgY29uc2VudFRyYWNraW5nQ2FsbGJhY2spO1xuICAgIH07XG5cbiAgICAvLyBTZXQgYSBjYWxsYmFjayBmb3IgaW50ZXJhY3Rpb24gd2l0aCB0aGUgVUkgc28gd2UgY2FuIHJlcG9ydCB0b1xuICAgIC8vIEZyaXNiZWUuXG4gICAgd2luZG93Ll9fY21wKCdzZXRDb25zZW50VWlDYWxsYmFjaycsIHVJQ2xpY2tlZENhbGxiYWNrKTtcbiAgfVxuXG4gIC8qKlxuICAgICAqIEdldHMgYSBzcGVjaWZpZWQgY29va2llLiBBdCBwcmVzZW50IGZpcnN0IHBhcnR5IGNvb2tpZXMgb25seS5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBjb29raWVOYW1lXG4gICAgICogQHJldHVybnMgeyp9XG4gICAgICovXG4gIHN0YXRpYyBnZXRDb29raWUgKGNvb2tpZU5hbWUpIHtcbiAgICBjb25zdCBwYXR0ZXJuID0gUmVnRXhwKCcoPzpefDsgKiknICsgY29va2llTmFtZSArICc9KC5bXjtdKiknKTtcblxuICAgIGNvbnN0IG1hdGNoZWQgPSBkb2N1bWVudC5jb29raWUubWF0Y2gocGF0dGVybik7XG5cbiAgICBpZiAobWF0Y2hlZCkge1xuICAgICAgcmV0dXJuIG1hdGNoZWRbMV07XG4gICAgfVxuXG4gICAgcmV0dXJuIGZhbHNlO1xuICB9XG5cbiAgLyoqXG4gICAgICogUmVjb3JkIG91ciBhdWRpdCB0cmFjZXMgaW4gRnJpc2JlZS5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBxdWFudGNhc3RDb25zZW50RGF0YVxuICAgICAqL1xuICBzdGF0aWMgc2VuZENvbnNlbnREYXRhVG9GcmlzYmVlIChxdWFudGNhc3RDb25zZW50RGF0YSkge1xuICAgIGNvbnN0IGZyaXNiZWUgPSBuZXcgRnJpc2JlZSh7XG4gICAgICAnbmFtZXNwYWNlJzogJ3Byb2QtZ2Rwci1zdHJlYW0nLFxuICAgICAgJ3VybCc6ICdodHRwczovL2NvbGxlY3Rvci5zaGVrbm93cy5jb20vZXZlbnQnXG4gICAgfSk7XG4gICAgcXVhbnRjYXN0Q29uc2VudERhdGEub3JpZ2luID0gd2luZG93LmxvY2F0aW9uLm9yaWdpbjtcbiAgICBmcmlzYmVlLmFkZChxdWFudGNhc3RDb25zZW50RGF0YSk7XG4gICAgZnJpc2JlZS5zZW5kQWxsKCk7XG4gIH1cbn1cbiIsImltcG9ydCBRUUNSZXBvcnRlciBmcm9tICcuL1FRQ1JlcG9ydGVyLmpzJztcbmltcG9ydCB7IENvbnNlbnRTdHJpbmcgfSBmcm9tICdjb25zZW50LXN0cmluZyc7XG4vKiogVGhlIF5eXiBDb25zZW50U3RyaW5nIGNsYXNzIGNvbWVzIGZyb20gdGhlIElBQi4gSXQgYWxsb3dzIHVzIHRvIGRlY29kZSB0aGUgY29uc2VudCBzdHJpbmcgdG8gZGV0ZXJtaW5lXG4gKiB3aGF0IGRlZ3JlZSBvZiBjb25zZW50IHRoZSB1c2VyIGhhcyBncmFudGVkIGZvciBkYXRhIGNvbGxlY3Rpb25cbiAqIFwiZGVzY3JpcHRpb25cIjogXCJFbmNvZGUgYW5kIGRlY29kZSB3ZWItc2FmZSBiYXNlNjQgY29uc2VudCBpbmZvcm1hdGlvbiB3aXRoIHRoZSBJQUIgRVUncyBHRFBSIFRyYW5zcGFyZW5jeSBhbmQgQ29uc2VudCBGcmFtZXdvcmtcIixcbiAqIFwiaG9tZXBhZ2VcIjogXCJodHRwczovL2dpdGh1Yi5jb20vSW50ZXJhY3RpdmVBZHZlcnRpc2luZ0J1cmVhdS9Db25zZW50LVN0cmluZy1TREstSlNcIixcbiAqL1xuY29uc3QgQ21wUHJvY2VzcyA9IHtcbiAgY2hlY2tGb3JOb25FVUNNUCAoKSB7XG4gICAgY29uc3QgZXVjb25zZW50ID0gdGhpcy5nZXRDb29raWUoJ2V1cHViY29uc2VudCcpO1xuICAgIGlmIChldWNvbnNlbnQpIHtcbiAgICAgIHRoaXMuaGFuZGxlQW5hbHl0aWNzQ29uc2VudChldWNvbnNlbnQpO1xuICAgIH0gZWxzZSB7XG4gICAgICAvLyBObyBvcHQtb3V0c1xuICAgICAgdGhpcy5pbml0QW5hbHl0aWNzKHRydWUpO1xuICAgIH1cbiAgfSxcbiAgc2V0Q29uc2VudEJvZHlDbGFzcyAoKSB7XG4gICAgZG9jdW1lbnQuYm9keS5jbGFzc0xpc3QucmVtb3ZlKCd3YWl0aW5nRm9yQ21wJyk7XG4gICAgZG9jdW1lbnQuYm9keS5jbGFzc0xpc3QuYWRkKCdoYXNDbXAnKTtcbiAgfSxcblxuICBnZXRDb29raWUgKGNuYW1lKSB7XG5cdGNvbnN0IGRlY29kZWRDb29raWUgPSBkZWNvZGVVUklDb21wb25lbnQoZG9jdW1lbnQuY29va2llKTtcbiAgICBjb25zdCBjb29raWVTcGx1dCA9IGRlY29kZWRDb29raWUuc3BsaXQoJzsgJyk7XG4gICAgbGV0IGNvbnNlbnRWYWx1ZSA9ICcnO1xuICAgIGNvb2tpZVNwbHV0LmZvckVhY2goKGNhKSA9PiB7XG4gICAgICBpZiAoY2EuaW5kZXhPZihjbmFtZSkgPT09IDApIHtcbiAgICAgICAgY29uc2VudFZhbHVlID0gY2Euc3BsaXQoJz0nKVsgMSBdO1xuICAgICAgfVxuICAgIH0pXG4gICAgO1xuICAgIHJldHVybiBjb25zZW50VmFsdWU7XG4gIH0sXG4gIC8vIFRoZXNlIGlkcyBhcmUgZnJvbSB0aGUgSUFCIHZlbmRvcnMgbGlzdDogaHR0cHM6Ly9naXRodWIuY29tL0ludGVyYWN0aXZlQWR2ZXJ0aXNpbmdCdXJlYXUvR0RQUi1UcmFuc3BhcmVuY3ktYW5kLUNvbnNlbnQtRnJhbWV3b3JrL2Jsb2IvbWFzdGVyL3JlZmVyZW5jZS9zcmMvZG9jcy9hc3NldHMvdmVuZG9ybGlzdC5qc29uXG4gIC8vIFdlIG5lZWQgdG8gZ2V0IFN0b3JhZ2UgY29uc2VudCBmb3IgY29va2llcy5cbiAgLy8gV2UgbmVlZCB0byBnZXQgTWVhc3VyZW1lbnQgY29uc2VudCBmb3IgR0EgYW5kIG9tbmkgYW5kIGhvdGphci5cbiAgaGFuZGxlQW5hbHl0aWNzQ29uc2VudCAoZXVjb25zZW50KSB7XG4gICAgY29uc3QgY29uc2VudERhdGEgPSBuZXcgQ29uc2VudFN0cmluZyhldWNvbnNlbnQpO1xuICAgIGlmICghY29uc2VudERhdGEpIHtcbiAgICAgIGNvbnNvbGUuZXJyb3IoYEVycm9yIHdpdGggY29uc2VudCBzdHJpbmc6IHtldWNvbnNlbnR9YCk7XG4gICAgICByZXR1cm47XG4gICAgfVxuICAgIGNvbnN0IHN0b3JhZ2VDb25zZW50ID0gY29uc2VudERhdGEuaXNQdXJwb3NlQWxsb3dlZCgxKTtcbiAgICBjb25zdCBtZWFzdXJlbWVudENvbnNlbnQgPSBjb25zZW50RGF0YS5pc1B1cnBvc2VBbGxvd2VkKDUpO1xuICAgIGlmIChtZWFzdXJlbWVudENvbnNlbnQpIHtcbiAgICAgIHRoaXMuaW5pdEFuYWx5dGljcyhzdG9yYWdlQ29uc2VudCk7XG4gICAgfVxuICB9LFxuICBtb2RpZnlDb25zZW50ICgpIHtcbiAgICAvLyBUaGlzIGlzIGNhbGxlZCB3aGVuIHRoZSBDb25zZW50IFVJIGlzIGNsb3NlZC4gVGhpcyBzaG91bGQgb25seSBmaXJlIGlmXG4gICAgLy8gdGhlIHVzZXIgbWFudWFsbHkgb3BlbnMgdGhlIHByZWZlcmVuY2Ugc2NyZWVuLiBNYXkgb3IgbWF5IG5vdCBoYXZlIGNoYW5nZWQgYW55IGNvbnNlbnQuXG4gICAgd2luZG93Ll9fY21wKCdnZXRDb25zZW50RGF0YScsIG51bGwsIGZ1bmN0aW9uIChjb25zZW50SW5mbywgc3VjY2Vzcykge1xuICAgICAgaWYgKGNvbnNlbnRJbmZvLmNvbnNlbnREYXRhKSB7XG4gICAgICAgIC8vIHdlIGhhdmUgYSBjb25zZW50IHN0cmluZy4gbGV0J3MgcGFyc2UgaXQuXG4gICAgICAgIGNvbnN0IGNvbnNlbnREYXRhID0gbmV3IENvbnNlbnRTdHJpbmcoY29uc2VudEluZm8uY29uc2VudERhdGEpO1xuICAgICAgICBpZiAoIWNvbnNlbnREYXRhKSB7XG4gICAgICAgICAgY29uc29sZS5lcnJvcihgRXJyb3Igd2l0aCBjb25zZW50IHN0cmluZzoge2NvbnNlbnRJbmZvLmNvbnNlbnREYXRhfWApO1xuICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuICAgICAgICBjb25zdCBzdG9yYWdlQ29uc2VudCA9IGNvbnNlbnREYXRhLmlzUHVycG9zZUFsbG93ZWQoMSk7XG4gICAgICAgIGNvbnN0IG1lYXN1cmVtZW50Q29uc2VudCA9IGNvbnNlbnREYXRhLmlzUHVycG9zZUFsbG93ZWQoNSk7XG4gICAgICAgIGlmICghbWVhc3VyZW1lbnRDb25zZW50KSB7XG4gICAgICAgICAgLy8gdHJ5IHRvIHN0b3AgY29sbGVjdGluZ1xuICAgICAgICAgIHdpbmRvdy5nYShmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICBjb25zdCB0cmFja2VycyA9IHdpbmRvdy5nYS5nZXRBbGwoKTtcbiAgICAgICAgICAgIHRyYWNrZXJzLmZvckVhY2goKHRyYWNrZXIpID0+IHtcbiAgICAgICAgICAgICAgbGV0IHVpZCA9IHRyYWNrZXIuYi5kYXRhLnZhbHVlc1snOnRyYWNraW5nSWQnXTtcbiAgICAgICAgICAgICAgaWYgKHVpZCkge1xuICAgICAgICAgICAgICAgIHdpbmRvd1snZ2EtZGlzYWJsZS0nICsgdWlkXSA9IHRydWU7XG4gICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgICAgICAgIH0pO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgIENtcFByb2Nlc3MuaW5pdEFuYWx5dGljcyhzdG9yYWdlQ29uc2VudCk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9KTtcbiAgfSxcbiAgaW5pdEFuYWx5dGljcyAoc3RvcmFnZUNvbnNlbnQpIHtcbiAgICAvLyBPbmUgcGxhY2UgdG8gY2FsbCBhbGwgZ2F0ZWQgbWVhc3VyZW1lbnRzXG4gICAgd2luZG93LmxvYWRHQSAmJiB3aW5kb3cubG9hZEdBKHN0b3JhZ2VDb25zZW50KTtcbiAgfVxufTtcbi8vIENvbGxlY3QgZm9yIGNvbXBsaWFuY2VcblFRQ1JlcG9ydGVyLmluaXQoKTtcblxud2luZG93Ll9fY21wKCdnZXRDb25zZW50RGF0YScsIG51bGwsIGZ1bmN0aW9uIChjb25zZW50SW5mbywgc3VjY2Vzcykge1xuICAvLyBVbnRpbCB0aGlzIGNhbGxiYWNrIGZpcmVzLCB3ZSBhcmUgd2FpdGluZyBvbiBhIGNvbnNlbnQgZGVjaXNpb24uIFRoZXJlZm9yZSxcbiAgLy8gbm8gb3RoZXIgdHJhY2tpbmcvYWR2ZXJ0aXNpbmcgaW4gdGhlIGJyb3dzZXIgc2hvdWxkIGhhcHBlbi5cblxuICAvLyBJZiB3ZSBnZXQgY29uc2VudEluZm8uY29uc2VudERhdGEsIHdlIGhhdmUgYSBkZWNpc2lvbiBtYWRlLCBlaXRoZXIgdGhpcyBzZXNzaW9uIG9yIHByZXZpb3VzbHkuXG4gIC8vIE5vdyB3ZSBwcm9jZXNzIHRoYXQgIHN0cmluZyBhbmQgcnVuIHdpdGggaXQuIFRoaXMgaW1wbGllcyB3ZSBhcmUgaW4gdGhlIEVVXG4gIC8vXG4gIC8vIElmIHRoZXJlIGlzbid0IGNvbnNlbnRJbmZvLmNvbnNlbnRkYXRhIHZhbHVlLCB3ZSdyZSBub3QgaW4gdGhlIEVVLiBQTUMgaGFzIG1hZGUgYSBkZWNpc2lvblxuICAvLyB0byByZXNwZWN0IGFsbCBwcml2YWN5IG9wdG9ucywgYW5kIHNvIHNob3VsZCBjaGVjayBmb3IgYSBjb29raWUganVzdCBpbiBjYXNlLlxuICBpZiAoY29uc2VudEluZm8uY29uc2VudERhdGEpIHtcbiAgICAvLyB3ZSBoYXZlIGEgY29uc2VudCBzdHJpbmcuIGxldCdzIHBhcnNlIGl0LlxuICAgIENtcFByb2Nlc3MuaGFuZGxlQW5hbHl0aWNzQ29uc2VudChjb25zZW50SW5mby5jb25zZW50RGF0YSk7XG4gIH0gZWxzZSB7XG4gICAgQ21wUHJvY2Vzcy5jaGVja0Zvck5vbkVVQ01QKCk7XG4gIH1cbiAgQ21wUHJvY2Vzcy5zZXRDb25zZW50Qm9keUNsYXNzKCk7XG4gIC8vIFRoaXMgIG5leHQgY2FsbGJhY2sgc2hvdWxkIG9ubHkgZmlyZSBpZiB0aGUgdXNlciBtYW51YWxseSBvcGVucyB0aGUgcHJlZmVyZW5jZSBzY3JlZW4uXG4gIC8vIFRoZSB0aW1lb3V0IGlzIG5lZWRlZCB0byBlc2NhcGUgdGhlIGN1cnJlbnQgY2FsbGJhY2sgKG90aGVyd2lzZSB3ZSByZWN1cnNlKS5cbiAgc2V0VGltZW91dChmdW5jdGlvbiAoKSB7IHdpbmRvdy5fX2NtcCgnc2V0Q29uc2VudFVpQ2FsbGJhY2snLCBDbXBQcm9jZXNzLm1vZGlmeUNvbnNlbnQpOyB9LCAxKTtcbn0pO1xuIl0sInNvdXJjZVJvb3QiOiIifQ==