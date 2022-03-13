var pmcPiano;
/******/ (function() { // webpackBootstrap
var __webpack_exports__ = {};
/*!******************************!*\
  !*** ./src/js/pmc-pixels.js ***!
  \******************************/
/* global gtag */
(function () {
  const PIANO_EVENT_CHECKOUT_START = 'startCheckout';
  const PIANO_EVENT_CHECKOUT_COMPLETE = 'checkoutComplete';
  const PIANO_EVENT_CHECKOUT_CUSTOM = 'checkoutCustomEvent';
  const PIANO_EVENT_PAYMENT_SUBMIT = 'submitPayment';
  const PIANO_EVENT_LOGIN_SUCCESS = 'loginSuccess';

  function checkTp() {
    if (window.tp && window.tp.main && window.tp.pianoId) {
      loadTp();
      return;
    }

    setTimeout(checkTp, 500);
  }

  function loadTp() {
    //
    // GA pixel tracking
    //
    // GA: Start a new checkout
    window.tp.push(['addHandler', PIANO_EVENT_CHECKOUT_START, function () {
      try {
        console.log(`PMC Piano tp.push [GA]: [addHandler=${PIANO_EVENT_CHECKOUT_START}]`);
        gtag('event', 'signup-start', {
          event_category: 'ecommerce'
        });
      } catch (error) {}
    }]);
    /**
     * GA: Registration completed
     * The `loginSuccess` event sets data.registration = true when login is due to a registration.
     *
     * @param data
     * @see https://docs.piano.io/callbacks/#loginsuccessevent
     */

    window.tp.push(['addHandler', PIANO_EVENT_LOGIN_SUCCESS, function (data) {
      // The registration property defines whether the action was due to registration or login
      if (data.registration === true) {
        console.log(`PMC Piano tp.push [GA]: [addHandler=${PIANO_EVENT_LOGIN_SUCCESS}]`);

        try {
          // GA tracking of this event...
          gtag('event', 'registration-complete', {
            event_label: 'Registration Complete',
            event_category: 'ecommerce'
          });
        } catch (error) {}
      }
    }]); // GA: Payment Submitted

    window.tp.push(['addHandler', PIANO_EVENT_PAYMENT_SUBMIT, function () {
      try {
        console.log(`PMC Piano tp.push [GA]: [addHandler=${PIANO_EVENT_PAYMENT_SUBMIT}]`);
        gtag('event', 'payment-submit', {
          event_label: 'Payment Submitted',
          event_category: 'ecommerce'
        });
      } catch (error) {}
    }]); // GA: Checkout completed

    window.tp.push(['addHandler', PIANO_EVENT_CHECKOUT_COMPLETE, function (conversion) {
      try {
        const conversionId = window.pmcPianoData.trackingPixels && window.pmcPianoData.trackingPixels.ga && window.pmcPianoData.trackingPixels.ga.conversion_id;

        if (!conversionId) {
          return;
        }

        console.log(`PMC Piano tp.push [GA]: [addHandler=${PIANO_EVENT_CHECKOUT_COMPLETE}, gaConversionId=${conversionId}]`, conversion); // @TODO send_to needs to be dynamic. This will be handled in PMCS-4438 (https://jira.pmcdev.io/browse/PMCS-4438)

        gtag('event', 'conversion', {
          send_to: conversionId,
          value: conversion.chargeAmount,
          currency: conversion.chargeCurrency,
          transaction_id: ''
        });
      } catch (error) {}
    }]); //
    // Facebook pixel tracking
    //
    // Facebook: Start a new checkout

    window.tp.push(['addHandler', PIANO_EVENT_CHECKOUT_START, function () {
      try {
        const contentName = window.pmcPianoData.trackingPixels && window.pmcPianoData.trackingPixels.facebook && window.pmcPianoData.trackingPixels.facebook.content_name;
        console.log(`PMC Piano tp.push [facebook]: [addHandler=${PIANO_EVENT_CHECKOUT_START}]`);
        fbq('track', 'InitiateCheckout', {
          content_ids: ['subscription'],
          content_name: contentName || 'Piano Subscription',
          content_type: 'product'
        });
      } catch (e) {}
    }]); // Facebook: checkout completed

    window.tp.push(['addHandler', PIANO_EVENT_CHECKOUT_COMPLETE, function (conversion) {
      try {
        console.log(`PMC Piano tp.push [Facebook]: [addHandler=${PIANO_EVENT_CHECKOUT_COMPLETE}]`, conversion);
        fbq('track', 'Purchase', {
          content_ids: ['subscription'],
          content_type: 'product',
          currency: conversion.chargeCurrency,
          value: conversion.chargeAmount
        });
      } catch (error) {}
    }]); //
    // LinkedIn pixel tracking
    //
    // LinkedIn: Start a new checkout

    window.tp.push(['addHandler', PIANO_EVENT_CHECKOUT_START, function () {
      const checkoutStartPixel = window.pmcPianoData.trackingPixels && window.pmcPianoData.trackingPixels.linkedin && window.pmcPianoData.trackingPixels.linkedin.pixel_checkout_start;

      if (!checkoutStartPixel || !checkoutStartPixel.startsWith('<img')) {
        return;
      }

      console.log(`PMC Piano tp.push [LinkedIn]: [addHandler=${PIANO_EVENT_CHECKOUT_START}]`);
      document.body.insertAdjacentHTML('beforeend', checkoutStartPixel);
    }]); // LinkedIn: Checkout completed

    window.tp.push(['addHandler', PIANO_EVENT_CHECKOUT_COMPLETE, function (conversion) {
      console.log(`PMC Piano tp.push [LinkedIn]: [addHandler=${PIANO_EVENT_CHECKOUT_COMPLETE}]`, conversion);
      const checkoutCompletePixel = window.pmcPianoData.trackingPixels && window.pmcPianoData.trackingPixels.linkedin && window.pmcPianoData.trackingPixels.linkedin.pixel_checkout_complete;

      if (!checkoutCompletePixel || !checkoutCompletePixel.startsWith('<img')) {
        return;
      }

      document.body.insertAdjacentHTML('beforeend', checkoutCompletePixel);
    }]); // Fire facebook pixel for paywalled content.
    // The experienceExecute event fires when an experience fires for a user pageview.

    window.tp.push(['addHandler', 'experienceExecute', function () {
      const pmcPaywall = document.querySelector('.pmc-paywall.a-article-cropped');

      if (pmcPaywall) {
        if (typeof fbq !== 'undefined' && fbq.version) {
          fbq('track', 'PaywallHit');
        }
      }
    }]); //Fire Digioh Pixel for checkout started.

    window.tp.push(['addHandler', PIANO_EVENT_CHECKOUT_START, function () {
      try {
        if (!window.pmcPianoData.trackingPixels || !window.pmcPianoData.trackingPixels.digioh) {
          return;
        }

        const checkoutStartPixel = window.pmcPianoData.trackingPixels.digioh.pixel_checkout_start;
        console.log(`PMC Piano tp.push [digioh]: [addHandler=${PIANO_EVENT_CHECKOUT_START}]`);
        var getScriptTag = document.getElementsByTagName("script");
        const scriptURL = `https://www.lightboxcdn.com/vendor/${checkoutStartPixel}/lightbox_inline.js`;
        var script_already_added = false;

        for (var i = 0; i < getScriptTag.length; i++) {
          if (getScriptTag[i].src == scriptURL) {
            script_already_added = true;
            break;
          }
        }

        if (false === script_already_added) {
          var tag = document.createElement('script');
          tag.type = "text/javascript";
          tag.src = scriptURL;
          tag.async = true;
          var parent = document.getElementsByTagName('script')[0];
          parent.parentNode.insertBefore(tag, parent);
        }
      } catch (e) {}
    }]);
  }

  checkTp();
})();
(pmcPiano = typeof pmcPiano === "undefined" ? {} : pmcPiano).pixels = __webpack_exports__;
/******/ })()
;
//# sourceMappingURL=pmc-pixels.js.map