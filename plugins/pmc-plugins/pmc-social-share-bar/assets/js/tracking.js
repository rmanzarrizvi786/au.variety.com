jQuery(function () {
    var social_share_bar_ga = {

        is_mobile: false,
        permalink: null,
        share_list: null,

        /**
         * Setup on init
         */
        init: function () {
            this.permalink = window.pmc_share_bar_lob_ga_tracking.permalink;
            this.is_mobile = window.pmc_share_bar_lob_ga_tracking.is_mobile;
            this.share_list = window.pmc_share_bar_lob_ga_tracking.share_list;
            this.bind_ui_click();
        },

        /**
         * Bind UI click action here
         *
         * @since 2016-02-19
         * @version 2016-02-19 Archana Mandhare - PMCVIP-815
         */
        bind_ui_click: function () {

            /**
             * Bind click event for single page social share icons
             */

            var self = this;
            jQuery.each(self.share_list, function (id, share_icon) {

                jQuery("."+share_icon.class).on("click", function (e) {

                    jQuery.event.trigger( {
                        type: 'pmc-social-share-bar-click',
                        id: id,
                        permalink: self.permalink,
                        class:share_icon.class
                    } );

                    if ('1' === self.is_mobile) {
                        self.track_social_click(id, 'click', self.permalink);
                    } else {
                        if (false === share_icon.popup) {
                            // For comment we do not want to open a popup
                            self.track_social_click(id, 'click', self.permalink);
                        } else {
                            e.preventDefault();

                            var popup_features = {
                                height: 570,
                                width: 530,
                                left: '50%',
                                top: '28%',
                                location: 1,
                                resizable: 0,
                                menubar: 0,
                                toolbar: 0,
                                personalbar: 0,
                                status: 0
                            }
                            var popup_options = {
                                source_el: el = jQuery(this),
                                title: share_icon.popup_title
                            }

                            if (self.open_popup(popup_options, popup_options)) {
                                self.track_social_click(id, 'click', self.permalink);
                                return false;
                            }
                        }
                    }
                });

                jQuery("."+share_icon.class).one("mouseover", function (e) {

                    jQuery.event.trigger( {
                        type: 'pmc-social-share-bar-mouseover',
                        id: id,
                        permalink: self.permalink,
                        class:share_icon.class
                    } );

                    self.track_social_mouseover(id);
                    return false;

                });
            });

        },

        /**
         * Fire ga social tracking
         *
         * @since 2016-02-19
         * @version 2016-02-19 Archana Mandhare - PMCVIP-815
         *
         * @param socialNetwork - name of the social share network
         * @param action - the type of the event -e.g facebook_click
         * @param target - the single page URL
         */
        track_social_click: function (socialNetwork, action, target) {

            if (typeof ga != 'undefined') {
                ga('send', 'social', socialNetwork, action, target);
                // see if an additional social tracking event should be generated
                var event = pmc.hooks.apply_filters('pmc_event_tracking_social_data', null, socialNetwork);
                if ( null !== event) {
                    ga('send', {
                        hitType: event.hitType,
                        eventCategory : event.eventCategory,
                        eventAction: event.eventAction,
                        eventLabel: event.eventLabel,
                        nonInteraction: event.nonInteraction
                    });
                }
            }

        },

        /**
         * Fire ga social tracking
         *
         * @since 2016-02-19
         * @version 2016-02-19 Archana Mandhare - PMCVIP-815
         *
         * @param socialNetwork - name of the social share network
         */
        track_social_mouseover: function (socialNetwork) {

            if (typeof ga != 'undefined') {
                ga("send", "event", "social_bar", "mouse-over", socialNetwork, 1, {nonInteraction: true});
            }
        },

        /**
         * Open a Popup window for certain share icons
         *
         * @since 2016-02-19
         * @version 2016-02-19 Corey Gilmore
         * @version 2016-02-19 Archana Mandhare - PMCVIP-815
         * @param opts array
         * @param features array
         *
         */
        open_popup: function (opts, features) {
            var index = 0, win, features_list = [], x, el = false, url = false;
            var screen_left, screen_top, win_left;

            var default_options = {
                source_el: false, // the element to store the window reference in, manage duplicate windows better
                url: false, // use anchor href,
                title: 'Window', // Default title for the window
                reuse: true // Reuse a window with the same title?
            };

            var bool_features = [
                'menubar',
                'toolbar',
                'location',
                'personalbar',
                'status',
                'resizable',
                'scrollbars',
                'dependent',
                'dialog',
                'minimizable'
            ];

            var default_features = {
                // Positioning
                left: '50%',
                top: '25%',
                height: 300, // screenY
                width: 500, // screenX

                // Toolbar/chrome
                menubar: 0,
                toolbar: 0,
                location: 1,
                personalbar: 0,
                status: 0,

                // Functionality
                resizable: 1,
                scrollbars: 1,
                dependent: 0,
                dialog: 0,
                minimizable: 0 // requires dialog=yes
            };

            opts = jQuery.extend(default_options, opts);
            features = jQuery.extend(default_features, features);

            if (opts.source_el) {
                el = jQuery(opts.source_el);
                if (!el.length) {
                    el = false;
                }
            }
            if (features.left.toString().indexOf('%')) {
                screen_left = window.screenLeft || window.screenX;
                features.left = features.left.replace('%', '') / 100;
                features.left = Math.round(screen_left + (jQuery(window).width() * features.left) - (features.width / 2));
            }
            features_list.push('left=' + features.left);

            if (features.top.toString().indexOf('%')) {
                screen_top = window.screenTop || window.screenY;
                features.top = Math.round(screen_top + (jQuery(window).height() * features.top.replace('%', '') / 100));
            }
            features_list.push('top=' + features.top);

            features_list.push('width=' + features.width);
            features_list.push('height=' + features.height);

            for (x = 0; x < bool_features.length; x++) {
                if (features[bool_features[x]]) {
                    features_list.push(bool_features[x] + '=yes');
                }
            }
            if (opts.url) {
                url = opts.url;
            } else if (el && !opts.url_sel) {
                url = el;
            }

            if (url) {
                return window.open(el.attr('href'), opts.title, features_list.join(','));
            }

            return false;

        }

    };

    if (typeof ga !== 'undefined') {
        social_share_bar_ga.init();
    }

});
