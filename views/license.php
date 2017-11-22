<?php

$license = $args['license'];
$status = $args['status'];
$message = $args['message'];
$minimum = isset( $minimum ) && $minimum;
?>
<div class="inside" data-plugin-id="<?php echo $plugin['id']; ?>">

    <?php printf( '<h4>%s</h4>', esc_html( $plugin['title'] ) ); ?>

    <label for="<?php echo esc_attr( $plugin['id'] ); ?>[license_key]">
        <?php _e( 'License Key:', FM_DIRNAME ); ?>
    </label><br>
    <input type="text"
           name="<?php echo $plugin['id']; ?>[license_key]"
           value="<?php echo $license; ?>"
           data-nonce="<?php echo wp_create_nonce( $plugin['id'] . '-license-nonce' ); ?>"
           class="large-text"<?php echo $minimum ? ' readonly': ''; ?>>
    <?php if ( ! $minimum ) : ?>
        <div class="button-wrapper alignright">
            <?php
            $atts = array( 'tabindex' => $args['key'] );

            if ( 'valid' === $status ) {
                submit_button( $this->get_strings()['deactivate-license'], 'button-primary', sprintf( '%s_deactivate', $plugin['id'] ), false, $atts );
                echo '&nbsp;&nbsp;';
                submit_button( $this->get_strings()['check-license'], 'button-secondary', sprintf( '%s_check_license', $plugin['id'] ), false, $atts );
            }
            else {
                submit_button( $this->get_strings()['activate-license'], 'button-primary', sprintf( '%s_activate', $plugin['id'] ), false, $atts );
            } ?>
        </div>
    <?php endif; ?>

    <p>
        <span class="description">
            <?php printf( __( 'Status: <span>%s</span>', FM_DIRNAME ), esc_html( $message ) ); ?>
        </span>
    </p>
    <hr>
</div>