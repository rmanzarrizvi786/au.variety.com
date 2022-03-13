jQuery(function () {
	jQuery(".voices-author").sortable().disableSelection();

	jQuery("#pmc-voices-form").submit(function (event) {
		var data = {};
		var count = jQuery(".voices-author li").length;
		jQuery(".voices-author li").each(function (i) {
			var p = jQuery(this).data('post-id');
			data[p] = count - jQuery(this).index();
		});
		data = JSON.stringify(data);
		jQuery("#sorted-value").val(data);

	});
});