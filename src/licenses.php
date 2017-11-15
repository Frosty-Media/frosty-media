<?php

namespace FrostyMedia\Includes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Licenses
 *
 * @package FrostyMedia
 * @subpackage Classes/Frosty_Media_Licenses
 * @author Austin Passy <http://austin.passy.co>
 * @copyright Copyright (c) 2015 - 2017, Austin Passy
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Licenses {

    /** @var string $title */
    protected $title;

    /** @var string $action */
    protected $action;

    /** @var string $api_url */
    protected $api_url;

    /** @var string $submenu_page */
    protected $submenu_page;

    /** @var string $handle */
    protected $handle;

    /**
     * Variables
     *
     * @since 1.0.0
     * @var array
     */
    protected $strings = [];

    /**
     * Plugins array
     *
     * @var array $plugins
     */
    private $plugins = [];

    /**
     * Constructor.
     */
    public function __construct() {
        $this->title = __( 'Licenses', FM_DIRNAME );
        $this->action = sanitize_title_with_dashes( FM_DIRNAME . ' ' . $this->title );
        $this->api_url = trailingslashit( FM_API_URL ) . 'edd-sl-api/'; // @see	https://github.com/Frosty-Media/edd-sl-api-endpoint
        $this->handle = $this->action;

        /* Register all plugins */
        $this->add_plugins();

        add_action( 'admin_menu', [ $this, 'admin_menu' ], 19 );
        add_action( 'wp_ajax_' . $this->action, [ $this, 'license_action_ajax' ] );

        add_filter( 'http_request_args', [ $this, 'hide_plugin_from_wp_repo' ], 5, 2 );
    }

    /**
     * Set plugins
     *
     * @param array $plugins plugin sections array
     */
    private function add_plugins( $plugins = [] ) {
        $plugins = apply_filters( 'frosty_media_add_plugin_license', $plugins );
        $this->plugins = $plugins;
    }

    /**
     * Add plugin
     *
     * @param array $plugin
     */
    public function add_plugin( $plugin ) {
        $this->plugins[] = $plugin;
    }

    /**
     * Register the plugin page
     */
    public function admin_menu() {
        $this->submenu_page = add_submenu_page(
            FM_DIRNAME,
            sprintf( 'Frosty Media %s %s', $this->title, __( 'Submenu Page', FM_DIRNAME ) ),
            sprintf( '%s', $this->title, FROSTYMEDIA()->update_html() ), // Maybe add second '%s' for update_html() ??
            'manage_options',
            trailingslashit( FM_DIRNAME ) . strtolower( $this->title ),
            [ $this, 'plugin_page' ]
        );

        add_action( 'load-' . $this->submenu_page, [ $this, 'load' ], 1 );
    }

    /**
     * Display the plugin settings options page
     */
    public function plugin_page() {
        include FM_PLUGIN_DIR . 'views/page-licenses.php';
    }

    /**
     * Create the action on page load.
     */
    public function load() {
        $this->load_updater();
        $this->set_strings();

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Load the plugin Updater and activate the updater for each plugin registered.
     */
    private function load_updater() {
        if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
            include __DIR__ . '/libraries/edd/EDD_SL_Plugin_Updater.php';
        }

        foreach ( $this->plugins as $plugin ) {
            $option = common::get_option( $plugin['id'], FM_DIRNAME, [] );
            $license = isset( $option['license'] ) ? $option['license'] : '';
            new \EDD_SL_Plugin_Updater( $this->api_url, $plugin['file'],
                [
                    'version' => $plugin['version'],
                    // current version number
                    'license' => $license,
                    // license key
                    'item_name' => $plugin['title'],
                    // name of this plugin in the Easy Digital Downloads system
                    'author' => $plugin['author'],
                    // author of this plugin
                ]
            );
        }
    }

    /**
     * Set settings sections
     */
    private function set_strings() {
        $this->strings = [
            'plugin-license' => __( 'Plugin License', FM_DIRNAME ),
            'enter-key' => __( 'Enter your plugin license key.', FM_DIRNAME ),
            'license-key' => __( 'License Key', FM_DIRNAME ),
            'license-action' => __( 'License Action', FM_DIRNAME ),
            'deactivate-license' => __( 'Deactivate License', FM_DIRNAME ),
            'activate-license' => __( 'Activate License', FM_DIRNAME ),
            'check-license' => __( 'Check License Status', FM_DIRNAME ),
            'status-unknown' => __( 'License status is unknown.', FM_DIRNAME ),
            'renew' => __( 'Renew?', FM_DIRNAME ),
            'unlimited' => __( 'unlimited', FM_DIRNAME ),
            'license-key-is-active' => __( 'License key is active.', FM_DIRNAME ),
            'expires%s' => __( 'Expires %s.', FM_DIRNAME ),
            '%1$s/%2$-sites' => __( 'You have %1$s / %2$s sites activated.', FM_DIRNAME ),
            'license-key-expired-%s' => __( 'License key expired %s.', FM_DIRNAME ),
            'license-key-expired' => __( 'License key has expired.', FM_DIRNAME ),
            'license-keys-do-not-match' => __( 'License keys do not match.', FM_DIRNAME ),
            'license-is-inactive' => __( 'License is inactive.', FM_DIRNAME ),
            'license-key-is-disabled' => __( 'License key is disabled.', FM_DIRNAME ),
            'site-is-inactive' => __( 'Site is inactive.', FM_DIRNAME ),
            'license-status-unknown' => __( 'License status is unknown.', FM_DIRNAME ),
            'update-notice' => __( "Updating this plugin will lose any customizations you have made. 'Cancel' to stop, 'OK' to update.", FM_DIRNAME ),
            'update-available' => __( '<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.', FM_DIRNAME ),
        ];
    }

    /**
     * Create the html loop.
     *
     * @param bool $minimum
     */
    public function plugins_html( $minimum = false ) {
        if ( empty( $this->plugins ) ) { ?>
            <div class="inside">
            <?php printf( __( '<h4>No Extensions are installed. Browse <a href="%s">here</a></h4>', FM_DIRNAME ),
                trailingslashit( FM_API_URL ) . 'plugins'
            ); ?>
            </div><?php
        } else {
            foreach ( $this->plugins as $key => $plugin ) {

                $option = common::get_option( $plugin['id'], FM_DIRNAME, [] );
                $license = isset( $option['license'] ) ? $option['license'] : '';
                $status = isset( $option['status'] ) ? $option['status'] : '';
                $trankey = common::get_transient_key( $plugin['id'] . '_license_message' );

                // Checks license status to display under license key
                if ( '' === $license ) {
                    $message = $this->strings['enter-key'];
                } else {
                    //delete_transient( $trankey );
                    if ( false === ( $message = get_transient( $trankey ) ) ) {
                        $message = $this->check_license( $license, $plugin );
                        set_transient( $trankey, $message, DAY_IN_SECONDS );
                    }
                }

                $atts = [
                    'license' => $license,
                    'status' => $status,
                    'message' => $message,
                    'key' => $key,
                ];
                $this->license_html( $plugin, $atts, $minimum );
            }
        }

        wp_nonce_field( FM_PLUGIN_BASENAME . '-license-nonce', 'nonce' ); // One global nonce field
    }

    /**
     * Output the singular plugin HTML
     */
    private function license_html( $plugin, $args = [], $minimum = false ) {
        include FM_PLUGIN_DIR . 'views/license.php';
    }

    /**
     * Enqueue License only script
     */
    function enqueue_scripts() {
        wp_register_script( $this->handle, trailingslashit( FM_PLUGIN_URL ) . 'js/licenses.js', [ 'jquery' ], FM_VERSION, false );
        wp_enqueue_script( $this->handle );

        $args = [
            'action' => $this->action,
            'dirname' => FM_DIRNAME,
            'nonce' => wp_create_nonce( FM_PLUGIN_BASENAME . $this->action . '-nonce' ),
            'loading' => admin_url( '/images/wpspin_light.gif' ),
            'ajaxurl' => admin_url( '/admin-ajax.php' ),
        ];
        wp_localize_script( $this->handle, str_replace( '-', '_', $this->handle ), $args );
    }

    /**
     *
     */
    public function license_action_ajax() {
        check_ajax_referer( FM_PLUGIN_BASENAME . $this->action . '-nonce', 'nonce' );

        foreach ( $this->plugins as $plugin ) {

            if ( ! isset( $_POST['plugin_id'] ) || $_POST['plugin_id'] !== $plugin['id'] ) {
                continue;
            }

            $license_key = sanitize_text_field( $_POST['license'] );
            $plugin_action = sanitize_text_field( $_POST['plugin_action'] );

            if ( $plugin_action === $plugin['id'] . '_activate' ) {
                if ( check_admin_referer( $plugin['id'] . '-license-nonce', 'plugin_nonce' ) ) {
                    if ( $this->activate_license( $license_key, $plugin['id'], $plugin['title'] ) ) {
                        wp_send_json_success();
                    }
                }
            }

            if ( $plugin_action === $plugin['id'] . '_deactivate' ) {
                if ( check_admin_referer( $plugin['id'] . '-license-nonce', 'plugin_nonce' ) ) {
                    if ( $this->deactivate_license( $license_key, $plugin['id'], $plugin['title'] ) ) {
                        wp_send_json_success();
                    }
                }
            }

            if ( $plugin_action === $plugin['id'] . '_check_license' ) {
                if ( check_admin_referer( $plugin['id'] . '-license-nonce', 'plugin_nonce' ) ) {
                    $message = $this->check_license( $license_key, $plugin, $update_option = true );
                    wp_send_json_success( $message );
                }
            }
        }

        wp_send_json_error();
    }

    /**
     * Makes a call to the API.
     *
     * @since 1.0.0
     *
     * @param array $api_params to be used for wp_remote_get.
     *
     * @return bool|object $response decoded JSON response.
     */
    private function get_api_response( $api_params ) {
        // Call the custom API.
        $response = wp_remote_get(
            add_query_arg( $api_params, $this->api_url ),
            [ 'timeout' => 15, 'sslverify' => false ]
        );

        // Make sure the response came back okay.
        if ( is_wp_error( $response ) ) {
            return false;
        }

        $response = json_decode( wp_remote_retrieve_body( $response ) );

        return $response;
    }

    /**
     * Activates the license key.
     *
     * @param string $license The incoming POST license key
     * @param string $plugin_id
     * @param string $item_name
     *
     * @since 1.0.0
     * @return bool
     */
    private function activate_license( $license, $plugin_id, $item_name = '' ) {
        if ( empty( $license ) ) {
            return false;
        }

        // Data to send in our API request.
        $api_params = [
            'edd_action' => 'activate_license',
            'license' => $license,
            'item_name' => urlencode( $item_name ),
        ];

        $license_data = $this->get_api_response( $api_params );

        // $response->license will be either "active" or "inactive"
        if ( $license_data && isset( $license_data->license ) ) {

            if ( $license_data->license !== 'invalid' ) {
                $trankey = common::get_transient_key( $plugin_id . '_license_message' );
                $option = get_option( FM_DIRNAME, [] );
                $option[ $plugin_id ]['license'] = trim( $license );
                $option[ $plugin_id ]['status'] = trim( $license_data->license );

                update_option( FM_DIRNAME, $option );
                delete_transient( $trankey );

                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Deactivates the license key.
     *
     * @param string $license The incoming POST license key
     * @param string $plugin_id
     * @param string $item_name
     *
     * @since 1.0.0
     * @return bool
     */
    private function deactivate_license( $license, $plugin_id, $item_name = '' ) {

        // Data to send in our API request.
        $api_params = [
            'edd_action' => 'deactivate_license',
            'license' => $license,
            'item_name' => urlencode( $item_name ),
        ];

        $license_data = $this->get_api_response( $api_params );

        // $license_data->license will be either "deactivated" or "failed"
        if ( $license_data && isset( $license_data->license ) && $license_data->license === 'deactivated' ) {
            $trankey = common::get_transient_key( $plugin_id . '_license_message' );
            $option = get_option( FM_DIRNAME, [] );
            $option[ $plugin_id ]['license'] = trim( $license );
            $option[ $plugin_id ]['status'] = '';

            update_option( FM_DIRNAME, $option );
            delete_transient( $trankey );

            return true;
        }

        return false;
    }

    /**
     * Checks if license is valid and gets expire date.
     *
     * @param string $license The incoming POST license key
     * @param array $plugin_args
     * @param bool $update_option
     *
     * @since 1.0.0
     * @return string $message License status message.
     */
    private function check_license( $license = null, $plugin_args = [], $update_option = false ) {
        $strings = $this->strings;

        // Bail early if no license key
        if ( empty( $license ) ) {
            return false;
        }

        $api_params = [
            'edd_action' => 'check_license',
            'license' => $license,
            'item_name' => urlencode( $plugin_args['title'] ),
        ];

        $license_data = $this->get_api_response( $api_params );

        // If response doesn't include license data, return
        if ( ! isset( $license_data->license ) ) {
            return $strings['license-unknown'];
        }

        // Get expire date
        $expires = false;

        if ( isset( $license_data->expires ) ) {
            $expires = date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires ) );
            $renew_link = '<a href="' . esc_url( $this->get_renewal_link( $license, $plugin_args['download_id'] ) ) . '" target="_blank">' . $strings['renew'] . '</a>';
        }

        // Get site counts
        $site_count = $license_data->site_count;
        $license_limit = $license_data->license_limit;

        // If unlimited
        if ( 0 == $license_limit ) {
            $license_limit = $strings['unlimited'];
        }

        if ( $license_data->license == 'valid' ) {
            $message = $strings['license-key-is-active'] . ' ';
            if ( $expires ) {
                $message .= sprintf( $strings['expires%s'], $expires ) . ' ';
            }
            if ( $site_count && $license_limit ) {
                $message .= sprintf( $strings['%1$s/%2$-sites'], $site_count, $license_limit );
            }
        } else if ( $license_data->license == 'expired' ) {
            if ( $expires ) {
                $message = sprintf( $strings['license-key-expired-%s'], $expires );
            } else {
                $message = $strings['license-key-expired'];
            }
            if ( isset( $renew_link ) ) {
                $message .= ' ' . $renew_link;
            }
        } else if ( $license_data->license == 'invalid' ) {
            $message = $strings['license-keys-do-not-match'];
        } else if ( $license_data->license == 'inactive' ) {
            $message = $strings['license-is-inactive'];
        } else if ( $license_data->license == 'disabled' ) {
            $message = $strings['license-key-is-disabled'];
        } else if ( $license_data->license == 'site_inactive' ) {
            // Site is inactive
            $message = $strings['site-is-inactive'];
        } else {
            $message = $strings['license-status-unknown'];
        }

        $option = get_option( FM_DIRNAME, [] );
        $status = isset( $option[ $plugin_args['id'] ]['status'] ) ?
            $option[ $plugin_args['id'] ]['status'] : '';

        $option[ $plugin_args['id'] ]['status'] = trim( $license_data->license );
        $trankey = common::get_transient_key( $plugin_args['id'] . '_license_message' );

        if ( $update_option ) {
            if ( ! empty( $status ) && $status != $option[ $plugin_args['id'] ]['status'] ) {
                update_option( FM_DIRNAME, $option );
                delete_transient( $trankey );
            }
        }

        return $message;
    }

    /**
     * Constructs a renewal link
     *
     * @param string $license_key
     * @param int|string $download_id
     *
     * @since 1.0.0
     * @return string
     */
    private function get_renewal_link( $license_key, $download_id = '' ) {
        // If download_id was passed in the config, a renewal link can be constructed
        if ( $license_key && '' !== $download_id ) {
            $url = add_query_arg(
                [
                    'edd_license' => $license_key,
                    'download_id' => $download_id,
                    'utm_source' => 'wordpress',
                    'utm_medium' => 'frosty-media-renew',
                    'utm_campaign' => 'frosty-media-license',
                ],
                sprintf( '%s/checkout/', untrailingslashit( esc_url( FM_API_URL ) ) )
            );

            return $url;
        }

        // Otherwise return the api_url
        return $this->api_url;
    }

    /**
     * Disable requests to wp.org repository for this plugin.
     *
     * @since 1.0.0
     *
     * @param $r
     * @param $url
     *
     * @return mixed
     */
    public function hide_plugin_from_wp_repo( $r, $url ) {
        $update_check = (bool) strpos( $url, '//api.wordpress.org/plugins/update-check/1.1/' );

        // If it's not a plugin update request, bail.
        if ( false === $update_check ) {
            return $r;
        }

        // Decode the JSON response
        $plugins = json_decode( $r['body']['plugins'] );

        // Loop through each plugin and remove the active plugin from the check
        foreach ( $this->plugins as $plugin ) {
            if ( ! isset( $plugin['basename'] ) ) {
                continue;
            }

            unset( $plugins->{$plugin['basename']} );
            // Make sure it's an array, since a cached $r['body']['plugins'] might still be an Object...
            if ( ! is_array( $plugins->active ) ) {
                continue;
            }
            unset( $plugins->active[ array_search( $plugin['basename'], $plugins->active ) ] );
        }

        // Encode the updated JSON response
        $r['body']['plugins'] = json_encode( $plugins );

        return $r;
    }

    /**
     * Plugin has update.
     *
     * @return array|bool
     */
    public function has_update() {
//        if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
//            set_site_transient( 'update_plugins', null );
//        }

        $update = [];
        $plugins = get_site_transient( 'update_plugins' );

        foreach ( $this->plugins as $plugin ) {
            if ( ! isset( $plugin['basename'] ) ) {
                continue;
            }
            if ( ! isset( $plugin['version'] ) ) {
                continue;
            }
            if ( ! isset( $plugins->response[ $plugin['basename'] ]->slug ) ||
                 ! in_array( basename( $plugin['basename'], '.php' ), (array) $plugins->response[ $plugin['basename'] ]->slug ) ) {
                continue;
            }
            $update[ $plugin['title'] ] = version_compare( $plugin['version'], $plugins->response[ $plugin['basename'] ]->new_version, '<' );
        }

        if ( ! empty( $update ) ) {
            $update['count'] = count( $update );

            return $update;
        }

        return false;
    }
}
