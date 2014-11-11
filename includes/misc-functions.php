<?php

function frosty_media_screen_icon() {
	echo get_frosty_media_screen_icon();
}

function get_frosty_media_screen_icon( $style = 'margin:10px 10px 0; width:32px;' ) {
	return sprintf( '<img src="%s" style="float:left; %s">',
		FROSTYMEDIA()->get_data_uri( 'svg/frosty-media.svg', 'svg+xml' ),
		$style
	);
}