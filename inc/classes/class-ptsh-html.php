<?php

defined( 'ABSPATH' ) || exit;

class PTSH_HTML {
    public static function prepare_page( $page_body ) {
        return apply_filters( 'ptsh_page_body', $page_body );
    }
}