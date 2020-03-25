<?php

defined( 'ABSPATH' ) || exit;

class PTSH_Link {
    public static function optimize( $page_body ) {
        return preg_replace( '~href=["\']' . preg_quote( home_url() ) . '\/?(.+?)["\']~', 'href="/$1"', $page_body );
    }
}