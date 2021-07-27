<?php

namespace Mrpay;

/**
 * Registers essential assets
 */
class Assets {
    /**
     * Construct assets class
     */
    function __construct() {
        add_action( 'wp_enqueue_scripts', [$this, 'register'] );
        add_action( 'admin_enqueue_scripts', [$this, 'register'] );
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue'] );
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue'] );
    }

    /**
     * Return scripts from array
     *
     * @return array
     */
    public function get_scripts() {
        return [
            'mr-pay-admin-script' => [
                'src'     => jsfile( 'admin.js' ),
                'version' => jsversion( 'admin.js' ),
                'deps'    => ['jquery'],
            ],
        ];
    }

    /**
     * Return styles from array
     *
     * @return array
     */
    public function get_styles() {
        return [
            'mr-pay-admin-style' => [
                'src'     => cssfile( 'admin.css' ),
                'version' => cssversion( 'admin.css' ),
            ],
        ];
    }

    /**
     * Return localize variable from array
     *
     * @return array
     */
    public function get_localize() {
        global $post;
        return [
            'mr-pay-admin-script' => [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            ],
        ];
    }

    /**
     * Registers scripts, styles and localize variables
     *
     * @return void
     */
    public function register() {
        $scripts = $this->get_scripts();

        foreach ( $scripts as $handle => $script ) {
            $deps = isset( $script['deps'] ) ? $script['deps'] : false;

            wp_register_script( $handle, $script['src'], $deps, ! empty( $script['version'] ) ? $script['version'] : false, true );

        }

        $styles = $this->get_styles();

        foreach ( $styles as $handle => $style ) {
            $deps = isset( $style['deps'] ) ? $style['deps'] : false;

            wp_register_style( $handle, $style['src'], $deps, ! empty( $style['version'] ) ? $style['version'] : false );
        }

        $localize = $this->get_localize();

        foreach ( $localize as $handle => $vars ) {
            wp_localize_script( $handle, 'mr_pay', $vars );
        }
    }

    /**
     * Loads the scripts to frontend
     *
     * @return void
     */
    public function enqueue() {
        if ( is_admin() ) {
            wp_enqueue_script( 'mr-pay-admin-script' );
            wp_enqueue_style( 'mr-pay-admin-style' );
        }
    }
}