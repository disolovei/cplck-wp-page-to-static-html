<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class PTSH_FS
 */
class PTSH_FS {
    /**
     * @param $slug
     * @param bool $with_file
     * @return string
     */
    public static function get_file_path_by_slug( $slug, $with_file = true ) {
        return ABSPATH . $slug . ( $with_file ? '/index.html' : '' );
    }

    /**
     * @param $slug
     * @return bool
     */
    public static function is_page_saved( $slug ) {
        return file_exists( self::get_file_path_by_slug( $slug ) );
    }

    /**
     * TODO
     * @param $slug
     * @return bool
     */
    public static function delete_page( $slug ) {
        return self::is_page_saved( $slug ) && unlink( self::get_file_path_by_slug( $slug ) ) && rmdir( self::get_file_path_by_slug( $slug, false ) ); //refactor me
    }

    /**
     * @param $slug
     * @param $body
     * @return bool
     * @throws PTSH_Exception
     */
    public static function write_file( $slug, $body ) {
        self::prepare_folder( $slug );
        return (bool)file_put_contents( self::get_file_path_by_slug( $slug ), $body );
    }

    /**
     * @param $slug
     * @throws PTSH_Exception
     */
    private static function prepare_folder( $slug ) {
        $folder_path = self::get_file_path_by_slug( $slug, false );

        if ( ! file_exists( $folder_path ) && ! wp_mkdir_p( $folder_path ) ) {
            throw new PTSH_Exception( "Cannot create a folder {$folder_path}!" );
        }
    }

    /**
     * TODO
     */
    private static function prepare_path( $path ) {
        return $path;
    }
}