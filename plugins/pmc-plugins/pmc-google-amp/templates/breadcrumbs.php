<?php
/**
 * Template for breadcrumbs for AMP page.
 *
 * @author Dhaval Parekh <dhaval.parekh@rtcmap.com>
 *
 * @since 2017-07-27 CDWE-446
 *
 * @package pmc-google-amp
 */
if ( ! isset( $breadcrumbs ) ) {
	return;
}
?>
<div class="article-breadcrumb-container">
	<ul class="article-header__breadcrumbs">
		<?php
		foreach ( $breadcrumbs as $crumb ) {
			if ( ! empty( $crumb['label'] ) ) {
				printf( '<li class="%s">', esc_attr( $crumb['class'] ) );

				if ( ! empty( $crumb['href'] ) ) {
					printf( '<a href="%s">%s</a>', esc_url( $crumb['href'] ), esc_html( $crumb['label'] ) );
				} else {
					echo esc_html( $crumb['label'] );
				}

				echo '</li>';
			}
		}
		?>
	</ul>
</div>
