<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class PTSH_Request
 */
class PTSH_Request {

    /**
     * @var string
     */
    private $slug = '';

    /**
     * PTSH_Request constructor.
     * @param $slug
     */
    public function __construct( $slug ) {
        $this->slug = $slug;
    }

    /**
     * @return mixed|void
     * @throws PTSH_Exception
     */
    public function fetch_page() {
        global $wp_version;
        $permalink = home_url( $this->slug );

        $remote_get = wp_remote_get( $permalink, array(
            'timeout'     => 5,
            'redirection' => 5,
            'httpversion' => '1.0',
            'user-agent'  => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
            'blocking'    => true,
            'headers'     => array(),
            'cookies'     => array(),
            'body'        => null,
            'compress'    => false,
            'decompress'  => true,
            'sslverify'   => true,
            'stream'      => false,
            'filename'    => null
        ) );

        if ( $remote_get['response']['code'] !== 200 ) {
            throw new PTSH_Exception( "Cannot fetch a page {$permalink}!" );
        }

        if ( $remote_get['body'] === '' ) {
            throw new PTSH_Exception( 'Page have empty body!' );
        }

        return PTSH_HTML::prepare_page( $remote_get['body'] );
    }
}