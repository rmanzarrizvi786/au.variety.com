jQuery( document ).ready( function () {

	jQuery( document ).on('change', '#offsite_leaderboard .offsite-widget-league', function () {
		jQuery('.offsite-widget-league-category').find('option').empty().append('<option value="loading">Loading....</option>');
		jQuery(this).closest('form').find('input[type=submit]').click();
	});
});