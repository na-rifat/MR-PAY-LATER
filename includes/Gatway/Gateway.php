<?php
require_once MR_PAY_PATH . "/includes/functions.php";
defined( 'ABSPATH' ) or exit;
wc_offline_gateway_init();
function wc_offline_gateway_init() {

    class MR_PAY_LATER extends WC_Payment_Gateway {
        public $mr_author_info;
        public $mr_order_id;

        /**
         * Constructor for the gateway.
         */
        public function __construct() {

            $this->id                 = 'mrpaylater';
            $this->icon               = imgfile( 'ico.jpeg' );
            $this->has_fields         = false;
            $this->method_title       = __( 'MR PAY LATER', 'mrpay' );
            $this->method_description = __( 'Installment Payment with 0% interest.', 'mrpay' );

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title = $this->get_option( 'title' );
            // $this->description  = $this->get_option( 'description' );
            $this->description  = 'Installment Payment with 0% interest. Confirmation will be upon completion of RM 1 authorization.';
            $this->instructions = $this->get_option( 'instructions', $this->description );

            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

            // Customer Emails
            add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
        }

        /**
         * Initialize Gateway Settings Form Fields
         */
        public function init_form_fields() {

            $this->form_fields = apply_filters( 'wc_offline_form_fields', array(

                'enabled'      => array(
                    'title'   => __( 'Enable/Disable', 'mrpay' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable MR PAY LATER', 'mrpay' ),
                    'default' => 'yes',
                ),

                'title'        => array(
                    'title'       => __( 'Title', 'mrpay' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'mrpay' ),
                    'default'     => __( 'MR PAY LATER', 'mrpay' ),
                    'desc_tip'    => true,
                ),

                // 'description'  => array(
                //     'title'       => __( 'Description', 'mrpay' ),
                //     'type'        => 'textarea',
                //     'description' => __( 'Payment method description that the customer will see on your checkout.', 'mrpay' ),
                //     'default'     => __( 'Installment Payment with 0% interest. Confirmation will be upon completion of RM 1 authorization.', 'mrpay' ),
                //     'desc_tip'    => true,
                // ),

                'instructions' => array(
                    'title'       => __( 'Instructions', 'mrpay' ),
                    'type'        => 'textarea',
                    'description' => __( 'Instructions that will be added to the thank you page and emails.', 'mrpay' ),
                    'default'     => 'Thank you for using MR PAY LATER.',
                    'desc_tip'    => true,
                ),
            ) );
        }

        /**
         * Output for the order received page.
         */
        public function thankyou_page() {
            session_start();
            var_dump( $_SESSION['test_info'] );
            if ( $this->instructions ) {
                echo wpautop( wptexturize( $this->instructions ) );
            }
        }

        /**
         * Add content to the WC emails.
         *
         * @access public
         * @param WC_Order $order
         * @param bool     $sent_to_admin
         * @param bool     $plain_text
         */
        public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {

            if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
                echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
            }
        }

        /**
         * Process the payment and return the result
         *
         * @param  int     $order_id
         * @return array
         */
        public function process_payment( $order_id ) {

            $order = wc_get_order( $order_id );

            // Mark as on-hold (we're awaiting the payment)
            $order->update_status( 'on-hold', __( 'Awaiting offline payment', 'wc-gateway-offline' ) );

            // Reduce stock levels
            wc_reduce_stock_levels( $order_id );

            // Remove cart
            WC()->cart->empty_cart();

            $this->mr_order_id = $order_id;
            session_start();

            $args = $this->send_info_to_author( $order );

            // $this->handle_payment_window( $args );

            // Return thank you redirect
            return array(
                'result'   => 'success',
                'redirect' => $args->msg, //$this->get_return_url( $args->msg ),
            );
        }

        public function send_info_to_author( $order ) {

            $atts = $this->mr_process_order_info( $order );

            // $postdata = http_build_query(
            //     array(
            //         'merchant_code'  => get_option( 'mr_pay_merchant_code' ),
            //         'invoice_number' => $atts['invoice_no'], //need to be unique and max 20 character,
            //         'customer_name'  => $atts['customer_name'],
            //         'customer_email' => $atts['customer_email'],
            //         'customer_phone' => $atts['customer_phone'], //remove any +6 (country code from the phone number)
            //         'amount'         => $atts['amount'],
            //         'remark'         => $atts['remark'], //series of product need to separate by comma
            //     )
            // );

            // $opts = array( 'http' => array(
            //     'method'  => 'POST',
            //     'header'  => 'Content-Type: application/x-www-form-urlencoded',
            //     'content' => $postdata,
            // ),
            // );

            // $context = stream_context_create( $opts ); //return $atts;
            // $result  = json_decode( file_get_contents( 'https://members.mrpaylater.com/wordpress/record', false, $context ) );

            $body = array(
                'merchant_code'  => get_option( 'mr_pay_merchant_code' ),
                'invoice_number' => $atts['invoice_no'], //need to be unique and max 20 character,
                'customer_name'  => $atts['customer_name'],
                'customer_email' => $atts['customer_email'],
                'customer_phone' => $atts['customer_phone'], //remove any +6 (country code from the phone number)
                'amount'         => $atts['amount'],
                'remark'         => $atts['remark'], //series of product need to separate by comma
            );

            $args = array(
                'body'        => $body,
                'timeout'     => '5',
                'redirection' => '5',
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     => array(),
                'cookies'     => array(),
            );
            
            $result = wp_remote_post( 'https://members.mrpaylater.com/wordpress/record', $args );

            return $result;
        }

        public function mr_process_order_info( $order ) {
            $result = [];

            // Process the product names
            $products = [];

            foreach ( $order->get_items() as $product ) {
                array_push( $products, $product['name'] );
            }

            $products = implode( ', ', $products );

            $formatted_phone = str_replace( '+6', '', $order->get_billing_phone() );

            if ( substr( $formatted_phone, 0, 1 ) == '6' ) {
                $formatted_phone = substr( $formatted_phone, 1, sizeof( $formatted_phone ) - 1 );
            }

            $result['invoice_no']     = 'wp-' . $order->get_id();
            $result['customer_name']  = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            $result['customer_email'] = $order->get_billing_email();
            $result['customer_phone'] = $formatted_phone;
            $result['amount']         = $order->get_total();
            $result['remark']         = $products;

            return $result;
        }

        public function handle_payment_window( $args ) {
            if ( $args->status != 'success' ) {
                return;
            }

            $loader = "<script>window.open({$args->msg})?>)</script>";
        }

    } // end \MR_PAY_LATER class

}
