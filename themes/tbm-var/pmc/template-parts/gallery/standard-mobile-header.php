<?php
$back_to_linked_post = pmc_gallery_back_to_linked_post();
?>
<header class="gallery__mobile-header">
	<a href="/" class="gallery__mobile-logo">
		<?php PMC::render_template(PMC_CORE_PATH . '/template-parts/svg/logo-pmc.php', [], true); ?>
	</a>
	<div class="gallery__slide-position">
		<span class="gallery__current-pic">1</span><span class="gallery__divider"> of </span><span class="gallery__total-pics">-</span>
	</div>

	<div class="gallery__mobile-utility-nav">
		<button class="gallery__viewthumbs" aria-label="thumbnails">
			<?php PMC::render_template(PMC_CORE_PATH . '/template-parts/svg/mobile-thumbnails-icon.php', [], true); ?>
		</button>

		<?php if (!empty($back_to_linked_post) && is_array($back_to_linked_post)) : ?>

			<?php if (!empty($back_to_linked_post['url']) && !empty($back_to_linked_post['text'])) : ?>

				<a href="<?php echo esc_url($back_to_linked_post['url']); ?>" title="<?php echo esc_attr($back_to_linked_post['text']); ?>">
					<button class="gallery__close" aria-label="close modal">
						<?php PMC::render_template(PMC_CORE_PATH . '/template-parts/svg/mobile-close-modal.php', [], true); ?>
					</button>
				</a>

			<?php endif; ?>

		<?php endif; ?>

	</div>
</header>
<div class="ad--horizontal">
	<?php // if (\PMC::is_mobile()) : 
	?>
	<?php pmc_adm_render_ads('header-leaderboard'); ?>
	<?php // endif; 
	?>
</div>