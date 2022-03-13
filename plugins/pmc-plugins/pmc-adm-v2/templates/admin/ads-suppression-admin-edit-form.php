<div class="form-wrap">
	<h2>
		<?php if ( ! empty( $data['id'] ) ) : ?>
			Update Schedule
		<?php else : ?>
			Add New Schedule
		<?php endif ?>
	</h2>
	<form id="addtag" method="post" action="<?php echo esc_url( $instance->form_url ); ?>" class="validate">

		<?php $instance->nonce->render(); ?>
		<input type="hidden" name="id" value="<?php echo esc_attr( $data['id'] ); ?>"/>
		<input type="hidden" name="action" id="action" value="save"/>

		<div class="form-field form-required term-name-wrap">
			<label for="name">Name <span class="required">*</span></label>
			<input name="name" id="tag-name" type="text" value="<?php echo esc_attr( $data['name'] ); ?>" size="40" aria-required="true">
			<p>The name to identify the ads suppression schedule</p>
		</div>

		<div class="form-field form-required">
			<label for="tag-apply-to">Ads suppress apply to</label>
			<select name="apply_to" id="tag-apply-to">
				<option value="all" <?php echo in_array('all', (array) $data['apply_to'], true ) ? 'selected' : ''; ?>>All Ads</option>
				<option value="jwplayer" <?php echo in_array('jwplayer', (array) $data['apply_to'], true ) ? 'selected' : ''; ?>>JWPlayer Only</option>
			</select>
		</div>

		<div class="form-field form-required">
			<label for="tag-start">Start Date & Time (YYYY-MM-DD HH:MM) <span class="required">*</span></label>
			<input name="start" id="tag-start" type="text" value="<?php echo esc_attr( $schedule['start'] ); ?>" size="20" aria-required="true" class="datetimepicker">
			<p>Timezone: <?php echo esc_html( $timezone ); ?></p>
		</div>

		<div class="form-field form-required">
			<label for="tag-end">End Date & Time (YYYY-MM-DD HH:MM) </label>
			<input name="end" id="tag-end" type="text" value="<?php echo esc_attr( $schedule['end'] ); ?>" size="20" class="datetimepicker">
			<p>Timezone: <?php echo esc_html( $timezone ); ?></p>
		</div>

		<div class="form-field">
			<label for="tag-target-tags">Target post tags (comma delimited)</label>
			<textarea name="target_tags" id="tag-target-tags" rows="3" cols="40"><?php echo esc_html( implode( ', ', (array) $data['target_tags'] ) ); ?></textarea>
		</div>

		<div class="form-field term-description-wrap">
			<label for="tag-description">Notes</label>
			<textarea name="description" id="tag-description" rows="2" cols="40"><?php echo esc_html( $data['description'] ); ?></textarea>
		</div>

		<p class="submit">
			<?php if ( ! empty( $data['id'] ) ) : ?>
				<input type="submit" id="submit" class="button button-primary" value="Update">
				<input type="submit" id="cancel" class="button button-primary" value="Cancel">
			<?php else : ?>
				<input type="submit" id="submit" class="button button-primary" value="Add New Schedule">
			<?php endif ?>
			<span class="spinner"></span>
		</p>
	</form>
</div>

<script>
jQuery(function ($) {
    var submittingForm = false;

    $('.datetimepicker').datetimepicker({
        format: 'Y-m-d H:i',
        formatDate: 'Y-m-d',
        formatTime: 'H:i',

        // Supporting older version of datetimepicket
        timeFormat: 'H:i',
        dateFormat: 'Y-m-d',
    });

    $('#cancel').on('click', function() {
        var form = $(this).parents('form');
        form.find('#action').val('cancel')
    });

    $('#submit').on('click', function () {
        var form = $(this).parents('form');

        var nodeName = form.find('#tag-name');
        var nodeStart = form.find('#tag-start');
        var nodeEnd = form.find('#tag-end');
        var formValid = true;

        form.find('.form-required').removeClass('form-invalid');

        if (!nodeName.val()) {
            nodeName.parent('div').addClass('form-invalid')
            formValid = false;
        }

        if (!nodeStart.val() || !nodeStart.val().match(/^\d{4}-\d{1,2}-\d{1,2}(?:\s\d{1,2}:\d{1,2})?$/)) {
            nodeStart.parent('div').addClass('form-invalid')
            formValid = false;
        }

        if (nodeEnd.val() && !nodeEnd.val().match(/^\d{4}-\d{1,2}-\d{1,2}(?:\s\d{1,2}:\d{1,2})?$/)) {
            nodeEnd.parent('div').addClass('form-invalid')
            formValid = false;
        }

        if (!formValid) {
            return false;
        }

        if (submittingForm) {
            return false;
        }

        submittingForm = true;
        form.find('.submit .spinner').addClass('is-active');

    });

    $('#addtag').submit(function(){
        $(this).find('input[type=submit]').prop('disabled', true);
    });

});
</script>
