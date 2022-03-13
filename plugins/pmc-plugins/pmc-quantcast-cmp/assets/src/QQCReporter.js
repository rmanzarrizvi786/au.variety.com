import CacheManager from './CacheManager';
import pmc_gdpr_utils from './pmc_gdpr_utils';

/**
 * Injects and manages Quantcast Choice.
 */
export default class QQCReporter {
  /**
     * Injects Choice into the page with a config object.
     *
     * @param {object} [cmpConfig]  Default is responsive to header.js
     */
  static init () {
    if (!window.__cmp) {
      return;
    }

    const daysBetweenDisplay = 7;

    /**
         * Handles signal that the UI has been clicked.
         */
    const uIClickedCallback = function () {
      /**
             *
             * @param {object} data - response from call to cmp getConsentData
             * @param {boolean} success - if true, we have a valid response
             */
      const consentTrackingCallback = function (data, success) {
        if (success) {
          const newEuPubConsent = QQCReporter.getCookie('eupubconsent');

          const newEuConsent = data.consentData;

          let consentId = CacheManager.getData('choiceConsentID');

          // If the new data does not match the previous data, the
          // user has changed consent.  Report it.
          if (!consentId || newEuPubConsent !== originalEuPubConsent || newEuConsent !== originalEuConsent) {
            const oneDay = 86400000;

            // Generate our own random ID for this consent record.
            if (!consentId) {
              consentId = pmc_gdpr_utils.generateUUID();
              CacheManager.setData('choiceConsentID', consentId, (pmc_gdpr_utils.getNow() + (oneDay * daysBetweenDisplay * 13)));
            }

            CacheManager.setData('euconsent', data.consentData, (pmc_gdpr_utils.getNow() + (oneDay * daysBetweenDisplay)));

            QQCReporter.sendConsentDataToFrisbee({
              choiceConsentID: consentId,
              euconsent: newEuConsent,
              eupubconsent: newEuPubConsent
            });
            CmpGate && CmpGate.handleAnalyticsConsent && CmpGate.handleAnalyticsConsent(newEuConsent);
            originalEuConsent = newEuConsent;
            originalEuPubConsent = newEuPubConsent;
          }
          // Add the
        }

        // Wait for the click again. The timeout is needed to escape the
        // current callback (otherwise we recurse).
        setTimeout(function () {
          window.__cmp('setConsentUiCallback', uIClickedCallback);
        }, 1);
      };

      // Once we have a click signal, ask for the consent data.
      window.__cmp('getConsentData', null, consentTrackingCallback);
    };

    // Set a callback for interaction with the UI so we can report to
    // Frisbee.
    window.__cmp('setConsentUiCallback', uIClickedCallback);
  }

  /**
     * Gets a specified cookie. At present first party cookies only.
     *
     * @param {string} cookieName
     * @returns {*}
     */
  static getCookie (cookieName) {
    const pattern = RegExp('(?:^|; *)' + cookieName + '=(.[^;]*)');

    const matched = document.cookie.match(pattern);

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
  static sendConsentDataToFrisbee (quantcastConsentData) {
    const frisbee = new Frisbee({
      'namespace': 'prod-gdpr-stream',
      'url': 'https://collector.sheknows.com/event'
    });
    quantcastConsentData.origin = window.location.origin;
    frisbee.add(quantcastConsentData);
    frisbee.sendAll();
  }
}
