<?php
/**
 * Template for modal box which is used to quickly edit post on zoninator edit page.
 *
 * @package pmc-plugins
 */

?>
<div id="pmc-zoninator-quick-post-edit-modal" title="Edit Post">
	<div class="ui-widget post-editor">
		<form>
			<table width="100%" class="full-width" cellpadding="5">
				<tbody>
				<tr>
					<td class="label"><label for="id">ID</label></td>
					<td>
						<input type="text" id="post_id" value="1" class="text ui-widget-content ui-corner-all" readonly tabindex="-1">
					</td>
				</tr>
				<tr>
					<td class="label"><label for="title">Title</label></td>
					<td>
						<input type="text" id="post_title" value="Jane Smith" class="text ui-widget-content ui-corner-all" readonly tabindex="-1">
					</td>
				</tr>
				<tr>
					<td class="label"><label for="slug">Slug</label></td>
					<td>
						<input type="text" id="post_slug" value="jane-smith" class="text ui-widget-content ui-corner-all" readonly tabindex="-1">
					</td>
				</tr>
				<tr>
					<td class="label"><label for="categories">Categories</label></td>
					<td>
						<div class="checklist-container">
							<ul id="checklist-category" class="checklist parent category">
								<?php wp_category_checklist(); ?>
							</ul>
						</div>
					</td>
				</tr>
				<?php if ( taxonomy_exists( 'editorial' ) ) { ?>
					<tr id="editorial-row" class="hide">
						<td class="label"><label for="editorial">Editorial</label></td>
						<td valign="top">
							<div class="checklist-container">
								<ul id="checklist-editorial" class="checklist parent editorial">
									<?php
									$args = array(
										'taxonomy' => 'editorial',
									);
									wp_terms_checklist( 0, $args );
									?>
								</ul>
							</div>
						</td>
					</tr>
				<?php } // ENDIF ?>
				</tbody>
			</table>
		</form>
	</div>
</div>
