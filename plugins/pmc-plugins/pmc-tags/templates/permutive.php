<?php

$raw_page_data    = [];
$raw_article_data = [];
$page_data        = [];
$article_data     = [];

if ( class_exists( 'PMC_Page_Meta' ) ) {
	$raw_page_data    = PMC_Page_Meta::get_page_data();
	$raw_article_data = PMC_Page_Meta::get_article_data();

	if ( ! empty( $raw_page_data ) ) {
		$page_data = [
			'page' => $raw_page_data
		];
	}

	if ( ! empty( $raw_article_data ) ) {
		$article_data = [
			'article' => $raw_article_data
		];
	}
}

$permutive_article_data = [];

if ( ! empty( $article_data ) ) {
	// Add Watson data to Permutive ONLY.
	$permutive_article_data = array_merge_recursive(
		$article_data,
		[
			'article' => [
				'watson' => [
					'taxonomy' => '$alchemy_taxonomy',
					'keywords' => '$alchemy_keywords',
					'entities' => '$alchemy_entities',
					'concepts' => '$alchemy_concepts',
				],
			],
		]
	);
}

// In Permutive, the article data is sent as a property of the page data.
$permutive_data = array_merge_recursive( $page_data, [ 'page' => $permutive_article_data ] );
$atlas_data     = array_merge_recursive( $page_data, $article_data );

// Ignoring coverage temporarily
// @codeCoverageIgnoreStart
$blocker_atts = [
	'type'  => 'text/javascript',
	'class' => '',
];

if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
	$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' );
}
// @codeCoverageIgnoreEnd
?>

<!--START Permutive Tag-->
<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
	!function(n,e,o,r,i){if(!e){e=e||{},window.permutive=e,e.q=[],e.config=i||{},e.config.projectId=o,e.config.apiKey=r,e.config.environment=e.config.environment||"production";for(var t=["addon","identify","track","trigger","query","segment","segments","ready","on","once","user","consent"],c=0;c<t.length;c++){var f=t[c];e[f]=function(n){return function(){var o=Array.prototype.slice.call(arguments,0);e.q.push({functionName:n,arguments:o})}}(f)}}}(document,window.permutive,'<?php echo esc_attr( $option['values']['project-id'] ); ?>','<?php echo esc_attr( $option['values']['api-key'] ); ?>',{});

	<?php if ( defined( 'DEFAULT_AD_PROVIDER' ) && 'google-publisher' === DEFAULT_AD_PROVIDER ) { ?>
	window.googletag=window.googletag||{},window.googletag.cmd=window.googletag.cmd||[],window.googletag.cmd.push(function(){if(0===window.googletag.pubads().getTargeting("permutive").length){window.headertag=window.headertag||{},window.headertag.cmd=window.headertag.cmd||[],window.headertag.cmd.push(function(){try{var e=JSON.parse(localStorage._pdfps||"[]").slice(0,250);window.headertag.setUserKeyValueData({segments:{permutive:e}})}catch(e){}});var e=localStorage.getItem("_pdfps");window.googletag.pubads().setTargeting("permutive",e?JSON.parse(e):[])}});
	<?php } ?>

	permutive.addon( 'web', <?php echo wp_json_encode( $permutive_data ); ?> );
</script>
<script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>" src='<?php echo esc_url( sprintf( 'https://cdn.permutive.com/%s-web.js', esc_attr( $option['values']['project-id'] ) ) ); ?>' async></script>
<!--END Permutive Tag-->

<!--START Atlas MG Tag-->
<script type="text/javascript">
var blogherads = blogherads || {};
blogherads.adq = blogherads.adq || [];

window.pmc_fpd = <?php echo wp_json_encode( $atlas_data ); ?>;

blogherads.adq.push(function () {
	try {
		blogherads.setPageMetaData( window.pmc_fpd );
	} catch (e) {
		// Do nothing
	}
});
</script>
<!--END Atlas MG Tag-->
