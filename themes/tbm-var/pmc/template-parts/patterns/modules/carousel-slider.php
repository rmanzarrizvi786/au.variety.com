<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="carousel-slider //">
	<div class="lrv-a-wrapper lrv-u-padding-tb-1">
		<div class="js-Flickity js-Flickity--one-thirds js-Flickity--nav-top-right js-Flickity--hide-nav@mobile-max">
			<?php foreach ( $galleries ?? [] as $item ) { ?>
				<div class="js-Flickity-cell lrv-u-margin-r-150">
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/objects/o-card.php', $item, true ); ?>
				</div>
			<?php } ?>
		</div>
	</div>
</section>
