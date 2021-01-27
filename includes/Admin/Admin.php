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

        $merchant_code = ! empty( $_POST['mr_pay_merchant_code'] ) ? $_POST['mr_pay_merchant_code'] : '';

        /**
         * Merchant code verifcation
         */
        if ( sizeof( $merchant_code ) == 0 ) {
            update_option( 'mr_pay_merchant_code', '' );
            wp_send_json_error(
                [
                    'message' => __( 'Invalid merchant code!', 'mrpay' ),
                ]
            );
            return;
        }

        if ( file_get_contents( "https://members.mrpaylater.com/wordpress/list?merchant={$merchant_code}" ) == 'Record not found.' ) {
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
}
