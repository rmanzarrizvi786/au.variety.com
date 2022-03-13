var pmc_breaking_news = {
	post_ajax: function () {
		var $title    = jQuery("#_pmc-breaking-news .title").val();
		var $link     = jQuery("#_pmc-breaking-news .link").val();
		var $image_id = jQuery( '#pmc_brk_news_image_id' ).val();
		var $post_id  = jQuery("#_pmc-breaking-news .pmclinkcontent-post").data('id');
		var $nonce    = jQuery("#_pmc-breaking-news #_pmc-breaking-news-link-box").val();
		var $active   = jQuery("#_pmc-breaking-news input:radio[name=pmc_brk_news_active]:checked").val();

		if ('off' == $active) {
			$title = $link = $post_id = $image_id = '';
		}

		var data = {
			action   : 'save-breaking-news',
			title    : $title,
			link     : $link,
			image_id : $image_id,
			post_id  : $post_id,
			active   : $active,
			nonce    : $nonce
		};
		jQuery("#_pmc-breaking-news .saving").html('saving.......');
		jQuery.post(ajaxurl, data, function (response) {
			if (-1 < response.indexOf('success')) {
				jQuery("#_pmc-breaking-news .saving").html("Saved data");
				
				if ('off' == $active) {
					var $title = jQuery("#_pmc-breaking-news .title").val("");
					var $link = jQuery("#_pmc-breaking-news .link").val("");
					jQuery( '#pmc_brk_news_image_id' ).val( '' );
					jQuery( '#pmc_brk_news_image_wrapper' ).html( '' );
					jQuery("#_pmc-breaking-news .pmclinkcontent-remove").trigger('click');
				}
			} else {
				jQuery("#_pmc-breaking-news .saving").html("Save failed");
			}

		});
	}
}

jQuery(document).ready(function () {

	jQuery('#pmc_brk_news_save').click(
		function () {
			pmc_breaking_news.post_ajax();
			return false;
		}
	)

	jQuery( "#pmc_brk_news_add_image" ).click( function() {
		var brk_news_img_frame = wp.media({
			title : 'Breaking News Banner Image',
			multiple : false, // set to false if you want only one image
			library : { type : 'image'},
			button : { text : 'Add Image' },
		});

		brk_news_img_frame.on( 'close',function( data ) {
			brk_news_img_frame.state().get( 'selection' ).each( function( image ) {
				var brk_news_img = jQuery( '<img />' ).attr( 'src', image.attributes.sizes.thumbnail.url ).attr( 'width', '100').attr( 'height', '100');
				var brk_news_clear_img = jQuery( '<a/>' ).attr( 'href', 'javascript:;').text( 'Clear' );
				jQuery( '#pmc_brk_news_image_wrapper' ).html( '' ).append( brk_news_img, brk_news_clear_img );

				jQuery( '#pmc_brk_news_image_id' ).val( image.id );
			});
		});

		brk_news_img_frame.open();
	});

	jQuery( document ).on( 'click', '#pmc_brk_news_image_wrapper a', function ( event ) {
		event.preventDefault();

		jQuery( '#pmc_brk_news_image_wrapper' ).html( '' );
		jQuery( '#pmc_brk_news_image_id' ).val( '' );

		return false;
	});

});
