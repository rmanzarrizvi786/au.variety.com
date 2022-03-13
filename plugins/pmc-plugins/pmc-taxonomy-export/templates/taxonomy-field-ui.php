<div class="taxonomy-section">
	<label for="taxonomy">Select Taxonomy : </label>
	<select name="taxonomy" id="taxonomy">
		<?php
		foreach ( $taxonomies as $taxonomy ) {
			$label = ( empty( $taxonomy->label ) ) ? $taxonomy->name : $taxonomy->label;
			echo "<option value=\"" . esc_attr( $taxonomy->name ) . "\"";
			echo ">" . esc_html( $label . "( " . $taxonomy->name . " )" ) . "</option>";
		}
		?>
	</select>
</div>
<div class="report-type-section">
	<label for="report-type">Choose Report Type : </label><br />
	<input name="report-type" id="report-type" type="radio" value="0" checked="checked"/>Detailed ( Show all the headers )<br />
	<input name="report-type" id="report-type" type="radio" value="1" />Compact ( Show for each term only 'term_id', 'name', 'slug', 'parent', 'article count' in CSV file )
</div>
