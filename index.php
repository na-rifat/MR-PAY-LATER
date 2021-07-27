<?php
    /**
     * Plugin Name: MR PAY LATER
     * Plugin URI: https://mrpaylater.com/
     * Description: Woocommerce payment gateway
     * Author: Mr Pay Later
     * Author https://mrpaylater.com/
     * Version: 1.0.0
     * Text Domain: mrpay
     * License: GNU General Public License v3.0
     * License URI: http://www.gnu.org/licenses/gpl-3.0.html
     */

    namespace Mrpay;

    // use MR_pay\Admin\Admin;

    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    require_once __DIR__ . "/vendor/autoload.php";

    /**
     * Initial class
     */
    class Mr_pay {
        const version = '1.0.0';
        /**
         * Builds the class
         */
        public function __construct() {
            $this->define_constants();
            add_action( 'plugins_loaded', [$this, 'load_classes'] );
        }

        /**
         * Defines constants
         *
         * @return void
         */
        public function define_constants() {
            define( 'MR_PAY_VERSION', self::version );
            define( 'MR_PAY_PATH', __DIR__ );
            define( 'MR_PAY_FILE', __FILE__ );
            define( 'MR_PAY_PLUGIN_PATH', plugins_url( '', MR_PAY_FILE ) );
            define( 'MR_PAY_ASSETS', MR_PAY_PLUGIN_PATH . '/assets' );
            define( 'MR_PAY_JS', MR_PAY_ASSETS . '/js' );
            define( 'MR_PAY_CSS', MR_PAY_ASSETS . '/css' );
            define( 'MR_PAY_IMAGES', MR_PAY_ASSETS . '/img' );
            define( 'MR_PAY_BASENAME', plugin_basename( __FILE__ ) );
        }

        /**
         * Initializes the class
         *
         * @return void
         */
        public static function init() {
            $instance = false;
            if ( ! $instance ) {
                $instance = new self();
            }
            return $instance;
        }

        public function load_classes() {
            new Admin\Admin();
            new Assets();
        }
    }

    /**
     * Loads the class, basically the plugin
     *
     * @return mixed
     */
    function load() {
        return Mr_pay::init();
    }

    load();
    return;
?>

