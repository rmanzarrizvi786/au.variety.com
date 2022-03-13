
window.pmc_sticky_rightrail = {

    scrollHeight : 1000,
    is_initialized : true,
    $window : jQuery( window ),
    $rightrail : '',
    $content : '',
    $leftrail : '',
    $pmcGallery : '',
    docHeight : '',
    rightrailTop : '',
    contentTop : '',
    scrollLimit : '',
    headerHeight: '',

    init : function( settings ) {
        if( typeof settings.rightrail != 'undefined' ){
            this.$rightrail = jQuery( settings.rightrail );
        }else{
            this.is_initialized = false;
            return;
        }
        if( typeof settings.content != 'undefined' ){
            this.$content = jQuery( settings.content );
        }else{
            this.is_initialized = false;
            return;
        }
        if( typeof settings.leftrail != 'undefined' ){
            this.$leftrail = jQuery( settings.leftrail );
        }else{
            this.is_initialized = false;
            return;
        }
        if( typeof settings.pmcGallery != 'undefined' ){
            this.$pmcGallery = jQuery( settings.pmcGallery );
        }else{
            this.$pmcGallery = jQuery('#pmc-gallery');
        }
        if( typeof settings.headerHeight != 'undefined' ){
            this.headerHeight = settings.headerHeight;
        }
        this.docHeight = this.$window.height();
        if( typeof this.$rightrail.offset() == 'undefined' || typeof this.$content.offset() == "undefined" ){
            this.is_initialized = false;
        }else{
            this.rightrailTop = this.$rightrail.offset().top;
            this.contentTop = this.$content.offset().top;
            this.scrollLimit = this.contentTop + this.scrollHeight;
            this.is_initialized = true;
        }


    },
    should_scroll : function(){

        var rightrailHeight = this.$rightrail.outerHeight();
        var leftRailHeight = this.$leftrail.outerHeight();
        var is_scroll = true;

        if(this.$pmcGallery.length > 0 ) {
            is_scroll = false;
            return is_scroll;
        }
        if (leftRailHeight < rightrailHeight) {
            is_scroll = false;
        } else if (leftRailHeight < rightrailHeight + this.scrollHeight  && leftRailHeight > rightrailHeight) {
            var scrollDiff = leftRailHeight - rightrailHeight;
            if (scrollDiff < 500) {
                is_scroll = false;
            }
            else if (500 < scrollDiff && scrollDiff < 1000) {
                is_scroll = true;
                this.scrollHeight = 500;
                this.scrollLimit = this.contentTop + this.scrollHeight;
            } else {
                is_scroll = true;
                this.scrollHeight = 1000;
                this.scrollLimit = this.contentTop + this.scrollHeight;
            }
        } else {
            is_scroll = true;
            this.scrollHeight = 1000;
            this.scrollLimit = this.contentTop + this.scrollHeight;
        }
        return is_scroll;
    },

    /**
     *
     * @param settings : {rightrail, content, leftrail }
     * if settings does not have rightrail, content and leftrail we don't proceed
     * with the rest of the plugin.
     */
    apply_sticky_rightrail : function( settings ){

        this.init( settings );

        if( !this.is_initialized ){
            return;
        }
        jQuery(window).scroll(function () {

            var $window = jQuery(window);

            var docViewTop = $window.scrollTop();

            var scrollHeight = window.pmc_sticky_rightrail.scrollHeight;


            // Calculate the rightrail top position after scrolling 20px
            // since there can be expandable billboard ad loading after DOM ready
            // and the top position of the might increase after DOM ready
            if ( docViewTop <= 20 ) {
                window.pmc_sticky_rightrail.rightrailTop = window.pmc_sticky_rightrail.$rightrail.offset().top;
                window.pmc_sticky_rightrail.is_scroll = window.pmc_sticky_rightrail.should_scroll();
            }

            if ( !window.pmc_sticky_rightrail.is_scroll ) {
                window.pmc_sticky_rightrail.$rightrail.removeClass('sticky-start');
                window.pmc_sticky_rightrail.$rightrail.removeClass('sticky-end-oneT');
                window.pmc_sticky_rightrail.$rightrail.removeClass('sticky-end-fiveH');
                return false;
            }
            // LOBs with sticky header need a little padding from the docviewtop.
            if( window.pmc_sticky_rightrail.headerHeight != ''){
                docViewTop = docViewTop + window.pmc_sticky_rightrail.headerHeight ;
                scrollHeight = scrollHeight + window.pmc_sticky_rightrail.headerHeight ;
            }
            // When Header is scrolled out of view
            // and we see only the content on the page
            // make the right rail sticky for the next 1000px
            if ( (docViewTop) >= (window.pmc_sticky_rightrail.rightrailTop ) && docViewTop <= window.pmc_sticky_rightrail.scrollLimit ) {
                window.pmc_sticky_rightrail.$rightrail.removeClass( 'sticky-end-oneT' );
                window.pmc_sticky_rightrail.$rightrail.removeClass( 'sticky-end-fiveH' );
                window.pmc_sticky_rightrail.$rightrail.addClass( 'sticky-start' );

            } else if ( window.pmc_sticky_rightrail.$rightrail.hasClass( 'sticky-start' ) ) {
                window.pmc_sticky_rightrail.$rightrail.removeClass( 'sticky-start' );
                if ( 1000 === scrollHeight ) {
                    window.pmc_sticky_rightrail.$rightrail.addClass( 'sticky-end-oneT' );
                } else {
                    window.pmc_sticky_rightrail.$rightrail.addClass( 'sticky-end-fiveH' );
                }

            }

            // Remove Sticky class when the user scrolls back to top
            if ( ( docViewTop ) <= window.pmc_sticky_rightrail.rightrailTop) {
                window.pmc_sticky_rightrail.$rightrail.removeClass('sticky-start');
                window.pmc_sticky_rightrail.$rightrail.removeClass('sticky-end-oneT');
                window.pmc_sticky_rightrail.$rightrail.removeClass('sticky-end-fiveH');
            }

        });
    }


}

