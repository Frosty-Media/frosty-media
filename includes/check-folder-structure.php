<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function frosty_media_check_folder_structure() {

    // When this plugin deactivate, deactivate another plugin too.
    register_deactivation_hook( __FILE__, function() {

        $dependent = 'frosty-media/frosty-media.php';

        if( !is_plugin_active($dependent) ){

            add_action( 'update_option_active_plugins', function() use ( $dependent ) {
                activate_plugin( $dependent );
            });
        }
    });
	
	if ( true === get_option( 'frosty_media_GitHub_folder_renamed', false ) )
		return;
	
	if ( stristr( FM_DIRNAME, 'master' ) ) {
		if ( rename( untrailingslashit( FM_PLUGIN_DIR ), str_replace( '-master', '', untrailingslashit( FM_PLUGIN_DIR ) ) ) ) {
			add_option( 'frosty_media_GitHub_folder_renamed', true );
			
			// Lets be sure the activate & deactivate functions are present.
			if ( !function_exists( 'activate_plugin' ) ) {
				require( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

            // Lets deactivate the old plugin.
			deactivate_plugins( 'frosty-media-master/frosty-media.php' );
			
			// Redirect to the dashboard.
			wp_redirect( admin_url() );
			exit;
		}
	}
	//die( FM_PLUGIN_DIR );
}
add_action( 'plugins_loaded', 'frosty_media_check_folder_structure', -99 );