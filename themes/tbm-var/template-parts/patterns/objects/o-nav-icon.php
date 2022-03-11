<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<nav class="o-nav-icon <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_nav_classes ?? '' ); ?>">

	<?php if ( ! empty( $o_nav_title_text ) ) { ?>
		<h4 id="<?php echo esc_attr( $o_nav_title_id_attr ?? '' ); ?>" class="o-nav__title <?php echo esc_attr( $o_nav_title_classes ?? '' ); ?>"><?php echo esc_html( $o_nav_title_text ?? '' ); ?></h4>
	<?php } ?>

	<ul class="o-nav__list <?php echo esc_attr( $o_nav_list_classes ?? '' ); ?>" aria-labelledby="<?php echo esc_attr( $o_nav_title_id_attr ?? '' ); ?>">
		<?php foreach ( $o_nav_list_items ?? [] as $item ) { ?>
			<li class="o-nav__list-item <?php echo esc_attr( $o_nav_list_item_classes ?? '' ); ?>">
				<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-icon-button.php', $item, true ); ?>
			</li>
		<?php } ?>
	</ul>
</nav>
