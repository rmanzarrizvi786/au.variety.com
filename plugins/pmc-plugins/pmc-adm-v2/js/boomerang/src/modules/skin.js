/**
 * Skin Ad rendering functions.
 *
 */

/* global pmc jQuery */

function pmcSkin() {
    this.properties = {
        dfpCreativeMarkup: undefined,
        dfpCreativeParameters: undefined,
        viewUrlTracked: false
    };

    this.init = function() {
        this.init_DOM();
        this.bind_GUI_events();
    };

    this.init_DOM = function() {
        this.dom = {
            adSection: jQuery('#skin-ad-section'),
            leftRailContainer: jQuery('#skin-ad-left-rail-container'),
            rightRailContainer: jQuery('#skin-ad-right-rail-container')
        };
    };
    this.bind_GUI_events = function() {
        var self = this;

        jQuery(window).on('message', function(wrappedEvent) {
            var event = wrappedEvent.originalEvent;

            if ('string' === typeof event.data) {

                var markupMessagePattern = 'pmcadm:dfp:skinad:markup';
                var parametersMessagePattern = 'pmcadm:dfp:skinad:parameters';

                /*
                 Only process the message event, if it is for dfp > prestital ad > markup
                 */
                if (event.data.substring(0, markupMessagePattern.length)
                    === markupMessagePattern) {
                    self.properties.dfpCreativeMarkup =
                        event.data.substring(markupMessagePattern.length) || '<!-- NOOP -->';

                    self.run();
                } else if (event.data.substring(0, parametersMessagePattern.length)
                    === parametersMessagePattern) {
                    /*
                     Only process the message event, if it is for dfp > prestital ad > parameters
                     */
                    var serializedParameters = event.data.substring(parametersMessagePattern.length);

                    self.properties.dfpCreativeParameters = jQuery.parseJSON(serializedParameters);

                    self.run();
                }
            }
        });

        jQuery(window).on('resize', function() {
            self.refresh_skin_rails();
        });

        jQuery(document).ready(function() {
            self.refresh_skin_rails();
        });

        /*
         * This is needed because prestitials cause there to be no-scrollbars.
         * This throws off the skin rails calculation. Hence we need to refresh skin rails.
         * */
        jQuery('body').on('prestitial-ad:stopped', function() {
            self.refresh_skin_rails();
        });

        //Add events for when user clicks on the skins
        self.dom.leftRailContainer.on('click', function() {
            self.skin_clicked_EventHandler();
        });

        self.dom.rightRailContainer.on('click', function() {
            self.skin_clicked_EventHandler();
        });
    };

    this.skin_clicked_EventHandler = function() {
        var self = this;
        window.open(self.properties.dfpCreativeParameters.clickThroughURL, '_blank');
    };

    this.refresh_skin_rails = function() {
        var self = this;

        if (!self.properties.dfpCreativeParameters) {
            return;
        }

        var browserWidth = jQuery(window).width();

        var RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH = {
            LARGE: 1900,
            MEDIUM: 1350,
            SMALL: 1260
        };

        var suppliedRailWidthToBeUsed = 0;


        if (browserWidth < RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH['MEDIUM']) {
            suppliedRailWidthToBeUsed = RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH['SMALL'];
        } else if (browserWidth < RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH['LARGE'] && browserWidth >= RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH['MEDIUM']) {
            suppliedRailWidthToBeUsed = RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH['MEDIUM'];
        } else {
            suppliedRailWidthToBeUsed = RAIL_MAX_WIDTH_TO_SUPPLIED_RAIL_WIDTH['LARGE'];
        }

        var imageToBeUsed = self.properties.dfpCreativeParameters.creative.image[suppliedRailWidthToBeUsed];

        var leftRailImageLink = self.properties.viewUrlTracked
            ? imageToBeUsed.left
            : self.properties.dfpCreativeParameters.viewURLPrefix + imageToBeUsed.left;

        var bodyBackgroundColor = self.properties.dfpCreativeParameters.bodyBackgroundColor
            ? (jQuery.trim(self.properties.dfpCreativeParameters.bodyBackgroundColor)
                || null)
            : null;

        jQuery('body').css('background-color', bodyBackgroundColor);

        self.properties.viewUrlTracked = true;

        self.dom.leftRailContainer.css("background-image", 'url("' + leftRailImageLink + '")'); // eslint-disable-line

        self.dom.rightRailContainer.css("background-image", 'url("' + imageToBeUsed.right + '")'); // eslint-disable-line

        var imgURL = imageToBeUsed.right;
        var img = jQuery('<img src="'+imgURL+'"/>').on('load', function(){ // eslint-disable-line
            var leftImageWidthToBeUsed = this.width;
            var mainContentReferenceDOM = self.get_content_DOM();
            self.dom.leftRailContainer
                .width(leftImageWidthToBeUsed)
                .offset({left: (mainContentReferenceDOM.offset().left - leftImageWidthToBeUsed)});

            self.dom.rightRailContainer
                .width(leftImageWidthToBeUsed)
                .offset({left: (mainContentReferenceDOM.offset().left + mainContentReferenceDOM.outerWidth())});

        });
    };

    this.get_content_DOM = function() {
        var self = this;
        var orderedDomCandidates = ['main-wrapper'];
        var skin_container = document.querySelector( '#skin-ad-section' );

        if ( 'undefined' !== typeof skin_container && 'undefined' !== typeof skin_container.dataset.contentContainer ) {
            orderedDomCandidates = skin_container.dataset.contentContainer.split(',');
        }
        orderedDomCandidates = self.apply_filters( 'pmc-adm-dfp-skin-main-content', orderedDomCandidates );

        for (var ii=0; ii<orderedDomCandidates.length; ii++) {

            var element = jQuery('#' + orderedDomCandidates[ii]);
            if (element && element.width()) {
                return element;
            }
        }
        return jQuery('body');
    };

    this.get_available_rail_width = function(contentDOM) {
        if ( ! contentDOM ) {
            contentDOM = jQuery('body');
        }

        var documentWidth = 0;

        //Compute document width
        //IE
        if (!window.innerWidth) {
            if (!(document.documentElement.clientWidth === 0)) {
                documentWidth = document.documentElement.clientWidth; //strict mode
            } else {
                documentWidth = document.body.clientWidth; //quirks mode
            }

        } else {
            documentWidth = window.innerWidth; //w3c
        }

        return (documentWidth - contentDOM.outerWidth()) / 2;
    };

    this.run = function() {
        var self = this;

        /*
         Don't do anything if one of/both the markup & the parametes are absent.
         */
        if (!self.properties.dfpCreativeMarkup
            || !self.properties.dfpCreativeParameters) {
            return;
        }

        self.dom.adSection.removeClass('hide');
        self.dom.adSection.append(self.properties.dfpCreativeMarkup);

        self.refresh_skin_rails();
    };

    this.apply_filters =  function ( filter, value, data1 ) {
        if ( typeof pmc == 'undefined' || typeof pmc.hooks == 'undefined' || typeof pmc.hooks.apply_filters == 'undefined') {
            return value;
        }
        return pmc.hooks.apply_filters( filter, value, data1 );
    }

}

export default pmcSkin;
