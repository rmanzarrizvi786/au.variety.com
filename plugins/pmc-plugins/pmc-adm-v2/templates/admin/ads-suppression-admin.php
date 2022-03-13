<?php if ( ! empty( $instance->error ) ) : ?>
<div class="error fade">
	<p><?php echo esc_html( $instance->error ); ?>
	</p>
</div>
<?php endif ?>

<div id="col-container" class="wp-clearfix">
	<h2>PMC Ads Suppression Scheduling</h2>

	<div id="col-left">
		<div class="col-wrap">
			<?php
			$instance->render_edit_form();
			?>
		</div>
	</div><!-- /col-left -->

	<div id="col-right">
		<div class="col-wrap">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<form method="GET" action="?">
							<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>"/>
							<p class="search-box">
								<label class="screen-reader-text" for="tag-search-input">Search Categories:</label>
								<input type="search" id="tag-search-input" name="search" value="<?php echo esc_attr( $search ); ?>">
								<input type="submit" id="search-submit" class="button" value="Search"></p>
						</form>
						<?php
						$instance->ads_suppression_table_obj->render();
						?>
					</div>
				</div>
			</div>
			<br class="clear">
		</div>
	</div><!-- /col-right -->

</div>
