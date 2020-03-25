<?php

defined( 'ABSPATH' ) || exit;

class PTSH_Minimize_HTML {
    public static function minify( $page_body ) {
        return preg_replace( ['/>\s+</', '/(?:[\n|\t]|<!--.*-->)/U', '/ {2,}]/'], ['><','', ' '], $page_body );
    }
}