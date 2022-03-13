<?php
if ( empty( $provider_id ) ) {
	return;
}

$ad_promo         = PMC_Ads::get_instance()->get_ad_property( 'ad-promo', $ad );
$ad_campaign_name = PMC_Ads::get_instance()->get_ad_property( 'ad-campaign-name', $ad );
$ad_creative      = PMC_Ads::get_instance()->get_ad_property( 'ad-creative', $ad );
$ad_url           = PMC_Ads::get_instance()->get_ad_property( 'ad-url', $ad );
$ad_image_url     = PMC_Ads::get_instance()->get_ad_property( 'ad-image', $ad );

?>
<div class="adm-column-2">
	<div class="adm-input form-required">
		<label for="<?php echo esc_attr( $provider_id . '-ad-promo' ); ?>" class="required">
			<strong><?php esc_html_e( 'Ad Promo Code', 'pmc-adm' ); ?></strong>
		</label>
		<br>
		<input
			type="text"
			name="ad-promo"
			id="<?php echo esc_attr( $provider_id . '-ad-promo' ); ?>"
			placeholder="Ad Promo Code (require) ex. 87K1DLNED"
			value="<?php echo esc_attr( $ad_promo ); ?>">
	</div>

	<div class="adm-input form-required">
		<label for="<?php echo esc_attr( $provider_id . '-ad-campaign-name' ); ?>" class="required">
			<strong><?php esc_html_e( 'Ad Campaign Name', 'pmc-adm' ); ?></strong>
		</label>
		<br>
		<input
			type="text"
			name="ad-campaign-name"
			id="<?php echo esc_attr( $provider_id . '-ad-campaign-name' ); ?>"
			placeholder="Ad Creative (require) ex. Top Banner"
			value="<?php echo esc_attr( $ad_campaign_name ); ?>">
	</div>

	<div class="adm-input form-required">
		<label for="<?php echo esc_attr( $provider_id . '-ad-creative' ); ?>" class="required">
			<strong><?php esc_html_e( 'Ad Creative', 'pmc-adm' ); ?></strong>
		</label>
		<br>
		<input
			type="text"
			name="ad-creative"
			id="<?php echo esc_attr( $provider_id . '-ad-creative' ); ?>"
			placeholder="Ad Campaign (require) ex. Black Friday"
			value="<?php echo esc_attr( $ad_creative ); ?>">
	</div>
	<div class="adm-input form-required">
		<label for="<?php echo esc_attr( $provider_id . '-ad-url' ); ?>" class="required">
			<strong><?php esc_html_e( 'Ad URL( Enter Ad URL )', 'pmc-adm' ); ?></strong>
		</label>
		<br>
		<input
			type="text"
			name="ad-url"
			id="<?php echo esc_attr( $provider_id . '-ad-url' ); ?>"
			size="50"
			placeholder="campaign URL"
			value="<?php echo esc_attr( $ad_url ); ?>">
	</div>

	<div class="adm-input form-required">
		<label for="<?php echo esc_attr( $provider_id . '-ad-image' ); ?>" class="required">
			<strong><?php esc_html_e( 'Ad Image', 'pmc-adm' ); ?></strong>
		</label>
		<br>
		<div class="attachment">
			<div class="attachment-preview js--select-attachment type-image subtype-png landscape">
				<div class="thumbnail">
					<div class="centered">
						<img
							data-id=""
							id="ad-image-preview"
							draggable="false"
							alt=""
							src="<?php echo esc_url( $ad_image_url ); ?>">
					</div>
				</div>
			</div>
			<button type="button" class="check" tabindex="0">
				<span class="media-modal-icon"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Deselect', 'pmc-adm' ); ?></span>
			</button>
		</div>
		<input
			type="button"
			name="ad-image"
			id="upload-ad-image"
			class="button"
			value="<?php esc_attr_e( 'Upload image', 'pmc-adm' ); ?>"/>
		<input
			type="hidden"
			name="ad-image"
			id="ad-image"
			value="<?php echo esc_attr( $ad_image_url ); ?>">
	</div>
</div>
