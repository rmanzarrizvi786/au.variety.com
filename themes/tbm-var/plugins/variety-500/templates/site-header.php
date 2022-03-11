<?php
/**
 * Site Header Template
 *
 * Template for the Header and main Nav Menu.
 *
 * @package pmc-variety-2017
 * @since 1.0
 */

$home_page_url   = Variety\Plugins\Variety_500\Templates::get_home_url();
$search_page_url = Variety\Plugins\Variety_500\Templates::get_search_url();
?>
<header class="l-site-header" data-trigger="header-manager">
	<div class="l-site-header__container">
		<div class="l-site-header__mobile-toggle">

			<button class="c-site-header__toggle c-site-header__toggle--mobile" data-header-trigger="mobile-nav">
					<span class="screen-reader-text">
						<?php esc_html_e( 'Menu', 'pmc-variety' ); ?>
					</span>
				<span class="c-site-header__hamburger">
						<span class="c-site-header__hamburger-bar"></span>
						<span class="c-site-header__hamburger-bar"></span>
						<span class="c-site-header__hamburger-bar"></span>
					</span>
			</button><!-- .c-site-header__toggle.c-site-header__toggle--mobile -->

		</div><!-- .l-site-header__mobile-toggle -->
		<div class="l-site-header__logo">

			<a class="c-site-header__logo" href="<?php echo esc_url( $home_page_url ); ?>">
				<span class="screen-reader-text">
					<?php esc_html_e( 'Variety 500', 'pmc-variety' ); ?>
				</span>
			</a>

		</div><!-- .l-site-header__logo -->
		<div class="l-site-header__sponsor">

			<?php
			$sponsor_logo = get_option( 'variety_500_sponsor_logo' );
			if ( ! empty( $sponsor_logo ) ) :
				$presented_by = get_option( 'variety_500_presented_by' );
				$presented_by = ! empty( $presented_by ) ? $presented_by : __( 'presented by', 'pmc-variety' );
				$sponsor_link = get_option( 'variety_500_sponsor_link' );
				?>
				<div class="c-site-header__sponsor">
					<?php if ( ! empty( $sponsor_link ) ) : ?>
						<a href="<?php echo esc_url( $sponsor_link ); ?>" target="_blank">
					<?php endif; ?>
						<?php echo esc_html( $presented_by ); ?>
						<span class="c-site-header__sponsor-logo">
							<img src="<?php echo esc_url( $sponsor_logo ); ?>" alt="<?php esc_attr_e( 'Variety 500 Sponsor', 'pmc-variety' ); ?>"  />
						</span>
					<?php if ( ! empty( $sponsor_link ) ) : ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div><!-- .l-site-header__sponsor -->
		<nav class="l-site-header__nav">

			<ul class="c-site-header__menu">
				<?php

				?>
				<li class="c-site-header__nav-item">
					<a class="c-site-header__nav-link" href="<?php echo esc_url( $home_page_url ); ?>#about"><?php esc_html_e( 'About', 'pmc-variety' ); ?></a>
				</li>
				<li class="c-site-header__nav-item">
					<a class="c-site-header__nav-link" href="<?php echo esc_url( $home_page_url ); ?>#spotlight"><?php esc_html_e( 'Spotlight', 'pmc-variety' ); ?></a>
				</li>
				<li class="c-site-header__nav-item">
					<a class="c-site-header__nav-link" href="<?php echo esc_url( $home_page_url ); ?>#by-the-numbers"><?php esc_html_e( 'By the numbers', 'pmc-variety' ); ?></a>
				</li>
				<li class="c-site-header__nav-item">
					<a class="c-site-header__nav-link" href="<?php echo esc_url( $search_page_url ); ?>"><?php esc_html_e( 'Explore the 500', 'pmc-variety' ); ?></a>
				</li>
			</ul>

		</nav><!-- .l-site-header__nav -->
		<div class="l-site-header__social">

			<ul class="c-site-header__menu">
				<li class="c-site-header__nav-item">
					<a href="https://www.facebook.com/Variety" class="c-site-header__nav-link c-site-header__nav-link--social" data-track="facebook" rel="nofollow" target="_blank">
							<span class="c-social-icon c-social-icon--muted c-social-icon__facebook">
								<span class="screen-reader-text"><?php esc_html_e( 'Facebook', 'pmc-variety' ); ?></span>
							</span>
					</a>
				</li>
				<li class="c-site-header__nav-item">
					<a href="https://twitter.com/variety" class="c-site-header__nav-link c-site-header__nav-link--social" rel="nofollow" target="_blank">
							<span class="c-social-icon c-social-icon--muted c-social-icon__twitter">
								<span class="screen-reader-text"><?php esc_html_e( 'Twitter', 'pmc-variety' ); ?></span>
							</span>
					</a>
				</li>
				<li class="c-site-header__nav-item">
					<a href="https://www.linkedin.com/company/variety" class="c-site-header__nav-link c-site-header__nav-link--social" rel="nofollow" target="_blank">
							<span class="c-social-icon c-social-icon--muted c-social-icon__linkedin">
								<span class="screen-reader-text"><?php esc_html_e( 'LinkedIn', 'pmc-variety' ); ?></span>
							</span>
					</a>
				</li>
			</ul>

		</div><!-- .l-site-header__social -->
		<div class="l-site-header__back">

			<a class="c-site-header__back" href="/">
				<?php esc_html_e( 'Back to Variety', 'pmc-variety' ); ?>
			</a>

		</div><!-- .l-site-header__back -->
		<div class="l-site-header__search-toggle">

			<button class="c-site-header__toggle c-site-header__toggle--search-form" data-header-trigger="search">
				<span class="screen-reader-text"><?php esc_html_e( 'Search', 'pmc-variety' ); ?></span>
			</button><!-- .c-site-header__toggle.c-site-header__toggle--search-form -->

		</div><!-- .l-site-header__search -->
		<div class="l-site-header__search" data-header-module="search">

			<div class="c-site-header__search">
				<form action="<?php echo esc_url( Variety\Plugins\Variety_500\Templates::get_search_url( array( 'q' => 'v500searchterm' ) ) ); ?>" class="c-site-header__search-form">
					<input type="text" class="c-site-header__search-field" name="q" id="search-form-q" placeholder="<?php esc_html_e( 'Search People and Companies', 'pmc-variety' ); ?>" />
				</form>
			</div>

		</div><!-- .l-site-header__search -->
	</div><!-- .l-site-header__container -->
</header><!-- .l-site-header -->
