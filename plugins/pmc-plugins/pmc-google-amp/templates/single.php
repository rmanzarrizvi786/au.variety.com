<?php
use PMC\Google_Amp\Single_Post;
use PMC\Google_Amp\Optimera;

$side_menu_status   = Single_Post::get_instance()->get_side_menu_position();
$next_page_hide_css = ( \PMC\Google_Amp\Plugin::get_instance()->is_at_least_version( '2.0.4' ) ) ? 'next-page-hide' : '';

?>
<!doctype html>
<html amp>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
		<?php do_action( 'amp_post_template_head', $this ); ?>
		<style amp-custom>
			<?php $this->load_parts( array( 'style' ) ); ?>
			<?php do_action( 'amp_post_template_css', $this ); ?>
		</style>
	</head>
	<body class="<?php echo esc_attr( $this->get( 'body_class' ) ); ?>">
	<?php Optimera::get_instance()->render_start(); ?>
		<?php do_action( 'pmc_amp_content_before_header', $this ); ?>
		<nav class="amp-wp-title-bar" <?php echo esc_attr( $next_page_hide_css ); ?>>
			<?php if ( ! empty( $side_menu_status ) ) { ?>
				<button class="btn hamburger <?php echo esc_attr( $side_menu_status ); ?>" on='tap:amp_side_menu.toggle'>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			<?php } ?>
			<div class="amp-wp-site-name">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php $site_icon_url = $this->get( 'site_icon_url' ); ?>
					<?php if ( $site_icon_url ) : ?>
						<amp-img src="<?php echo esc_url( $site_icon_url ); ?>" width="75px" height="18px" class="amp-wp-site-icon"></amp-img>
					<?php endif; ?>
					<?php bloginfo( 'name' ); ?>
				</a>
			</div>
			<?php do_action( 'pmc_amp_content_nav_bottom', $this ); ?>
		</nav>
		<?php do_action( 'pmc_amp_content_after_header', 'amp-header', true ); ?>
		<div class="amp-wp-content">
			<?php do_action( 'pmc_amp_before_post_title' ); ?>
			<h1 class="amp-wp-title">
				<?php echo apply_filters( 'pmc_post_amp_title', wp_kses_data( $this->get( 'post_title' ) ) ); ?>
			</h1>
			<?php do_action( 'pmc_amp_after_post_title' ); ?>
			<ul class="amp-wp-meta">
				<?php $this->load_parts( apply_filters( 'amp_post_template_meta_parts', array(
					'meta-author',
					'meta-time',
					'meta-taxonomy',
				) ) ); ?>
			</ul>
			<?php do_action( 'amp_display_social_share', 'top' ); ?>
			<?php do_action( 'pmc_amp_before_post_content' ); ?>
			<div class="amp-fn-content-wrapper">
				<div class="amp-fn-content">
					<?php echo apply_filters( 'pmc_post_amp_content', $this->get( 'post_amp_content' ) ); // amphtml content; no kses ?>
				</div>
			</div>
			<?php do_action( 'amp_display_comments_link' ); ?>
			<?php do_action( 'amp_display_social_share', 'bottom' ); ?>
		</div>
		<?php do_action( 'pmc_amp_content_before_footer', 'amp-bottom', true ); ?>
		<?php do_action( 'amp_post_template_footer', $this ); ?>
	<?php Optimera::get_instance()->render_end(); ?>
	</body>
</html>
