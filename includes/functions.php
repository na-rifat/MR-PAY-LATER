<?php
/**
 * Author: Rafalo tech
 * Description: MR PAY LATER essential functions
 */

/**
 * Return a image files url
 *
 * @param  [type] $filename
 * @return void
 */
if ( ! function_exists( 'imgfile' ) ) {
    function imgfile( $filename ) {
        return MR_PAY_IMAGES . "/$filename";
    }
}

/**
 * Return a css files url
 *
 * @param  [type] $filename
 * @return void
 */
if ( ! function_exists( 'cssfile' ) ) {
    function cssfile( $filename ) {
        return MR_PAY_CSS . "/$filename";
    }
}

/**
 * Return a js files url
 *
 * @param  [type] $filename
 * @return void
 */
if ( ! function_exists( 'jsfile' ) ) {
    function jsfile( $filename ) {
        return MR_PAY_JS . "/$filename";
    }
}

/**
 * Get js files version based on date modified
 *
 * @param  [type] $filename
 * @return void
 */
if ( ! function_exists( 'jsversion' ) ) {
    function jsversion( $filename ) {
        return filemtime( convert_path_slash( MR_PAY_PATH . "/assets/js/$filename" ) );
    }
}

/**
 * Get css files version based on date modified
 *
 * @param  [type] $filename
 * @return void
 */
if ( ! function_exists( 'cssversion' ) ) {
    function cssversion( $filename ) {
        return filemtime( convert_path_slash( MR_PAY_PATH . "/assets/css/$filename" ) );
    }
}

/**
 * Replaces back slashes with slashes from a files path
 *
 * @param  [type] $path
 * @return void
 */
if ( ! function_exists( 'convert_path_slash' ) ) {
    function convert_path_slash( $path ) {
        return str_replace( "\\", "/", $path );
    }
}
