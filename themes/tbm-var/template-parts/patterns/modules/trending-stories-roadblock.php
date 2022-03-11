<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="trending-stories-roadblock // u-border-a-125@desktop-xl u-border-lr-16@tablet u-border-tb-16 u-border-color-blue-light u-margin-lr-n10@mobile-max u-padding-lr-2@tablet lrv-u-padding-a-1">
	<div class="lrv-u-padding-b-1">
		<?php if (!empty($c_heading)) { ?>
			<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true); ?>
		<?php } ?>
	</div>
	<div class="js-Flickity js-Flickity--fifths js-Flickity--isContained js-Flickity--nav-top-right js-Flickity--bordered-buttons js-Flickity--isFreeScroll a-counter a-counter-config--brand-bottom-left lrv-u-padding-b-1">
		<?php foreach ($stories ?? [] as $item) { ?>
			<div class="js-Flickity-cell lrv-u-margin-lr-050 u-margin-lr-1@tablet">
				<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/objects/o-card.php', $item, true); ?>
			</div>
		<?php } ?>
	</div>
</section>