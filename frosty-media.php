<?php
/**
 * Plugin Name: Frosty Media (License Manager)
 * Plugin URI: http://frosty.media/
 * Description: The core functionallity that manages all Frosty.Media licenses, settings, auto-updates and notifications.
 * Version: 1.0.7
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
 *
 * @class Frosty_Media
 */

if ( !class_exists( 'Frosty_Media' ) ) :

/**
 * Main Frosty_Media Class
 *
 * @since 1.0
 */
final class Frosty_Media {

	/** Singleton *************************************************************/
	private static $instance;

	/**
	 * Plugin vars
	 * @return string
	 */
	var	$version = '1.0.7',
		$menu_page;

	/**
	 * Main Instance
	 *
	 * @staticvar 	array 	$instance
	 * @return 		The one true instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Frosty_Media ) ) {
			self::$instance = new Frosty_Media;
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', array( self::$instance, 'plugin_textdomain' ) );

			self::$instance->includes();
			self::$instance->actions();
		}
		return self::$instance;
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 1.4
	 * @return void
	 */
	private function setup_constants() {

		// API URL
		if ( ! defined( 'FM_API_URL' ) ) {
			define( 'FM_API_URL', 'https://frosty.media' );
		}

		// Plugin version
		if ( ! defined( 'FM_VERSION' ) ) {
			define( 'FM_VERSION', $this->version );
		}

		// Plugin Folder Path
		if ( ! defined( 'FM_PLUGIN_DIR' ) ) {
			define( 'FM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'FM_PLUGIN_URL' ) ) {
			define( 'FM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'FM_PLUGIN_FILE' ) ) {
			define( 'FM_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Root Basename
		if ( ! defined( 'FM_PLUGIN_BASENAME' ) ) {
			define( 'FM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		}

		// Plugin Dirname
		if ( ! defined( 'FM_DIRNAME' ) ) {
			define( 'FM_DIRNAME', dirname( FM_PLUGIN_BASENAME ) );
		}

		// Plugin Script Name
		if ( ! defined( 'FM_SCRIPTNAME' ) ) {
			define( 'FM_SCRIPTNAME', str_replace( '-', '_', FM_DIRNAME ) );
		}
	}

	/**
	 * Load the plugin translations
	 *
	 */
	public function plugin_textdomain() {
		load_plugin_textdomain( FM_DIRNAME, false, FM_DIRNAME . '/languages/' );
	}

	/**
	 * Includes required functions
	 *
	 */
	private function includes() {
		require_once( trailingslashit( FM_PLUGIN_DIR ) . 'includes/libraries/github-updater/updater.php' );
		
		require_once( trailingslashit( FM_PLUGIN_DIR ) . 'includes/check-folder-structure.php' );
		require_once( trailingslashit( FM_PLUGIN_DIR ) . 'includes/class-dashboard.php' );
		require_once( trailingslashit( FM_PLUGIN_DIR ) . 'includes/class-fm-common.php' );
		require_once( trailingslashit( FM_PLUGIN_DIR ) . 'includes/class-frosty-media-notifications.php' );
		require_once( trailingslashit( FM_PLUGIN_DIR ) . 'includes/class-frosty-media-licenses.php' );
		require_once( trailingslashit( FM_PLUGIN_DIR ) . 'includes/misc-functions.php' );
	}

	/**
	 * To infinity and beyond
	 */
	private function actions() {
		
		add_action( 'admin_init',		array( $this, 'github_updater' ) );
		add_action( 'admin_menu',		array( $this, 'admin_menu' ) );
	}

    /**
	 * Initiate the Github Updater.
	 */
	public function github_updater() {
		
		if ( is_admin() ) {
			
			$config = array(
				'slug'					=> FM_PLUGIN_BASENAME, // this is the slug of your plugin
				'proper_folder_name'	=> FM_DIRNAME, // this is the name of the folder your plugin lives in
				'api_url'				=> 'https://api.github.com/repos/Frosty-Media/frosty-media', // the github API url of your github repo
				'raw_url'				=> 'https://raw.github.com/Frosty-Media/frosty-media/master', // the github raw url of your github repo
				'github_url'			=> 'https://github.com/Frosty-Media/frosty-media', // the github url of your github repo
				'zip_url'				=> 'https://github.com/Frosty-Media/frosty-media/zipball/master', // the zip url of the github repo
				'sslverify'			=> true, // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
				'requires'				=> '3.9', // which version of WordPress does your plugin require?
				'tested'				=> '4.1', // which version of WordPress is your plugin tested up to?
				'readme'				=> 'README.md', // which file to use as the readme for the version number
				'access_token'			=> '', // Access private repositories by authorizing under Appearance > Github Updates when this example plugin is installed
			);
			new WP_GitHub_Updater( $config );
			
			if ( !function_exists( 'GHL_extra_headers' ) || !function_exists( 'GHL_plugin_link' ) ) {
				require_once( trailingslashit( FM_PLUGIN_DIR ) . 'includes/libraries/github-link/github-link.php' );
			}
		}
	}

    /**
	 * Register the plugin page
	 */
	public function admin_menu() {

		$this->menu_page = add_menu_page(
			sprintf( 'Frosty Media %s', __( 'Dashboard', FM_DIRNAME ) ),
			sprintf( 'Frosty Media%s', $this->update_html() ),
			'manage_options',
			FM_DIRNAME,
			array( $this, 'plugin_page' ),
			FM_Common::get_data_uri( 'svg/frosty-media.svg', 'svg+xml' ),
			'80.0000001'
		);

		add_action( 'admin_footer-' . $this->menu_page, array( $this, 'inline_jquery' ) );
	}

	/**
	 * Return the HTML if any plugin(s) has an update.
	 */
	public function update_html() {
		global $frosty_media_licenses;

		$has_update = $frosty_media_licenses->has_update();

		return !empty( $has_update ) ? '&nbsp;<span id="fm-update" title="' . esc_attr__( 'Update Available', FM_DIRNAME ) . '" class="update-plugins count-' . absint( $has_update['count'] ) . '"><span class="plugin-count">' . absint( $has_update['count'] ) . '</span></span>' : '';
	}

	/**
	 * Display the plugin settings options page
	 */
	public function plugin_page() {
		$screen = get_current_screen(); ?>

		<div class="wrap">

			<?php frosty_media_screen_icon(); ?>
			<h2><?php printf( 'Frosty Media %s %s', __( 'Dashboard', FM_DIRNAME ), '<small class="">v.' . FM_VERSION . '</small>' ); ?></h2>

			<div id="dashboard-widgets-wrap">

				<div id="dashboard-widgets" class="metabox-holder">

					<div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( $this->menu_page, 'side', '' ); ?>
					</div>

					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( $this->menu_page, 'normal', '' ); ?>
					</div>

					<br class="clear">

					<?php do_meta_boxes( $this->menu_page, 'bottom', '' ); ?>
					<br class="clear">

				</div>

			</form>

		</div><?php
	}

	/**
	 * Inline jQuery script
	 */
	public function inline_jquery() { ?>
		<script>
		jQuery(document).ready( function($) {
			// close postboxes that should be closed
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			// postboxes setup
			postboxes.add_postbox_toggles('<?php echo $this->menu_page; ?>');
		});
		</script><?php
	}

}

endif; // End if class_exists check

/**
 * The main function responsible for returning the one true
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $fm = FROSTYMEDIA(); ?>
 *
 * @return The one true Instance
 */
if ( !function_exists( 'FROSTYMEDIA' ) ) :
	function FROSTYMEDIA() {
		return Frosty_Media::instance();
	}
endif;

// Out of the frying pan, and into the fire.
FROSTYMEDIA();