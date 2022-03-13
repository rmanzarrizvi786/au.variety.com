var pmclinkcontent = {};
(function($, window, undefined) {

	pmclinkcontent.init = function() {
		pmclinkcontent.autocompleteCache = pmclinkcontent.autocompleteAjax = {};

		$('.pmclinkcontent-include-old').on('click', function() {
			// reset autocomplete cache when changing between including old results and not, since results will differ
			pmclinkcontent.autocompleteCache = pmclinkcontent.autocompleteAjax = {};
		});

		$(".pmclinkcontent-post-search").each(function(){
			pmclinkcontent.pmclinkcontentPostSearch = $(this);

			// Initialize autocomplete
			if(pmclinkcontent.pmclinkcontentPostSearch.length) {
				pmclinkcontent.pmclinkcontentPostSearch
					.autocomplete({
						minLength: 3,
						// Remote source with caching
						source: function( request, response ) {
							var term = request.term;
							var type = 'Article';
							var selected = jQuery(this.element[0]).parent().parent().find("input[type='radio']:checked");
							if (selected.length > 0)
								type = selected.val();

							if ( ( type + '%' + term ) in pmclinkcontent.autocompleteCache ) {
								response( pmclinkcontent.autocompleteCache[ type + '%' + term ] );
								return;
							}

							// Append more request vars
							request.action = 'pmclinkcontent_search_posts';
							request.exclude = [];
							request.type = ('Section Front'==type)?'sf':('Article'==type)?'a':type;
							request.includeold = jQuery(this.element[0]).parent().find('.pmclinkcontent-include-old').is(':checked') ? '1' : '0';

							pmclinkcontent.autocompleteAjax = $.getJSON( ajaxurl, request,
								function ( data, status, xhr ) {
									pmclinkcontent.autocompleteCache[ type + '%' + term ] = data;
									if ( xhr === pmclinkcontent.autocompleteAjax ) {
										response( data );
									}else{
										response("");
									}
								} );
						},
						select: function( e, ui ) {
							$data = {
								"id": ui.item.post_id,
								"url": ui.item.post_url,
								"title": ui.item.title, // Added on PPT-3486
								"content": ui.item.post_excerpt
							}
							if ( ui.item.hasOwnProperty("post_type") ) {
								$data["taxonomy"] = ui.item.post_type;
							}
							pmclinkcontent.addPost( $data, this );
						}
					});

				// Compat with jQuery 1.8 and 1.9; the latter uses ui- prefix for data attribute
				var autocomplete = pmclinkcontent.pmclinkcontentPostSearch.data( 'autocomplete' ) || pmclinkcontent.pmclinkcontentPostSearch.data( 'ui-autocomplete' );

				autocomplete._renderItem = function( ul, item ) {
					var content = '<a class= "pmc" data-id="' + item.post_id + '" data-url="' + item.post_url + '" >'
						+ '<span class="title">' + item.title + '</span>'
						+ '<span class="type">' + item.post_type + '</span>'
						+ '<span class="date">' + item.date + '</span>'
						+ '<span class="status">' + item.post_status + '</span>'
						+ '<br class="clear"/>'
						+ '</a>';
					return $( '<li></li>' )
						.data( 'item.autocomplete', item )
						.append( content )
						.appendTo( ul )
						;
				}
			}
		});
	}

	pmclinkcontent.addPost = function(post, context) {
		var type = 'Article';
		var selected = $("input[type='radio'][name='pmc_type']:checked");
		if (selected.length > 0)
			type = selected.val();

		if ( 'fail' == post.id ) {
			return;
		}

		jQuery(context).parent().find('.pmclinkcontent-type').val(type);

		// PPT-3486
		// Date: Oct 20, 2014
		// By: Javier Martinez
		// Added first 200 characters of post.content
		// PPT-3844 Amit Sannad 2014-12-12
		var data_post_content = "";
		if ("undefined" != typeof post.content) {
			data_post_content = post.content.substr(0, 200);
		}
		jQuery(context).parent().find('.pmclinkcontent-post-result').html('Selected ' + type + ': <a class="pmclinkcontent-post" href="' + post.url + '" data-post-content="' + data_post_content + '" data-id="' + post.id + '" target="_blank" >' + post.title + '</a> <span class="pmclinkcontent-remove">(remove)</span>');

		jQuery(context).parent().find('.pmclinkcontent-post-value').val(JSON.stringify(post));

		//fire a custom event
		jQuery.event.trigger({
			type: 'pmclinkcontent_addpost',
			elem: jQuery(context).parent()
		});
	}

	//Leaving intact from zoninator in case we wanna extend
	pmclinkcontent.getAjaxAction = function(action) {
		return '' + action;
	}

	$(document).ready(function() {
		pmclinkcontent.init();
	});
	$(document).on( 'click', '.pmclinkcontent-remove', function(){
		//fire a custom event
		$.event.trigger({
			type: 'pmclinkcontent_remove',
			elem: $(this).parent().parent()
		});

		$(this).parent().parent().find('.pmclinkcontent-post-value').val('');
		$(this).parent().parent().find('.pmclinkcontent-post-result').html('');
	});
	$(document).on( 'click', "input[type='radio']", function(){
		var searchdesc = $(this).parent().parent().parent().find('p.description');
		var searchlabel = $(this).parent().parent().parent().find('.pmclinkcontent-post-search-label');
		var $includeOld = $(this).parent().parent().parent().find('.pmclinkcontent-include-old-container');

		if ( 'Section Front' == this.value ) {
			if (searchdesc.length > 0) {
				searchdesc.html( (searchdesc.html()).replace('Article', 'Section Front') );
			}
			if (searchlabel.length > 0) {
				searchlabel.html( (searchlabel.html()).replace('Article', 'Section Front') );
			}
			$includeOld.addClass('hidden');
		} else {
			if (searchdesc.length > 0) {
				searchdesc.html( (searchdesc.html()).replace('Section Front', 'Article') );
			}
			if (searchlabel.length > 0) {
				searchlabel.html( (searchlabel.html()).replace('Section Front', 'Article') );
			}
			$includeOld.removeClass('hidden');
		}
	});
})(jQuery, window);
