<div class="hp-panel">
	<div class="pmc-stocks-market-movers-widget">
		<h3>
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 101.6 29.8"><title>WWD logo</title><path d="M84.8 29.8c8.7 0 16.8-3.1 16.8-14.9S93.5 0 84.8 0H77l-3.1 11.7v18.1h10.9zm-2.3-7.6V7.6h2.4c5.4 0 7.8 1.9 7.8 7.3s-2.4 7.3-7.8 7.3h-2.4zm-40.4 7.6h8.5l4.2-15.9L59 29.8h8.6l8-29.8h-8l-4.2 16.3L59.1 0H51l-4.4 16.5-4-15.4-4.1 15.5 3.6 13.2zm-34 0h8.6l4.2-15.9 4.2 15.9h8.5l8-29.8h-7.9l-4.2 16.4L25.1 0H17l-4.3 16.3L8.5 0H0l8.1 29.8z"></path></svg>
			<?php echo esc_html( $widget_data['title'] ); ?>
		</h3>
		<div class="pmc-stocks-market-movers-table"></div>
		<?php if ( ! empty( $widget_data['url'] ) ) : ?>
			<h4 class="pmc-stocks-url">
				<a href="<?php echo esc_url( $widget_data['url'] ); ?>">
					Business Page
					<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1536 1536" style="enable-background:new 0 0 1536 1536;" xml:space="preserve"><title>right arrow</title><path d="M1285,768c0-18-6-33-18-45l-91-91L814,270c-12-12-27-18-45-18s-33,6-45,18l-91,91c-12,12-18,27-18,45s6,33,18,45l189,189
	H320c-17.3,0-32.3,6.3-45,19s-19,27.7-19,45v128c0,17.3,6.3,32.3,19,45s27.7,19,45,19h502l-189,189c-12.7,12.7-19,27.7-19,45
	s6.3,32.3,19,45l91,91c12,12,27,18,45,18s33-6,45-18l362-362l91-91C1279,801,1285,786,1285,768z M1536,768
	c0,139.3-34.3,267.8-103,385.5s-161.8,210.8-279.5,279.5S907.3,1536,768,1536s-267.8-34.3-385.5-103S171.7,1271.2,103,1153.5
	S0,907.3,0,768s34.3-267.8,103-385.5S264.8,171.7,382.5,103S628.7,0,768,0s267.8,34.3,385.5,103s210.8,161.8,279.5,279.5
	S1536,628.7,1536,768z"></path></svg>
				</a>
			</h4>
		<?php endif; ?>
	</div>
	<script>
		// Document Ready.
		jQuery(function() {
			if ( 'undefined' !== typeof window.pmc && 'undefined' !== typeof window.pmc.stocks ) {
				pmc.stocks.build_market_movers( <?php echo wp_json_encode( $gainers ); ?>, <?php echo wp_json_encode( $decliners ); ?> );
			}
		});
	</script>
</div>
