<?php

/**
 * @description This template is used to display the last indexing request status in admin
 */

?>

<div class="wrap">
	<h2><?php esc_html_e( 'Google Real time Indexing' ); ?></h2>
	<div class="wrap">
		<div>
			<h4>Last request response</h4>
		</div>
		<pre><?php esc_html_e( $last_status ); ?></pre>

		<div>
			<h4>Last request body</h4>
		</div>
		<pre><?php esc_html_e( $last_request ); ?></pre>
	</div>

</div>

<?php

// EOF