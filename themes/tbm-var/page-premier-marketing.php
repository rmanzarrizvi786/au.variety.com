<?php
/**
 * Template Name: Premier Marketing Page
 *
 * CDWE-580 -- Copied from pmc-variety-2014 theme
 *
 * @package pmc-variety-2017
 *
 * @since   2017-08-21
 */

$template_name = 'premier-marketing-page';
?>
<!DOCTYPE html>

<!--[if lt IE 7]>
<html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if (IE 7)&!(IEMobile)]>
<html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if (IE 8)&!(IEMobile)]>
<html <?php language_attributes(); ?> class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!-->
<html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

	<title><?php bloginfo( 'name' ); ?></title>

	<?php wp_head(); ?>
	<?php do_action( 'pmc_tags_head' ); // phpcs:ignore ?>
</head>

<body <?php body_class(); ?>>
<?php do_action( 'pmc-tags-top' ); // phpcs:ignore ?>
<div class="login-signin-topper">
	<a class="back-to-variety-link" href="<?php echo esc_url( '/' ); ?>">
		<i class="fa fa-arrow-left"></i><?php esc_html_e( 'Back to Variety', 'pmc-variety' ); ?>
	</a>
</div>
<div id="site-wrapper">

	<?php
	$location = get_query_var( 'location', 'us' );

	switch ( $location ) {
		case 'canada':
			PMC::render_template( 'template-parts/module/premier/marketing-page-canada.php', [], true, [] );
			break;
		case 'international':
			PMC::render_template( 'template-parts/module/premier/marketing-page-international.php', [], true, [] );
			break;
		default:
			PMC::render_template( 'template-parts/module/premier/marketing-page-us.php', [], true, [] );
	}
	?>

	<section class="content-group" id="exclusive-content">

		<header class="content-group-header bottom-arrow">
			<h1>Exclusive Content</h1>
		</header>

		<section class="content" id="subscriptions">
			<div class="column-group">
				<div class="column left third centered-type">
					<img
							src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/cover.jpg' ); ?>"
							alt="Variety Covers"/>
				</div>
				<div class="column right two-thirds centered-type">
					<img
							src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/Variety-Logo.png' ); ?>"
							alt="Variety Logo" class="secondary-content"/><br/><br/>
					<h3><?php esc_html_e( 'The Industry News Leader For Over 100 Years', 'pmc-variety' ); ?></h3>
					<hr/>
					<h4>
						<?php
						esc_html_e(
							'Variety Magazine is and has always been the most important and trusted provider of industry news
										and information to the entertainment industry. And in this era of digital revolution in the
										media business, Variety is leading the conversation about how the industry can optimize the
										convergence of technology and entertainment. Through a combination of news stories, provocative
										insights, feature articles, and exclusive analyses, the editors of Variety inform the leaders in
										the film, television, music, and digital media industries of everything they need to know to
										shape their business decisions, both now and in the future. Trends are analyzed, the big
										questions are posited and explored, projects are forecasted, deals are assessed, and movers and
										shakers are interviewed in 48 beautifully designed issues each year.',
							'pmc-variety'
						);
						?>
					</h4>
				</div>
				<div class="clear"></div>
				<br/>
			</div>
		</section>
	</section><!-- Content Group -->

	<!-- Content Group -->
	<section class="content-group" id="industry-tools-data">

		<header class="content-group-header bottom-arrow">
			<h1><?php esc_html_e( 'Industry Tools &amp; Data', 'pmc-variety' ); ?></h1>
		</header>

		<section class="content" id="vscore-top-250">
			<div class="column-group">
				<div class="column left half secondary-content">
					<div class="grid actors-grid">
						<div class="grid-cell">
							<img
									src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/1_VScore_Depp.jpg' ); ?>"
									alt="Jhonny Depp"/>
						</div>
						<div class="grid-cell">
							<img
									src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/1_VScore_Jolie.jpg' ); ?>"
									alt="Angelina Jolie"/>
						</div>
						<div class="grid-cell last">
							<img
									src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/3_VScore_Pitt.jpg' ); ?>"
									alt="Brad Pitt"/>
						</div>
						<div class="grid-cell">
							<img
									src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/2_VScore_Lawrence.jpg' ); ?>"
									alt="Jennifer Lawrence"/>
						</div>
						<div class="grid-cell">
							<img
									src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/2_VScore_Smith.jpg' ); ?>"
									alt="Will Smith"/>
						</div>
						<div class="grid-cell last">
							<img
									src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/4_VScore_Streep.jpg' ); ?>"
									alt="Merril Streep"/>
						</div>
					</div>
				</div>
				<div class="column left half centered-type">
					<div class="column centered third">
						<h2><?php esc_html_e( 'Vscore Top 250', 'pmc-variety' ); ?></h2>
						<h3>
							<?php esc_html_e( 'Identify the true value', 'pmc-variety' ); ?>
							<br/>
							<?php esc_html_e( 'an actor brings to your', 'pmc-variety' ); ?>
							<br/>
							<?php esc_html_e( 'next project', 'pmc-variety' ); ?>
						</h3>
						<hr/>
						<h4>
							<?php
							esc_html_e(
								'Vscore is the industry’s most accurate familiarity and appeal metric quantifying the value
										of more than 17,000 working actors based on success across television, film, social media,
										awards and upcoming projects. Vscore Top 250 allows users to view, sort and filter a list of
										the top 250 actors in the Vscore database.',
								'pmc-variety'
							);
							?>
						</h4>
					</div>
					<div id="vscore-top-250-characteristics">
						<h4><strong>
								<?php
								esc_html_e(
									'The Only Familiarity and Appeal Metric that Includes',
									'pmc-variety'
								);
								?>
							</strong></h4>
						<div class="grid vscore-top-250">
							<div class="grid-cell" id="social-listening">
								<div class="icon"></div>
								<span>Social<br/>Listening</span>
							</div>
							<div class="grid-cell" id="box-office">
								<div class="icon"></div>
								<span>Box<br/>Office</span>
							</div>
							<div class="grid-cell" id="tv-performance">
								<div class="icon"></div>
								<span>TV<br/>Performance</span>
							</div>
							<div class="grid-cell" id="awards">
								<div class="icon"></div>
								<span>Awards</span>
							</div>
							<div class="grid-cell" id="upcoming-projects">
								<div class="icon"></div>
								<span>Upcoming<br/>Projects</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="column-group">
				<div class="column left third border-right padded right-type secondary-content">
					<p>Premier subscribers can easily sort and filter the Vscore Top 250 list across the following data
						elements:</p>
					<ul>
						<li>Name</li>
						<li>Age</li>
						<li>Gender</li>
						<li>Ethnicity</li>
						<li>Country of Origin</li>
						<li>Overall Vscore</li>
						<li>TV score</li>
						<li>Film score</li>
						<li>Social score</li>
					</ul>
				</div>
				<div class="column left two-thirds">
					<img
							src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/mackbook-air-mock-up_vscore.png' ); ?>"
							alt="Variety Vscore 250"/>
				</div>
				<div class="clear"></div>
				<br/>
			</div>
		</section>
		<section class="content alternate-background" id="production-charts">
			<div class="column-group">
				<div class="column left third centered-type">
					<h2>Production Charts</h2>
					<h3>The best way to stay on<br/>top of opportunities across TV<br/>and film productions</h3>
					<hr/>
					<h4>Production Charts list every pre-production and production commitment across TV and film for all
						projects with expected distribution in the US.</h4>
					<img
							src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/The-Wolf-of-Wall-Street-2013-TWOWS-01535Rv2.jpg' ); ?>"
							alt="Wolf of Wall Street" class="alignleft secondary-content"/>
					<img
							src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/aaron-vince-729-620x349.jpg' ); ?>"
							alt="Breaking Bad" class="alignleft secondary-content"/>
				</div>
				<div class="column right two-thirds mobile-rounded">
					<img
							src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/Divergent-2014-CBD-12513.jpg' ); ?>"
							alt="Divergent"/>
				</div>
			</div>
			<div class="column-group">
				<div class="column left two-thirds">
					<img
							src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/imac_prod_charts.jpg' ); ?>"
							alt="Variety Production Charts"/>
				</div>
				<div class="column right third border-left padded">
					<p>Premier subscribers can easily sort and filter using any of the following data elements:</p>
					<ul>
						<li>Title</li>
						<li>Type of Project (TV or Film)</li>
						<li>Studio</li>
						<li>Genre and Arena</li>
						<li>Shoot Dates</li>
						<li>Shoot Location</li>
						<li>Commitment Type</li>
						<li>Production Status</li>
					</ul>
				</div>
			</div>
		</section>

		<section class="content" id="variety-archives">
			<div class="column-group">
				<div class="column right thin-third secondary-content">
					<img
							src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/Variety_over_the_years_2.jpg' ); ?>"
							alt="Variety Over the Years"/>
				</div>
				<div class="column right thin-third centered-type padded">
					<h2>Variety Archives</h2>
					<h3>Access 15 years of<br/>editorial archives</h3>
					<hr/>
					<h4>All Premier subscribers receive access to 15 years of Variety Archives, the entertainment
						industry’s paper of record from 1905 to today. If your goal is to thrive in the present, then
						Archives is the indispensable tool for learning what’s worked in the past.</h4>
				</div>
				<div class="column right thin-third">
					<div class="nostalgia">
						<img
								src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/VarietyArchives_ClaireDanes_270x373.jpg' ); ?>"
								alt="Frank Sinatra Reading Variety"/>
					</div>
				</div>
				<div class="clear"></div>
				<br/>
			</div>
		</section>
	</section><!-- Content Group -->

	<section class="content-group" id="access">
		<header class="content-group-header bottom-arrow">
			<h1>Access</h1>
		</header>
		<section class="content" id="variety-summits">
			<div class="column-group">
				<div class="column left third centered-type">
					<img
							src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/486759569_IA_0079_15135436F7AF37665F49A9CC9EF4C293.jpg' ); ?>"
							alt="Variety Summits" class="rounded secondary-content"/>
					<br class="secondary-content"/><br class="secondary-content"/>
					<h2>Variety<br/>Conferences &amp; Events</h2>
					<h3>Save 25%</h3>
					<hr/>
					<h4>Our industry is about who you know and what you know, and no one keeps you in the know like
						Variety. Variety produces 12 major conferences and more than 15 additional events annually,
						keeping entertainment and media professionals engaged and informed at first class events in Los
						Angeles, New York, and around the world. </h4>
				</div>
				<div class="column left two-thirds">
					<img
							src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/482323051.jpg' ); ?>"
							alt="Variety Summits"/>
					<h5 class="uppercase centered-type">Variety Summits Include:</h5>

					<div class="column-group variety-conferences">
						<div class="column third left">
							<ul class="red-bullets alignleft">
								<li><span>CES Summit</span></li>
								<li><span>MASSIVE Summit</span></li>
								<li><span>Variety Studio: Sundance</span></li>
								<li><span>Entertainment &amp; Technology Summit</span></li>
								<li><span>Power of Women Event</span></li>
								<li><span>TV Summit</span></li>
							</ul>
						</div>
						<div class="column two-thirds left">
							<ul class="red-bullets alignleft">
								<li><span>Writers Room</span></li>
								<li><span>Country Music Event</span></li>
								<li><span>Asia Entertainment Summit</span></li>
								<li><span>Big Data Summit</span></li>
								<li><span>Variety Screening Series/Dinner Series</span></li>
								<li><span>Dealmakers Breakfast</span></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</section>
	</section><!-- Content Group -->

	<?php
	PMC::render_template(
		CHILD_THEME_PATH . '/template-parts/module/premier/footer.php',
		compact( 'template_name' ),
		true,
		[]
	);
	?>
</div>

<?php wp_footer(); ?>
<?php do_action( 'pmc-tags-footer' ); // phpcs:ignore ?>
</body>
</html>
