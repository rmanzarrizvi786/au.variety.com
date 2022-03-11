<?php
/**
 * Footer Template
 *
 * Template for the V500 footer.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

$home_page_url   = Variety\Plugins\Variety_500\Templates::get_home_url();
$search_page_url = Variety\Plugins\Variety_500\Templates::get_search_url();
?>
	<footer class="l-site-footer">
		<div class="l-site-footer__container">

			<div class="c-site-footer">
				<a class="c-site-footer__logo" href="https://www.pmc.com/" title="PMC Network"><?php esc_html_e( 'PMC', 'pmc-variety' ); ?></a>

				<?php
				wp_nav_menu( array(
					'menu_class'     => 'c-site-footer__nav',
					'theme_location' => 'pmc_variety_500_footer',
					'container'      => false,
					'items_wrap'     => '<ul class="u-flex-justify-content-center u-flex-wrap-wrap u-no-bulletin u-style-wp-nav-menu-items u-margin-tb-20 %2$s">%3$s</ul>',
				) );
				?>

				<div class="c-site-footer__content">
					<p>
						<?php
						// translators: %s current year.
						echo wp_kses_post( sprintf( __( 'Variety is a part of Penske Media Corporation. &copy; %s Variety Media, LLC. All Rights Reserved.', 'pmc-variety' ), gmdate( 'Y' ) ) );
						?>
					</p>
					<p><?php esc_html_e( 'Powered by', 'pmc-variety' ); ?> <a href="https://vip.wordpress.com/?utm_source=vip_powered_wpcom&amp;utm_medium=web&amp;utm_campaign=VIP%20Footer%20Credit" rel="generator nofollow"><?php esc_html_e( 'WordPress.com VIP', 'pmc-variety' ); ?></a></p>
				</div><!-- .c-site-footer__content -->

				<div class="c-site-footer__social">
					<ul class="l-list l-list--inline">
						<li class="l-list__item l-list__item--inline">
							<a href="https://www.facebook.com/Variety" class="c-social-icon c-social-icon--muted c-social-icon__facebook" data-track="facebook" rel="nofollow" target="_blank">
								<span class="screen-reader-text"><?php esc_html_e( 'Facebook', 'pmc-variety' ); ?></span>
							</a>
						</li>
						<li class="l-list__item l-list__item--inline">
							<a href="https://twitter.com/variety" class="c-social-icon c-social-icon--muted c-social-icon__twitter" rel="nofollow" target="_blank">
								<span class="screen-reader-text"><?php esc_html_e( 'Twitter', 'pmc-variety' ); ?></span>
							</a>
						</li>
						<li class="l-list__item l-list__item--inline">
							<a href="https://www.linkedin.com/company/variety" class="c-social-icon c-social-icon--muted c-social-icon__linkedin" rel="nofollow" target="_blank">
								<span class="screen-reader-text"><?php esc_html_e( 'LinkedIn', 'pmc-variety' ); ?></span>
							</a>
						</li>
					</ul>
				</div><!-- .c-site-footer__social -->
			</div><!-- .c-site-footer -->

		</div><!-- .l-site-footer__container -->
	</footer><!-- .l-site-footer -->

</div><!-- .l-offcanvas__site -->
<nav class="l-offcanvas__nav">
	<div class="l-offcanvas__menu">

		<ul class="c-site-header__menu c-site-header__menu--vertical">
			<li class="c-site-header__nav-item"><a class="c-site-header__nav-link" href="<?php echo esc_url( $home_page_url ); ?>#about"><?php esc_html_e( 'About', 'pmc-variety' ); ?></a></li>
			<li class="c-site-header__nav-item"><a class="c-site-header__nav-link" href="<?php echo esc_url( $home_page_url ); ?>#spotlight"><?php esc_html_e( 'Spotlight', 'pmc-variety' ); ?></a></li>
			<li class="c-site-header__nav-item"><a class="c-site-header__nav-link" href="<?php echo esc_url( $home_page_url ); ?>#by-the-numbers"><?php esc_html_e( 'By the numbers', 'pmc-variety' ); ?></a></li>
			<li class="c-site-header__nav-item"><a class="c-site-header__nav-link" href="<?php echo esc_url( $search_page_url ); ?>"><?php esc_html_e( 'Explore the 500', 'pmc-variety' ); ?></a></li>
		</ul>

	</div><!-- .l-offcanvas__menu -->
	<div class="l-offcanvas__social">

		<div class="c-site-header__social">
			<ul class="l-list l-list--inline">
				<li class="l-list__item l-list__item--inline">
					<a href="https://www.facebook.com/Variety" class="c-social-icon c-social-icon--muted c-social-icon__facebook" data-track="facebook" rel="nofollow" target="_blank">
						<span class="screen-reader-text"><?php esc_html_e( 'Facebook', 'pmc-variety' ); ?></span>
					</a>
				</li>
				<li class="l-list__item l-list__item--inline">
					<a href="https://twitter.com/variety" class="c-social-icon c-social-icon--muted c-social-icon__twitter" rel="nofollow" target="_blank">
						<span class="screen-reader-text"><?php esc_html_e( 'Twitter', 'pmc-variety' ); ?></span>
					</a>
				</li>
				<li class="l-list__item l-list__item--inline">
					<a href="https://www.linkedin.com/company/variety" class="c-social-icon c-social-icon--muted c-social-icon__linkedin" rel="nofollow" target="_blank">
						<span class="screen-reader-text"><?php esc_html_e( 'LinkedIn', 'pmc-variety' ); ?></span>
					</a>
				</li>
			</ul>
		</div>

	</div><!-- .l-offcanvas__social -->
	<div class="l-offcanvas__back">

		<a class="c-site-header__back c-site-header__back--standalone" href="/"><?php esc_html_e( 'Back to Variety', 'pmc-variety' ); ?></a>

	</div><!-- .l-offcanvas__back -->
</nav><!-- .l-offcanvas__nav -->

<?php
wp_footer();
do_action( 'pmc-tags-footer' ); //@codingStandardsIgnoreLine
do_action( 'pmc-tags-bottom' ); //@codingStandardsIgnoreLine
?>

<?php
$sponsor_pixel = get_option( 'variety_500_sponsor_pixel' );
if ( ! empty( $sponsor_pixel ) ) :
?>
<img src="<?php echo esc_url( $sponsor_pixel ); ?>" alt="" />
<?php endif; ?>

</body>
</html>
