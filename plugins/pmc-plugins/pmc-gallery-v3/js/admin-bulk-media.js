(function ($) {
	'use strict';
	$(document).ready(function () {
		if ($('.wp-admin.upload-php .view-list.current').length <= 0) {
			return;
		}
		$('select#bulk-action-selector-top option:last-child').before('<option value="bulk_attachment_tag">Add Attachment Tags</option>');
		$('select#bulk-action-selector-bottom option:last-child').before('<option value="bulk_attachment_tag">Add Attachment Tags</option>');
		$('select#bulk-action-selector-top').on('change', function () {
			var attachment_tag_top = $('#bulk-attachment-tag-top');
			if ('bulk_attachment_tag' === $(this).val()) {
				attachment_tag_top.show();
			} else {
				attachment_tag_top.hide();
			}
		}).after('<input id="bulk-attachment-tag-top" type="search" placeholder="Comma-delimited list of Tags" name="bulk-attachment-tag-top" class="bulk-attachment-tag" style="display: none"/>');
		$('select#bulk-action-selector-bottom').on('change', function () {
			var bulk_attachment_tag = $('#bulk-attachment-tag-bottom');
			if ('bulk_attachment_tag' === $(this).val()) {
				bulk_attachment_tag.show();
			} else {
				bulk_attachment_tag.hide();
			}
		}).after('<input id="bulk-attachment-tag-bottom" type="search" placeholder="Comma-delimited list of Tags" name="bulk-attachment-tag-bottom" class="bulk-attachment-tag" style="display: none"/>');
	});

})(jQuery);
