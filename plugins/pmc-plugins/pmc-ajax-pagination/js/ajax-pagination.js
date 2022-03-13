/**
 * Javascript for the Ajax Pagination Plugin
 *
 * Handles auto ajax page load if pagination # given
 * Handles ajax page load if pagination links clicked
 * Assigns the select class as appropriate
 * Animates to top of loaded data an offset can be given in code
 * as parameter "top_adjust" to manage the position it scrolls to
 *
 * Uses browserstate History.js
 * https://github.com/browserstate/history.js
 *
 * @author Vicky Biswas
 * @since 20131010
 */

jQuery(document).ready(function(){
	var History = window.History;

	if (History.enabled) {
		State = History.getState();
		// set initial state to first page that was loaded
		if ( 1 == pmc_ajax_pagination.current ) {
			$url = pmc_ajax_pagination.url;
		} else {
			$url = pmc_ajax_pagination.url + 'pg/' + pmc_ajax_pagination.current + '/' + pmc_ajax_pagination.key + '/' + pmc_ajax_pagination.term_id;
		}
		History.pushState(
			{page: pmc_ajax_pagination.current },
			jQuery("title").text(),
			$url
		);
	} else {
		return false;
	}

	var updateContent = function(State) {
		jQuery.ajax({
			type: 'GET',
			context: this,
			url: pmc_ajax_pagination.url + 'pg/' + State.data.page + '/' + pmc_ajax_pagination.key + '/' + pmc_ajax_pagination.term_id + '?json',
			success: function(data){

				//Ok we have the data lets put it in
				jQuery( '.' + pmc_ajax_pagination.dataclass ).html(data.html);
				
				//Lazy loading if needed
				if ( jQuery.fn.lazyload ) {
					jQuery( '.' + pmc_ajax_pagination.dataclass + ' img' ).lazyload({
						effect: "fadeIn"
					});
				}

				//change current page
				pmc_ajax_pagination.current=State.data.page;

				//selected array to add current class
				var $selected = new Array(' .nav'+State.data.page);
				//update previous link url
				var $prev = State.data.page-1;
				if ($prev<=0) {
					//edge
					$prev=1;
					$selected.push(' .prev');
				}

				jQuery( '.' + pmc_ajax_pagination.navclass + ' .prev' )
				.attr('href', pmc_ajax_pagination.url + 'pg/' + $prev + '/' + pmc_ajax_pagination.key + '/' + pmc_ajax_pagination.term_id )
				.data( "pagination", { pos: $prev } );

				//update next link url
				var $next = parseInt(State.data.page)+1;
				if ($next>pmc_ajax_pagination.total) {
					//edge
					$next=pmc_ajax_pagination.total;
					$selected.push(' .next');
				}

				jQuery( '.' + pmc_ajax_pagination.navclass + ' .next' )
				.attr('href', pmc_ajax_pagination.url + 'pg/' + $next + '/' + pmc_ajax_pagination.key + '/' + pmc_ajax_pagination.term_id )
				.data( "pagination", { pos: $next } );

				//lets make a beautiful animation
				jQuery('html, body').animate({scrollTop:jQuery('.' + pmc_ajax_pagination.dataclass).offset().top - parseInt(pmc_ajax_pagination.topadjust)}, 'slow');

				//set the select class as needed
				jQuery( '.' + pmc_ajax_pagination.navclass + ' a').removeClass('current');
				for (var i = 0; i < $selected.length; i++) {
					jQuery( '.' + pmc_ajax_pagination.navclass + $selected[i]).addClass('current');
				}

			}
		});
	};

	// Content update and back/forward button handler
	History.Adapter.bind(window, 'statechange', function() {
		updateContent(History.getState());
	});

	// navigation link handler
	jQuery('body').on('click', '.page-numbers', function(e) {
		var urlPath = jQuery(this).attr('href');
		var title = jQuery(this).text();
		data=jQuery(this).data("pagination");

		if ( 1 == data.pos ) {
			$url = pmc_ajax_pagination.url;
		} else {
			$url = pmc_ajax_pagination.url + 'pg/' + data.pos + '/' + pmc_ajax_pagination.key + '/' + pmc_ajax_pagination.term_id;
		}
		History.pushState(
			{page: data.pos },
			jQuery("title").text(),
			$url
		);
		return false; // prevents default click action of <a ...>
	});
 });