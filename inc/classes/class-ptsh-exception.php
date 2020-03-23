<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class PTSH_Exception
 */
class PTSH_Exception extends Exception {
    public function send_message() {
        PTSH_Utils::send_fail( $this->getMessage() );
    }
}