<?php
/**
 * Template part of hollywood executives metabox.
 *
 * @package pmc-variety-2017
 *
 * @since 2017-09-01 Milind More CDWE-499
 */

?>
<table id="celeb-meta-info">
	<tr>
		<td colspan="2">
			<textarea cols="90" rows="20" name="<?php echo esc_attr( $parent_right_col ); ?>"><?php echo esc_textarea( $right_col ); ?></textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td class="celeb-meta left">
			<?php esc_html_e( 'First Name', 'pmc-variety' ); ?>
		</td>
		<td>
			<input type="text" name="firstname" value="<?php echo esc_attr( $firstname ); ?>" />
		</td>
	</tr>
	<tr>
		<td class="celeb-meta left">
			<?php esc_html_e( 'Last Name', 'pmc-variety' ); ?>
		</td>
		<td>
			<input type="text" name="lastname" value="<?php echo esc_attr( $lastname ); ?>" />
		</td>
	</tr>
	<tr>
		<td class="celeb-meta left">
			<?php esc_html_e( 'Gender', 'pmc-variety' ); ?>
		</td>
		<td>
			<input type="radio" name="gender" id="gender_male" value="male" <?php checked( esc_attr( $gender ), 'male' ); ?> /> <label for="gender_male"><?php esc_html_e( 'Male', 'pmc-variety' ); ?></label>
			&nbsp;&nbsp;
			<input type="radio" name="gender" id="gender_female" value="female" <?php checked( esc_attr( $gender ), 'female' ); ?> /> <label for="gender_female"><?php esc_html_e( 'Female', 'pmc-variety' ); ?></label>
		</td>
	</tr>
	<tr>
		<td class="celeb-meta left">
			<?php printf( esc_html__( 'Nicknames, Aliases,%1$Misspellings, Character Names', 'pmc-variety' ), '<br />' ); ?>
		</td>
		<td>
			<textarea cols="50" rows="3" name="nicknames"><?php echo esc_textarea( $nicknames ); ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="celeb-meta left">
			<?php
			/* translators: string with em tag. */
			printf( '%1$s <em>( %2$s )</em>', esc_html__( 'Replace This Tag', 'pmc-variety' ), esc_html__( 'tag slug', 'pmc-variety' ) );
			?>
		</td>
		<td>
			<input type="text" name="replace_tag" value="<?php echo esc_attr( $replace_tag ); ?>" />
		</td>
	</tr>
	<tr>
		<td class="celeb-meta left">
			<?php esc_html_e( 'Date of Birth', 'pmc-variety' ); ?>
		</td>
		<td>
			<select name="dob_month">
				<?php
				// escaped previously
				// @codingStandardsIgnoreLine
				echo $dob_month;
				?>
			</select>
			<select name="dob_date">
				<?php
				// escaped previously
				// @codingStandardsIgnoreLine
				echo $dob_date;
				?>
			</select>
			<select name="dob_year">
				<?php
				// escaped previously.
				// @codingStandardsIgnoreLine
				echo $dob_year;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="celeb-meta left">
			<?php esc_html_e( 'Height', 'pmc-variety' ); ?>
		</td>
		<td>
			<select name="height_f">
				<?php
				// escaped previously.
				// @codingStandardsIgnoreLine
				echo $height_f;
				?>
			</select> <?php esc_html_e( 'foot', 'pmc-variety' ); ?>
			<select name="height_i">
				<?php
				// escaped previously.
				// @codingStandardsIgnoreLine
				echo $height_i;
				?>
			</select> <?php esc_html_e( 'inches', 'pmc-variety' ); ?>
		</td>
	</tr>
	<tr>
		<td class="celeb-meta left">
			<?php esc_html_e( 'Hometown', 'pmc-variety' ); ?>
		</td>
		<td>
			<input type="text" name="hometown" value="<?php echo esc_attr( $hometown ); ?>" />
		</td>
	</tr>
	<tr>
		<td class="celeb-meta left">
			<?php esc_html_e( 'State', 'pmc-variety' ); ?>
		</td>
		<td>
			<input type="text" name="state" value="<?php echo esc_attr( $state ); ?>" />
		</td>
	</tr>
	<tr>
		<td class="celeb-meta left">
			<?php esc_html_e( 'Country', 'pmc-variety' ); ?>
		</td>
		<td>
			<select name="country">
			<?php
			// escaped previously
			// @codingStandardsIgnoreLine
			echo $country;
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="celeb-meta left">
			<?php
			/* translators: string with em and br html tag. */
			printf( '%1$s <br /> (<em>%2$s</em>', esc_html__( 'Verified Twitter Handle', 'pmc-variety' ), esc_html__( 'must have @ prefix', 'pmc-variety' ) );
			?>
		</td>
		<td>
			<input type="text" name="twitter" value="<?php echo esc_attr( $twitter ); ?>" />
		</td>
	</tr>

	<tr>
		<td class="celeb-meta left">
			<?php esc_html_e( 'In a Relationship With', 'pmc-variety' ); ?>
		</td>
		<td>
			<input type="text" name="<?php echo esc_attr( $parent_relation_name ); ?>" value="<?php echo esc_attr( $relation_name ); ?>" />
			&nbsp;&nbsp;&nbsp;&nbsp;
			<?php
			/* translators: string with em tag. */
			printf( '%1$s <em>(%2$s):</em>', esc_html__( 'Profile Slug', 'pmc-variety' ), esc_html__( 'optional', 'pmc-variety' ) );
			?>
			<input type="text" name="<?php echo esc_attr( $parent_relation_slug ); ?>" value="<?php echo esc_attr( $relation_slug ); ?>" />
		</td>
	</tr>

	<tr>
		<td class="celeb-meta left">
			<?php
			/* translators: string with em and br tag. */
			printf( '%1$s <br /> (<em>%2$s</em>)', esc_html__( 'Quotes', 'pmc-variety' ), esc_html__( 'separate with one blank line between two quotes', 'pmc-variety' ) );
			?>
		</td>
		<td>
			<textarea cols="66" rows="10" name="quotes"><?php echo esc_textarea( $quotes ); ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="celeb-meta left">
			<?php esc_html_e( 'Last modified', 'pmc-variety' ); ?>
		</td>
		<td>
			<?php
			if ( ! empty( $time ) ) {
				echo esc_html( date( 'Y-m-d H:i:s', $time ) );
			}

			wp_nonce_field( 'hollywood-exec-profile-admin', 'hollywood_exec_profile_admin_noncefield' );
			?>
			<input type="hidden" name="hid_exec" value="exec" />
		</td>
	</tr>
</table>
