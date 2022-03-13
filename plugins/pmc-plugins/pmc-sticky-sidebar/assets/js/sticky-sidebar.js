/**
 * PMC's Sticky Sidebar script
 *
 * version: 1.1
 *
 * Minify this file with:
 *
 * sudo npm install -g uglify
 * cd pmc-sticky-sidebar/assets/js/
 *
 * uglify -s sticky-sidebar.js -o sticky-sidebar.min.js
 */
/*global jQuery,pmc_sticky_sidebar_js,_ */
/*eslint no-undef: "error"*/

jQuery( document ).ready( function ( $ ) {

	var pmc_sticky_sidebar = {

		$sticky: $( '.pmc-sticky-sidebar' ),
		$footer: $( 'footer' ),
		$top_bar: $( '#wpadminbar' ),
		update_sticky_sidebar_position: {},

		/**
		 * A function that initializes functionality.
		 */
		init: function () {

			var self = this;

			if ( 'undefined' !== typeof pmc_sticky_sidebar_js && 'footer_selector' in pmc_sticky_sidebar_js ) {
				self.$footer = $( pmc_sticky_sidebar_js.footer_selector );
			}

			if ( 'undefined' !== typeof pmc_sticky_sidebar_js && 'top_bar_selector' in pmc_sticky_sidebar_js ) {
				self.$top_bar = $( pmc_sticky_sidebar_js.top_bar_selector );
			}

			this.update_sticky_sidebar_position = _.debounce( function () {
				self.start_scroll();
			}, 300 );

			$( window ).on( 'scroll', self.update_sticky_sidebar_position );
		},

		reset_scroll: function () {
			this.$sticky.trigger( 'resetScroll' );
			this.$sticky.trigger( 'detach.ScrollToFixed' );
			this.$sticky.attr( 'style', '' );
		},

		start_scroll: function () {

			var self = this;

			//reset fixed positions
			self.reset_scroll();

			self.$sticky.scrollToFixed(
				{
					marginTop: function () {
						return self.$top_bar.outerHeight() + 10;
					},
					removeOffsets: true,
					limit: function () {
						return self.$footer.offset().top - self.$sticky.outerHeight(true) - 10;
					},
					fixed: function () {
						$( window ).off( 'scroll', self.update_sticky_sidebar_position );
					},
					postAbsolute: function () {
						self.$sticky.attr( 'style', '' );
					},
					unfixed: function () {
						if( self.$sticky.hasClass( 'scroll-to-fixed-fixed' ) ) {
							self.$sticky.parent().attr( 'style', 'position: relative;' );
							self.$sticky.attr( 'style', 'position: absolute; z-index:unset; top: unset; bottom: 5px;' );
						}
					}
				}
			);
		},
	};

	pmc_sticky_sidebar.init();

} );
