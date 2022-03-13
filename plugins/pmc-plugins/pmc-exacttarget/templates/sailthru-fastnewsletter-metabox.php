<table width="100%">
	<tbody>
		<tr>
			<td valign="top">
				<label for="fastnewsletters-subject" title="Custom Subject Line">Custom Subject Line</label>
			</td>
			<td>
				<input type="text" id="fastnewsletters-subject" name="fastnewsletters-subject" placeholder="Enter Custom Subject Line" value="<?php echo esc_attr( $custom_subject_line ); ?>" style="width: 350px;" >
				<div class="mt_counter">Character Count : <span id="fastnewsletters-subject-counter" class="count positive"><?php echo esc_attr( strlen( $custom_subject_line ) ); ?></span></div>
			</td>
		</tr>
	</tbody>
</table>
