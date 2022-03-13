<?php
/**
 * Loading youtube iframe api script in the header tags.
 */
?>
<!-- Start: Youtube Iframe API -->
<script>
	( function(){ 
		var tag = document.createElement('script');
		tag.src = "https://www.youtube.com/iframe_api";
		tag.async = true;
		window.onload = function() {
			document.getElementsByTagName('head')[0].appendChild( tag );
		}
	} )();
</script>
<!-- End: Youtube Iframe API -->
