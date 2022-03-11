<?php
/**
 * Template for Variety Hollywood Exec Profile meta tags
 *
 * @since 2017.1.0
 *
 * @package pmc-variety-2017
 */

$image = '';

if ( ! empty( $profile['honoree_image'] ) ) {
	$image = $profile['honoree_image'];
} elseif ( has_post_thumbnail( $post ) ) {
	$image = get_the_post_thumbnail_url( $post->ID );
}

$content_type = ( empty( $in_500 ) || $in_500 !== true ) ? 'Exec Profile' : 'Variety 500';

$biography = '';

if ( ! empty( $profile['biography'] ) ) {
	$biography = $profile['biography'];
}

?>

<!-- Swiftype Meta Tags Start -->

<meta class="swiftype" name="content_type" data-type="string" content="<?php echo esc_attr( $content_type ); ?>" />
<meta class="swiftype" name="published_at" data-type="date" content="<?php echo esc_attr( get_the_date( 'Y-m-d H:i:s', $post->ID ) ); ?>" />
<meta class="swiftype" name="name" data-type="string" content="<?php printf( '%s %s', esc_attr( $profile['first_name'] ), esc_attr( $profile['last_name'] ) ); ?>" />
<meta class="swiftype" name="title" data-type="string" content="<?php printf( '%s %s', esc_attr( $profile['first_name'] ), esc_attr( $profile['last_name'] ) ); ?>" />
<meta class="swiftype" name="image" data-type="enum" content="<?php echo esc_attr( $image ); ?>" />
<meta class="swiftype" name="job_title" data-type="string" content="<?php echo esc_attr( $profile['job_title'] ); ?>" />

<?php

if ( ! empty( $profile['companies'] ) ) {

	if ( ! is_array( $profile['companies'] ) ) {
		$companies = (array) $profile['companies'];
	} else {
		$companies = wp_list_pluck( $profile['companies'], 'company_name' );
	}

	$companies = array_filter( array_unique( array_map( 'trim', $companies ) ) );

	foreach ( $companies as $company ) {
		printf( '<meta class="swiftype" name="companies" data-type="string" content="%s" />', esc_attr( $company ) );
		echo "\n";
	}

}

if ( ! empty( $profile['line_of_work'] ) ) {

	if ( ! is_array( $profile['line_of_work'] ) ) {
		$profile['line_of_work'] = (array) $profile['line_of_work'];
	}

	$profile['line_of_work'] = array_filter( array_unique( array_map( 'trim', $profile['line_of_work'] ) ) );

	foreach ( $profile['line_of_work'] as $line_of_work ) {
		printf( '<meta class="swiftype" name="line_of_work" data-type="string" content="%s" />', esc_attr( $line_of_work ) );
		echo "\n";
	}

}

if ( ! empty( $profile['media_category'] ) ) {

	if ( ! is_array( $profile['media_category'] ) ) {
		$profile['media_category'] = (array) $profile['media_category'];
	}

	$profile['media_category'] = array_filter( array_unique( array_map( 'trim', $profile['media_category'] ) ) );

	foreach ( $profile['media_category'] as $media_category ) {
		printf( '<meta class="swiftype" name="media_category" data-type="string" content="%s" />', esc_attr( $media_category ) );
		echo "\n";
	}

}

if ( ! empty( $profile['country_of_citizenship'] ) ) {

	if ( ! is_array( $profile['country_of_citizenship'] ) ) {
		$profile['country_of_citizenship'] = (array) $profile['country_of_citizenship'];
	}

	$profile['country_of_citizenship'] = array_filter( array_unique( array_map( 'trim', $profile['country_of_citizenship'] ) ) );

	foreach ( $profile['country_of_citizenship'] as $country_of_citizenship ) {
		printf( '<meta class="swiftype" name="country_of_citizenship" data-type="string" content="%s" />', esc_attr( $country_of_citizenship ) );
		echo "\n";
	}

}

if ( ! empty( $profile['country_of_residence'] ) ) {

	if ( ! is_array( $profile['country_of_residence'] ) ) {
		$profile['country_of_residence'] = (array) $profile['country_of_residence'];
	}

	$profile['country_of_residence'] = array_filter( array_unique( array_map( 'trim', $profile['country_of_residence'] ) ) );

	foreach ( $profile['country_of_residence'] as $country_of_residence ) {
		printf( '<meta class="swiftype" name="country_of_residence" data-type="string" content="%s" />', esc_attr( $country_of_residence ) );
		echo "\n";
	}

}


$tags = get_the_tags( $post->ID );

if ( ! empty( $tags ) && ! is_wp_error( $tags ) && is_array( $tags ) ) {

	$tags = wp_list_pluck( $tags, 'name' );

	if ( empty( $tags ) || ! is_array( $tags ) ) {
		$tags = array();
	}

}

if ( ! empty( $companies ) && is_array( $companies ) ) {

	if ( ! is_array( $tags ) ) {
		$tags = (array) $tags;
	}

	$tags = array_merge( $tags, $companies );

}

if ( ! empty( $tags ) ) {

	if ( ! is_array( $tags ) ) {
		$tags = (array) $tags;
	}

	$tags = array_filter( array_unique( array_map( 'trim', $tags ) ) );

	foreach ( $tags as $tag ) {
		printf( '<meta class="swiftype" name="tags" data-type="string" content="%s" />', esc_attr( $tag ) );
		echo "\n";
	}

}

$vy_500_years = get_the_terms( $post->ID, 'vy500_year' );

if ( ! empty( $vy_500_years ) && ! is_wp_error( $vy_500_years ) ) {

	foreach ( $vy_500_years as $vy_500_year ) {

		if ( ! is_a( $vy_500_year, 'WP_Term' ) ) {
			continue;
		}

		printf( '<meta class="swiftype" name="vy500_year" data-type="string" content="%s" />', esc_attr( $vy_500_year->slug ) );
		echo "\n";
	}
}

$brief_synopsis = wp_strip_all_tags( $profile['brief_synopsis'] );
if ( ! empty( $brief_synopsis ) ) {
	printf( '<meta class="swiftype" name="brief_synopsis" data-type="string" content="%s" />', esc_attr( $brief_synopsis ) );
	echo "\n";
}
?>

<meta class="swiftype" name="body" data-type="text" content="<?php echo esc_attr( $biography ); ?>" />
<meta name="st:robots" content="follow, index">

<!-- Swiftype Meta Tags End -->

