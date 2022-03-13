<?php
/* TODO: Rename files to be more descriptive */
/*
Description: Fetches and prepares the data from sailthru to show various statuses of the recurring and breaking news sent.
*/
if ( ! current_user_can( 'publish_posts' ) ) {
	die( 'Access Denied' );
}

$operator = 'greaterThan';
$first_request = true;
$sailthru_next = '';

if ( ! empty( $_GET['next'] ) ) {
	$sailthru_next = sanitize_text_field( $_GET['next'] );
	$first_request = false;
}

if ( ! empty( $_GET['prev'] ) ) {
	$sailthru_prev = sanitize_text_field( $_GET['prev'] );
	$first_request = false;
	$operator      = 'lessThan';
}
$date            = new DateTime( "-7 days" );
$sailthru_result = Exact_Target::get_sends( $date->format( 'Y-m-d' ), $operator );

if ( ! empty( $sailthru_result->results ) ) {
	$sailthru_blasts = $sailthru_result->results;
}

$page_count = count( $sailthru_blasts );

$url        = menu_page_url( 'sailthru_newsletter_statuses', false );

if ( ! empty( $sailthru_blasts[ $page_count - 1 ]->SendDate ) && $sailthru_result->moreResults ) {
	$sailthru_next = $sailthru_blasts[ $page_count - 1 ]->SendDate;
}

if ( ! empty( $sailthru_next ) ) {
	$sailthru_next_url = add_query_arg( 'next', $sailthru_next, $url );
}

if ( ! empty( $sailthru_blasts[0]->SendDate ) && !$first_request ) {
	$sailthru_prev = $sailthru_blasts[0]->SendDate;
}

if ( ! empty( $sailthru_prev ) ) {
	$sailthru_prev_url = add_query_arg( 'prev', $sailthru_next, $url );
}

require( 'views/newsletter-statuses-tpl.php' );
