var pmc_gallery_attachment = new function () {

	this.set_suggest = function (id) {
		if (jQuery.isFunction(jQuery.suggest)) {
			jQuery('#' + id).suggest(ajaxurl + "?action=ajax-tag-search&tax=pmc_attachment_tags", {
				multiple: true,
				multipleSep: ","
			});
		}

		this.add_checkbox();
	}

	this.add_checkbox = function () {
		var ck_name = 'pmc_gallery_mediainput';

		if (!jQuery('.media-toolbar-primary .media-toolbar-primary.search-form #tmpl-pmc-gallery-attachment-tax-settings').length) {
			jQuery(".media-toolbar-primary.search-form").prepend(jQuery('#tmpl-pmc-gallery-attachment-tax-settings'));
			jQuery('.media-frame-toolbar .media-toolbar-primary.search-form #tmpl-pmc-gallery-attachment-tax-settings').hide();
			jQuery(".attachments-browser .media-toolbar-primary.search-form #tmpl-pmc-gallery-attachment-tax-settings").show();


			//Dont have better way to pass if the checkbox is checked therefore use cookie
			jQuery('#pmc-gallery-media-search-input').on('change', function () {
				// From the other examples
				if (this.checked) {
					pmc.cookie.set(ck_name, 1);
				} else {
					pmc.cookie.expire(ck_name);
				}
			});
		}
		//Make sure that if the checkbox is checked or unchecked clear the cookie
		if (jQuery('#pmc-gallery-media-search-input').prop('checked')) {
			pmc.cookie.set(ck_name, 1);
		} else {
			pmc.cookie.expire(ck_name);
		}

	}
}

//Document Ready
jQuery(
	function () {
		jQuery("#insert-media-button").on('click', function () {
			setTimeout(function () {
				pmc_gallery_attachment.add_checkbox();
				jQuery(".media-modal-content .media-router .media-menu-item").on('click', function () {
					pmc_gallery_attachment.add_checkbox();
				});
			}, 100);


		});
		setTimeout(function () {
			jQuery("#pmc-gallery .media-frame-menu .media-menu-item").on('click', function () {
				setTimeout(function () {
					pmc_gallery_attachment.add_checkbox();
				}, 1000);
			});
		}, 1000);
	}
);