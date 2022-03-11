<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="lrv-a-wrapper">
	<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/read-on.php', $read_on, true ); ?>

	<div id="cx-paywall" class="u-max-width-618 lrv-u-margin-lr-auto"></div>

	<?php if ( ! empty( $view_full ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/view-full.php', $view_full, true ); ?>
	<?php } ?>

	<?php if ( ! empty( $view_full_extended ) ) { ?>
		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/view-full-extended.php', $view_full_extended, true ); ?>
	<?php } ?>
</div>
