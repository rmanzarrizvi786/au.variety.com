<?php
/**
 * Used for showing AMP subscription config.
 * - Rendered when visiting <site.com>/amp
 * - PIANO Docs     : https://docs.piano.io/amp-experiences/#AMPinitializesub
 * - AMP Docs       : https://amp.dev/documentation/components/amp-subscriptions/#configuration
 * - Variable usage : https://amp.dev/documentation/components/amp-subscriptions/#url-variables
 */

$amp_config = [
	"services"            => [
		[
			"authorizationUrl" => $authorization_url ?? '',
			"noPingback"       => "true",
			"actions"          => [
				"login"     => $login_url . '&reader_id=READER_ID&url=SOURCE_URL&_=RANDOM',
				"subscribe" => $subscription_url . '?reader_id=READER_ID&url=SOURCE_URL&_=RANDOM',
				"logout"    => $logout_url . '&reader_id=READER_ID&_=RANDOM',
			],
			"baseScore"        => 100,
		], // Local service (required)   - https://amp.dev/documentation/components/amp-subscriptions#local-service
	],
	"fallbackEntitlement" => [
		"source"      => "fallback",
		"granted"     => true,
		"grantReason" => "METERING",
		"data"        => [
			"error" => true,
		],
	], // Fallback (optional) - https://amp.dev/documentation/components/amp-subscriptions#fallback-entitlement
];


?>

<script id="amp-subscriptions" type="application/json">
<?php echo wp_json_encode( $amp_config ); ?>
</script>
