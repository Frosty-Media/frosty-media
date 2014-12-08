Frosty Media
============

The core functionallity that manages all Frosty.Media licenses, settings, auto-updates and notifications.

### Usage

Download zip and install, or as a standalone include. (Download and install is required for all [Frosty Media](http://frosty.media) purchased plugins. 

Use the function below inside your plugin you need licensed and managed.

```php
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
```

### Changelog

~Current Version:1.0.2~

##### Version 1.0.2 *12/08/14*
* Update FM_API_URL to https.
* Updated EDD_SL_Plugin_Updater.php to version 1.5.

##### Version 1.0.1 *11/14/14*
* Added: edd-sl-api/ enpoint to license API URL.

##### Version 1.0.0 *11/11/14*
* Initial Release.