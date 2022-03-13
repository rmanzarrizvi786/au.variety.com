<?php
/**
 * Helper functions.
 */

function pmc_sp_get_amazon_store_id() {
	return defined( 'PMC_SP_AMAZON_STORE_ID' ) ? PMC_SP_AMAZON_STORE_ID : '';
}

function pmc_sp_get_amazon_api_access_key() {
	return defined( 'PMC_SP_AMAZON_API_ACCESS_KEY' ) ? PMC_SP_AMAZON_API_ACCESS_KEY : '';
}

function pmc_sp_get_amazon_api_access_secret() {
	return defined( 'PMC_SP_AMAZON_API_ACCESS_SECRET' ) ? PMC_SP_AMAZON_API_ACCESS_SECRET : '';
}
