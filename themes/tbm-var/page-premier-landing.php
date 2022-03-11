<?php
/**
 * Template Name: Premier Landing Page
 *
 * CDWE-580 -- Copied from pmc-variety-2014 theme
 *
 * @package pmc-variety-2017
 *
 * @since   2017-08-21
 */

$template_name = 'premier-landing-page';

pmc_add_body_class( [ 'authenticated', 'authenticated-pp' ] );
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
	<?php do_action( 'pmc_tags_head' ); ?>
</head>

<body <?php body_class( 'hide' ); ?>>
<?php do_action( 'pmc-tags-top' ); // phpcs:ignore ?>
<div id="site-wrapper" class="centered-type">

	<header>
		<a id="back-to-variety" href="<?php echo esc_url( '/' ); ?>" title="back to Variety.com">back to
			Variety.com</a>
		<div class="clear"></div>
		<figure id="variety-premier-logo">
			<a href="<?php echo esc_url( '/' ); ?>" title="Variety">
				<img src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/premier-logo-w-reflection.png' ); ?>" alt="Variety Premier Logo"/>
			</a>
		</figure>
		<h1>As a subscriber you have access to Variety’s premium content!</h1>
		<h2 class="uppercase">Click any feature below</h2>
	</header>

	<section class="content">
		<div class="grid">

			<div id="variety-digital-edition" class="grid-cell">
				<figure>
					<a href="<?php echo esc_url( '/access-digital/' ); ?>" title="Digital Edition">
						<img src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/1001_EditIns1d.jpg' ); ?>" class="rounded"/>
					</a>
				</figure>
				<figcaption>
					<a href="<?php echo esc_url( '/access-digital/' ); ?>" title="Digital Edition">
						<h3>Variety<br/>Digital Edition</h3>
					</a>
					<h4>Browse or search the current and past digital issues of Variety Magazine.</h4>
				</figcaption>
			</div>

			<div id="variety-production-charts" class="grid-cell">
				<figure>
					<a href="<?php echo esc_url( '/production-charts/' ); ?>" title="Production Charts">
						<img src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/Divergent-2014-CBD-12513-small.jpg' ); ?>" class="rounded"/>
					</a>
				</figure>
				<figcaption>
					<a href="<?php echo esc_url( '/production-charts/' ); ?>" title="Production Charts">
						<h3>Production<br/>Charts</h3>
					</a>
					<h4>Stay on Top of Opportunities Across TV and Film Produtions.</h4>
				</figcaption>
			</div>

			<div id="variety-vscore-top" class="grid-cell end">
				<figure>
					<a href="<?php echo esc_url( '/vscore-top-250/' ); ?>" title="Vscore Top 250">
						<img src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/V_v6.png' ); ?>" class="rounded"/>
					</a>
				</figure>
				<figcaption>
					<a href="<?php echo esc_url( '/vscore-top-250/' ); ?>" title="Vscore Top 250">
						<h3>Vscore<br/>Top 250</h3>
					</a>
					<h4>The Entertainment Industry’s Actor Measurement Tool.</h4>
				</figcaption>
			</div>

			<div id="variety-thought-leader" class="grid-cell">
				<figure>
					<a href="<?php echo esc_url( '/thought-leaders/' ); ?>" title="Thought Leader Special Reports" target="_blank">
						<img src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/thought-leaders.jpg' ); ?>" class="rounded"/>
					</a>
				</figure>
				<figcaption>
					<a href="<?php echo esc_url( '/thought-leaders/' ); ?>" title="Thought Leader Special Reports" target="_blank">
						<h3>Thought Leader<br/>Special Reports</h3>
					</a>

					<h4>Thought Leader is a series of special reports written by Variety's senior editors.</h4>
				</figcaption>
			</div>

			<div id="variety-summits" class="grid-cell">
				<figure>
					<a href="<?php echo esc_url( 'https://events.variety.com/' ); ?>" title="Variety Summits" target="_blank">
						<img src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/125828074.jpg' ); ?>" class="rounded"/>
					</a>
				</figure>
				<figcaption>
					<a href="<?php echo esc_url( 'https://events.variety.com/' ); ?>" title="Variety Summits" target="_blank">
						<h3>Variety<br/>Summits</h3>
					</a>

					<h4>Stay engaged and informed,<br/>25% off Variety Summits</h4>
				</figcaption>
			</div>

			<div id="variety-archives" class="grid-cell end">
				<figure>
					<a href="<?php echo esc_url( '/premier-archives-registration/' ); ?>" title="Variety Archives" target="_blank">
						<img src="<?php echo esc_url( VARIETY_THEME_URL . '/assets/build/images/premier/Sinatra-no-frame.jpg' ); ?>" class="rounded"/>
					</a>
				</figure>
				<figcaption>
					<a href="<?php echo esc_url( '/premier-archives-registration/' ); ?>" title="Variety Archives" target="_blank">
						<h3>Variety<br/>Archives</h3>
					</a>
					<h4>Access the past 15 years of editorial content through the Variety Archives.</h4>
				</figcaption>
			</div>
		</div>
	</section>

	<?php
	PMC::render_template(
		CHILD_THEME_PATH . '/template-parts/module/premier/footer.php',
		[],
		true,
		[]
	);
	?>
</div>

<?php wp_footer(); ?>
<?php do_action( 'pmc-tags-footer' ); // phpcs:ignore ?>
</body>
</html>
