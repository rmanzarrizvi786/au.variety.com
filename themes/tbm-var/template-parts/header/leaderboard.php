<?php
// if ( ! PMC::is_mobile() ) 
{
	// Need this markup to show Video Wall only on desktop/tablet.
?>
	<div id="leaderboard-no-padding" class="l-header__leaderboard-no-padding u-margin-a-1@desktop-xl lrv-u-padding-tb-075">
		<?php pmc_adm_render_ads('leaderboard'); ?>
	</div>
<?php

}
