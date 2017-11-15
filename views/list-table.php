<?php

if ( ! class_exists( '\FrostyMedia\List_Table' ) ) {
    require_once dirname( __DIR__ ) . '/src/list-table.php';
}

if ( ! isset( $this->title ) ) {
    $notices_table = new \FrostyMedia\Includes\ListTable();
    $notices_table->prepare_items( 4, true );
    $notices_table->display();
} else { ?>
    <div class="wrap"><?php

    frosty_media_screen_icon();
    printf( '<h2>Frosty Media %s</h2>', $this->title );

    $notices_table = new \FrostyMedia\Includes\ListTable();
    $notices_table->prepare_items();
    $notices_table->display(); ?>
    </div><?php
}
