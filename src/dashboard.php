<?php

namespace FrostyMedia\Includes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Dashboard
 *
 * @package FrostyMedia\Includes
 */
class Dashboard {

    /**
     * Dashboard constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );
    }

    public function admin_menu() {
        add_action( 'load-' . FROSTYMEDIA()->menu_page, array( $this, 'add_meta_boxes' ) );
    }

    public function add_meta_boxes() {
        wp_enqueue_script( 'common' );
        wp_enqueue_script( 'wp-lists' );
        wp_enqueue_script( 'postbox' );

        if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
            add_meta_box(
                FM_DIRNAME . '-debug',
                'var_dump( FROSTYMEDIA() )',
                function() {
                    var_dump( FROSTYMEDIA() );
                },
                FROSTYMEDIA()->menu_page,
                'bottom',
                'core'
            );
        }

        add_meta_box(
            FM_DIRNAME . '-notifications',
            __( 'Notifications', FM_DIRNAME ),
            array( $this, 'notifications_metabox' ),
            FROSTYMEDIA()->menu_page,
            'normal',
            'core'
        );

        add_meta_box(
            FM_DIRNAME . '-sidebar-1',
            __( 'License Status', FM_DIRNAME ),
            array( $this, 'license_status_metabox' ),
            FROSTYMEDIA()->menu_page,
            'side',
            'core'
        );
    }

    public function notifications_metabox( $data ) {
        include FM_PLUGIN_DIR . 'views/list-table.php';
    }

    public function license_status_metabox( $data ) {
        FROSTYMEDIA()->licenses->plugins_html( $minimum = true );
    }
}
