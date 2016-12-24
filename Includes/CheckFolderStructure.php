<?php

namespace FrostyMedia\Includes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CheckFolderStructure {

    const OPTION_NAME = 'frosty_media_GitHub_folder_renamed';

    /**
     *
     */
    public function init() {

        // When this plugin deactivate, deactivate another plugin too.
        register_deactivation_hook( __FILE__, function() {

            $dependent = 'frosty-media/frosty-media.php';

            if ( ! is_plugin_active( $dependent ) ) {

                add_action( 'update_option_active_plugins', function() use ( $dependent ) {
                    activate_plugin( $dependent );
                } );
            }
        } );

        if ( get_option( self::OPTION_NAME, false ) === true ) {
            return;
        }

        if ( stristr( FM_DIRNAME, 'master' ) ) {
            if ( rename( untrailingslashit( FM_PLUGIN_DIR ), str_replace( '-master', '', untrailingslashit( FM_PLUGIN_DIR ) ) ) ) {
                add_option( self::OPTION_NAME, true );

                // Lets be sure the activate & deactivate functions are present.
                if ( ! function_exists( 'activate_plugin' ) ) {
                    require( ABSPATH . 'wp-admin/includes/plugin.php' );
                }

                // Lets deactivate the old plugin.
                deactivate_plugins( 'frosty-media-master/frosty-media.php' );

                // Redirect to the dashboard.
                wp_safe_redirect( admin_url() );
                exit;
            }
        }
    }

}

add_action( 'plugins_loaded', array( new CheckFolderStructure(), 'init' ), - 99 );
