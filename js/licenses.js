(function ($) {

	"use strict";

	$(function() {

		var	$this = null,
			$license,
			$plugin_id,
			$plugin_action,
			$text;

		var	button,
			buttonwidth;

		// Variable input style width
		$('form#' + frosty_media_licenses.dirname + '-licenses div.inside').each(function(index, element) {
			$this			= $(this);
			button			= $this.children('.button-wrapper');
			buttonwidth	= 100 * button.outerWidth()/$this.width();
			buttonwidth	= buttonwidth <= 50 ? (98 - buttonwidth) : 100;

			$this.children('input[type="text"]:first').css('width', buttonwidth + '%');
		});

		// On click let's run the update.
		$(document).on('click', 'form#' + frosty_media_licenses.dirname + '-licenses input[type="submit"]', function(e) {

			e.preventDefault();

			$this			= $(this);
			$plugin_id		= $this.parents('div.inside').data('plugin-id');
			$license		= $this.parents('div.inside').find('input[name="' + $plugin_id + '[license_key]"]');
			$plugin_action	= $this.prop('name');
			
			$this.before('<img id="img-' + $plugin_id + '" src="' + frosty_media_licenses.loading + '" style="margin:6.5px 0px 0px -30px;position:absolute;">');

			// Initiate a request to the server-side
			$.post( ajaxurl, {
				action			: frosty_media_licenses.action,
				nonce			: frosty_media_licenses.nonce,
				license			: $license.val(),
				plugin_id		: $plugin_id,
				plugin_action	: $plugin_action,
			}, function( response ) {

				if ( 'success' === response ) {
					location.reload();
				}
				else {
					console.log(response);
				}
			})
			.fail( function() {
				alert( "error" );
			})
			.always( function() {
				$('body').delay(1000).find('img#img-' + $plugin_id).remove();
			});

		});

	});
}(jQuery));