<?php global $amp_post_id;
$amp_post_id = $this->get('post_id'); ?>
<!doctype html>
<html amp <?php echo AMP_HTML_Utils::build_attributes_string($this->get('html_tag_attributes')); ?>>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
	<?php do_action('amp_post_template_head', $this); ?>
	<style amp-custom>
		<?php $this->load_parts(array('style')); ?><?php do_action('amp_post_template_css', $this); ?>
	</style>

	<script async custom-element="amp-social-share" src="https://cdn.ampproject.org/v0/amp-social-share-0.1.js"></script>
	<script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>

	<script async custom-element="amp-apester-media" src="https://cdn.ampproject.org/v0/amp-apester-media-0.1.js"></script>
	<meta name="monetization" content="$ilp.uphold.com/68Q7DryfNX4d">
</head>

<body class="<?php echo esc_attr($this->get('body_class')); ?>">
	<?php
	if (isset($_GET['screenshot'])) {
		$pagepath = 'screenshot';
	} else if (isset($_GET['dfp_key'])) {
		$pagepath = $_GET['dfp_key'];
	} else if (is_home() || is_front_page()) {
		$pagepath = 'homepage';
	} else {
		$pagepath_e = explode('/', $_SERVER['REQUEST_URI']);
		$pagepath = substr($pagepath_e[1], 0, 40);
	}
	?>

	<?php $this->load_parts(array('header-bar')); ?>
	<article class="amp-wp-article" style="margin-top: 35px; margin-bottom: 15px;">
		<div class="amp-ad" style="text-align: center; margin: auto;">
			<?php echo pmc_adm_render_ads('header'); ?>
		</div>

		<?php
		/* $video = rollingstone_get_video_source(false);
		if (!empty($video)) {
			\PMC::render_template(
				CHILD_THEME_PATH . '/template-parts/video/picture-no-caption.php',
				compact('video'),
				true
			);
		} else {
		} */
		$this->load_parts(array('featured-image'));
		?>

		<header class="amp-wp-article-header">
			<h1 class="amp-wp-title" id="story_title"><?php echo wp_kses_data($this->get('post_title')); ?></h1>
			<?php $this->load_parts(apply_filters('amp_post_article_header_meta', array('meta-author', 'meta-time'))); ?>
		</header>

		<div class="amp-social-share-bar" style="text-align: center;" next-page-hide>
			<amp-social-share class="rounded" type="email" aria-label="Share by email" width="40" height="40" data-param-url="<?php echo get_permalink(); ?>"></amp-social-share>
			<amp-social-share class="rounded" type="facebook" aria-label="Share on Facebook" data-param-app_id="663250544407899" width="40" height="40" data-param-url="<?php echo get_permalink(); ?>"></amp-social-share>
			<amp-social-share class="rounded" type="linkedin" aria-label="Share on LinkedIn" width="40" height="40" data-param-url="<?php echo get_permalink(); ?>"></amp-social-share>
			<amp-social-share class="rounded" type="twitter" aria-label="Share on Twitter" width="40" height="40" data-param-url="<?php echo get_permalink(); ?>"></amp-social-share>
			<amp-social-share class="rounded" type="whatsapp" aria-label="Share on WhatsApp" width="40" height="40" data-param-url="<?php echo get_permalink(); ?>"></amp-social-share>
		</div>

		<div class="amp-wp-article-content">
			<?php
			$content = $this->get('post_amp_content');

			$content = str_replace('frameborder="0"', '', $content);
			$content = str_replace('frameborder', '', $content);

			//                $content = preg_replace('/<amp-img[^>]+>/i', '', $content);

			$content = explode("</p>", $content);
			for ($i = 0; $i < count($content); $i++) :
				if (count($content) > 2 && $i == 2) :
			?>
					<div class="amp-ad" style="text-align: center; margin: auto;">
						<?php echo pmc_adm_render_ads('mrec_1'); ?>
					</div>
			<?php
				endif;
				echo $content[$i] . "</p>";
			endfor;
			?>
		</div>

		<div class="amp-ad" style="text-align: center; margin: auto;">
			<?php echo pmc_adm_render_ads('mrec_2'); ?>
		</div>

		<div class="amp-ad" style="text-align: center; margin: auto;">
			<?php echo pmc_adm_render_ads('sticky_footer'); ?>
		</div>

		<footer class="amp-wp-article-footer">
			<?php $this->load_parts(apply_filters('amp_post_article_footer_meta', array('meta-taxonomy'))); ?>
		</footer>

		<?php
		/**
		 * Hidden because infinite scroll has been added
		 * 12 Oct, 2021
		 */
		if (0) :
		?>
			<div class="related-stories-wrap">
				<h2 class="title">You may also like</h2>
				<?php
				$post_id = get_the_ID();
				// Related Posts from tags
				$tags = wp_get_post_tags($post_id);
				$arg_tags = array();
				foreach ($tags as $tag) {
					array_push($arg_tags, $tag->term_id);
				}
				$args = array(
					'post_status' => 'publish',
					'tag__in' => $arg_tags,
					'post__not_in' => array($post_id),
					'posts_per_page' => 2,
					'orderby' => 'rand',
					'date_query' => array(
						'column' => 'post_date',
						'after' => '-60 days'
					)
				);
				$related_posts_query = new WP_Query($args);
				if ($related_posts_query->have_posts()) :
					while ($related_posts_query->have_posts()) :
						$related_posts_query->the_post();
				?>
						<div class="related-story">
							<div class="post-thumbnail">
								<?php if ('' !== get_the_post_thumbnail()) : ?>
									<a href="<?php echo get_permalink() . 'amp'; ?>">
										<?php // the_post_thumbnail( 'thumbnail' ); 
										?>
										<?php
										$thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id(), 'thumbnail');
										?>
										<figure class="amp-wp-article-featured-image wp-caption">
											<amp-img src="<?php echo $thumbnail[0]; ?>" class="attachment-large size-large wp-post-image amp-wp-enforced-sizes" width="75" height="75"></amp-img>
										</figure>
									</a>
								<?php endif; ?>
							</div><!-- .post-thumbnail -->
							<div class="post-content">
								<h2><a href="<?php echo get_permalink() . 'amp'; ?>"><?php the_title(); ?></a></h2>
								<p class="excerpt"><?php echo get_the_excerpt(); ?></p>
							</div>
						</div>
					<?php
					endwhile;
					wp_reset_query();
				endif;

				// Related Posts from Categories
				$cats = wp_get_post_categories($post_id);
				$arg_tags = array();
				foreach ($tags as $tag) {
					array_push($arg_tags, $tag->term_id);
				}
				$args = array(
					'post_status' => 'publish',
					'category__in' => $cats,
					'post__not_in' => array($post_id),
					'posts_per_page' => 2,
					'orderby' => 'rand',
					'date_query' => array(
						'column' => 'post_date',
						'after' => '-30 days'
					)
				);
				$related_posts_query = new WP_Query($args);
				if ($related_posts_query->have_posts()) :
					while ($related_posts_query->have_posts()) :
						$related_posts_query->the_post();
					?>
						<div class="related-story">
							<div class="post-thumbnail">
								<?php if ('' !== get_the_post_thumbnail()) : ?>
									<a href="<?php echo get_permalink() . 'amp'; ?>">
										<?php // the_post_thumbnail( 'thumbnail' ); 
										?>
										<?php
										$thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id(), 'thumbnail');
										?>
										<figure class="amp-wp-article-featured-image wp-caption">
											<amp-img src="<?php echo $thumbnail[0]; ?>" class="attachment-large size-large wp-post-image amp-wp-enforced-sizes" width="75" height="75"></amp-img>
										</figure>
									</a>
								<?php endif; ?>
							</div><!-- .post-thumbnail -->
							<div class="post-content">
								<h2><a href="<?php echo get_permalink() . 'amp'; ?>"><?php the_title(); ?></a></h2>
								<p class="excerpt"><?php echo get_the_excerpt(); ?></p>
							</div>
						</div>
				<?php
					endwhile;
					wp_reset_query();
				endif;
				?>
				<div class="clear">&nbsp;</div>

				<?php
				//        remove_all_filters( 'posts_where', 'filter_where2' );
				?>
			</div>
			<div class="clear"></div>
		<?php endif; ?>

	</article>

	<?php $this->load_parts(array('footer')); ?>

	<!-- <div class="share-buttons-bottom">
		<amp-social-share type="email" width="40" height="40"></amp-social-share>
		<amp-social-share type="facebook" data-param-app_id="812299355633906" width="40" height="40"></amp-social-share>
		<amp-social-share type="gplus" width="40" height="40"></amp-social-share>
		<amp-social-share type="linkedin" width="40" height="40"></amp-social-share>
		<amp-social-share type="twitter" width="40" height="40"></amp-social-share>
		<amp-social-share type="whatsapp" width="40" height="40"></amp-social-share>
	</div> -->

	<?php do_action('amp_post_template_footer', $this); ?>

	<!-- Start Alexa AMP Certify Javascript -->
	<amp-analytics type="alexametrics">
		<script type="application/json">
			{
				"vars": {
					"atrk_acct": "O3NOq1WyR620WR",
					"domain": "thebrag.com"
				}
			}
		</script>
	</amp-analytics>
	<!-- End Alexa AMP Certify Javascript -->

	<?php $prevPost = get_previous_post();
	if ($prevPost) :
	?>
		<amp-next-page>
			<script type="application/json">
				[{
					"image": "<?php echo get_the_post_thumbnail_url($prevPost->ID, 'thumbnail'); ?>",
					"title": "<?php echo get_the_title($prevPost->ID); ?>",
					"url": "<?php echo get_the_permalink($prevPost->ID) ?>?amp"
				}]
			</script>
		</amp-next-page>
	<?php endif; ?>

</body>

</html>