<div class="wrap">
	<div id="poststuff">

		<div id="postbox-container-1" class="postbox-container">

			<div class="postbox">

				<div class="handlediv" title="Click to toggle"><br></div>

				<h3 class="hndle ui-sortable-handle"><span>Export Taxonomy Terms - CSV File</span></h3>

				<div class="inside">
					<form action='options.php' method='post'>
						<?php
						settings_fields( 'taxonomy-export-page' );
						do_settings_sections( 'taxonomy-export-page' );
						submit_button( 'Start Download' );
						?>
						<div class="spin-loader"></div>

						<div id="progressbar">
							<div class="progress-label">Processing...</div>
						</div>
						<div id="term-csv-files"></div>

						<div><span id="error-log-data"></span></div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
</div>