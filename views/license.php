<?php

$license = $args['license'];
$status = $args['status'];
$message = $args['message'];
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
</div>