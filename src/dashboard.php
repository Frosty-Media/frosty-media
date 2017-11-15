<?php

namespace FrostyMedia\Includes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Dashboad
 *
 * @package     FrostyMedia
 * @subpackage  Classes/Common
 * @author      Austin Passy <http://austin.passy.co>
 * @copyright   Copyright (c) 2015, Austin Passy
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @ref         https://gist.github.com/bueltge/757903
 */
class Dashboard {

    /**
     * Constructor.
     */
    function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );
    }

    /**
     *
     */
    function admin_menu() {
        add_action( 'load-' . FROSTYMEDIA()->menu_page, array( $this, 'add_meta_boxes' ) );
    }

    /**
     *
     */
    function add_meta_boxes() {
        wp_enqueue_script( array( 'common', 'wp-lists', 'postbox' ) );

        $title = FM_DIRNAME;

        if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
            add_meta_box(
                $title . '-debug',
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
            $title . '-notifications',
            __( 'Notifications', FM_DIRNAME ),
            array( $this, 'notifications_metabox' ),
            FROSTYMEDIA()->menu_page,
            'normal',
            'core'
        );

        add_meta_box(
            $title . '-sidebar-1',
            __( 'License Status', FM_DIRNAME ),
            array( $this, 'license_status_metabox' ),
            FROSTYMEDIA()->menu_page,
            'side',
            'core'
        );
    }

    /**
     *
     */
    function notifications_metabox( $data ) {
        include( FM_PLUGIN_DIR . 'views/list-table.php' );
    }

    /**
     *
     */
    function license_status_metabox( $data ) {
        FROSTYMEDIA()->licenses->plugins_html( $minimum = true );
    }

}