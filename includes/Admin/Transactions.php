<?php

namespace Mrpay\Admin;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . '/wp-admin/includes/class-wp-list-table.php';
}

class Transactions extends \WP_List_Table {
    function __construct() {
        parent::__construct( array(
            'singular' => 'transaction',
            'plural'   => 'transactions',
            'ajax'     => false,
        ) );
    }

    /**
     * Get columns
     *
     * @return void
     */
    function get_columns() {
        return array(
            'invoice_number' => __( 'Invoice number', 'mrpay' ),
            'customer_name'  => __( 'Customer name', 'mrpay' ),
            'customer_email' => __( 'Customer email', 'mrpay' ),
            'amount'         => __( 'Amount', 'mrpay' ),
            'product'        => __( 'Product', 'mrpay' ),
            'status'         => __( 'Status', 'mrpay' ),
            'created_at'     => __( 'Created at', 'mrpay' ),
        );
    }

    /**
     * Sortable columns list
     *
     * @return void
     */
    function get_sortable_columns() {
        $sortable_columns = [

        ];
        return $sortable_columns;
    }

    /**
     * Formats and sends default comments
     *
     * @param  [type] $item
     * @param  [type] $column_name
     * @return void
     */
    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'action':
                $url = admin_url( "admin.php?page=current-year-clients&action=manage&client_id={$item->client_id}" );
                return esc_html( "<a href='{$url}' class='button button-large'>Manage</a>" );
                break;
            default:
                return esc_html( isset( $item->$column_name ) ? $item->$column_name : '' );
                break;
        }
    }

    /**
     * Prepares items
     *
     * @return void
     */
    public function prepare_items() {
        $column   = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array( $column, $hidden, $sortable );

        $items = $this->get_transaction_list();

        $this->items = $items['transactions'];

        $this->set_pagination_args( array(
            'total_items' => $items['count'],
            'per_page'    => $items['count'],
        ) );
    }

    /**
     * Generates content for a single row of the table.
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     * @since 3.1.0
     *
     * @param object|array $item The current item
     */
    public function single_row( $item ) {
        // complete the order

        $class = $this->status2class( $item->status );
        echo "<tr class='{$class}'>";
        $this->single_row_columns( $item );
        echo '</tr>';
    }

    /**
     * Converts status to class
     *
     * @param  [type] $status
     * @return void
     */
    public function status2class( $status ) {
        // return $status;
        if ( $status == 'success' ) {
            return 'mr-pay-transaction-success';
        } elseif ( $status == 'fail' ) {
            return 'mr-pay-transaction-failed';
        } elseif ( $status == 'paid' ) {
            return 'mr-pay-transaction-paid';
        }
    }

    /**
     * Gets transaction list from API
     *
     * @return void
     */
    function get_transaction_list() {
        $merchant_code = get_option( 'mr_pay_merchant_code' );
        $transactions  = json_decode( wp_remote_retrieve_body( wp_remote_get( "https://members.mrpaylater.com/wordpress/list?merchant={$merchant_code}" ) ) );

        // $transactions = json_decode( file_get_contents( "https://members.mrpaylater.com/wordpress/list?merchant={$merchant_code}" ) );

        return [
            'transactions' => $transactions,
            'count'        => $transactions == NULL ? 0 : sizeof( $transactions ),
        ];
    }

    /**
     * Creates the list
     *
     * @param  [type] $list
     * @return void
     */
    function _show( $list ) {
        $list->prepare_items();
        $list->display();
    }
}
