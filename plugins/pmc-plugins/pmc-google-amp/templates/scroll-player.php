<?php
/**
 * Adding the Scroll Player to AMP Template.
 *
 * @ticket PMCVIP-2489
 * @since 2021-09-27 - Mahbubur Rahman
 */
?>

<div amp-access="NOT scroll.scroll">
	<?php \PMC\Google_Amp\Plugin::get_instance()->render_jwplayer_tag( $args ); ?>
</div>

<?php

$scroll_jwplayer_id = \PMC\Partner_Scroll\Plugin::get_instance()->scroll_jwplayer_id();

if ( ! empty( $scroll_jwplayer_id ) ) {
    $args['data-player-id'] = $scroll_jwplayer_id;

	?>
        <div amp-access="scroll.scroll" amp-access-hide>
            <?php \PMC\Google_Amp\Plugin::get_instance()->render_jwplayer_tag( $args ); ?>
        </div>
	<?php
}
