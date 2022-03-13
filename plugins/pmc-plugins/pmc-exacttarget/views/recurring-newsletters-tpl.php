<?php
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
?>
<div class="wrap">
	<h2>Options</h2>

	<h2>Newsletters</h2>
	<a href="<?php menu_page_url( 'sailthru_recurring_newsletter' );?>" id="add_alert_type">Add
		Newsletter</a>

	<table cellspacing="2" cellpadding="2" class="widefat editablecontainerNWS">
		<tbody>
		<tr style="height: 1.5em;" class="alternate">
			<td align="left"><strong>TITLE</strong></td>
			<td align="left"><strong>DATA EXTENSIONS</strong></td>
			<td align="left"><strong>TEMPLATE</strong></td>
			<td align="left"><strong>FREQUENCY</strong></td>
			<td align="left"><strong>STATUS</strong></td>
			<td align="left" ><strong>ACTIONS</strong></td>
		</tr>
		<?php
		if ( sailthru_isset_notempty( $sailthru_repeats ) ) {
			$et_data_extensions = \PMC\Exacttarget\Cache::get_instance()->get_data_extensions();
			foreach ( $sailthru_repeats as $id=>$sailthru_repeat ) {
				$sailthru_days = '';
				if ( ! empty( $sailthru_repeat['days'] ) ) {
					$days = \pmc_et_maybe_decode( $sailthru_repeat['days'] );
					if ( $days && is_array( $days ) ) {
						$sailthru_days = implode( ',', $days );
					}
				}
				$data_extension =  $sailthru_repeat['dataextension'];
				if ( ! empty( $et_data_extensions[ $data_extension ] ) ) {
					$data_extension = $et_data_extensions[ $data_extension ];
				}

				if ( ! empty( $sailthru_repeat['content_builder'] ) && 'yes' === $sailthru_repeat['content_builder'] && ! empty( $content_builder_templates[ $sailthru_repeat['template'] ] ) ) {
					$template = $content_builder_templates[ $sailthru_repeat['template'] ];
				} else {
					$template = $sailthru_templates[ $sailthru_repeat['template'] ];
				}

				$newsletter_name = ( ! empty( $sailthru_repeat['content_builder'] ) && 'yes' === $sailthru_repeat['content_builder'] ) ? $sailthru_repeat['name'] . ' (CB)' : $sailthru_repeat['name'];

				if ( ! empty( $sailthru_templates[ $sailthru_repeat['template'] ] ) ) {
					$template = $sailthru_templates[ $sailthru_repeat['template'] ];
				}

				$pause_class = '';
				$play_class  = 'et-nl-state-active';
				if ( ! empty( $sailthru_repeat['state'] ) ) {
					if ( 'play' == $sailthru_repeat['state'] ) {
						$pause_class = '';
						$play_class  = 'et-nl-state-active';
					} elseif ( 'pause' == $sailthru_repeat['state'] ) {
						$play_class  = '';
						$pause_class = 'et-nl-state-active';
					}
				}

				?>
				<tr title="<?php echo  esc_html( $sailthru_repeat['name'] ); ?>" data-feed-ref="<?php echo esc_attr( $sailthru_repeat['feed_ref'] ); ?>">
						<td class="recurring-newsletter-name"><?php echo  esc_html( $newsletter_name ); ?></td>
						<td><?php echo esc_html( $data_extension ); ?></td>
						<td><?php echo esc_html( $template ); ?></td>
						<td><?php echo esc_html( $sailthru_days ); ?></td>
						<td id="status-<?php echo esc_attr( $sailthru_repeat['repeat_id'] ); ?>">
							<?php
							if ( 'pause' === $sailthru_repeat['state'] ) {
								echo 'Paused';
							} else {
								echo 'Active';
							}
							?>
						</td>
						<td>
							<a href="<?php echo esc_url( add_query_arg( [ 'feed'=>'sailthru', 'repeathash' => $sailthru_repeat['feed_ref'] ], get_home_url() ) ); ?>">View Feed</a>
							| <a href="#" class="test-dialog-open" rel="<?php echo esc_attr( $sailthru_repeat['repeat_id'] ); ?>">TEST</a>
							| <a href="#" class="send-newsletter-now" rel="<?php echo esc_attr( $sailthru_repeat['repeat_id'] ); ?>">SEND NOW</a>
							| <a href="<?php echo esc_url( menu_page_url( 'sailthru_recurring_newsletter', false ) . "&id={$sailthru_repeat['repeat_id']}" ); ?>">Edit</a>
							| <a class="delete_newsletter" href="<?php echo esc_url( menu_page_url( 'sailthru_recurring_newsletters', false ) . "&delete={$sailthru_repeat['repeat_id']}" . '&' . $mmcnws_nonce_key . '=' . $mmcnws_nonce ); ?>">Delete</a>
							| <a href="#" class="status-toggle" data-status="<?php echo esc_attr( $sailthru_repeat['state'] ); ?>" data-repeat-id="<?php echo esc_attr( $sailthru_repeat['repeat_id'] ); ?>">
								<?php
								if ( 'pause' === $sailthru_repeat['state'] ) {
									echo 'Activate';
								} else {
									echo 'Deactivate';
								}
								?>
							</a>
					</td>
				</tr>
				<?php
			}
		}
		?>

		</tbody>
	</table>
	<div style="display:none;background: #f0f0b8;padding-top: 40px;padding-left: 10px;" id="test-email-dialog">
		<select name="list" id="et-email-list">
			<?php
			$email_lists = Exact_Target::get_lists();
			foreach ( $email_lists as $value => $name ) {
				if ( false === stripos( $name, 'editorial test' ) ) {
					continue;
				}
				?>
				<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $name ); ?></option>
			<?php
			} ?>
		</select>
		<input type="hidden" id="blast-repeat-id"/>
		<button class="test-recurring-newsletter">Send Test</button>
	</div>

	<script type="text/javascript">
		setTimeout(function () {
			jQuery(document).ready(function () {

				jQuery('.test-recurring-newsletter').click(function () {
					var data = {
						action:'sailthru_test_send_repeat',
						blast_repeat_id:jQuery("#blast-repeat-id").val(),
						email_list_id:jQuery( "select#et-email-list").val(),
						<?php echo $mmcnws_nonce_key.":'".$mmcnws_nonce."'"; ?>
					};
					jQuery.post(ajaxurl, data, function (response) {
						if (response.error) {
							alert(response.error);
						} else {
							if ( typeof response === 'string' ) {
								new_msg = jQuery.parseJSON(response);
								if (new_msg.error) {
									alert( response );
								} else {
									alert( "Blast Sent Successfully" );
								}
							} else {
								alert( "Blast Sent Successfully" );
							}
						}
						jQuery('#test-email-dialog').dialog("close");
					});
				});
				jQuery('.test-dialog-open').click(function () {
					jQuery('#test-email-dialog').dialog();
					jQuery('#test-email-dialog').dialog("open");
					jQuery("#blast-repeat-id").val(jQuery(this).attr('rel'));
					return false;
				});
				jQuery('.send-newsletter-now').click(function () {
					var answer, newsletter_name;
					newsletter_name = jQuery(this).parents('tr').attr('title');
					answer = confirm("Are you sure you want to send the " + newsletter_name + " newsletter now?");
					if (answer === true) {
						var data = {
							action:'sailthru_send_repeat_now',
							repeat_id:jQuery(this).attr('rel'),
							<?php echo $mmcnws_nonce_key.":'".$mmcnws_nonce."'"; ?>
						};
						jQuery.post(ajaxurl, data, function (response) {
							if (response.error) {
								alert(response.error);
								//return false;
							} else if (response.confirm) {
								var answer = confirm(response.confirm);
								if (answer) {
									data.confirm = 1;
									jQuery.post(ajaxurl, data, function (response) {
										if (response.error) {
											alert(response.error);
										} else {
											alert(response);
											return true;
										}
									});
								}
							} else {
								return true;
							}
						}, 'json');
					} else {
						return false;
					}
				})
			});
		}, 200);

		jQuery('.status-toggle').click(function () {
			var current_node = jQuery(this);
			var repeat_id    = current_node.data('repeat-id');
			var status       = current_node.data('status');
			var new_status   = 'play' === status ? 'pause' : 'play';
			var data = {
				action: 'exacttarget_pause_newsletter',
				blast_repeat_id: repeat_id,
				state: new_status,
				_mmcnws_recurring_nonce: jQuery("#et-nl-nonce").val()
			};
			jQuery.post(ajaxurl, data, function (response) {
				if (response.error) {
					alert(response.error);
				} else {
					new_msg = jQuery.parseJSON(response);
					if (new_msg.error) {
						alert(response);
					} else {
						current_node.data('status',new_status);
						if ( 'pause' === new_status ) {
							jQuery('#status-' + repeat_id).html('Paused');
							current_node.html('Activate');
						} else {
							jQuery('#status-' + repeat_id).html('Active');
							current_node.html('Deactivate');
						}
					}
				}
			});
		});
	</script>

			<input id="et-nl-nonce" type="hidden" name="<?php echo esc_attr( $mmcnws_nonce_key ); ?>" value="<?php echo esc_attr( $mmcnws_nonce ); ?>"/>
</div>
