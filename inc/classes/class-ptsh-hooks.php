<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class PTSH_Hooks
 */
class PTSH_Hooks {

    /**
     * @param $slug
     * @throws PTSH_Exception
     */
    public static function save_page( $slug ) {
        self::start_process( $slug, 'save' );
    }

    /**
     * @param $slug
     * @throws PTSH_Exception
     */
    public static function delete_page( $slug ) {
        self::start_process( $slug, 'delete' );
    }

    /**
     * @param $slug
     * @throws PTSH_Exception
     */
    public static function regenerate_page( $slug ) {
        self::start_process( $slug, 'regenerate' );
    }

    /**
     * @param $slug
     * @param string $process_type
     * @throws PTSH_Exception
     */
    private static function start_process( $slug, $process_type = 'save' ) {
        if ( ! in_array( $process_type, ['save', 'delete', 'regenerate'] ) ) {
            throw new PTSH_Exception( "Unauthorized operation {$process_type}!" );
        }

        try {
            call_user_func_array([PTSH(), $process_type], [PTSH_Utils::prepare_slug($slug)]);
        } catch ( PTSH_Exception $e ) {
            PTSH_Utils::send_fail( $e->getMessage() );
        }
    }
}