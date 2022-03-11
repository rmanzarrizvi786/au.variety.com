<?php
/**
 * Breadcrumb HTML
 */
$breadcrumbs = \PMC\Core\Inc\Theme::get_instance()->get_breadcrumb();
if ( empty( $breadcrumbs ) || ! is_array( $breadcrumbs ) ) {
	return;
}
?>
<div id="bread-crumb" class="site__bread-crumbs desktop clearfix">
	<ul>
		<?php

		for ( $i = 0; $i < count( $breadcrumbs ); $i ++ ) {

			$breadcrumb = $breadcrumbs[ $i ];
			$term_link  = $breadcrumb->link;

			if ( empty( $breadcrumb->link ) ) {
				$link = get_term_link( $breadcrumb );
				if ( ! is_wp_error( $link ) ) {
					$term_link = $link;
				}
			} else {
				$term_link = $breadcrumb->link;
			}
			$term_link = trailingslashit( $term_link );

			$span  = '';
			$class = '';

			if ( count( $breadcrumbs ) - 1 == $i ) {
				$span  = "<span class='square'></span>";
				$class = 'bread-crumb-selected';
			}
			?>
			<li class="post-header__category">
				<a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $term_link ); ?>">
					<?php echo wp_kses_post( $span . $breadcrumb->name ); ?>
				</a>
			</li>
			<?php
		}
		?>
	</ul>
</div>
<?php
//EOF
