<?php
/**
 * @package     FrostyMedia
 * @subpackage  Classes/FM_Common
 * @author      Austin Passy <http://austin.passy.co>
 * @copyright   Copyright (c) 2014, Austin Passy
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'FM_Common' ) ) :
	
class FM_Common {
	
	/**
	 * Helper function to return the data URI.
	 */
	public static function get_data_uri( $_image, $mime = '' ) {

		$image  = trailingslashit( FM_PLUGIN_URL );
		$image .= $_image;

		$data = base64_encode( file_get_contents( $image ) );

		return !empty( $data ) ? 'data:image/' . $mime . ';base64,' . $data : '';
	}

	/**
	 * Get's the cached transient key.
	 *
	 * @since v.1.0
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
     * @param string  $option  settings field name
     * @param string  $section the section name this field belongs to
     * @param string  $default default text if it's not found
     * @return string
     */
    public static function get_option( $option, $section = FM_DIRNAME, $default = '' ) {

        $options = get_option( $section );

        if ( isset( $options[$option] ) ) {
            return $options[$option];
        }

        return $default;
    }
  
}
endif;