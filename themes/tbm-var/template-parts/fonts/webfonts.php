<?php
$font_url = CHILD_THEME_URL . '/assets/public/';
?>
<style type="text/css">
@font-face {
	font-family: 'IBM Plex Mono';
	src: url('<?php echo esc_url( $font_url ); ?>ibm-plex-mono-v5-latin-500.woff2') format('woff2'), /* Chrome 26+, Opera 23+, Firefox 39+ */ url('<?php echo esc_url( $font_url ); ?>ibm-plex-mono-v5-latin-500.woff') format('woff'); /* Chrome 6+, Firefox 3.6+, IE 9+, Safari 5.1+ */
	font-style: normal;
	font-weight: 500;
	font-display: swap;
}

@font-face {
	font-family: 'IBM Plex Sans';
	src: url('<?php echo esc_url( $font_url ); ?>ibm-plex-sans-v7-latin-regular.woff2') format('woff2'), /* Chrome 26+, Opera 23+, Firefox 39+ */ url('<?php echo esc_url( $font_url ); ?>ibm-plex-sans-v7-latin-regular.woff') format('woff'); /* Chrome 6+, Firefox 3.6+, IE 9+, Safari 5.1+ */
	font-style: normal;
	font-weight: 400;
	font-display: swap;
}

@font-face {
	font-family: 'IBM Plex Sans';
	src: url('<?php echo esc_url( $font_url ); ?>ibm-plex-sans-v7-latin-700.woff2') format('woff2'), /* Chrome 26+, Opera 23+, Firefox 39+ */ url('<?php echo esc_url( $font_url ); ?>ibm-plex-sans-v7-latin-700.woff') format('woff'); /* Chrome 6+, Firefox 3.6+, IE 9+, Safari 5.1+ */
	font-style: normal;
	font-weight: 700;
	font-display: swap;
}

@font-face {
	font-family: 'IBM Plex Serif';
	src: url('<?php echo esc_url( $font_url ); ?>ibm-plex-serif-v8-latin-regular.woff2') format('woff2'), /* Chrome 26+, Opera 23+, Firefox 39+ */ url('<?php echo esc_url( $font_url ); ?>ibm-plex-serif-v8-latin-regular.woff') format('woff'); /* Chrome 6+, Firefox 3.6+, IE 9+, Safari 5.1+ */
	font-style: normal;
	font-weight: 400;
	font-display: swap;
}

@font-face {
	font-family: 'Graphik XX Cond';
	src: url('<?php echo esc_url( $font_url ); ?>Graphik\ XX\ Cond-Semibold\ BETA.otf') format('opentype');
	font-style: normal;
	font-weight: 600;
	font-display: swap;
}

@font-face {
	font-family: 'Graphik XX Cond';
	src: url('<?php echo esc_url( $font_url ); ?>Graphik\ XX\ Cond-Medium\ BETA.otf') format('opentype');
	font-style: normal;
	font-weight: 500;
	font-display: swap;
}

@font-face {
	font-family: 'Para Supreme Regular';
	src:
		url('<?php echo esc_url( $font_url ); ?>2020.04.03-ParaSupreme-Regular.woff2') format('woff2'),
		url('<?php echo esc_url( $font_url ); ?>2020.04.03-ParaSupreme-Regular.woff') format('woff'),
		url('<?php echo esc_url( $font_url ); ?>2020.04.03-ParaSupreme-Regular.ttf') format('truetype');
	font-style: normal;
	font-weight: 400;
	font-display: swap;
}
</style>
<?php
//EOF
