<?php
/**
 * The template for displaying the footer.
 *
 * @package pmc-variety
 */

?>

<?php if ( ! \PMC\Gallery\View::is_standard_gallery() ) : ?>
	</div>
	</main>
<?php endif; ?>
<?php

get_template_part( 'template-parts/footer/simplified' );

?>

<?php wp_footer(); ?>

<?php do_action( 'pmc-tags-footer' ); // @codingStandardsIgnoreLine ?>

<?php do_action( 'pmc-tags-bottom' ); // @codingStandardsIgnoreLine ?>
<?php if ( ! \PMC\Gallery\View::is_standard_gallery() ) : ?>
	</div><!-- /#main-wrapper -->
<?php endif; ?>
</body>
</html>
