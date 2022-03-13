import QQCReporter from './QQCReporter.js';
import { ConsentString } from 'consent-string';
/** The ^^^ ConsentString class comes from the IAB. It allows us to decode the consent string to determine
 * what degree of consent the user has granted for data collection
 * "description": "Encode and decode web-safe base64 consent information with the IAB EU's GDPR Transparency and Consent Framework",
 * "homepage": "https://github.com/InteractiveAdvertisingBureau/Consent-String-SDK-JS",
 */
const CmpProcess = {
  checkForNonEUCMP () {
    const euconsent = this.getCookie('eupubconsent');
    if (euconsent) {
      this.handleAnalyticsConsent(euconsent);
    } else {
      // No opt-outs
      this.initAnalytics(true);
    }
  },
  setConsentBodyClass () {
    document.body.classList.remove('waitingForCmp');
    document.body.classList.add('hasCmp');
  },

  getCookie (cname) {
	const decodedCookie = decodeURIComponent(document.cookie);
    const cookieSplut = decodedCookie.split('; ');
    let consentValue = '';
    cookieSplut.forEach((ca) => {
      if (ca.indexOf(cname) === 0) {
        consentValue = ca.split('=')[ 1 ];
      }
    })
    ;
    return consentValue;
  },
  // These ids are from the IAB vendors list: https://github.com/InteractiveAdvertisingBureau/GDPR-Transparency-and-Consent-Framework/blob/master/reference/src/docs/assets/vendorlist.json
  // We need to get Storage consent for cookies.
  // We need to get Measurement consent for GA and omni and hotjar.
  handleAnalyticsConsent (euconsent) {
    const consentData = new ConsentString(euconsent);
    if (!consentData) {
      console.error(`Error with consent string: {euconsent}`);
      return;
    }
    const storageConsent = consentData.isPurposeAllowed(1);
    const measurementConsent = consentData.isPurposeAllowed(5);
    if (measurementConsent) {
      this.initAnalytics(storageConsent);
    }
  },
  modifyConsent () {
    // This is called when the Consent UI is closed. This should only fire if
    // the user manually opens the preference screen. May or may not have changed any consent.
    window.__cmp('getConsentData', null, function (consentInfo, success) {
      if (consentInfo.consentData) {
        // we have a consent string. let's parse it.
        const consentData = new ConsentString(consentInfo.consentData);
        if (!consentData) {
          console.error(`Error with consent string: {consentInfo.consentData}`);
          return;
        }
        const storageConsent = consentData.isPurposeAllowed(1);
        const measurementConsent = consentData.isPurposeAllowed(5);
        if (!measurementConsent) {
          // try to stop collecting
          window.ga(function () {
            const trackers = window.ga.getAll();
            trackers.forEach((tracker) => {
              let uid = tracker.b.data.values[':trackingId'];
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
  initAnalytics (storageConsent) {
    // One place to call all gated measurements
    window.loadGA && window.loadGA(storageConsent);
  }
};
// Collect for compliance
QQCReporter.init();

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
  CmpProcess.setConsentBodyClass();
  // This  next callback should only fire if the user manually opens the preference screen.
  // The timeout is needed to escape the current callback (otherwise we recurse).
  setTimeout(function () { window.__cmp('setConsentUiCallback', CmpProcess.modifyConsent); }, 1);
});
