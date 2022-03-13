<!-- BEGIN Amazon Apstag -->
<script>
	!function(a9,a,p,s,t,A,g){if(a[a9])return;function q(c,r){a[a9]._Q.push([c,r])}a[a9]={init:function(){q("i",arguments)},fetchBids:function(){q("f",arguments)},setDisplayBids:function(){},_Q:[]};A=p.createElement(s);A.async=!0;A.src=t;g=p.getElementsByTagName(s)[0];g.parentNode.insertBefore(A,g)}("apstag",window,document,"script","//c.amazon-adsystem.com/aax2/apstag.js");
	apstag.init({
		pubID: <?php echo wp_json_encode( $pub_id ); ?>,
		adServer: 'googletag',
		videoAdServer: 'DFP',
		bidTimeout: 2e3
	});
</script>
<!-- End Amazon Apstag -->
