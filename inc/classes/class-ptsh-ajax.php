<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class PTSH_AJAX
 */
class PTSH_AJAX {

    /**
     * @var string
     */
    private static $nonce_action = 'ptsh_ajax_nonce';

    /**
     * 
     */
    public static function __callStatic( $name, $args ) {
        $method_name = $name . '_page';
        if ( is_callable( 'PTSH_Hooks', $method_name ) && $slug = self::check_slug() ) {
            call_user_func_array( ['PTSH_Hooks', $method_name], [$slug] );
        }

        PTHS_Utils::send_fail( 'Unknown operation: ' . $name . '!' );
    }

    /**
     * @return false|string
     */
    public static function get_ajax_nonce() {
        return wp_create_nonce( self::$nonce_action );
    }

    /**
     * @return string|void
     */
    private static function check_slug() {
        if ( ! empty( $_GET['nonce'] ) && ! empty( $_GET['slug'] ) && wp_verify_nonce( $_GET['nonce'], self::$nonce_action ) ) {
            return filter_input( INPUT_GET, 'slug', FILTER_SANITIZE_SPECIAL_CHARS );
        }

        PTSH_Utils::send_fail( 'Validation failed!' );
    }
}