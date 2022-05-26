<?php

/**
 * Manifest for plugin configurations.
 *
 * All plugin configurations are loaded/initialized in here
 *
 * @since 2018-02-02
 *
 * @package pmc-core-v2
 */

// Load pmc-tags plugin configurations.
\PMC\Core\Plugins\Config\PMC_Tags::get_instance();

/*
 * Unless otherwise needed, load CheezCap configuration towards the end
 */
\PMC\Core\Plugins\Config\Cheezcap::get_instance();

//EOF
