<?php
/*
 * Nonce Key Prefix
 * @since 2011-06-22 Amit Gupta
 */
$mmcnws_nonce_pfx = "mmc_nwsltr";
/*
 * Nonce Keys for all Admin pages
 * @since 2011-07-08 Amit Gupta
 */
$mmcnws_nonce_keys = array(
	'addedit_nwsltr' => "{$mmcnws_nonce_pfx}_addeditnwsltr",
	'addedit_bna' => "{$mmcnws_nonce_pfx}_addeditbna",
	'bna_opt' => "{$mmcnws_nonce_pfx}_bnaopt",
	'mc_conn' => "{$mmcnws_nonce_pfx}_mcconnectivity",
	'nwsltr_settings' => "{$mmcnws_nonce_pfx}_settings",
	'nwsltr_opt' => "{$mmcnws_nonce_pfx}_nwsltropt"
);

/*
 * Newsletter thumbnail sources
 * @since 2011-06-22 Amit Gupta
 */
$mmcnws_thumb_src_default = "auto";
$mmcnws_thumb_src = array(
	$mmcnws_thumb_src_default => 'Automatic Thumbnail Generation'
);
/* include wppt presets in options only if wppt plugin is active */


$mmcnws_thumb_src_optgrp = array(
	'wppt_preset0' => array(
		'end' => 'wppt_preset4',
		'name' => 'WP Post Thumbnail'
	)
);

//EOF