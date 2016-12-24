<?php

namespace FrostyMedia\Includes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Common
 *
 * @package     FrostyMedia
 * @subpackage  Classes/Common
 * @author      Austin Passy <http://austin.passy.co>
 * @copyright   Copyright (c) 2015, Austin Passy
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Common {

    /**
     * Helper function to return the data URI.
     *
     * @param string $_image
     * @param string $mime
     *
     * @return string
     */
    public static function get_data_uri( $_image, $mime = '' ) {
        $image = trailingslashit( FM_PLUGIN_URL );
        $image .= $_image;

        if ( ini_get( 'allow_url_fopen' ) ) {
            $data = file_get_contents( $image );
        } else {
            $response = wp_remote_get( esc_url_raw( $image ) );
            $data     = wp_remote_retrieve_body( $response );
        }

        return ! empty( $data ) ? 'data:image/' . $mime . ';base64,' . base64_encode( $data ) : '';
    }

    /**
     * Get's the cached transient key.
     *
     * @param string $input
     *
     * @since 1.0
     * @return string
     */
    public static function get_transient_key( $input ) {
        $key = 'frosty_media_';
        $key = $key . substr( md5( $input ), 0, 45 - strlen( $key ) );

        return $key;
    }

    /**
     * Get the value of a settings field
     *
     * @param string $option settings field name
     * @param string $section the section name this field belongs to
     * @param string $default default text if it's not found
     *
     * @return string
     */
    public static function get_option( $option, $section = FM_DIRNAME, $default = '' ) {
        $options = get_option( $section );

        if ( isset( $options[ $option ] ) ) {
            return $options[ $option ];
        }

        return $default;
    }
}
