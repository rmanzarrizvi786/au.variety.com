<?php
/**
 * Profiles archive template
 */

get_header();

$more_stories_button = PMC\Larva\Json::get_instance()->get_json_data( 'modules/more-stories-button.profile' );

PMC::render_template( PROFILES_ROOT . '/template-parts/shared/profile-header.php', [], true );
?>

<div class="lrv-a-wrapper lrv-a-grid lrv-a-cols4@tablet lrv-u-padding-t-2 js-ProfileFilter">

	<?php
	// Filter render_template would go here.
	?>

	<div class="lrv-a-span3@tablet">
		<?php PMC::render_template( PROFILES_ROOT . '/template-parts/index/profile-card-list.php', [], true ); ?>
		<?php PMC::render_template( PROFILES_ROOT . '/template-parts/index/profile-card.php', [], true ); ?>

		<div class="u-grid-span-all lrv-u-margin-t-2">
			<?php
			\PMC::render_template(
				sprintf( '%s/build/patterns/modules/more-stories-button.php', \PMC\Larva\Config::get_instance()->get( 'core_directory' ) ),
				$more_stories_button,
				true
			);
			?>
		</div>
	</div>
</div>

<?php

get_footer();
