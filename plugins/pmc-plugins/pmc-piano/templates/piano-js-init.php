<?php
// @codeCoverageIgnoreStart
namespace PMC\Piano;

use PMC\Global_Functions\Traits\Singleton;

use PMC_Cheezcap;

$piano_custom_variables = $piano_custom_variables ?? new stdClass();
$piano_author = $piano_author ?? '';
$piano_tags = $piano_tags ?? [];
$cxense_site_ID = PMC_Cheezcap::get_instance()->get_option( Plugin::CXENSE_SITE_ID ) ?? '';

?>

<script>
window.tp = window.tp || [];

(function (tp) {
	var customVariables = JSON.parse( decodeURIComponent( '<?php
		echo rawurlencode( wp_json_encode( $piano_custom_variables ) );
	?>' ) );

	var contentAuthor = decodeURIComponent( '<?php echo rawurlencode( (string) $piano_author ); ?>' );

	var cxense_site_ID = decodeURIComponent( '<?php echo rawurlencode( (string) $cxense_site_ID ); ?>' );

	var tags = JSON.parse( decodeURIComponent( '<?php
		echo rawurlencode( wp_json_encode( $piano_tags ) );
	?>' ) );

	if (contentAuthor) {
		console.log('PMC Piano tp.push: [setContentAuthor=' + contentAuthor + ']');
		tp.push(['setContentAuthor', contentAuthor]);
	}


	if (tags.length > 0) {
		console.log('PMC Piano tp.push: [setTags=]', tags);
		tp.push(['setTags', tags]);
	}

	Object.keys(customVariables).forEach(function (key) {
		if (!Object.prototype.hasOwnProperty.call(customVariables, key)) {
			return;
		}

		var val = Array.isArray(customVariables[key])
			? customVariables[key].join(',')
			: customVariables[key];

		console.log('PMC Piano tp.push: [setCustomVariable, ' + key + '=' + val + ']');

		tp.push(['setCustomVariable', key, val]);
	});
	
	// Set CxenseSiteId for the Content Module execution
	if (cxense_site_ID) {
		console.log('PMC Piano tp.push: [setCxenseSiteID=' + cxense_site_ID + ']');
		tp.push(['setCxenseSiteId', cxense_site_ID]);
	}

})(window.tp);

</script>

<?php
// @codeCoverageIgnoreEnd
