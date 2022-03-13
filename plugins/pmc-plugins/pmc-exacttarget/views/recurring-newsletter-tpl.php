<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable

?>
<div class="wrap">
<a href="<?php menu_page_url( "sailthru_recurring_newsletters"); ?>">&laquo;Back to options</a>

<h2>Add/Edit Newsletter</h2>
<?php
	if ( $notices ) {
		echo "<div class='notice notice-info'><ul>";
		foreach ( $notices as $notice ) {
			echo '<li>' . esc_html( $notice ) . '</li>';
		}
		echo '</ul></div>';
	}

	if ( $sailthru_errors ) {
		echo "<div class='error'><ul>";
		foreach ( $sailthru_errors as $error ) {
			echo '<li>' . esc_html( $error ) . '</li>';
		}
		echo '</ul></div>';
	}

?>
<form method="post" action="" id="sailthru-edit-recurring-newsletter-form">
<?php if ( isset( $sailthru_repeat['repeat_id'] ) ) { ?>
<input type="hidden" name="repeat_id" value="<?php echo esc_attr( $sailthru_repeat['repeat_id'] ); ?>"/><?php } ?>
<table class="mmc_newsletter_edit_table" style="float:left;">
<thead>
<td style="float:right;"></td>
<td></td>
</thead>
<tr>
	<td><label for="name">Name</label></td>
	<td>
		<input type="text" name="name" id="title"
			   value="<?php echo  esc_attr( $sailthru_repeat['name'] ); ?>"/>
	</td>
</tr>
	<tr class="odd">
		<td class="label"><label for="dataextension">Data Extension</label></td>
		<td class="control">
			<select name="dataextension" id="dataextension">
				<?php foreach ( $sailthru_dataextension as $value => $name ) {
					echo "<option value=\"". esc_attr( $value ) . "\"";
					selected( $sailthru_repeat['dataextension'], $value );
					echo ">" . esc_html( $name ) . "</option>";
				}?>
			</select>
		</td>
	</tr>
	<tr>
		<td><label for="external_feed_url">External Feed Url.</label></td>
		<td>
			<p>
				Overrides automatically created feed url in this plugin.
			</p>
			<input type="text" name="external_feed_url" value="<?php
			if ( ! empty( $sailthru_repeat['external_feed_url'] ) ) {
				echo esc_url( $sailthru_repeat['external_feed_url'] );
			}
			?>">
		</td>
	</tr>
	<tr>
		<td><label for="content_builder">Content Builder</label></td>
		<td>
			<select name="content_builder" id="content_builder">
				<option value="yes" <?php echo ( ! empty( $sailthru_repeat['content_builder'] ) && 'yes' === $sailthru_repeat['content_builder'] ) ? 'selected' : ''; ?> >Yes</option>
				<option value="no" <?php echo ( empty( $sailthru_repeat['content_builder'] ) || 'yes' !== $sailthru_repeat['content_builder'] ) ? 'selected' : ''; ?> >No</option>
			</select>
		</td>
	</tr>
	<tr class="odd">
		<td>
			<label for="template">HTML Template</label>
			<label for="content_builder_template">HTML Template <br> <strong>( Content Builder )</strong></label>
		</td>
		<td>
			<p>
				To Set @xml use syntax below & feed url will be automatically inserted.
				<br/>
				<strong>##pmc-auto-insert-feed-url##</strong>
			</p>
			<select name="template" id="template">
				<?php foreach ( $sailthru_templates as $value => $name ) {
					echo "<option value=\"" . esc_attr( $value ) . "\"";
					selected( $sailthru_repeat['template'], $value );
					echo ">" . esc_html( $name ) . "</option>";
				} ?>
			</select>
			<select name="content_builder_template" id="content_builder_template">
				<?php
				foreach ( $content_builder_templates as $template_id => $name ) {
					printf(
						'<option value="%s" %s >%s</option>',
						esc_attr( $template_id ),
						selected( $sailthru_repeat['template'], $template_id ),
						esc_html( $name )
					);
				}
				?>
			</select>
		</td>

	</tr>
<tr>
	<td>
		<label>Subject</label>
	</td>
	<td >
		<input type="text" name="subject"
			   value="<?php echo isset( $sailthru_repeat['subject'] ) ? esc_attr( stripslashes( $sailthru_repeat['subject'] ) ) : '' ?>"/>
	</td>
</tr>
<tr>
	<?php

		if( isset( $sailthru_repeat['pmc_newsletter_senddefinition'] ) ){
			$send_classification = $sailthru_repeat['pmc_newsletter_senddefinition'];
		}else{
			$send_classification = 0;

		}
	?>
	<td style="padding: 20px 0;"><label> Newsletter Senddefinition:</label></td>
	<td style="border-bottom: 1px solid #000000; padding:20px 0;"><select name="pmc_newsletter_recurr_senddefinition" id="pmc_newsletter_recurr_senddefinition">
		<option value="0" <?php selected( $send_classification, 0 ); ?> >Please Select a Senddefinition</option>
		<?php foreach ( $et_sendclassification as $value => $name ) {
		echo "<option value=\"" . esc_attr( $value ) . "\"";
		selected( $send_classification, $value );
		echo ">" . esc_html( $name ) . "</option>";
	} ?>
	</select></td>
</tr>
<tr>
	<td style="padding-top: 10px;"><label for="posts[number_of_posts]">Number of posts to display</label></td>
	<td style="padding-top: 10px;">
		<select name="posts[number_of_posts]" id="number_of_posts">
			<?php						  for ( $i = 1; $i < 50; $i++ ) {
			$num_post = 10;
			if ( isset( $sailthru_postsquery['number_of_posts'] ) ) {
				$num_post = $sailthru_postsquery['number_of_posts'];
			}
			?>
			<option value='<?php echo $i?>' <?php selected( $num_post, $i ); ?>/><?php echo $i?></option>
			<?php }?>
			?>
		</select>
	</td>
</tr>

<tr>
	<td><label for="default_thumbnail_src">Default thumbnail image<br/>Enter image source url</label></td>
	<td>
		<input type="text" name="default_thumbnail_src" id="default_thumbnail_src"
			   value="<?php if ( isset( $sailthru_repeat['default_thumbnail_src'] ) ) echo esc_url( $sailthru_repeat['default_thumbnail_src'] ); ?>"/>
		<input id="upload_default_thumbnail_src" type="button" value="upload new image"/>
	</td>
</tr>
	<tr>
		<td>
			<label for="pmc_image_size">Image size to be used in the feed</label>
		</td>
		<td>
			<input style="width: 275px;" id="pmc_image_size" placeholder="Image Size ( thumbnail, small, large etc. )" type="text" name="pmc_image_size" value="<?php echo esc_attr( $sailthru_repeat['img_size'] ); ?>"/>

			<input style="width: 275px;" id="pmc_image_type" placeholder="Image Type ( portrait, landscape etc. )" type="text" name="pmc_image_type" value="<?php echo esc_attr( $sailthru_repeat['img_type'] ); ?>"/>
		</td>
	</tr>
<tr class="odd">
	<td>Get posts by</td>
	<td>
		<select id="story_source" name="posts[story_source]">
			<option value="">Choose</option>
			<option
				value="most_commented" <?php selected( $sailthru_postsquery['story_source'], 'most_commented' ); ?>>
				Most Commented
			</option>
			<option
				value="most_popular" <?php selected( $sailthru_postsquery['story_source'], 'most_popular' ); ?>>
				Most Popular
			</option>
			<option value="wp_most_popular" <?php selected( $sailthru_postsquery['story_source'], 'wp_most_popular' ); ?>>
				WP Most Popular
			</option>
		</select>
                        <span id="story_source_days">
    				in last
                            <select name="posts[story_source_days]">
								<?php
								for ( $i = 0; $i <= 60; $i++ ) {
									echo "<option value='$i'";
									selected( (int)$sailthru_postsquery['story_source_days'], $i );
									echo ">$i</option>";
								}
								?>
							</select> days
                            </span>

	</td>
</tr>
<tr class="odd">
	<td style="vertical-align:top"><label for="posts[filter_posts_by_cat]">Filter posts by category</label></td>
	<td>
		<input type='checkbox' id='mmc_newsletter_filter_posts_by_cat' name='posts[filter_posts_by_cat]'
			   value='1' <?php if ( isset( $sailthru_postsquery['filter_posts_by_cat'] ) ) checked( $sailthru_postsquery['filter_posts_by_cat'], 1 ); ?>/>

		<div id="category_select_box">
			<?php								 $cats = get_categories( array( 'hierarchical' => 1,
																			   'hide_empty' => 0 ) );
			foreach ( $cats as $c ) {
				$st_checked = false;
				if ( isset( $sailthru_postsquery['filter_categories'] ) ) {
					$st_checked = is_array( $sailthru_postsquery['filter_categories'] ) && in_array( $c->cat_ID, $sailthru_postsquery['filter_categories'], true ) ;
				}
				echo "<input type='checkbox' name='posts[filter_categories][]' value='" . absint( $c->cat_ID ) . "' ";
				checked( $st_checked, true );
				echo " /> " . esc_html( $c->name ) . "<br />";
			}
			?>
		</div>
	</td>
</tr>
<tr>
	<td style="vertical-align:top"><label for="posts[filter_posts_by_tag]">Filter posts by tag</label></td>
	<td>
		<input type='checkbox' id='mmc_newsletter_filter_posts_by_tag' name='posts[filter_posts_by_tag]'
			   value='1' <?php if ( isset( $sailthru_postsquery['filter_posts_by_tag'] ) ) checked( $sailthru_postsquery['filter_posts_by_tag'], 1 ); ?>/>

		<div id="tag_select_box">
			<br />
			<div class="ui-widget">
				<input id="sailthru-autocomplete-tags" type="text" />
				<span class="description">Start typing Tag name to get a list of tags</span>
			</div>
			<div id="sailthru-selected-tags">
<?php
	$sailthru_postsquery['filter_tags'] = ( ! isset( $sailthru_postsquery['filter_tags'] ) || ! is_array( $sailthru_postsquery['filter_tags'] ) ) ? array() : $sailthru_postsquery['filter_tags'];
	$sailthru_postsquery['filter_tags'] = array_filter( array_unique( array_map( 'intval', $sailthru_postsquery['filter_tags'] ) ) );

	echo '<select id="sailthru-filter-tags" name="posts[filter_tags][]" multiple>';

	if( ! empty( $sailthru_postsquery['filter_tags'] ) ) {
		foreach( $sailthru_postsquery['filter_tags'] as $t_id ) {
			$t = get_tag( $t_id );

			if( empty( $t ) ) {
				continue;
			}

			echo '<option value="' . $t_id . '">' . $t->name . '</option>';

			unset( $t );
		}
	}

	echo '</select>';
	echo '&nbsp;<span class="description">Select tags and click "Remove" button to remove them</span>';
	echo '<br /><br />';
	echo '<input type="button" id="btn-sailthru-remove-tags" name="btn-sailthru-remove-tags" class="button-secondary" value="Remove Tags" />';
	echo '<br />&nbsp;';
?>
			</div>
		</div>
	</td>
</tr>

	<tr class="odd">
		<?php
		//Suppost for zoninator filter.
		if ( function_exists( 'z_get_zones' ) ):
			$zones = z_get_zones();

			$filter_by_zone = 0;
			if ( ! empty( $sailthru_postsquery['filter_posts_by_zone'] ) ) {
				$filter_by_zone = 1;
			}

			$filtered_zones = array();
			if ( ! empty( $sailthru_postsquery['filter_zones'] ) ) {
				$filtered_zones = $sailthru_postsquery['filter_zones'];
			}
			?>
			<td style="vertical-align: top;">
				Filter Posts by Zone:
			</td>
			<td>
				<div id="zone_select_box">
					<input type='checkbox' name='posts[filter_posts_by_zone]' value='1' <?php checked( $filter_by_zone, 1 );?>/><br/>
					<?php foreach ( $zones as $zone ):
						$zone_checked = 0;
						if ( in_array( $zone->slug, $filtered_zones ) ) {
							$zone_checked = 1;
						}

						?>
						<input type='checkbox' name='posts[filter_zones][]' value='<?php echo esc_attr( $zone->slug ); ?>' <?php checked( 1, $zone_checked ); ?> /> <?php echo esc_html( $zone->name ); ?><br/>
					<?php
					endforeach;
					?>
				</div>
			</td>
		<?php
		endif;

		?>
	</tr>

	<tr>
	<td><label>Require featured post?</label></td>
	<td><input type="checkbox" name="posts[require_featured]"
			   value="1" <?php if ( !empty( $sailthru_postsquery['require_featured'] ) ) checked( $sailthru_postsquery['require_featured'], 1 ); ?>/>
	</td>
</tr>
<tr>
	<td><label>Automatically set featured post<br/> if none is available?</label></td>
	<td>
		<input type="checkbox" name="posts[auto_set_featured]" value="1" <?php  if ( !empty( $sailthru_postsquery['auto_set_featured'] ) ) checked( $sailthru_postsquery['auto_set_featured'], 1 ); ?>/>
	</td>
</tr>
<tr>
	<td><label>Only send when posts are found</td>
	<td>
		<input type="checkbox" name="posts[disable_empty_send]" value="1"
			<?php if ( !empty( $sailthru_postsquery['disable_empty_send'] ) ) : ?>

				<?php checked( $sailthru_postsquery['disable_empty_send'], 1 ); ?>

			<?php endif; ?>
		/>
	</td>
</tr>
<?php do_action( 'pmc-exacttarget-recurring-newsletter-featured-post-options-ui', $sailthru_postsquery ); ?>
<tr>
	<td colspan="2" style="border-bottom: 1px solid #000000; padding-bottom:40px;"></td>
</tr>

<tr>
	<td style="border-bottom: 1px solid #000000; padding-bottom:40px;">
		<label>
			Special feed handling by LOB's<br/>( For ex: Variety Charts ).<br/>Set text option here.
		</label>
	</td>
	<td style="border-bottom: 1px solid #000000; padding-bottom:40px;">
		<input type="text" name="posts[special_case]" value="<?php
			if( isset( $sailthru_postsquery['special_case'] ) ){
				echo esc_attr( $sailthru_postsquery['special_case'] );
			}
		?>">
	</td>
</tr>
<tr class="odd">
	<td>Schedule Frequency</td>
	<td>
		<div id="weekdays">
			<?php								 foreach ( $sailthru_schedule_days as $text => $day ):
			$checked = @in_array( $day, $sailthru_repeat['days'] ) ?>
			<input type='checkbox' name='days[]'
				   value="<?php echo $day ?>" <?php checked( $checked, true) ?>/> <?php echo $text ?><br/>
			<?php endforeach?>
		</div>
	</td>
</tr>
<tr>
	<td>Schedule Start Date<br/>(timezone:)</td>
	<td>Date: <input class="datepicker" name="posts[schedule_start_date]"
					 value="<?php $date_arr=""; if ( isset( $sailthru_postsquery['schedule_start_date'] ) )
						 $date_arr = explode( '/', $sailthru_postsquery['schedule_start_date'] );
						 if( sailthru_isset_notempty($date_arr) && isset( $date_arr[2] ) ){
						 	if( checkdate( $date_arr[0], $date_arr[1], $date_arr[2] ) )
						 		echo esc_attr( $sailthru_postsquery['schedule_start_date'] );
						 } else '';
					 ?>"/>
	</td>
</tr>
<tr>
	<td>Time:</td>
	<td><select name="hour" value="">
		<?php for ( $i = 11; $i < 23; $i++ ) {
		$t = sprintf( "%02d", ( $i % 12 + 1 ) );
		?>

		<option
			value="<?php echo $t ?>" <?php selected( substr( $sailthru_repeat['send_time'], 0, 2 ), $t ); ?>><?php echo $t ?></option>
		<?php } ?>
	</select>
		<select name="minute">
			<?php  for ( $i = 0; $i < 60; $i += 15 ) {
			$i = sprintf( '%02d', $i );
			?>
			<option
				value='<?php echo "$i" ?>' <?php selected( substr( $sailthru_repeat['send_time'], 3, 2 ), $i ); ?>><?php echo $i ?></option>
			";
			<?php } ?>
		</select>
		<select name="ampm">
			<option
				value="AM" <?php selected( substr( $sailthru_repeat['send_time'], -2 ), 'AM' );?>>
				AM
			</option>
			<option
				value="PM" <?php selected( substr( $sailthru_repeat['send_time'], -2 ), 'PM' ); ?>>
				PM
			</option>
		</select></td>
</tr>
<tr>
	<td></td>
	<td colspan="2">
		<input class="button-primary" type="submit" name="btn_addedit" id="btn_addedit" value=" Save "/>
	</td>
</tr>
</table>

<?php if ( isset( $sailthru_featured_post ) ): ?>
<h3>Featured Post</h3>
<table style="margin-top:20px; border: darkgrey solid thin" style="float:left;">
	<th>
		<td>Title</td>
		<td>Excerpt</td>
		<td>Image</td>
		<td><a href="<?php echo esc_url( $sailthru_featured_post->guid ) ?>">Preview</a></td>
	</th>
	<tr>
		<td><?php echo esc_html( $sailthru_featured_post->post_title); ?></td>
		<td><?php echo esc_html( $sailthru_featured_post->excerpt ); ?></td>
		<td><?php echo esc_url( sailthru_get_featured_image( $sailthru_featured_post ) );?></td>
		<td></td>
	</tr>
</table>
	<?php endif;?>
<input type="hidden" name="featured_post_id" value="<?php echo esc_attr($sailthru_featured_post_id) ?>"/>
		<input type="hidden" name="<?php echo $mmcnws_nonce_key; ?>" value="<?php print( $mmcnws_nonce ); ?>"/>
</form>
</div>
