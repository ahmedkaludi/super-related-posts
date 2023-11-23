<?php
/**
 * WP Settings API
 * @since 1.5
*/
add_action('admin_init', 'srpp_settings_init');
function srpp_settings_init()
{
	register_setting('srp_data_group', 'srp_data');

	add_settings_section('srp_settings_section', __return_false(), '__return_false', 'srp_settings_section');
	add_settings_field(
		'general_settings',					// ID
		'', 								// Title
		'srp_settings_page_callback',		// Callback
		'srp_settings_section', 			// Page Slug
		'srp_settings_section' 				// Section ID
	);
}

/**
 * @since 1.5
 * */
function srp_settings_page_callback()
{
	
}
