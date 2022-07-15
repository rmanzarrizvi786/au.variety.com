<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="author // <?php echo esc_attr($author_classes ?? ''); ?>" data-author="<?php echo get_field('author') ? get_field('author') : get_the_author_meta('display_name', get_post_field('post_author', get_the_ID())); ?>">
	<div class="<?php echo esc_attr($author_inner_classes ?? ''); ?>">
		<div class="<?php echo esc_attr($author_wrapper_classes ?? ''); ?>">
			<div class="<?php echo esc_attr($author_content_classes ?? ''); ?>">
				<?php if (!empty($is_byline_only)) { ?>
					<div class="<?php echo esc_attr($author_byline_classes ?? ''); ?>">
						By <?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true); ?>
					</div>
				<?php } else { ?>
					<button class="lrv-a-unstyle-button lrv-u-flex-grow-1 lrv-u-align-items-center lrv-u-width-100p" data-collapsible="collapsed">
						<div class="author-toggle lrv-u-flex <?php echo esc_attr($author_toggle_classes ?? ''); ?>">
							<p class="lrv-u-margin-tb-00 <?php echo esc_attr($author_byline_classes ?? ''); ?>">
								By <?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-link.php', $c_link, true); ?>
							</p>

							<div class="js-author__toggle // lrv-u-flex lrv-u-align-items-center lrv-u-cursor-pointer" data-collapsible-toggle="always-show">
								<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-icon.php', $c_icon, true); ?>
							</div>
						</div>

						<div class="lrv-u-width-100p lrv-u-margin-t-1" data-collapsible-panel>
							<?php \PMC::render_template(CHILD_THEME_PATH . '/template-parts/patterns/modules/author-details.php', $author_details, true); ?>
						</div>
					</button>
				<?php } ?>
			</div>
		</div>
	</div>

	<?php if (!empty($c_tagline_optional)) { ?>
		<?php \PMC::render_template(PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline_optional, true); ?>
	<?php } ?>

</div>