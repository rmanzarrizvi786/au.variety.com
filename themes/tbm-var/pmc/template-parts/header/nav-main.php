<?php

/**
 * Header Main Nav Template
 *
 * @package pmc-core-v2;
 */
?>

<?php if (is_front_page()) : ?>
	<h1 class="l-header__logo">
		<?php
		printf(
			'<a href="%1$s" title="%2$s" class="c-logo c-logo--flexible"><span class="screen-reader-text">%3$s</span></a>',
			esc_url(home_url('/')),
			esc_attr(get_bloginfo('name')),
			esc_html(get_bloginfo('name'))
		);
		?>
	</h1>
<?php else : ?>
	<div class="l-header__logo">
		<?php
		printf(
			'<a href="%1$s" title="%2$s" class="c-logo c-logo--flexible"><span class="screen-reader-text">%3$s</span></a>',
			esc_url(home_url('/')),
			esc_attr(get_bloginfo('name')),
			esc_html(get_bloginfo('name'))
		);
		?>
	</div>
<?php endif; ?>

<div class="l-header__hamburger">

	<button class="c-hamburger" data-toggle="mega-menu">
		<span class="screen-reader-text"><?php esc_html_e('Menu', 'pmc-core'); ?></span>
	</button>

</div><!-- .l-header__hamburger -->
<div class="l-header__nav-area">
	<?php
	wp_nav_menu(array(
		'menu_class'      => 'c-nav c-nav--main',
		'theme_location'  => 'pmc_core_header',
		'container'       => 'nav',
		'container_class' => 'l-header__nav',
		'items_wrap'      => '<ul class="%2$s">%3$s</ul>',
		'walker'          => new \PMC\Core\Inc\Menus\Primary_Menu_Walker(),
		'depth'           => 1,
	));

	wp_nav_menu(array(
		'container'       => 'div',
		'container_class' => 'uber-nav',
		'items_wrap'      => '%3$s',
		'theme_location'  => 'pmc_core_header',
		'walker'          => new \PMC\Core\Inc\Menus\Uber_Menu_Walker(),
	));

	?>
</div>
<?php
//EOF
