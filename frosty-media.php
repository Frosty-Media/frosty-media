<?php
/**
 * Plugin Name: Frosty Media (License Manager)
 * Plugin URI: https://frosty.media/plugins/frosty-media/
 * Description: The core functionality that manages all Frosty.Media licenses, settings, auto-updates and notifications.
 * Version: 1.1.0
 * Author: Austin Passy
 * Author URI: http://austin.passy.co
 * Text Domain: frosty-media
 * GitHub Plugin URI: https://github.com/Frosty-Media/frosty-media
 * GitHub Branch: master
 *
 * @copyright 2014 - 2015
 * @author Austin Passy
 * @link http://austin.passy.co/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Plugin Folder Path
if ( ! defined( 'FM_PLUGIN_FILE' ) ) {
    define( 'FM_PLUGIN_FILE', __FILE__ );
}

// Include the core class.
require_once( plugin_dir_path( FM_PLUGIN_FILE ) . 'includes/frosty-media.php' );

// Out of the frying pan, and into the fire.
add_action( 'plugins_loaded', array( 'FrostyMedia\Core', 'instance' ) );
