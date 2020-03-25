<?php

defined( 'ABSPATH' ) || exit;

class PTSH_Image {
    public static function work_with_images( $page_body ) {
        if ( true ) {
            $page_body = self::lazy_load( $page_body );
        }

        return $page_body;
    }

    private static function lazy_load( $page_body ) {
        return preg_replace( '/(<img.+src="(.+?)".+?(?:\/?)>)/', '<img class="lazy" src="' . PTSH_PLUGIN_DIR_URL . 'assets/img/placeholder.png" data-src="$2" /><noscript>$1</noscript>', $page_body );
    }
}