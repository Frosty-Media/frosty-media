<div class="wrap">

    <?php frosty_media_screen_icon(); ?>
    <?php printf( '<h2>Frosty Media %s</h2>', $this->title ); ?>

    <div class="postbox" style="margin-top:10px">
        <form autocomplete="off" action="" id="<?php printf( '%s-%s', FM_DIRNAME, sanitize_title( $this->title ) ); ?>" method="post">
            <?php $this->plugins_html(); ?>
        </form>
    </div>

</div>
