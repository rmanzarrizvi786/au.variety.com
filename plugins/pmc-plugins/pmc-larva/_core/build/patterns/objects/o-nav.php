<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<?php if ( ! empty( $o_nav_screen_reader_text ) ) { ?>
	<h2 id="<?php echo esc_attr( $o_nav_screen_reader_id_attr ?? '' ); ?>" class="lrv-a-screen-reader-only">
		<?php echo esc_html( $o_nav_screen_reader_text ?? '' ); ?>
	</h2>
<?php } ?>
<nav class="o-nav <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_nav_classes ?? '' ); ?>" data-dropdown="<?php echo esc_attr( $o_nav_data_attributes ?? '' ); ?>"
	<?php if ( ! empty( $o_nav_title_id_attr ) ) { ?>
		aria-label="<?php echo esc_attr( $o_nav_title_id_attr ?? '' ); ?>"
	<?php } ?>
	<?php if ( ! empty( $o_nav_aria_labelledby_attr ) ) { ?>
		aria-labelledby="<?php echo esc_attr( $o_nav_aria_labelledby_attr ?? '' ); ?>"
	<?php } ?>
>

	<?php if ( ! empty( $o_nav_title_text ) ) { ?>
		<?php if ( ! empty( $o_nav_tag_text ) ) { ?>
			<<?php echo esc_html( $o_nav_tag_text ?? '' ); ?> id="<?php echo esc_attr( $o_nav_title_id_attr ?? '' ); ?>" class="o-nav__title <?php echo esc_attr( $o_nav_title_classes ?? '' ); ?>"><?php echo esc_html( $o_nav_title_text ?? '' ); ?></<?php echo esc_html( $o_nav_tag_text ?? '' ); ?>>
		<?php } else { ?>
			<h4 id="<?php echo esc_attr( $o_nav_title_id_attr ?? '' ); ?>" class="o-nav__title <?php echo esc_attr( $o_nav_title_classes ?? '' ); ?>"><?php echo esc_html( $o_nav_title_text ?? '' ); ?></h4>
		<?php } ?>
	<?php } ?>

	<ul class="o-nav__list <?php echo esc_attr( $o_nav_list_classes ?? '' ); ?>"
		<?php if ( ! empty( $o_nav_list_labelledby_attr ) ) { ?>
			aria-labelledby="<?php echo esc_attr( $o_nav_list_labelledby_attr ?? '' ); ?>"
		<?php } ?>
	>
		<?php foreach ( $o_nav_list_items ?? [] as $item ) { ?>
			<li class="o-nav__list-item <?php echo esc_attr( $o_nav_list_item_classes ?? '' ); ?>">
				<?php \PMC\Larva\Pattern::get_instance()->render_pattern_template( 'components/c-link', $item, true ); ?>
			</li>
		<?php } ?>
	</ul>
</nav>
