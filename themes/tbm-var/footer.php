<?php

/**
 * The template for displaying the footer.
 *
 * @package pmc-variety
 */

?>

<?php if (!\PMC\Gallery\View::is_standard_gallery() && !is_page_template('page-editorial-hub.php') && !is_tag('documentaries-to-watch')) : ?>

	<?php // PMC::render_template( CHILD_THEME_PATH . '/template-parts/module/newswire.php', [], true ); 
	?>

	</main>
<?php endif; ?>

<?php

// \pmc_adm_render_ads('mobile-footer');

get_template_part('template-parts/footer/main');

?>

<?php wp_footer(); ?>

<?php do_action('pmc-tags-footer'); ?>

<?php do_action('pmc-tags-bottom'); ?>

<?php if (!\PMC\Gallery\View::is_standard_gallery()) : ?>
	</div><!-- /#main-wrapper -->
<?php endif; ?>

<!-- 22071836792/outpfpage/outpfpage -->
<div data-fuse="22782702645"></div>

<div id="body-overlay" style="display: none;"></div>
</body>

</html>