<?php
/**
 * Landing Template.
 *
 * @package pmc-variety
 */


// One-off module customizations for the homepage.
$print_plus_item_variety_digital_copy   = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/print-plus-item.variety-digital-copy' );
$print_plus_item_variety_thought_leader = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/print-plus-item.variety-thought-leader' );
$print_plus_item_variety_archives       = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/print-plus-item.variety-archives' );
?>
<section class="print-plus-landing // u-padding-lr-1@mobile-max">
<div class="print-plus-landing-header // lrv-u-width-100p " >
	<div class="print-plus-landing-header-title // lrv-u-text-align-center lrv-u-font-size-40 lrv-u-padding-tb-1 lrv-u-font-size-26@mobile-max lrv-u-font-family-primary u-font-weight-normal@mobile-max lrv-u-line-height-small"><?php esc_html_e( 'Access your Variety Print Plus Features Below', 'pmc-variety' ); ?></div>
	<hr />
	<?php
			\PMC::render_template(
				sprintf( '%s/template-parts/patterns/modules/print-plus-item.php', untrailingslashit( CHILD_THEME_PATH ) ),
				$print_plus_item_variety_digital_copy,
				true
			);

			\PMC::render_template(
				sprintf( '%s/template-parts/patterns/modules/print-plus-item.php', untrailingslashit( CHILD_THEME_PATH ) ),
				$print_plus_item_variety_archives,
				true
			);

			\PMC::render_template(
				sprintf( '%s/template-parts/patterns/modules/print-plus-item.php', untrailingslashit( CHILD_THEME_PATH ) ),
				$print_plus_item_variety_thought_leader,
				true
			);
			?>
</div>
</section>
