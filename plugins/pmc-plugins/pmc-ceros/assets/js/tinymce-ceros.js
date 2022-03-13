(function () {
	if (!tinymce) {
		// eslint-disable-line
		return;
	}

	let PMCCeros = (() => {
		return {
			init: () => {
				tinymce.create('tinymce.plugins.pmcceros', {
					// eslint-disable-line
					init: (editor, url) => {
						let $modal = jQuery('#pmc-ceros-dialog');

						$modal.dialog({
							title: PMC_CEROS_EDITOR.buttonTitle, // eslint-disable-line
							dialogClass: 'pmc-ceros-dialog',
							autoOpen: false,
							draggable: false,
							width: 'auto',
							modal: true,
							resizable: false,
							closeOnEscape: true,
							position: {
								my: 'center',
								at: 'center',
								of: window,
							},
							open: () => {
								// Close dialog by clicking the overlay behind it.
								$('.ui-widget-overlay').on('click', () => {
									$modal.dialog('close');
								});
							},
							create: () => {
								// Style fix for WordPress admin.
								$('.ui-dialog-titlebar-close').addClass(
									'ui-button'
								);
							},
						});

						editor.addCommand('pmc_ceros_insert_shortcode', () => {
							$modal.dialog('open');
						});

						editor.addButton('pmcceros', {
							title: PMC_CEROS_EDITOR.buttonTitle, // eslint-disable-line
							cmd: 'pmc_ceros_insert_shortcode',
							image: PMC_CEROS_EDITOR.cerosImageUrl,
						});
					},
				});

				tinymce.PluginManager.add('pmcceros', tinymce.plugins.pmcceros); // eslint-disable-line
			},

			events: () => {
				jQuery(function ($) {
					let $modal = $('#pmc-ceros-dialog');

					// Modal form submit.
					$modal.find('form').on('submit', (e) => {
						e.preventDefault();

						let textarea = $modal.find('textarea[name=embed_code]');
						//let embed      = ;
						let embed_html = $($.parseHTML($(textarea).val()));
						let iframe = $(embed_html).find('iframe');
						let content = '[pmc-ceros ';

						let div_style = embed_html.attr('style');
						if (
							typeof div_style !== typeof undefined &&
							div_style !== false
						) {
							content += 'div_style="' + div_style + '" ';
						}

						let id = embed_html.attr('id');
						if (typeof id !== typeof undefined && id !== false) {
							content += 'id="' + id + '" ';
						}

						let aspect_ratio = embed_html.attr('data-aspectRatio');
						if (
							typeof aspect_ratio !== typeof undefined &&
							aspect_ratio !== false
						) {
							content += 'aspect_ratio="' + aspect_ratio + '" ';
						}

						let mobile_aspect_ratio = embed_html.attr(
							'data-mobile-aspectRatio'
						);
						if (
							typeof mobile_aspect_ratio !== typeof undefined &&
							mobile_aspect_ratio !== false
						) {
							content +=
								'mobile_aspect_ratio="' +
								mobile_aspect_ratio +
								'" ';
						}

						let src = iframe.attr('src');
						if (typeof src !== typeof undefined && src !== false) {
							content += 'src="' + src + '" ';
						}

						let iframe_style = iframe.attr('style');
						if (
							typeof iframe_style !== typeof undefined &&
							iframe_style !== false
						) {
							content += 'iframe_style="' + iframe_style + '" ';
						}

						content += ']';

						tinymce.execCommand('mceInsertContent', false, content); // eslint-disable-line

						$modal.dialog('close');

						textarea.val('');
					});

					// Close form modal.
					$modal.find('.pmc-ceros-close').on('click', (e) => {
						e.preventDefault();

						$modal.dialog('close');
					});
				});
			},
		};
	})();

	try {
		PMCCeros.init();
		PMCCeros.events();
	} catch (error) {
		console.log(error);
	}
})();
