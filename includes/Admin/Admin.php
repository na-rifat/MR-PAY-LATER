<?php

namespace Mrpay\Admin;

/**
 * Handles the admin side
 */
class Admin {
    /**
     * Builds the class
     */
    function __construct() {
        add_action( 'admin_menu', [$this, 'register_menu'] );
        add_action( 'wp_ajax_mr_pay_save_merchant_code', [$this, 'mr_pay_save_merchant_code'] );
        add_action( 'init', [$this, 'handle_gateway'] );
        add_filter( 'init', [$this, 'wpex_wc_register_post_statuses'] );
        add_filter( 'init', [$this, 'update_status'] );
        add_filter( 'wc_order_statuses', [$this, 'wpex_wc_add_order_statuses'] );
    }

    public function handle_gateway() {
        if ( $this->have_woocommerce() ) {
            add_filter( 'woocommerce_payment_gateways', [$this, 'add_gateway'] );
            add_filter( 'plugin_action_links_' . MR_PAY_BASENAME, [$this, 'gateway_plugin_links'] );

            require_once MR_PAY_PATH . "/includes/Gatway/Gateway.php";
        }
    }

    /**
     * Adds plugin page links
     *
     * @param  array $links all plugin links
     * @return array $links all plugin links + our custom links (i.e., "Settings")
     */
    function gateway_plugin_links( $links ) {

        $plugin_links = array(
            '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=mrpaylater' ) . '">' . __( 'Configure', 'mrpay' ) . '</a>',
        );

        return array_merge( $plugin_links, $links );
    }

    /**
     * Add the gateway to WC Available Gateways
     * @param  array $gateways all available WC gateways
     * @return array $gateways all WC gateways + offline gateway
     */
    public function add_gateway( $gateways ) {

        if ( empty( get_option( 'mr_pay_merchant_code' ) ) ) {
            return $gateways;
        }

        $gateways[] = 'MR_PAY_LATER';

        return $gateways;
    }

    /**
     * Saves the merchant code from ajax request
     *
     * @return void
     */
    public function mr_pay_save_merchant_code() {
        /**
         * Nonce verification
         */
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'mr_pay_save_merchant_code' ) ) {
            wp_send_json_error(
                [
                    'message' => __( 'Invalid nonce!', 'mrpay' ),
                ]
            );
            return;
        }

        $merchant_code = sanitize_text_field( ! empty( $_POST['mr_pay_merchant_code'] ) ? $_POST['mr_pay_merchant_code'] : '' );

        /**
         * Merchant code verifcation
         */
        if ( strlen( $merchant_code ) == 0 ) {
            update_option( 'mr_pay_merchant_code', '' );
            wp_send_json_error(
                [
                    'message' => __( 'Invalid merchant code!', 'mrpay' ),
                ]
            );
            return;
        }

        if ( json_decode( wp_remote_retrieve_body( wp_remote_get( "https://members.mrpaylater.com/wordpress/list?merchant={$merchant_code}" ) ) ) == 'Record not found.' ) {
            update_option( 'mr_pay_merchant_code', '' );
            wp_send_json_error(
                [
                    'message' => __( 'Invalid merchant code!', 'mrpay' ),
                ]
            );
            return;
        }

        update_option( 'mr_pay_merchant_code', $merchant_code );

        wp_send_json_success(
            [
                'message' => __( 'Merchant code saved successfully!', 'mrpay' ),
            ]
        );
        return;
    }

    /**
     * Registers the menu to admin panel
     *
     * @return void
     */
    public function register_menu() {
        $page_title = $menu_title = 'MR PAY LATER';
        add_menu_page( $page_title, $menu_title, 'manage_options', 'mr-pay-later', [$this, 'admin_page'], 'dashicons-money-alt', 5 );
    }

    /**
     * Loads admin page
     *
     * @return void
     */
    public function admin_page() {
        $transactions = new Transactions();
        include __DIR__ . "/views/admin_page.php";
    }

    /**
     * Checks if the site have woocommerce installed
     *
     * @return void
     */
    public function have_woocommerce() {
        if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            return false;
        } else {
            return true;
        }
    }

    // Register New Order Statuses
    public function wpex_wc_register_post_statuses() {
        register_post_status( 'wc-success', array(
            'label'                     => _x( 'Success', 'WooCommerce Order status', 'mrpay' ),
            'paid'                      => true,
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Success (%s)', 'Success (%s)', 'mrpay' ),
        ) );
    }

    // Add New Order Statuses to WooCommerce
    public function wpex_wc_add_order_statuses( $order_statuses ) {
        $order_statuses['wc-success'] = _x( 'Success', 'WooCommerce Order status', 'mrpay' );
        return $order_statuses;
    }

    public function update_status() {
        $merchant_code = get_option( 'mr_pay_merchant_code', '' );

        if ( empty( $merchant_code ) ) {
            return;
        }

        // if ( ! isset( $_GET['post_type'] ) && ! isset( $_GET['page'] ) ) {
        //     return;
        // }

        // if ( $_GET['post_type'] != 'shop_order' && $_GET['page'] != 'wc-admin' ) {
        //     return;
        // }

        if ( in_array( 'post_type', $_GET ) && $_GET['post_type'] != 'shop_order' ) {
            return;
        }

        $transactions = json_decode( wp_remote_retrieve_body( wp_remote_get( "https://members.mrpaylater.com/wordpress/list?merchant={$merchant_code}" ) ) );

        foreach ( $transactions as $transaction ) {
            $order_id = str_replace( 'wp-', '', $transaction->invoice_number );
            switch ( $transaction->status ) {
                case 'success':
                    $order = wc_get_order( $order_id );

                    if ( ! empty( $order ) ) {
                        $order->update_status( 'success' );
                    }
                    break;
                case 'paid':
                    $order = wc_get_order( $order_id );

                    if ( ! empty( $order ) ) {
                        $order->update_status( 'completed' );
                    }
                    break;
                case 'fail':
                    $order = wc_get_order( $order_id );
                    if ( ! empty( $order ) ) {
                        $order->update_status( 'failed' );
                    }
                    break;
                case 'reject':
                    $order = wc_get_order( $order_id );
                    if ( ! empty( $order ) ) {
                        $order->update_status( 'cancelled' );
                    }
                    break;
                default:
                    break;
            }
        }
    }
}
