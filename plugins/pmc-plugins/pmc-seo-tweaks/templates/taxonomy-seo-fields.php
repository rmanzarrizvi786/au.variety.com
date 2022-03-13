<tr class="form-field">
	<th scope="row" valign="top">
		<label for="pmc_seo_tweaks_title">SEO Title</label>
	</th>
	<td>
		<textarea name="pmc_seo_tweaks_title" id="pmc_seo_tweaks_title" rows='3' cols="30" style="width:60%;"><?php echo esc_html( $title ); ?></textarea>
		<br />
		<span class="description">SEO Title for current tag/category</span>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top">
		<label for="pmc_seo_tweaks_description">SEO Description</label>
	</th>
	<td>
		<textarea name="pmc_seo_tweaks_description" id="pmc_seo_tweaks_description" rows='3' cols="30" style="width:60%;"><?php echo esc_html( $description ); ?></textarea>
		<br />
		<span class="description">SEO Description for current tag/category. Use {tag}/{category} for replacement.</span>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top">
		<label for="pmc_seo_tweaks_keywords">SEO Keywords</label>
	</th>
	<td>
		<textarea name="pmc_seo_tweaks_keywords" id="pmc_seo_tweaks_keywords" rows='3' cols="30" style="width:60%;"><?php echo esc_html( $keywords ); ?></textarea>
		<br />
		<span class="description">SEO Keywords for current tag/category. Use {tag}/{category} for replacement.</span>
	</td>
</tr>