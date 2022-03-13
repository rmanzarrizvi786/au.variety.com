(function($) {
	/**
	 * Check image size and display warning dimensions do not meet criteria.
	 *
	 * @param $parent
	 */
	var image_check = function($parent) {
		var modal = $parent.find('.media-frame-title').text(),
			html = '';

		try {
			for (var i = 0; i < pmc_images.image_size_warning.length; i++) {
				var image = pmc_images.image_size_warning[i];
				if (modal === image.title) {
					html = $parent.find('.' + image.class).html();
					$parent.find('.media-sidebar div.attachment-details :header:first').after(html);
					break;
				}
			}
		} catch(e) {}
	}

	/**
	 * Remove standard WP image sizes from Insert Media modal.
	 *
	 * @param $frame
	 */
	var remove_standard_image_sizes = function($frame) {
		try {
			if ('' !== pmc_images.remove_standard_images) {
				var $select = $frame.find('.attachment-display-settings select.size'),
					options = [];

				for (var i = 0; i < pmc_images.customize_remove_standard_images.length; i++) {
					var size = pmc_images.customize_remove_standard_images[i];
					options[i] = 'option[value="' + size + '"]';
				}
				options = options.join();
				if ( '' !== options ) {
					$select.find(options).remove();
				}
			}
		} catch(e) {}
	};

	/**
	 * Checks if the image being selected or uploaded has the correct dimensions.
	 * If it doesn't, a warning is displayed to the user.
	 */
	$(function() {
		if (wp.media) {
			var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;

			wp.media.view.Modal.prototype.on('open', function() {
				var $frame = $('.media-modal-content:visible');
				window.pmc_image_check_observer = new MutationObserver(function(mutations) {
					if (mutations.length) {
						image_check($frame);
						remove_standard_image_sizes($frame);
					}
				});

				window.pmc_image_check_observer.observe(document.body, {
					childList: true
				});
			});

			wp.media.view.Modal.prototype.on('close', function() {
				window.pmc_image_check_observer.disconnect();
			});
		}
	});


})(jQuery);
