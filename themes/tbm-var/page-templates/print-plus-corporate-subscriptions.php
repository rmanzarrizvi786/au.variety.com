<?php
/*
 * Template Name: Print Plus - Corporate Subscription
 */

$vcs_submit = filter_input( INPUT_POST, 'vcsSubmit', FILTER_SANITIZE_STRING );
$honeypot   = filter_input( INPUT_POST, 'print-plus-subscription-check', FILTER_SANITIZE_STRING );

if ( ! empty( $vcs_submit ) && empty( $honeypot ) ) {
	$display_error = true;

	VarietySubscriptionsHelper::set_internal_email( 'varietyhelp@pmc.com' );

	$vy_form_nonce = filter_input( INPUT_POST, 'vy_form', FILTER_SANITIZE_STRING );
	$nonce         = ( ! empty( $vy_form_nonce ) ) ? $vy_form_nonce : '';
	$validation    = true;

	if ( wp_verify_nonce( $vy_form_nonce, 'submit_corp_sub' ) ) {

		// Check if value is not empty and email address matches field pattern.
		foreach ( $_POST as $post_name => $post_val ) {
			/**
			 * https://regex101.com/r/UMS6rk/1
			 */
			if ( empty( $post_val ) || ( 'vcsEmailAddress' === $post_name && ! preg_match( '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/', $post_val ) ) ) {
				$validation = false;
				break;
			}
		}

		// Send email only if fields are validated.
		if ( $validation ) {
			if ( VarietySubscriptionsHelper::send_internal_email( 'vcs', 'Variety Corporate Subscription Submission' ) ) {
				$display_error = false;
			}
		}
	}
}

get_header();

$print_plus_corporate_subscriptions = PMC\Core\Inc\Larva::get_instance()->get_json( 'modules/print-plus-corporate-subscriptions.prototype' );

if ( ! empty( $vcs_submit ) ) {
	if ( ! $display_error ) {
		$print_plus_corporate_subscriptions['print_plus_corporate_subscriptions_submission_text'] =
			__( 'Thank you for your submission! A member of our staff will contact you shortly.', 'pmc-variety' );
	} else {
		$print_plus_corporate_subscriptions['print_plus_corporate_subscriptions_submission_text'] =
			__( 'We\'re sorry, there was an error. Please try to submit your information again.', 'pmc-variety' );
	}
}
?>

<form method="POST">
	<?php wp_nonce_field( 'submit_corp_sub', 'vy_form' ); ?>

	<?php
	\PMC::render_template(
		sprintf( '%s/template-parts/patterns/modules/print-plus-corporate-subscriptions.php', untrailingslashit( CHILD_THEME_PATH ) ),
		$print_plus_corporate_subscriptions,
		true
	);
	?>

</form>

<?php
get_footer();
