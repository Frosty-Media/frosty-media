<?php

namespace FrostyMedia\Includes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Notifications
 *
 * @package     FrostyMedia
 * @subpackage  Classes/Frosty_Media_Notifications
 * @author      Austin Passy <http://austin.passy.co>
 * @copyright   Copyright (c) 2015, Austin Passy
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Notifications {

    /**
     * Variables
     *
     * @since 1.0.0
     * @type string
     */
    protected $dirname;
    protected $title;
    protected $action;
    protected $api_url;

    /**
     * Notifications constructor.
     */
    public function __construct() {

        $this->dirname = FM_DIRNAME;
        $this->title   = __( 'Notifications', FM_DIRNAME );
        $this->action  = sanitize_title_with_dashes( $this->dirname . ' ' . $this->title );
        $this->api_url = add_query_arg( 'get_notifications', 'true', FM_API_URL );
        $this->handle  = $this->action;

        add_action( 'admin_menu', array( $this, 'admin_menu' ), 19 );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'wp_ajax_' . $this->action, array( $this, 'ajax' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Register the plugin page
     */
    public function admin_menu() {
        add_submenu_page(
            $this->dirname,
            sprintf( 'Frosty Media %s %s', $this->title, __( 'Submenu Page', FM_DIRNAME ) ),
            sprintf( '%s', $this->title ),
            'manage_options',
            trailingslashit( FM_DIRNAME ) . strtolower( $this->title ),
            array( $this, 'plugin_page' )
        );
    }

    /**
     * Display the plugin settings options page
     */
    public function plugin_page() {
        include( FM_PLUGIN_DIR . 'views/list-table.php' );
    }

    /**
     *
     */
    public function admin_init() {
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    /**
     * Registers and enqueues admin-specific JavaScript.
     */
    public function enqueue_scripts() {
        wp_enqueue_style( $this->handle, trailingslashit( FM_PLUGIN_URL ) . 'css/admin.css', false, FM_VERSION, 'screen' );

        wp_register_script( $this->handle, trailingslashit( FM_PLUGIN_URL ) . 'js/admin.js', array( 'jquery' ), FM_VERSION, false );
        wp_enqueue_script( $this->handle );

        $args = array(
            'action'  => $this->action,
            'handle'  => $this->handle,
            'dirname' => FM_DIRNAME,
            'nonce'   => wp_create_nonce( FM_PLUGIN_BASENAME . $this->action . '-nonce' ),
            'loading' => admin_url( '/images/wpspin_light.gif' ),
        );
        wp_localize_script( $this->handle, str_replace( '-', '_', $this->handle ), $args );
    }

    /**
     * Helper function to get the notices array and return them.
     * Look into updating $delete_all to maybe just wipe the latest KEY or just the latest KEY read/read_date. -
     * 01/05/2015
     */
    public function get_notices( $delete_all = false ) {
        $option  = get_option( FM_DIRNAME, array() );
        $title   = strtolower( $this->title );
        $notices = isset( $option[ $title ] ) ? $option[ $title ] : array();

        if ( $delete_all ) {
            $option[ $title ] = array();

            delete_transient( Common::get_transient_key( FM_DIRNAME . '_notifications' ) );
            update_option( FM_DIRNAME, $option );
        }

        return $notices;
    }

    /**
     * Helper function to update the notices.
     *
     * @since 1.0.0
     */
    public function maybe_update_notices( $new_notice ) {
        $option   = get_option( FM_DIRNAME, array() );
        $title    = strtolower( $this->title );
        $_notices = isset( $option[ $title ] ) ? $option[ $title ] : array();

        if ( isset( $_notices[ key( $_notices ) ] ) && $_notices[ key( $_notices ) ]->date < $new_notice[ key( $new_notices ) ]->date ) {
            array_unshift( $_notices, $new_notice[ key( $new_notice ) ] ); // Add the new notice to the beginning of the array.
            $_notices[]       = $new_notice;
            $_notices         = array_filter( $_notices, array( $this, 'is_not_null' ) );
            $option[ $title ] = $_notices;
            update_option( FM_DIRNAME, $option );    // Update the new notices
        } // First time install...
        elseif ( empty( $_notices ) ) {
            $option[ $title ] = $new_notice;
            update_option( FM_DIRNAME, $option );    // Update the new notices
        }

        return $_notices;
    }

    /**
     * Renders the administration notice.
     * Also renders a hidden nonce used for security when processing the AJAX request.
     */
    public function admin_notices() {
        $notices = $this->get_notices(); // Remove 'true' to delete ALL.
        $trankey = Common::get_transient_key( FM_DIRNAME . '_notifications' );

        if ( empty( $notices ) || false === ( get_transient( $trankey ) ) ) {
            $notices = $this->wp_remote_get( $this->api_url, $trankey );
        }

        // If it's not an array, lets bail.
        if ( empty( $notices ) ) {
            return;
        }

        end( $notices );
        $key_id = key( $notices ); // Fetches the key of the element pointed to by the internal pointer
        $notice = $notices[ $key_id ]; // Get latest notice.

        // In case there is an error and a null key gets entered.
        if ( null === $notice ) {
            return;
        }

        // If the latest notice has been read, lets bail.
        if ( true === $notice->read ) {
            return;
        }

        $html = '<div id="' . $this->handle . '" class="updated"><p>';

        $html .= sprintf( '%s %s %s',
            get_frosty_media_screen_icon( 'margin:-2px 10px 0 0; width:24px;' ),
            $notice->message,
            sprintf( '<span class="alignright"> <a href="#" id="%1$s[%2$s]" data-notice-id="%2$s">%3$s</a></span>',
                $this->handle,
                $key_id,
                esc_html__( 'Mark as read', FM_DIRNAME )
            )
        );

        $html .= '</p></div>';

        echo $html;
    }

    /**
     * Helper function to make remote calls
     *
     * @since 1.0.0
     *
     * @param bool $url
     * @param      $transient_key
     * @param null $expiration
     *
     * @return array|bool|mixed|object
     */
    public function wp_remote_get( $url = false, $transient_key, $expiration = null ) {
        if ( ! $url ) {
            return false;
        }

        $expiration = null !== $expiration ? $expiration : DAY_IN_SECONDS * 2;

        if ( false === ( $json = get_transient( $transient_key ) ) ) {

            $response = wp_remote_get(
                esc_url_raw( $url ),
                array(
                    'timeout'   => 15,
                    'sslverify' => false,
                )
            );

            if ( ! is_wp_error( $response ) ) {

                if ( isset( $response['body'] ) && strlen( $response['body'] ) > 0 ) {

                    $json = json_decode( wp_remote_retrieve_body( $response ) );

                    // For when I mess up the JSON or github is down.
                    if ( is_wp_error( $json ) ) {
                        return false;
                    }

                    // Cache the results for '$expiration'
                    set_transient( $transient_key, $json, $expiration );
                    $json = $this->maybe_update_notices( $json );

                    // Return the data
                    return $json;
                }
            } else {
                return false; // Error, lets return!
            }
        }

        return $json;
    }

    /**
     * JavaScript callback used to hide the administration notice when the 'Dismiss' anchor is clicked on the front end.
     */
    public function ajax() {
        check_ajax_referer( FM_PLUGIN_BASENAME . $this->action . '-nonce', 'nonce' );

        if ( ! isset( $_POST['notice_id'] ) || ! is_numeric( $_POST['notice_id'] ) ) {
            die( '0' );
        }

        $option = get_option( FM_DIRNAME, array() );
        $option = array_filter( $option, array( $this, 'is_not_null' ) );
        $title  = strtolower( $this->title );
        $key_id = absint( $_POST['notice_id'] );

        if ( ! isset( $option[ $title ][ $key_id ] ) ) {
            die( '0' );
        }

        $option[ $title ][ $key_id ]->read      = true;
        $option[ $title ][ $key_id ]->read_date = date_i18n( 'c', time() );

        if ( update_option( FM_DIRNAME, $option ) ) {
            die( '1' );
        } else {
            die( '0' );
        }
    }

    /**
     * @param $var
     * @ref http://stackoverflow.com/a/20676918
     *
     * @return bool
     */
    protected function is_not_null( $var ) {
        return ! is_null( $var );
    }
}