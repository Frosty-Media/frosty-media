<?php
/**
 * @package     FrostyMedia
 * @subpackage  Classes/Frosty_Media_Licenses
 * @author      Austin Passy <http://austin.passy.co>
 * @copyright   Copyright (c) 2014, Austin Passy
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
 
class Frosty_Media_Licenses {
	
	/**
	 * Plugins array
	 *
	 * @var array
	 */
	private $plugins = array();
		
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
	protected $strings = null;
	
	public function __construct() {
		
		$this->dirname	= FM_DIRNAME;
		$this->title	= __( 'Licenses', FM_DIRNAME );
		$this->action	= sanitize_title_with_dashes( $this->dirname . ' ' . $this->title );
		$this->api_url	= trailingslashit( FM_API_URL ) . 'edd-sl-api/'; // @see	https://github.com/Frosty-Media/edd-sl-api-endpoint
		$this->handle	= $this->action;
		
		/* Register all plugins */
		$this->add_plugins();
		
		add_action( 'admin_init',					array( $this, 'admin_init' ), 1 );
		add_action( 'admin_menu',					array( $this, 'admin_menu' ), 19 );
		
		add_action( 'wp_ajax_' . $this->action,	array( $this, 'license_action_ajax' ) );
		
		add_filter( 'http_request_args',			array( $this, 'hide_plugin_from_wp_repo' ), 5, 2 );		
	}

    /**
     * Set plugins
     *
     * @param	array	$plugins plugin sections array
     */
    private function add_plugins( $plugins = array() ) {
		$plugins = apply_filters( 'frosty_media_add_plugin_license', $plugins );				
		$this->plugins = $plugins;
    }

    /**
     * Add plugin
     *
     * @param	array
     */
    public function add_plugin( $plugin ) {		
		$this->plugins[] = $plugin;
    }
	
	/**
	 *
	 */
	public function admin_init() {
		$this->load_updater();
		$this->set_strings();
	}
	
	/**
	 * Load the plugin Updater and activate the updater for each plugin registered.
	 *
	 */
	private function load_updater() {
		if ( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			include( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'EDD_SL_Plugin_Updater.php' );
		}
		
		foreach ( $this->plugins as $plugin ) {
			
			$option  = FM_Common::get_option( $plugin['id'], FM_DIRNAME, array() );
			$license = isset( $option['license'] ) ? $option['license'] : '';
			$updater = new EDD_SL_Plugin_Updater( $this->api_url, $plugin['file'],
				array(
					'version'   => $plugin['version'],	// current version number
					'license'   => $license,				// license key
					'item_name' => $plugin['title'],		// name of this plugin in the Easy Digital Downloads system
					'author'    => $plugin['author'],		// author of this plugin
				)
			); // $updater			
		}
	}
	
    /**
     * Set settings sections
     *
     * @param	array	$sections setting sections array
     */
    private function set_strings() {
		$this->strings = array(
			'plugin-license'				=> __( 'Plugin License', FM_DIRNAME ),
			'enter-key' 					=> __( 'Enter your plugin license key.', FM_DIRNAME ),
			'license-key'					=> __( 'License Key', FM_DIRNAME ),
			'license-action'				=> __( 'License Action', FM_DIRNAME ),
			'deactivate-license'			=> __( 'Deactivate License', FM_DIRNAME ),
			'activate-license'				=> __( 'Activate License', FM_DIRNAME ),
			'check-license'				=> __( 'Check License Status', FM_DIRNAME ),
			'status-unknown'				=> __( 'License status is unknown.', FM_DIRNAME ),
			'renew'							=> __( 'Renew?', FM_DIRNAME ),
			'unlimited'					=> __( 'unlimited', FM_DIRNAME ),
			'license-key-is-active'		=> __( 'License key is active.', FM_DIRNAME ),
			'expires%s'					=> __( 'Expires %s.', FM_DIRNAME ),
			'%1$s/%2$-sites'				=> __( 'You have %1$s / %2$s sites activated.', FM_DIRNAME ),
			'license-key-expired-%s'		=> __( 'License key expired %s.', FM_DIRNAME ),
			'license-key-expired'			=> __( 'License key has expired.', FM_DIRNAME ),
			'license-keys-do-not-match'	=> __( 'License keys do not match.', FM_DIRNAME ),
			'license-is-inactive'			=> __( 'License is inactive.', FM_DIRNAME ),
			'license-key-is-disabled'		=> __( 'License key is disabled.', FM_DIRNAME ),
			'site-is-inactive'				=> __( 'Site is inactive.', FM_DIRNAME ),
			'license-status-unknown'		=> __( 'License status is unknown.', FM_DIRNAME ),
			'update-notice'				=> __( "Updating this plugin will lose any customizations you have made. 'Cancel' to stop, 'OK' to update.", FM_DIRNAME ),
			'update-available'				=> __('<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.', FM_DIRNAME )
		);
    }

    /**
	 * Register the plugin page
	 */
	public function admin_menu() {
		
		$this->submenu_page = add_submenu_page(
			$this->dirname,
			sprintf( 'Frosty Media %s %s', $this->title, __( 'Submenu Page', FM_DIRNAME ) ),
			sprintf( '%s', $this->title, FROSTYMEDIA()->update_html() ), // Maybe add second '%s' for update_html() ??
			'manage_options',
			trailingslashit( FM_DIRNAME ) . strtolower( $this->title ),
			array( $this, 'plugin_page' )
		);
		
		add_action( 'load-' . $this->submenu_page,	array( $this, 'load' ) );
	}
	
	/**
	 * Create the action on page load.
	 */
	public function load() {
		
		add_action( 'admin_enqueue_scripts',			array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Display the plugin settings options page
	 */
	public function plugin_page() { ?>
		
		<div class="wrap">
			
			<?php frosty_media_screen_icon(); ?>
			<?php printf( '<h2>Frosty Media %s</h2>', $this->title ); ?>
						
           <div class="postbox" style="margin-top:10px">
			<form autocomplete="off" action="" id="<?php printf( '%s-%s', FM_DIRNAME, sanitize_title( $this->title ) ); ?>" method="post">
			
				<?php $this->plugins_html(); ?>				
			</form>			
			</div>
		
		</div><?php
	}
	
	/**
	 * Create the html loop
	 */
	public function plugins_html( $minimum = false ) {
		
		if ( empty( $this->plugins ) ) { ?>
			<div class="inside">
				<?php printf( __( '<h4>No Extensions are installed. Browse <a href="%s">here</a></h4>', FM_DIRNAME ), trailingslashit( FM_DIRNAME ) . 'plugins' ); ?>
			</div><?php
		}
		else {
			foreach ( $this->plugins as $key => $plugin ) {
						
				$option		= FM_Common::get_option( $plugin['id'], FM_DIRNAME, array() );
				$license	= isset( $option['license'] ) ? $option['license'] : '';
				$status		= isset( $option['status'] ) ? $option['status'] : '';
				$trankey	= FM_Common::get_transient_key( $plugin['id'] . '_license_message' );
				
				// Checks license status to display under license key
				if ( '' === $license ) {
					$message = $this->strings['enter-key'];
				}
				else {
					//delete_transient( $trankey );
					if ( false === ( $message = get_transient( $trankey ) ) ) {
						$message = $this->check_license( $license, $plugin );
						set_transient( $trankey, $message, DAY_IN_SECONDS );
					}
				}
				
				$atts = array(
					'license'	=> $license,
					'status'	=> $status,
					'message'	=> $message,
					'key'		=> $key
				);
				$this->license_html( $plugin, $atts, $minimum );
				
			}
		}
		
		wp_nonce_field( FM_PLUGIN_BASENAME . '-license-nonce', 'nonce' ); // One global nonce field
	}
	
	/**
	 * Output the singular plugin HTML
	 */
	private function license_html( $plugin, $args = array(), $minimum = false ) {
		$license	= $args['license'];
		$status		= $args['status'];
		$message	= $args['message'];
		?>
		<div class="inside" data-plugin-id="<?php echo $plugin['id']; ?>">
					
			<?php printf( '<h4>%s</h4>', $plugin['title'] ); ?>
			
				<label for="<?php echo $plugin['id']; ?>[license_key]"><?php _e( 'License Key:', FM_DIRNAME ); ?></label><br>
				<input type="text" name="<?php echo $plugin['id']; ?>[license_key]" value="<?php echo $license; ?>" class="large-text"<?php echo $minimum ? ' readonly': ''; ?>>
				<?php if ( !$minimum ) : ?>
					<div class="button-wrapper alignright">
					<?php
					$atts = array( 'tabindex' => $args['key'] );
					
					if ( 'valid' === $status ) {
						submit_button( $this->strings['deactivate-license'], 'button-primary', sprintf( '%s_deactivate', $plugin['id'] ), false, $atts );
						echo '&nbsp;&nbsp;';
						submit_button( $this->strings['check-license'], 'button-secondary', sprintf( '%s_check_license', $plugin['id'] ), false, $atts );
					}
					else {
						submit_button( $this->strings['activate-license'], 'button-primary', sprintf( '%s_activate', $plugin['id'] ), false, $atts );
					} ?>
					</div>
				<?php endif; ?>
			
			<p>
				<span class="description">
					<?php printf( __( 'Status: <span>%s</span>', FM_DIRNAME ), $message ); ?>
				</span>
			</p>
			<hr>
		</div><?php
	}
	
	/**
	 * Enqueue License only script
	 */
	function enqueue_scripts() {
		wp_enqueue_style( $this->handle, trailingslashit( FM_PLUGIN_URL ) . 'css/licenses.css', false, FM_VERSION, 'screen' );
		
		wp_register_script( $this->handle, trailingslashit( FM_PLUGIN_URL ) . 'js/licenses.js', array( 'jquery' ), FM_VERSION, false );
		wp_enqueue_script( $this->handle );
		
		$args	= array(
			'action'	=> $this->action,
			'dirname'	=> FM_DIRNAME,
			'nonce'		=> wp_create_nonce( FM_PLUGIN_BASENAME . $this->action . '-nonce' ),
			'loading'	=> admin_url( '/images/wpspin_light.gif' ),
		);
		wp_localize_script( $this->handle, str_replace( '-', '_', $this->handle ), $args );
	}
	
	/**
	 *
	 */	
	function license_action_ajax( $ajax = true ) {
		
		if ( $ajax ) {
			check_ajax_referer( FM_PLUGIN_BASENAME . $this->action . '-nonce', 'nonce' );
		}
		
		foreach ( $this->plugins as $plugin ) {
			
			if ( $_POST['plugin_id'] !== $plugin['id'] )
				continue;
			
			$license_key	= $_POST['license'];
			$plugin_action	= $_POST['plugin_action'];
			
			if ( $plugin_action === $plugin['id'] . '_activate' ) {
				if ( !$ajax || check_admin_referer( FM_PLUGIN_BASENAME . '-license-nonce', 'nonce' ) ) {
					if ( $this->activate_license( $license_key, $plugin['id'], $plugin['title'] ) ) {
						die( 'success' );
					}
					die( 'error' );
				}
			}
	
			if ( $plugin_action === $plugin['id'] . '_deactivate' ) {
				if ( !$ajax || check_admin_referer( FM_PLUGIN_BASENAME . '-license-nonce', 'nonce' ) ) {
					if ( $this->deactivate_license( $license_key, $plugin['id'], $plugin['title'] ) ) {
						die( 'success' );
					}
					die( 'error' );
				}
			}
	
			if ( $plugin_action === $plugin['id'] . '_check_license' ) {
				if ( !$ajax || check_admin_referer( FM_PLUGIN_BASENAME . '-license-nonce', 'nonce' ) ) {
					if ( false === ( $message = $this->check_license( $license_key, $plugin, $update_option = true ) ) ) {
						die( $message );
					}
					die( 'error' );
				}
			}
		}
		die;
	}
	
	/**
	 * Makes a call to the API.
	 *
	 * @since 1.0.0
	 *
	 * @param array $api_params to be used for wp_remote_get.
	 * @return array $response decoded JSON response.
	 */
	 function get_api_response( $api_params ) {

		// Call the custom API.
		$response = wp_remote_get(
			add_query_arg( $api_params, $this->api_url ),
			array( 'timeout' => 15, 'sslverify' => false )
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
	 * @since 1.0.0
	 */
	function activate_license( $license, $plugin_id, $item_name = '' ) {

		// Data to send in our API request.
		$api_params = array(
			'edd_action'	=> 'activate_license',
			'license'		=> $license,
			'item_name'	=> urlencode( $item_name )
		);

		$license_data = $this->get_api_response( $api_params );

		// $response->license will be either "active" or "inactive"
		if ( $license_data && isset( $license_data->license ) ) {
			$trankey							= FM_Common::get_transient_key( $plugin_id . '_license_message' );
			//$option							= FM_Common::get_option( $plugin_id, FM_DIRNAME, array() );
			$option								= get_option( FM_DIRNAME, array() );
			$option[$plugin_id]['license']	= trim( $license );
			$option[$plugin_id]['status']		= trim( $license_data->license );
		
			update_option( FM_DIRNAME, $option );
			delete_transient( $trankey );
			return true;
		}
		return false;
	}

	/**
	 * Deactivates the license key.
	 *
	 * @since 1.0.0
	 */
	function deactivate_license( $license, $plugin_id, $item_name = '' ) {

		// Data to send in our API request.
		$api_params = array(
			'edd_action'	=> 'deactivate_license',
			'license'		=> $license,
			'item_name'	=> urlencode( $item_name )
		);

		$license_data = $this->get_api_response( $api_params );

		// $license_data->license will be either "deactivated" or "failed"
		if ( $license_data && ( $license_data->license == 'deactivated' ) ) {
			$trankey							= FM_Common::get_transient_key( $plugin_id . '_license_message' );
			//$option							= FM_Common::get_option( $plugin_id, FM_DIRNAME, array() );
			$option								= get_option( FM_DIRNAME, array() );
			$option[$plugin_id]['license']	= trim( $license );
			$option[$plugin_id]['status']		= '';
		
			update_option( FM_DIRNAME, $option );
			delete_transient( $trankey );
			return true;
		}
		return false;
	}
	
	/**
	 * Checks if license is valid and gets expire date.
	 *
	 * @since 1.0.0
	 *
	 * @return string $message License status message.
	 */
	function check_license( $license = null, $plugin_args = array(), $update_option = false ) {
			
		$strings = $this->strings;
		
		// Bail early if no license key
		if ( is_null( $license ) )
			return false;

		$api_params = array(
			'edd_action'	=> 'check_license',
			'license'		=> $license,
			'item_name'	=> urlencode( $plugin_args['title'] )
		);

		$license_data = $this->get_api_response( $api_params );

		// If response doesn't include license data, return
		if ( !isset( $license_data->license ) ) {
			$message = $strings['license-unknown'];
			return $message;
		}

		// Get expire date
		$expires = false;
		if ( isset( $license_data->expires ) ) {
			$expires		= date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires ) );
			$renew_link	= '<a href="' . esc_url( $this->get_renewal_link( $license, $plugin_args['download_id'] ) ) . '" target="_blank">' . $strings['renew'] . '</a>';
		}

		// Get site counts
		$site_count		= $license_data->site_count;
		$license_limit		= $license_data->license_limit;

		// If unlimited
		if ( 0 == $license_limit ) {
			$license_limit	= $strings['unlimited'];
		}

		if ( $license_data->license == 'valid' ) {
			$message = $strings['license-key-is-active'] . ' ';
			if ( $expires ) {
				$message .= sprintf( $strings['expires%s'], $expires ) . ' ';
			}
			if ( $site_count && $license_limit ) {
				$message .= sprintf( $strings['%1$s/%2$-sites'], $site_count, $license_limit );
			}
		}
		else if ( $license_data->license == 'expired' ) {
			if ( $expires ) {
				$message = sprintf( $strings['license-key-expired-%s'], $expires );
			}
			else {
				$message = $strings['license-key-expired'];
			}
			if ( $renew_link ) {
				$message .= ' ' . $renew_link;
			}
		}
		else if ( $license_data->license == 'invalid' ) {
			$message = $strings['license-keys-do-not-match'];
		}
		else if ( $license_data->license == 'inactive' ) {
			$message = $strings['license-is-inactive'];
		}
		else if ( $license_data->license == 'disabled' ) {
			$message = $strings['license-key-is-disabled'];
		}
		else if ( $license_data->license == 'site_inactive' ) {
			// Site is inactive
			$message = $strings['site-is-inactive'];
		}
		else {
			$message = $strings['license-status-unknown'];
		}
		
		//$option									= FM_Common::get_option( $plugin_args['id'], FM_DIRNAME, array() );
		$option										= get_option( FM_DIRNAME, array() );
		$status										= isset( $option[$plugin_args['id']]['status'] ) ? $option[$plugin_args['id']]['status'] : '';
		$option[$plugin_args['id']]['status']	= trim( $license_data->license );
		$trankey									= FM_Common::get_transient_key( $plugin_args['id'] . '_license_message' );
		
		if ( $update_option ) {
			if ( !empty( $status ) && $status != $option[$plugin_args['id']]['status'] ) {
				update_option( FM_DIRNAME, $option );
				delete_transient( $trankey );
				return true;
			}
			return false;
		}

		return $message;
	}
	 
	/**
	 * Constructs a renewal link
	 *
	 * @since 1.0.0
	 */
	function get_renewal_link( $license_key, $download_id = '' ) {

		// If download_id was passed in the config, a renewal link can be constructed
		if ( '' != $download_id && $license_key ) {
			$url = add_query_arg(
				array(
					'edd_license'	=> $license_key,
					'download_id'	=> $download_id,
					'utm_source'	=> 'wordpress',
					'utm_medium'	=> 'frosty-media-renew',
					'utm_campaign'	=> 'frosty-media-license' ),
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
	 */
	function hide_plugin_from_wp_repo( $r, $url ) {
		
		$update_check = (bool) strpos( $url, '//api.wordpress.org/plugins/update-check/1.1/' );
		
		// If it's not a plugin update request, bail.
		if ( false === $update_check ) {
 			return $r;
 		}

 		// Decode the JSON response
 		$plugins = json_decode( $r['body']['plugins'] );
		
		// Loop through each plugin and remove the active plugin from the check
		foreach ( $this->plugins as $plugin ) {
			if ( !isset( $plugin['basename'] ) )
				continue;
			unset( $plugins->plugins->$plugin['basename'] );
			// Make sure it's an array, since a cached $r['body']['plugins'] might still be an Object...
			if ( !is_array( $plugins->active ) )
				continue;
			unset( $plugins->active[ array_search( $plugin['basename'], $plugins->active ) ] );
		}
		
 		// Encode the updated JSON response
 		$r['body']['plugins'] = json_encode( $plugins );

 		return $r;
	}
	
	/**
	 * Plugin has update.
	 */
	public function has_update() {
		
		if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
		//	set_site_transient( 'update_plugins', null );
		}		
		$update		= array();
		$plugins	= get_site_transient( 'update_plugins' );
		
		foreach ( $this->plugins as $plugin ) {
			if ( !isset( $plugin['basename'] ) )
				continue;
			if ( !isset( $plugin['version'] ) )
				continue;
			if ( !isset( $plugins->response[ $plugin['basename'] ]->slug ) || !in_array( basename( $plugin['basename'], '.php' ), (array) $plugins->response[ $plugin['basename'] ]->slug ) )
				continue;
			$update[ $plugin['title'] ] = version_compare( $plugin['version'], $plugins->response[ $plugin['basename'] ]->new_version, '<' );
		}
		
		if ( !empty( $update ) ) {
			$update['count'] = count( $update );
			return $update;
		}
		return false;
    }
  
}
$GLOBALS['frosty_media_licenses'] = new Frosty_Media_Licenses;