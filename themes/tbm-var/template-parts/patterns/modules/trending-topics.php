<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="trending-topics // <?php echo esc_attr( $trending_topics_classes ?? '' ); ?>">
	<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>

	<div class="trending-topics__inner // <?php echo esc_attr( $trending_topics_inner_classes ?? '' ); ?>">
		<?php foreach ( $trending_topics ?? [] as $item ) { ?>
			<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/objects/o-topic.php', $item, true ); ?>
		<?php } ?>
	</div>
</section>
