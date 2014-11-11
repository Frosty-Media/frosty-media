Frosty Media
============

~Current Version:1.0.0~

The core functionallity that manages all Frosty.Media licenses, settings, auto-updates and notifications.

### Usage

// Include the plugin or as a standalone
`
/**
 * Register our plugin for license management.
 *
 * @return 	array plugins
 */
function frosty_media_register_licensed_plugin( $plugins ) {
	
	$plugins[] = array(
		'id' 			=> 'prefix_plugin_title', // Option title
		'title' 		=> 'Plugin Title', // Must match EDD post_title!
		'version'		=> '1.0.0',
		'file'			=> __FILE__,
		'basename'		=> plugin_basename( __FILE__ ),
		'download_id'	=> '2345', // EDD download ID!
		'author'		=> 'Austin Passy' // Author of this plugin
	);	
	return $plugins;
}
add_filter( 'frosty_media_add_plugin_license', 'frosty_media_register_licensed_plugin' );
`

#### Changelog

**Version 1.0.0 (*11/11/14*)**
* Initial Release.