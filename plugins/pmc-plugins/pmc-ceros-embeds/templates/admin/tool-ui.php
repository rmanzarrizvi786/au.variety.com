<?php
/**
 * Template for the admin tool page UI
 *
 * @author Amit Gupta <agupta@pmc.com>
 *
 * @since  2020-08-12
 */
?>
<div class="wrap pmc-ceros-embeds-admin">
	<h2><?php echo esc_html( $page_title ); ?></h2>
	<p>&nbsp;</p>
	<h4><?php esc_html_e( 'You can convert Ceros embed code to WP shortcode here which can be used on this site.', 'pmc-ceros-embeds' ); ?></h4>
	<p>&nbsp;</p>
	<div class="section">
		<p class="label">
			<label for="pmc-ceros-embeds-html">Ceros embed HTML</label>
		</p>
		<p>
			<textarea id="pmc-ceros-embeds-html" class="code-box"></textarea>
		</p>
		<p class="description">Place the HTML embed code from Ceros in the box above</p>
	</div>
	<div class="section">
		<button id="btn-pmc-ceros-embeds-converter" class="button-primary">Convert HTML to Shortcode</button>
	</div>
	<div class="section hidden">
		<p class="notice notice-success"></p>
	</div>
	<div class="section">
		<p class="label">
			<label for="pmc-ceros-embeds-shortcode">Ceros embed Shortcode</label>
		</p>
		<p>
			<textarea id="pmc-ceros-embeds-shortcode" class="code-box" readonly></textarea>
		</p>
		<p class="description">The shortcode in above box can be used anywhere on this site where shortcodes are allowed</p>
	</div>
</div>
