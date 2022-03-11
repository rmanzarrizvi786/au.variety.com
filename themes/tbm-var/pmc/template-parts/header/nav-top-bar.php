<?php
/**
 * Header Top Bar Template
 *
 * @package pmc-variety-2017;
 */

// Find a random post to display.
$random_post = \PMC\Core\Inc\Theme::get_instance()->get_random_recent_post();
$page_meta   = PMC_Page_Meta::get_page_meta();
?>
<div class="l-header__topbar">
	<div class="c-top-bar">
		<?php if ( ! empty( $random_post ) ) : ?>
			<div class="c-top-bar__read-next">
				<a href="<?php echo esc_url( get_the_permalink( $random_post->ID ) ); ?>"
				   class="c-top-bar__read-next__link">
					<?php esc_html_e( 'Read Next:', 'pmc-variety' ); ?>
					<strong><?php echo esc_html( $random_post->post_title ); ?></strong>
				</a>
			</div><!-- .c-top-bar__read-next -->
		<?php endif; ?>

		<?php
		wp_nav_menu( array(
			'menu_class'     => 'c-top-bar__social',
			'theme_location' => 'pmc_core_social',
			'container'      => false,
			'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
			'link_before'    => '<span class="screen-reader-text">',
			'link_after'     => '</span>',
		) );
		?>

		<div class="c-top-bar__search">

			<div class="c-search c-search--expandable">
				<div data-st-search-form="small_search_form"></div>
			</div><!-- .c-search c-search--expandable -->

		</div><!-- .c-top-bar__search -->

	</div><!-- .c-top-bar -->

</div><!-- .l-header__topbar -->
