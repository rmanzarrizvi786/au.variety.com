<?php
/**
 * Mega Menu Template
 *
 * NOTE: While this template is used in the footer, its trigger is actually in
 * the header.
 *
 * @package pmc-core-v2-2017;
 */

?>
<div class="l-page__mega" id="mega-menu">
	<div class="l-mega">

		<div class="l-mega__top-bar">
			<div class="l-mega__logo">

				<a class="c-logo c-logo--flexible" href="<?php home_url(); ?>" title="<?php esc_attr_e( 'Variety', 'pmc-core' ); ?>">
					<span class="screen-reader-text"><?php esc_html_e( 'Variety.com', 'pmc-core' ); ?></span>
				</a>

			</div><!-- .l-mega__logo -->
			<div class="l-mega__search">

				<div class="c-search">
					<div data-st-search-form="search_form"></div>
				</div><!-- .c-search c-search--expandable -->

			</div><!-- .l-mega__search -->
			<div class="l-mega__close">

				<button class="c-button c-button--mega-close" data-toggle="mega-menu">
					<span class="screen-reader-text"><?php esc_html_e( 'Close Menu', 'pmc-core' ); ?></span>
				</button>

			</div><!-- .l-mega__close -->
		</div><!-- .l-mega__top-bar -->

		<div class="l-mega__nav-primary">
			<?php
				wp_nav_menu( array(
					'menu_class'      => 'c-nav c-nav--mega',
					'theme_location'  => 'pmc_variety_mega',
					'container_class' => 'l-mega__nav-primary',
					'items_wrap'      => '<ul class="%2$s">%3$s</ul>',
					'depth'           => 2,
				) );
			?>
		</div><!-- .l-mega__nav-primary -->

		<div class="l-mega__bottom-bar">
			<a class="c-signin" href="<?php echo esc_url( home_url( 'digital-subscriber-access/#r=/premier/', 'https' ) ); ?>">
				<span class="c-icon c-icon--user"></span>
				<span class="c-signin__text"><?php esc_html_e( 'Login', 'pmc-core' ); ?></span>
			</a><!-- .c-signin -->

			<div class="l-mega__social">

				<div class="l-mega__bottom-bar__heading">
					<h3 class="c-heading"><?php esc_html_e( 'Follow Us', 'pmc-core' ); ?></h3>
				</div>

				<?php
					wp_nav_menu( array(
						'menu_class'      => 'l-list l-list--row',
						'theme_location'  => 'pmc_core_social',
						'container'       => false,
						'items_wrap'      => '<ul class="%2$s">%3$s</ul>',
						'link_before'     => '<span class="screen-reader-text">',
						'link_after'      => '</span>',
					) );
				?>

			</div><!-- .l-mega__social -->

			<div class="l-mega__newsletter">

				<div class="l-mega__bottom-bar__heading">
					<h3 class="c-heading"><?php esc_html_e( 'Alerts &amp; Newsletters', 'pmc-core' ); ?></h3>
				</div>
				<div class="c-newsletter c-newsletter--mega">

					<?php $pages_url = 'https://pages.email.' . apply_filters( 'pmc_core_site_domain', wp_parse_url( get_home_url(), PHP_URL_HOST ) ); ?>

					<form method="post" action="<?php echo esc_url( $pages_url . '/signup' ); ?>" name="newsletter-module-form" class="c-newsletter__form" target="_blank">
						<input name="EmailAddress" required class="c-newsletter__email js-newsletter-email" placeholder="<?php esc_attr_e( 'Email address', 'pmc-core' ); ?>" type="email">
						<span class="c-newsletter__tooltiptext tooltip-bottom"><?php esc_html_e( 'Please fill out this field with valid email address.', 'pmc-core' ); ?></span>
						<button type="submit" class="c-newsletter__button"><?php esc_html_e( 'Sign Up', 'pmc-core' ); ?></button>
						<input name="Editorial_Daily_Headlines_Opted_In" value="Yes" type="hidden">
						<input name="__contextName" value="FormPost" type="hidden">
						<input name="__executionContext" value="Post" type="hidden">
						<input name="__successPage" class="js-newsletter-successpage" value="" data-base-url="<?php echo esc_url( $pages_url . '/newsletters/?signup=success' ); ?>" type="hidden">
					</form>
				</div>

			</div><!-- .l-mega__newsletter -->
		</div><!-- .l-mega__bottom-bar -->
		<div class="l-mega__footer">
			<div class="l-mega__footer-nav">

				<?php
					wp_nav_menu( array(
						'menu_class'      => 'l-list l-list--row',
						'theme_location'  => 'pmc_core_mega_bottom',
						'container_class' => 'c-nav c-nav--row',
						'items_wrap'      => '<ul class="%2$s">%3$s</ul>',
					) );
				?>

			</div><!-- .l-mega__footer-nav -->
			<div class="l-mega__copyright">

				<div class="c-copyright">
					<a class="c-copyright__logo" href="<?php echo esc_url( 'https://pmc.com/' ); ?>" title="<?php esc_attr_e( 'PMC Network', 'pmc-core' ); ?>"  target="_blank">
						<span class="screen-reader-text"><?php esc_html_e( 'PMC', 'pmc-core' ); ?></span>
					</a>

					<p class="c-copyright__text"><?php echo esc_html( sprintf( '&copy; %1$s Penske Media Corporation', date( 'Y' ) ), 'pmc-core' ); ?></p>
				</div>

			</div><!-- .l-mega__copyright -->
		</div><!-- .l-mega__footer -->
	</div><!-- .l-mega -->
	<div class="l-mega__mobile-close">

		<button class="c-button c-button--mega-close" data-toggle="mega-menu">
			<span class="screen-reader-text">Close menu</span>
		</button>

	</div><!-- .l-mega__mobile-close -->
</div><!-- .l-page__mega -->
