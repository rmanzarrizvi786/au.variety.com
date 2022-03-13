<?php
/**
 * Settings page template
 *
 * @package PMC Genre 1.0
 */
?>
<div class="wrap">
	<h2><?php echo esc_html( $plugin_name ); ?> Settings</h2>
	<h4 class="description">
		Genres can be mapped or un-mapped to Categories/Verticals. When creating a post only those genres would be available which are mapped to selected
		categories/verticals on that post.
	</h4>
	<form action="<?php menu_page_url( $plugin_id . '-page', true ); ?>" method="post" id="<?php echo esc_attr( $settings->get_field_name( 'form' ) ); ?>" name="<?php echo esc_attr( $settings->get_field_name( 'form' ) ); ?>" class="<?php echo esc_attr( $settings->get_field_name( 'form' ) ); ?>">
		<table id="<?php echo esc_attr( $settings->get_field_name( 'ui' ) ); ?>" width="85%" border="0">
			<tr>
				<td width="25%" align="center">
					<label for="<?php echo esc_attr( $settings->get_field_name( 'terms' ) ); ?>">Categories &amp; Verticals</label>
				</td>
				<td>
					<select id="<?php echo esc_attr( $settings->get_field_name( 'terms' ) ); ?>" name="<?php echo esc_attr( $settings->get_field_name( 'terms' ) ); ?>">
						<option value="">Select a Category or Vertical</option>
					<?php if ( ! empty( $categories ) && is_array( $categories ) ) { ?>
						<optgroup label="Categories">
						<?php foreach ( $categories as $category_id => $category_name ) { ?>
							<option value="<?php echo esc_attr( $category_id ); ?>"><?php echo esc_attr( $category_name ); ?></option>
						<?php } ?>
						</optgroup>
					<?php } ?>
					<?php if ( ! empty( $verticals ) && is_array( $verticals ) ) { ?>
						<optgroup label="Verticals">
						<?php foreach ( $verticals as $vertical_id => $vertical_name ) { ?>
							<option value="<?php echo esc_attr( $vertical_id ); ?>"><?php echo esc_attr( $vertical_name ); ?></option>
						<?php } ?>
						</optgroup>
					<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td align="center">
					<label for="<?php echo esc_attr( $settings->get_field_name( 'mapped-genres' ) ); ?>">Mapped Genres</label>
				</td>
				<td>
					<select size="5" class="select-list" id="<?php echo esc_attr( $settings->get_field_name( 'mapped-genres' ) ); ?>" name="<?php echo esc_attr( $settings->get_field_name( 'mapped-genres' ) ); ?>"></select>
					&nbsp;&nbsp;
					<input type="button" id="<?php echo esc_attr( $settings->get_field_name( 'remove-btn' ) ); ?>" name="<?php echo esc_attr( $settings->get_field_name( 'remove-btn' ) ); ?>" class="button btn-select-list" value="Remove Selected" disabled="true" />
				</td>
			</tr>
			<tr>
				<td align="center">
					<label for="<?php echo esc_attr( $settings->get_field_name( 'unmapped-genres' ) ); ?>">Available Genres</label>
				</td>
				<td>
					<select size="5" class="select-list" id="<?php echo esc_attr( $settings->get_field_name( 'unmapped-genres' ) ); ?>" name="<?php echo esc_attr( $settings->get_field_name( 'unmapped-genres' ) ); ?>">
					<?php foreach ( $unmapped_genres as $genre_id => $genre_name ) { ?>
						<option value="<?php echo esc_attr( $genre_id ); ?>"><?php echo esc_attr( $genre_name ); ?></option>
					<?php } ?>
					</select>
					&nbsp;&nbsp;
					<input type="button" id="<?php echo esc_attr( $settings->get_field_name( 'add-btn' ) ); ?>" name="<?php echo esc_attr( $settings->get_field_name( 'add-btn' ) ); ?>" class="button btn-select-list" value="Add Selected" disabled="true" />
				</td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<?php wp_nonce_field( $nonce['action'], $nonce['name'] ); ?>
					<input type="hidden" id="<?php echo esc_attr( $settings->get_field_name( 'mappings-hdn' ) ); ?>" name="<?php echo esc_attr( $settings->get_field_name( 'mappings-hdn' ) ); ?>" value="" />
					<input type="submit" id="<?php echo esc_attr( $settings->get_field_name( 'save-btn' ) ); ?>" name="<?php echo esc_attr( $settings->get_field_name( 'save-btn' ) ); ?>" class="button button-primary" value="Save Settings" />
				</td>
			</tr>
		</table>
	</form>
</div>
