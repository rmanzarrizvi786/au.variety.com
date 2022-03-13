<fieldset class="inline-edit-col-right inline-edit-book">
	<div class="inline-edit-col inline-edit-<?php echo esc_attr( $column_name ); ?>">
		<label>
			<span style="width: 8em" class="title"><?php echo esc_html( $column_label ); ?></span>
			<span class="input-text-wrap"><input type="text" style="width:91%" name="<?php echo esc_attr( $column_name); ?>" id="<?php echo esc_html( $column_name ); ?>" /></span>
		</label>
	</div>
</fieldset>