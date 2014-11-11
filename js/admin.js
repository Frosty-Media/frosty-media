(function ($) {
	
	"use strict";
	
	$(function () {
		
		var $notifications = $('#' + frosty_media_notifications.handle),
			$table = $('table.wp-list-table.notices'),
			$this;
		
		if ( $notifications.length > 0 || $table.length > 0 ) {

			// If so, we need to setup an event handler to trigger it's dismissal
			$(document).on('click', 'a[id^="' + frosty_media_notifications.action + '"]', function(e) {
				
				$this = $(this);
				
				e.preventDefault();

				// Initiate a request to the server-side
				$.post( ajaxurl, {
					action		: frosty_media_notifications.action,
					notice_id	: $this.data('notice-id'),
					nonce		: frosty_media_notifications.nonce
				}, function (response) {
					// If the response was successful (that is, 1 was returned), hide the notification;
					// Otherwise, we'll change the class name of the notification
					if ( '1' === response ) {
						
						if ( $notifications.length ) {
							$notifications.fadeOut('slow');
						}
						
						if ( $table.length ) {
							$table.find('a[data-notice-id="' + $this.data('notice-id') + '"]').fadeTo(250,0).parents('tr').css('background','');
						}
					}
					else {
							
						if ( $notifications.length ) {
							$notifications.fadeTo(400,0);
							setTimeout(function() {
								$notifications.removeClass('updated').addClass('error').fadeTo(350,1);
							}, 800);
						}
					}
				});
				
			});
		
			// Notifications highlight
			if ( $('table.notices td.read a').length ) {
				
				$('table.notices td.read a').each( function(index, element) {
					$(element).parents('tr').css('background','#FFFFE0');				
				});
			}
			
		} // end if
		
	});
	
	$.fn.utm_tracking = function(domain, source, medium, campaign) {
		$(this).find('a[href^="' + domain + '"]').each(function() {
			var url = $(this).attr('href');
			$(this).attr( 'href', url + '?utm_source=' + source + '&utm_medium=' + medium + '&utm_campaign=' + campaign );
		});
	}
	
}(jQuery));