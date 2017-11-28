<?php

/**
 *
 */
function frosty_media_screen_icon() {
	echo get_frosty_media_screen_icon();
}

/**
 * @param string $style
 *
 * @return string
 */
function get_frosty_media_screen_icon( $style = 'margin:-2px 10px 0; width:32px;' ) {
	return sprintf( '<img src="%s" style="float:left; %s">',
		FrostyMedia\Includes\Common::get_data_uri( 'svg/frosty-media.svg', 'svg+xml' ),
		$style
	);
}

/**
 * The main function responsible for returning the one true
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $fm = FROSTYMEDIA(); ?>
 *
 * @return FrostyMedia\Includes\Core The one true Instance
 */
function FROSTYMEDIA() {
    return FrostyMedia\Includes\Core::instance();
}
