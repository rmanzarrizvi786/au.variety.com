<?php

/**
 * Author Blurb
 *
 * Used on Aruthor archive
 *
 * @package pmc-variety
 */

$author_blurb = PMC\Core\Inc\Larva::get_instance()->get_json('modules/author-blurb.prototype');

$author_slug = get_query_var('author_name');

$guest_author = pmc_get_coauthor_data_by('user_nicename', $author_slug);

$author_blurb['c_heading']['c_heading_text'] = (empty($guest_author['display_name'])) ? $guest_author['first_name'] . ' ' . $guest_author['last_name'] : $guest_author['display_name'];
$author_blurb['c_heading']['c_heading_is_primary_heading'] = true;

$author_blurb['c_tagline']['c_tagline_text'] = $guest_author['description'];


if (empty($guest_author['user_email'])) {
	$author_email = $user->data->user_email ?? '';
} else {
	$author_email = $guest_author['user_email'];
}

if (empty($author_email)) {
	$author_blurb['c_link__email'] = null;
} else {
	$author_blurb['c_link__email']['c_link_text'] = $author_email;
	$author_blurb['c_link__email']['c_link_url']  = (!empty($author_email)) ? sprintf('mailto:%1$s', $author_email) : '';
}

$author_blurb['c_link__email'] = null;

if (empty($guest_author['user_twitter'])) {
	$author_blurb['c_icon__twitter'] = null;
} else {
	$twitter_handle = trim($guest_author['user_twitter'], '@');
	$twitter_url    = sprintf('https://twitter.com/%s', trim($guest_author['user_twitter'], '@'));

	$author_blurb['c_icon__twitter']['c_icon_url']       = $twitter_url;
	$author_blurb['c_icon__twitter']['c_icon_real_name'] = $twitter_handle;

	$author_blurb['c_link__twitter']['c_link_text'] = $twitter_handle;
	$author_blurb['c_link__twitter']['c_link_url']  = $twitter_url;
}

\PMC::render_template(
	sprintf('%s/template-parts/patterns/modules/author-blurb.php', untrailingslashit(CHILD_THEME_PATH)),
	$author_blurb,
	true
);
