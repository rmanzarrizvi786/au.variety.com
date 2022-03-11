<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<section class="author-blurb // a-author-blurb-grid lrv-u-margin-b-2">
	<div class="author-blurb-grid__details">
		<?php if (!empty($c_heading)) { ?>
			<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-heading.php', $c_heading, true); ?>
		<?php } ?>
		<ul class="author-blurb-social-list // <?php echo esc_attr($author_blurb_social_list__classes ?? ''); ?>">
			<?php if (!empty($c_icon__twitter)) { ?>
				<li class="social-list__item // <?php echo esc_attr($author_blurb_social_list_item__classes ?? ''); ?>">
					<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $c_icon__twitter, true); ?>
					<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link__twitter, true); ?>
				</li>
			<?php } ?>
			<?php if (!empty($c_link__email)) { ?>
				<li class="social-list__item // <?php echo esc_attr($author_blurb_social_list_item__classes ?? ''); ?>">
					<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link__email, true); ?>
				</li>
			<?php } ?>
		</ul>
	</div>
	<div class="a-author-blurb-grid__text">
		<?php if (!empty($c_tagline)) { ?>
			<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true); ?>
		<?php } ?>
	</div>
</section>