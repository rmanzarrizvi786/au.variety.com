<?php

$voices_author = PMC_Voices::get_instance()->get_guest_author_voices( 'menu_order' );

?>
	<form id="pmc-voices-form" action="options.php" method="post">
		<?php
		settings_fields( "pmc_voice_setting_grp" );

		?>

		<h1> PMC Voices Ordering</h1>

		<ul class="voices-author">

			<?php

			foreach ( $voices_author as $author ) {
				?>
				<li data-post-id="<?php echo esc_attr( $author->ID ); ?>">
					<strong><?php echo esc_html( $author->menu_order . ". " ); ?></strong>
					<span><?php echo esc_html( $author->post_title ); ?></span>
				</li>

			<?php
			}

			?>
		</ul>
		<input id="sorted-value" type="hidden" name="pmc_voice_setting">

		<?php submit_button( 'Save All' ); ?>
	</form></div>
<?php
//EOF