/**
 * JavaScript plugin for add sharable buttons (Pinterest + Facebook) to provided images
 * This plugin export two globally jQuery extensions ( scSharableImagesAdd() and scSharableImagesUpdate() ).
 */
(function($) {
    /**
     * Init plugin variables
     */
    var pinButtonWidth = 70,
        minImgWidth = 100,
        minImgHeight = 100;

    var containerSelectors = [
        '.isa_post_container',
        '.thirty-days-landing',
        '.sc-video',
        '.parallax-container',
        '.sc-quiz'
    ];

    /**
     * update sharable buttons position on load image with sharable buttons
     */
    var fnUpdatePositionAtAll = function() {
        // If image src has changed - update all buttons position on the page because they can drive out
        if (typeof sc_isa !== 'undefined') {
            $('img.sc-share-buttons-rendered', sc_isa.getCurrentPostContainer()).scSharableImagesUpdate();
        } else {
            $('img.sc-share-buttons-rendered').scSharableImagesUpdate();
        }

        if ( $('.mobile-vertical-slideshow .slides-list').length ) {
            $('.mobile-vertical-slideshow img.sc-share-buttons-rendered').scSharableImagesUpdate();
        }
    };

    /**
     * If loading of image is broken, then we will hide pin buttons.
     */
    var fnImageLoadError = function() {
         var image = $(this);
         var element = image.closest('a').length ? image.closest('a') : image;
         element.next('.scpin-wrapper').addClass('d-none');
    };

    /**
     * Re-calc position for Pinterest button
     * @param $image
     * @param $pinButton
     */
    var fnReCalcPosition = function( $image, $pinButton ) {

        // Prevent scrollers appearing that can broke coordinates
        $pinButton.css({
            'top': 0,
            'left': 0
        });

        // nothing to do if we not have sharable buttons
        if( $pinButton.length == 0) {
            return;
        }

        var parent = $pinButton.parent();

        // We should consider the top border
        var parentOffsetTop = parent.offset().top + parent[0].clientTop;
        var imageOffsetTop = $image.offset().top + $image[0].clientTop;

        // Calculate position for right top corner
        var iOffsetY = imageOffsetTop - parentOffsetTop;
        //var iOffsetX = parent.offset().left + parent.outerWidth() - ( $image.offset().left + $image.outerWidth() );

        if( ! $image.is(':visible') ) {
            iOffsetX = 0;
            iOffsetY = 0;
        }

        $pinButton.css({
            'top': iOffsetY,
            'left': 0,
            //'right': iOffsetX
        });
    };

    /**
     * Add sharable buttons to images
     * @returns {*|HTMLElement}
     */
    $.fn.scSharableImagesAdd = function( postTitle, postDescription, postUrl ) {
        var target = $(this);

        // use current post title by default
        if ( ! postTitle ) {
            postTitle = $('title').text();
        }

        // use current post description by default
        if ( ! postDescription ) {
            postDescription = $('meta[property="og:description"]:first').attr('content');
        }

        // Filter images that we need to process
        var images = $(this).filter('img:not(.scpin-no,.scpin-no img,.sc-share-buttons-rendered)');
        images.each(function() {
            var image = $(this);

            // If the image hasn't loaded - add the buttons then it happens.
            if ( hasLazyLoad( image ) ) {
                if ( false === isLazyLoadComplete( image ) ) {
                    image.on( 'sc-lazy-load-complete', function() {
                        $(this).scSharableImagesAdd( postTitle, postDescription, postUrl );
                    });

                    return;
                }
            } else {
                if ( ! this.complete ) {
                    image.on('load', function() {
                        $(this).scSharableImagesAdd( postTitle, postDescription, postUrl );
                    });

                    return;
                }
            }

            // don't add pin it button to images which smaller than min sizes
            // if image is hidden, then image sizes equal 0
            if ( image.is(':visible') && (image.width() < minImgWidth || image.height() < minImgHeight) ) {
                return;
            }

            // Image src may be change
            if (typeof this.addEventListener === 'function') {
                this.addEventListener('load', fnUpdatePositionAtAll);
                this.addEventListener('error', fnImageLoadError);
            } else {

                // IE8 don't support addEventListener but we can use attachEvent
                this.attachEvent('onload', fnUpdatePositionAtAll);
                this.attachEvent('onerror', fnImageLoadError);
            }

            // Get the information that we will send to pinterest and facebook services
            var shareImage = image.prop('src');
            // for images in the post content and mobile slideshow use ALT attribute as description, if it exist
            if ( ( image.closest('div.post_content').length || image.closest('div.mobile-vertical-slideshow').length ) && image.attr('alt') ) {
                var shareDescription = image.attr( 'alt' );
            } else {
                var shareDescription = image.data( 'scpin-desc' ) ? image.data( 'scpin-desc' ) : postTitle;
            }

            var shareUrl = image.data('scpin-url') ? image.data('scpin-url') : image.closest('a').attr('href');

            // If div with buttons doesn't have parent link we get current location url
            if ( typeof shareUrl === 'undefined' || shareUrl === null ) {
                shareUrl = (typeof postUrl !== 'undefined') ? postUrl : $(location).attr('href');
            }

            // add pin it button after image, or after link if image located in link
            // because we don't want insert one link to another
            // element after which pin it button will be inserted
            var element = image.closest('a').length ? image.closest('a') : image;

            // .scpin-detector can be used by another handlers to detect whether this button was automatically created by scSharableImages or it's a default Pin button
            var currentPin = $('<div></div>').addClass('scpin-no scpin-wrapper scpin-detector')
                .append(
                  $('<a></a>').addClass('sc-pinterest-image-share icon-icomoon-pinterest icon-icomoon-pinterest--red')
                      .attr('href', 'javascript:void(0)')
                      .data('url', shareUrl)
                      .data('media', shareImage)
                      .data('description', shareDescription)
            );

            element.after(currentPin);

            var $container;
            $(containerSelectors).each(function (i, c) {
                $container = element.closest(c);
                if($container.length) {
                    return false;
                }
            });
            if($container.length) {
                $container.trigger('sharable-image-wrapped', {
                    pin: currentPin.find('.sc-pinterest-image-share').get(0),
                    image: this
                });
            }

            //create PIN form and add GA tracking for Pinterest share button
            currentPin.find('a').click(function() {
                // check that we have Pinterest Utils
                if ( typeof PinUtils == 'undefined' ) {
                    return;
                }

                var selfPin = $(this);
                // create PIN form (Pinterest popup)
                PinUtils.pinOne({
                    description: selfPin.data('description'),
                    media: selfPin.data('media'),
                    url: selfPin.data('url')
                });

                // Skimlinks swaps HREFs of some links, so we should care about this
                // if it happens the skimlink replace the HREF with http://go.redirectingat.com/?url=...
                if (shareUrl.indexOf('http://go.redirectingat.com/') != -1){
                    shareUrl = getUrlParam(getUrlParam(shareUrl, 'url'), 'url');
                }

                _gtmq.push({
                    'event':         'SocialShareButtonClick',
                    'ev_network':    'Pinterest',
                    'ev_target_url': shareUrl,
                    'ev_button_type': 'image',
                    'ev_button_location': $(this).closest('[data-gtm-pin-location]').length ? $(this).closest('[data-gtm-pin-location]').data('gtm-pin-location') : ''
                });

                // Track the PinIt Like action. Pinterest library does not have any means of letting us know about successful pins, so we just track click on pin it button
                _gtmq.push({
                    'event':         'SocialShareComplete',
                    'ev_network':    'Pinterest',
                    'ev_target_url': shareUrl,
                    'ev_action':     'Pin',
                    'ev_button_type': 'image',
                    'ev_button_location': $(this).closest('[data-gtm-pin-location]').length ? $(this).closest('[data-gtm-pin-location]').data('gtm-pin-location') : ''
                });
            });

            // If image is hidden, then buttons should be hidden too
            // It need for slideshow
            if ( false == image.is(':visible') ) {
                currentPin.addClass( 'd-none' );
            }

            // fix for shareble images wrong position in the post content
            if ( image.closest('div.post_content') ) {
                image.parent().addClass('pin-buttons-parent');
            }

            fnReCalcPosition( image, currentPin );

            // Set that the image already has a rendered buttons
            image.addClass('sc-share-buttons-rendered');
        });

        return target;
    };

    /**
     * Update sharable buttons position for images
     * @returns {*|HTMLElement}
     */
    $.fn.scSharableImagesUpdate = function() {
        var target = $(this);

        // Filter images that we need to process
        var images = $(this).filter('img.sc-share-buttons-rendered:not(.scpin-no,.scpin-no img)');

        images.each(function() {
            var image = $(this);

            var element = image.closest('a').length ? image.closest('a') : image;

            var pinButton = element.next('.scpin-wrapper');

            if ( pinButton.hasClass('d-none') && image.is(':visible') && image.width() >= minImgWidth && image.height() >= minImgHeight ) {

                // slides with lazy load have sizes more than 100x100 in the mobile slideshow
                if ( hasLazyLoad( image ) ) {
                    if ( isLazyLoadComplete( image ) ) {
                        pinButton.removeClass('d-none');
                    }
                } else {
                    pinButton.removeClass('d-none');
                }
            }

            fnReCalcPosition( image, pinButton );
        });

        return target;
    };

    /**
     * Has the image the capacity of lazy load?
     * @param $image
     * @returns {boolean}
     */
    function hasLazyLoad( $image ) {
        // if image is loaded or lazy load is completed
        if ( 'string' === typeof $image.attr('data-lazy-load-status') ) {
            return true;
        }

        // image is ready for lazy load
        if ( 'undefined' !== typeof $image.data('original') ) {
            return true;
        }

        // image of vertical slideshow is ready for lazy load
        if ( 'string' === typeof $image.attr('data-lazy-src') ) {
            return true;
        }
        return false;
    }

    /**
     * Was the lazy load of image completed ?
     * @param $image
     * @returns {boolean}
     */
    function isLazyLoadComplete( $image ) {
        return ( 'string' === typeof $image.attr('data-lazy-load-status') ) && ( 'complete' === $image.attr('data-lazy-load-status') );
    }

    /**
     * init custom sharable buttons
     */
    var fnCustomButtonsHandler = function() {
        //create PIN form and add GA tracking for Pinterest share button
        $('.sc-custom-sharebuttons .sc-pinterest-image-share').click(function() {
            // check that we have Pinterest Utils
            if ( typeof PinUtils == 'undefined' ) {
                return;
            }
            // create PIN form (Pinterest popup)
            PinUtils.pinOne({
                description: $(this).data('description'),
                media: $(this).data('media'),
                url: $(this).data('url')
            });
        });

        $('.sc-custom-sharebuttons .sc-facebook-image-share').click(function() {
            // check that we have Pinterest Utils
            FB.ui({
                method: 'feed',
                link: $(this).data('url'),
                picture: $(this).data('media'),
                description: $(this).data('description')
            });
        });
    };

    /**
     * Method for initialize sharable images plugin
     */
    var fnInit = function(){
        // update sharable block position on resize or orientation change
        $(window).on('resize orientationchange', fnUpdatePositionAtAll);

        // init custom sharable buttons
        fnCustomButtonsHandler();

        // Initialize show/hide sharable buttons on image hover
        // disabled on touch devices and single pages
        // but enabled on feed pages
        if ( ! ( ('ontouchstart' in window) || Boolean(navigator.MaxTouchPoints) || Boolean(navigator.msMaxTouchPoints) ) && ( false == $('body').hasClass("single") || true == $('body').hasClass('single-feed_page') ) ) {
            $(document).on('mouseenter', "div.container img,div.hp-section_top_spot img", function () {
                if( ! $(this).hasClass('scpin-no') && $(this).is(':visible') ) {
                    $(this).closest('a').parent().addClass('sc-show-sharable-buttons');
                }
            });

            $(document).on('mouseleave', 'div.container img,div.hp-section_top_spot img', function () {
                if( ! $(this).hasClass('scpin-no') && $(this).is(':visible') ) {
                    $(this).closest('a').parent().removeClass('sc-show-sharable-buttons');
                }
            });

            $(document).on( 'mouseenter', '.scpin-wrapper', function() {
                $(this).parent().find('img.sc-share-buttons-rendered').trigger('mouseenter');
            });

            $(document).on( 'mouseleave', '.scpin-wrapper', function() {
                $(this).parent().find('img.sc-share-buttons-rendered').trigger('mouseleave');
            });
        }
    };
    fnInit();
    $('.entry-content img').scSharableImagesAdd();
})(jQuery);

