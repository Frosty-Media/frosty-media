<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function frosty_media_check_folder_structure() {
	
	if ( true === get_option( 'frosty_media_GitHub_folder_renamed', false ) )
		return;
	
	if ( stristr( FM_DIRNAME, 'master' ) ) {
		if ( rename( untrailingslashit( FM_PLUGIN_DIR ), str_replace( '-master', '', untrailingslashit( FM_PLUGIN_DIR ) ) ) ) {
			add_option( 'frosty_media_GitHub_folder_renamed', true );
			wp_redirect( admin_url() );
			exit;
		}
	}
	//die( FM_PLUGIN_DIR );
}
add_action( 'plugins_loaded', 'frosty_media_check_folder_structure', -99 );