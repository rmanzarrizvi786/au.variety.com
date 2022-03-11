<?php
// This is a generated file. Refer to the relevant Twig file for adjusting this markup.
?>
<div class="page // <?php echo esc_attr( $page_classes ?? '' ); ?>">
	<div class="page__inner <?php echo esc_attr( $page_inner_classes ?? '' ); ?>">

		<?php \PMC::render_template( CHILD_THEME_PATH . '/template-parts/patterns/modules/article-title.php', $article_title, true ); ?>

		<div class="page__content <?php echo esc_attr( $page_content_classes ?? '' ); ?>">

			<div class="lrv-a-grid lrv-a-cols3@tablet">
				<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-lazy-image.php', $c_lazy_image, true ); ?>
				<div class="lrv-a-span2@tablet">
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-title.php', $c_title, true ); ?>
					<?php \PMC::render_template( PMC_CORE_PATH . '/template-parts/patterns/components/c-tagline.php', $c_tagline, true ); ?>
				</div>
			</div>

			<p>To get set up with access, please send an email to <a href="mailto:premier@variety.com">premier@variety.com</a> or call 323-617-9555 with the following information:</p>

			<ul>
				<li>Your name</li>
				<li>Your email address (must be the same one you use to log in to Variety.com)</li>
				<li>Your Variety Premier account # (if you have it)</li>
			</ul>

			<p>Please note that you may also search Variety.com for all content published on the website dating back to 1999.</p>

			<hr/>

			<p>If you would like unlimited access to ALL of Variety’s print publications dating back to 1906, please consider purchasing a subscription to Variety Archives.</p>

			<p><a title="Follow link" href="http://www.varietyultimate.com/" rel="nofollow">Click here</a> or call either 1-800-552-3632 or +1 818-487-4561, or email <a href="mailto:varietyarchives@pubservice.com">varietyarchives@pubservice.com</a>
			for more information.</p>

			<p>Thank you.</p>

		</div>
	</div>
</div>
