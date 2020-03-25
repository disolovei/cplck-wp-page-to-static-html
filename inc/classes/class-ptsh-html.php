<?php

defined( 'ABSPATH' ) || exit;

final class PTSH_HTML implements Iterator {

    private $position = 0;

    private $handlers = [];

    public static function prepare_page( $page_body ) {
        $walker = new self();

        foreach ( $walker as $handler ) {

            if ( is_callable( $handler ) ) {
                $page_body = call_user_func_array( $handler, [$page_body] );
            }
        }

        return $page_body;
    }

    public function rewind() {
        $this->position = 0;
    }

    public function current() {
        return $this->handlers[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return ! empty( $this->handlers[$this->position] );
    }

    private function __construct() {
        $this->position = 0;

        $this->handlers = array_merge( 
            [
                ['PTSH_Image', 'work_with_images'],
                ['PTSH_Minimize_HTML', 'minify'],
                ['PTSH_Link', 'optimize'],
            ],
            apply_filters( 'ptsh_html_handlers', [] )
         );
    }
}