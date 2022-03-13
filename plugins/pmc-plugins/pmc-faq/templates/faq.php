<?php
if ( ! isset( $title ) || ! isset( $description ) || ! isset( $questions ) || ! is_array( $questions ) ) {
	return;
}
?>
<div class="pmc-faq lrv-u-margin-b-150">
	<div class="pmc-faq-header lrv-u-margin-b-150">
		<h2 class="pmc-faq-title">
			<?php echo esc_html( $title ); ?>
		</h2>
		<p class="pmc-faq-description">
			<?php echo esc_html( $description ); ?>
		</p>
	</div>
	<div class="pmc-faq-body lrv-u-background-color-grey-lightest lrv-u-padding-a-2 lrv-u-padding-a-125@mobile-max" itemscope itemtype="https://schema.org/FAQPage">
		<?php
		foreach ( $questions as $question ) {
			if ( ! is_array( $question ) ) {
				continue;
			}
			?>
			<div class="pmc-faq-questions" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
				<div class="pmc-faq-question lrv-u-font-size-24 lrv-u-font-size-18@mobile-max lrv-u-margin-b-075">
					<strong itemprop="name">
						<?php echo esc_html( $question['pmc_faq_question'] ); ?>
					</strong>
				</div>
				<div class="pmc-faq-answer lrv-u-font-size-18 lrv-u-margin-b-150 lrv-u-margin-l-150 lrv-u-margin-l-00@mobile-max" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
					<div itemprop="text">
						<?php echo wp_kses_post( $question['pmc_faq_answer'] ); ?>
					</div>
				</div>
			</div>
			<?php
		}
		?>
	</div>
</div>
