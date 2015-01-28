<?php
/**
 * @package     FrostyMedia
 * @subpackage  Classes/Frosty_Media_Updater
 * @author      Austin Passy <http://austin.passy.co>
 * @copyright   Copyright (c) 2014, Austin Passy
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
 * @ref	         https://gist.github.com/bueltge/757903
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Frosty_Media_Dashboad {
	
	function __construct() {
		add_action( 'admin_menu',						array( $this, 'admin_menu' ), 99 );
	}
	
	/**
	 *
	 */
	function admin_menu() {
		add_action( 'load-' . FROSTYMEDIA()->menu_page,	array( $this, 'add_meta_boxs' ) );
	}
	
	/**
	 *
	 */
	function add_meta_boxs() {
		wp_enqueue_script( array( 'common', 'wp-lists', 'postbox' ) );
		
		$title = FM_DIRNAME;
		
		if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
			add_meta_box(
				$title . '-debug',
				'var_dump( FROSTYMEDIA() )',
				create_function( '', 'var_dump( FROSTYMEDIA() );' ),
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
		
		if ( !class_exists( 'FM_Notices_List_Table' ) ) {
			require_once( trailingslashit( FM_PLUGIN_DIR ) . 'includes/class-fm-notices-list-table.php' );
		}
		
		$notices_table = new FM_Notices_List_Table();
		$notices_table->prepare_items( 4, true ); 
		$notices_table->display();
	}
	
	/**
	 *
	 */
	function license_status_metabox( $data ) {
		global $frosty_media_licenses;
		
		$frosty_media_licenses->plugins_html( $minimum = true );
	}
	
}
$frosty_media_dashboad = new Frosty_Media_Dashboad;