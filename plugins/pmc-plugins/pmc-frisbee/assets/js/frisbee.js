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
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
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
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";

// CONCATENATED MODULE: ./src/Queue.js
function Queue () {
  var queue = []
  var _this = this
  this.length = 0

  var updateLength = function () {
    _this.length = queue.length
  }

  this.enqueue = function (item) {
    queue.push(item)
    updateLength()
  }

  this.dequeue = function (quantity) {
    if (!quantity) {
      return
    }
    // Time complexity for Array.prototype.splice is O(n)
    // For a very large queue this method needs to be optimized
    var dequeued = queue.splice(0, quantity)
    updateLength()

    return dequeued
  }
}

/* harmony default export */ var src_Queue = (Queue);

// CONCATENATED MODULE: ./src/Request.js
function Request (options) {
  options = options || {}
  var xhr = new XMLHttpRequest()
  var headers = options.headers || {}
  var async = true

  xhr.open(options.method, options.url, async)

  Object.keys(headers).forEach(function (headerKey) {
    xhr.setRequestHeader(headerKey, headers[headerKey])
  })

  return xhr
}

/* harmony default export */ var src_Request = (Request);

// CONCATENATED MODULE: ./src/utils.js
var utils = {
  // RFC4122 complaint UUID
  uuid: function () {
    var uuid = ''
    var i
    var random

    for (i = 0; i < 32; i++) {
      random = Math.random() * 16 | 0

      if (i === 8 || i === 12 || i === 16 || i === 20) {
        uuid += '-'
      }

      uuid += (i === 12
        ? 4
        : (i === 16
          ? (random & 3 | 8)
          : random
        )
      ).toString(16)
    }

    return uuid
  },

  getXhrOptions: function (options) {
    return {
      method: options.method || 'POST',
      url: options.url,
      headers: options.headers || {
        'Content-type': 'application/json; charset=utf-8'
      }
    }
  },

  getMeta: function (options) {
    return {
      id: this.uuid(),
      namespace: options.namespace
    }
  },

  getRequestData: function (data, meta) {
    return JSON.stringify({
      payload: data,
      meta: meta
    })
  }
}

/* harmony default export */ var src_utils = (utils);

// CONCATENATED MODULE: ./src/Frisbee.js





function Frisbee (options) {
  var queue = new src_Queue()
  options = options || {}
  var maxItems = options.maxItems || 5
  var xhrOptions = src_utils.getXhrOptions(options)
  var meta = src_utils.getMeta(options)
  var getRequestData = typeof options.getRequestData === 'function'
    ? options.getRequestData
    : src_utils.getRequestData

  var send = function (quantity) {
    var data = queue.dequeue(quantity)
    var xhr = new src_Request(xhrOptions)
    var requestData = getRequestData(data, meta)

    if (requestData) {
      xhr.send(requestData)
    }
  }

  this.add = function (item) {
    queue.enqueue(item)
    if (queue.length === maxItems) {
      send(maxItems)
    }
  }

  this.sendAll = function () {
    var queueLength = queue.length
    if (!queueLength) {
      return
    }

    send(queueLength)
  }
}

/* harmony default export */ var src_Frisbee = (Frisbee);

// CONCATENATED MODULE: ./src/build.js


window.Frisbee = src_Frisbee


/***/ })
/******/ ]);
