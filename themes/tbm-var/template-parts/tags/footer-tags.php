<?php
/**
 * Site specific tags in the footer of every page.
 */

$blocker_atts = [
	'type'  => 'text/javascript',
	'class' => '',
];

if ( class_exists( '\PMC\Onetrust\Onetrust' ) ) {
	$blocker_atts = \PMC\Onetrust\Onetrust::get_instance()->block_cookies_script_type( 'optanon-category-C0004' );
}
?>

<!-- LinkedIn Tag -->
<script type="text/javascript">
_linkedin_partner_id = "1429113";
window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
window._linkedin_data_partner_ids.push(_linkedin_partner_id);
</script><script type="<?php echo esc_attr( $blocker_atts['type'] ); ?>" class="<?php echo esc_attr( $blocker_atts['class'] ); ?>">
(function(){var s = document.getElementsByTagName("script")[0];
var b = document.createElement("script");
b.type = "text/javascript";b.async = true;
b.src = "https://snap.licdn.com/li.lms-analytics/insight.min.js";
s.parentNode.insertBefore(b, s);})();
</script>
<noscript>
<img height="1" width="1" style="display:none;" alt="" src="https://px.ads.linkedin.com/collect/?pid=1429113&fmt=gif" />
</noscript>
<!-- End LinkedIn Tag -->
