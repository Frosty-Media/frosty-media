<?php

namespace FrostyMedia\Includes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class List_Table
 *
 * @package FrostyMedia
 * @ref http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/
 */
class ListTable extends \WP_List_Table {

    public $data;

    protected $action;

    /**
     *
     */
    public function __construct() {

        $this->data = FROSTYMEDIA()->notifications->get_notices();
        $this->action = sanitize_title_with_dashes( FM_DIRNAME . ' Notifications' );

        parent::__construct( [
            'singular' => __( 'notice', FM_DIRNAME ),
            'plural' => __( 'notices', FM_DIRNAME ),
            'ajax' => false,
        ] );
    }

    /**
     * @param object $item
     * @param string $column_name
     *
     * @return mixed|string
     */
    public function column_default( $item, $column_name ) {
        $notice_id = array_search( $item, $this->items );

        switch ( $column_name ) {
            case 'notice_id':
                return $notice_id;

            case 'posted':
                return date_i18n( get_option( 'date_format' ), strtotime( $item->date ) );

            case 'message':
                return wp_kses( $item->message, [
                    'a' => [
                        'href' => [],
                        'title' => [],
                    ],
                    'br' => [],
                    'em' => [],
                    'strong' => [],
                ] );

            case 'read':
                return $item->read ?
                    sprintf( '<strong>%s</strong><br><span>%s</span>',
                        __( 'Yes', FM_DIRNAME ),
                        date_i18n( get_option( 'date_format' ), strtotime( $item->read_date ) )
                    ) :
                    sprintf( '<strong>%1$s</strong><br><a href="#" id="%2$s[%3$s]" data-notice-id="%3$s">%4$s</a>',
                        __( 'No', FM_DIRNAME ),
                        $this->action,
                        $notice_id,
                        __( 'Mark as read', FM_DIRNAME )
                    );

            default:
                return print_r( $item, true );
        }
    }

    /**
     * @return array
     */
    public function get_columns() {
        $columns = [
            'notice_id' => __( 'ID', FM_DIRNAME ),
            'posted' => __( 'Posted', FM_DIRNAME ),
            'message' => __( 'Message', FM_DIRNAME ),
            'read' => __( 'Read', FM_DIRNAME ),
        ];

        return $columns;
    }

    /**
     * @param int $per_page
     * @param bool $pageination
     */
    public function prepare_items( $per_page = 10, $pageination = false ) {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];
        $this->_column_headers = [ $columns, $hidden, $sortable ];

        // First let's sort the data
        if ( is_array( $this->data ) ) {
            arsort( $this->data ); // Sort in reverse order and maintain index
        }

        $current_page = $this->get_pagenum();
        $total_items = count( $this->data );

        if ( ! $pageination ) {
            $this->set_pagination_args( [
                'total_items' => $total_items,
                'per_page' => $per_page,
                'total_pages' => ceil( $total_items / $per_page ),
            ] );
        }

        $this->items = $this->data;
    }
}
