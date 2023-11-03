<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<header class="sub-header // <?php echo esc_attr( $sub_header_classes ?? '' ); ?>">
	<div class="lrv-a-wrapper">
		<div class="u-background-color-white@mobile-max lrv-u-flex lrv-u-flex-direction-column lrv-u-align-items-center lrv-u-padding-tb-1 u-border-t-6@mobile-max u-border-color-brand-primary">
			<?php if ( ! empty( $c_heading ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true ); ?>
			<?php } ?>

			<?php if ( ! empty( $o_nav ) ) { ?>
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-nav.php', $o_nav, true ); ?>
			<?php } ?>
		</div>
	</div>
	<?php if( $c_heading['c_heading_text'] ) : ?>
	<a href="https://www.stan.com.au/?utm_source=the-brag-media&utm_medium=sponsorship&utm_campaign=variety-tv-btyb" style="height: 110px; background-color: #0172fc; margin-bottom: 21px; display: block;">
		<img src="https://images-r2.thebrag.com/var/uploads/bb_stan.jpg" style="height: 6.875rem" alt="">
	</a>
	<?php endif; ?>
</header>
