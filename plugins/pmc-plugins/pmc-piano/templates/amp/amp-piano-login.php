<?php
/**
 * Piano AMP Login template.
 */

use PMC\Piano\Plugin;

$application_id  = apply_filters( 'piano_application_id', '' );
$login_end_point = 'https://sandbox.tinypass.com/api/v3';
if ( PMC::is_production() ) {
	$login_end_point = 'https://buy.tinypass.com/api/v3';
}
?>
<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
		<title>Login</title>
		<script type='text/javascript' async src='<?php echo esc_url( Plugin::get_instance()->get_script_url() ); ?>'></script>
	</head>
	<body>
	<script type="text/javascript">
			tp = window["tp"] || [];
			tp.push(["setAid", <?php echo wp_json_encode( $application_id ); ?>]);
			tp.push(["setEndpoint", <?php echo wp_json_encode( $login_end_point ); ?>]);
			tp.push(["setUsePianoIdUserProvider", true]);
			tp.push(["init", function() {
				tp.amp.showLogin();
			}]);
	</script>
	</body>
</html>
