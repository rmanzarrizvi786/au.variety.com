<?php

/**
 * Header bar template part.
 *
 * @package AMP
 */

?>
<header id="top" class="amp-wp-header" next-page-hide>
	<div class="amp-wp-header-inner">
		<?php
		$logo_link = esc_url($this->get('home_url'));
		?>
		<a href="<?php echo $logo_link; ?>">
			<?php $site_icon_url = $this->get('site_icon_url'); ?>
			<?php if ($site_icon_url) : ?>
				<amp-img src="<?php echo esc_url($site_icon_url); ?>" width="32" height="32" class="amp-wp-site-icon"></amp-img>
			<?php endif; ?>
			<span class="amp-site-title">
				<?php echo esc_html(wptexturize($this->get('blog_name'))); ?>
			</span>
		</a>
	</div>
</header>