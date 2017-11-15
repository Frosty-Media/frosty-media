/** @global frosty_media_licenses */
(function ($) {
  "use strict";

  $(function () {

    var $this = null,
      $license,
      $plugin_id,
      $plugin_action;

    var button,
      buttonwidth;

    // Variable input style width
    $('form#' + frosty_media_licenses.dirname + '-licenses div.inside').each(function (i, el) {
      $this = $(el);
      button = $this.children('.button-wrapper');
      buttonwidth = 100 * button.outerWidth() / $this.width();
      buttonwidth = buttonwidth <= 50 ? (98 - buttonwidth) : 100;

      $this.find('input[name$="[license_key]"]').css({
        width: buttonwidth + '%',
        padding: '6px',
        marginTop: '-1px'
      });
    });

    // On click let's run the update.
    $(document).on('click', 'form#' + frosty_media_licenses.dirname + '-licenses input[type="submit"]', function (e) {

      e.preventDefault();

      $this = $(this);
      $plugin_id = $this.parents('div.inside').data('plugin-id');
      $license = $this.parents('div.inside').find('input[name="' + $plugin_id + '[license_key]"]');
      $plugin_action = $this.prop('name');

      $this.before('<img id="img-' + $plugin_id + '" src="' + frosty_media_licenses.loading + '" style="margin:6px 0 0 -30px;position:absolute;">');

      // Initiate a request to the server-side
      var jqxhr = $.ajax({
        method: "POST",
        url: frosty_media_licenses.ajaxurl,
        data: {
          action: frosty_media_licenses.action,
          nonce: frosty_media_licenses.nonce,
          license: $license.val(),
          plugin_id: $plugin_id,
          plugin_action: $plugin_action,
          plugin_nonce: $license.data('nonce')
        },
        success: function (response) {
          if (typeof response.success !== 'undefined' && response.success) {
            window.location.reload();
          } else {
            console.log(response);
          }
        },
        fail: function () {
          alert('Unknown Error');
        }
      });

      jqxhr.always(function() {
        $('body').delay(1000).find('img#img-' + $plugin_id).remove();
      });

    });

  });
}(jQuery));