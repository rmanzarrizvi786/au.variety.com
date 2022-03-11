<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<nav class="o-nav-breadcrumblist <?php echo esc_attr( $modifier_class ?? '' ); ?> <?php echo esc_attr( $o_nav_classes ?? '' ); ?>">

	<?php if ( ! empty( $o_nav_title_text ) ) { ?>
		<h4 id="<?php echo esc_attr( $o_nav_title_id_attr ?? '' ); ?>" class="o-nav-breadcrumblist__title <?php echo esc_attr( $o_nav_title_classes ?? '' ); ?>"><?php echo esc_html( $o_nav_title_text ?? '' ); ?></h4>
	<?php } ?>

	<ol class="o-nav-breadcrumblist__list <?php echo esc_attr( $o_nav_list_classes ?? '' ); ?>"
		<?php if ( ! empty( $o_structured_data ) ) { ?>
			aria-labelledby="<?php echo esc_attr( $o_nav_title_id_attr ?? '' ); ?>" itemscope itemtype="http://schema.org/BreadcrumbList"
		<?php } ?>>
		<?php foreach ( $o_nav_list_items ?? [] as $item ) { ?>
			<li class="o-nav-breadcrumblist__list-item <?php echo esc_attr( $o_nav_list_item_classes ?? '' ); ?>"
			<?php if ( ! empty( $o_structured_data ) ) { ?>
				itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"
			<?php } ?>>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $item, true ); ?>
			</li>
		<?php } ?>
	</ol>
</nav>
