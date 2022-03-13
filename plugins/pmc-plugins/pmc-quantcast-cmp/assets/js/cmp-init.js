// This code is based on the Quantcast CMP generator, originally modified to load in mezzobit
//  https://www.youtube.com/watch?v=GiPOBzTqlv8.
var pmc_gdpr_elem = document.createElement('script');
pmc_gdpr_elem.src = 'https://quantcast.mgr.consensu.org/cmp.js';
pmc_gdpr_elem.async = true;
pmc_gdpr_elem.type = 'text/javascript';
var pmc_gdpr_scpt = document.getElementsByTagName('script')[ 0 ];
pmc_gdpr_scpt.parentNode.insertBefore(pmc_gdpr_elem, pmc_gdpr_scpt);
(function () {
  var gdprAppliesGlobally = false;

  function addFrame () {
    if (!window.frames[ '__cmpLocator' ]) {
      if (document.body) {
        var body = document.body;

        var iframe = document.createElement('iframe');
        iframe.style = 'display:none';
        iframe.name = '__cmpLocator';
        body.appendChild(iframe);
      } else {
        // In the case where this stub is located in the head,
        // this allows us to inject the iframe more quickly than
        // relying on DOMContentLoaded or other events.
        setTimeout(addFrame, 5);
      }
    }
  }

  addFrame();

  function stubCMP() {
    var b = arguments;
    __cmp.a = __cmp.a || [];
    if (!b.length) {
		return __cmp.a;
	} else if (b[0] === 'ping') {
      b[2]({"gdprAppliesGlobally": gdprAppliesGlobally,
        "cmpLoaded": false}, true);
    } else {
      __cmp.a.push([].slice.apply(b));
    }
  }

  function cmpMsgHandler (event) {
    var msgIsString = typeof event.data === 'string';
    var json;
    if (msgIsString) {
      json = event.data.indexOf('__cmpCall') != -1 ? JSON.parse(event.data) : {};
    } else {
      json = event.data;
    }
    if (json.__cmpCall) {
      var i = json.__cmpCall;
      window.__cmp(
        i.command,
        i.parameter,
        function (retValue, success) {
          var returnMsg = {
            '__cmpReturn': {
              'returnValue': retValue,
              'success': success,
              'callId': i.callId
            }
          };
          event.source.postMessage(
            msgIsString
              ? JSON.stringify(returnMsg) : returnMsg,
            '*'
          );
        }
      );
    }
  }

	if (typeof (__cmp) !== 'function') {
		window.__cmp = stubCMP;
		__cmp.msgHandler = cmpMsgHandler;
	}
	
	if (window.addEventListener) {
		window.addEventListener('message', cmpMsgHandler, false);
	} else {
		window.attachEvent('onmessage', cmpMsgHandler);
	}

})();
window.__cmp('init', JSON.parse(cmp_init_params));
