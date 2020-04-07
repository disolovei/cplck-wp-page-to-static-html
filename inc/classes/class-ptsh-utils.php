<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class Utils
 */
class PTSH_Utils {

    /**
     * @return bool
     */
    public static function is_ajax() {
        return function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' );
    }

    /**
     * @param $message
     * @param int $code
     */
    public static function send_success( $message, $code = 200 ) {
        if ( self::is_ajax() ) {
            wp_send_json_success( $message, $code );
        }
    }

    /**
     * @param $message
     * @param int $code
     */
    public static function send_fail( $message, $code = 400 ) {
        if ( self::is_ajax() ) {
            wp_send_json_error( $message, $code );
        }

        error_log( $message );
    }

    /**
     * @param $slug
     * @return mixed
     */
    public static function prepare_slug( $slug ) {
        $get_posts_args = [
            'post_type'     => 'any',
            'numberposts'   => 1,
            'post_status'   => 'publish',
        ];

        if ( is_numeric( $slug ) ) {
            $get_posts_args['include'] = (int)$slug;
        } else {
            $get_posts_args['name'] = $slug;
        }

        $posts = get_posts( $get_posts_args );

        return empty( $posts ) ? $slug : str_replace( get_option( 'siteurl' ), '', get_the_permalink( $posts[0]->ID ) );
    }
}