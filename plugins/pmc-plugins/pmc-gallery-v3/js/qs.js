/**
 * Based on hash.js
 */
;( function(window, undefined) {
	"use strict";

	var qs = (function() {

		var fromQuerystring = function() {
			var params = window.location.search ? window.location.search.substr(1).split("&") : [],
				paramsObject = {};

			for(var i = 0; i < params.length; i++) {
				var a = params[i].split("=");
				paramsObject[a[0]] = decodeURIComponent(a[1]);
			}
			return paramsObject;
		};

		var toQuerystring = function(params) {
			var str = [];
			for(var p in params) {
				if ( params[p] && params[p] !== "undefined" ) {
					str.push(p + "=" + encodeURIComponent(params[p]));
				} else {
					str.push(p);
				}
			}
			window.location.search = str.join("&");
		};

		return {
			get: function(param) {
				var params = fromQuerystring();
				if (param) {
					if ( params[param] ) {
						return params[param];

					} else {
						return null;
					}
				} else {
					return params;
				}
			},
			add: function(newParams) {
				var params = fromQuerystring();
				for (var p in newParams) {
					params[p] = newParams[p];
				}
				toQuerystring(params);
			},
			remove: function(removeParams) {
				removeParams = (typeof(removeParams)=='string') ? [removeParams] : removeParams;
				var params = fromQuerystring();
				for (var i = 0; i < removeParams.length; i++) {
					delete params[removeParams[i]];
				}
				toQuerystring(params);
			},
			clear: function() {
				toQuerystring({});
			}
		};
	})();

	window.qs = qs;
})(window);