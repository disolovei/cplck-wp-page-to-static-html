<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class _Page_To_Static_HTML
 */
final class Page_To_Static_HTML {

    /**
     * @var null
     */
    private static $instance = null;

    /**
     * @return Page_To_Static_HTML
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param $slug
     * @throws PTSH_Exception
     */
    public function save( $slug ) {
        $ptsh_request = new PTSH_Request( $slug );

        if ( PTSH_FS::write_file( $slug, $ptsh_request->fetch_page() ) ) {

            do_action( 'ptsh_page_saved', $slug );

            PTSH_Utils::send_success( [
                'html'      => PTSH_Meta_Box::the_already_saved_markup( true ),
                'message'   => 'Saved success!',
            ] );
        }

        throw new PTSH_Exception( 'Cannot write HTML to file!' );
    }

    /**
     * @param $slug
     * @throws PTSH_Exception
     */
    public function regenerate( $slug ) {
        if ( ! PTSH_FS::delete_page( $slug ) ) {
            throw new PTSH_Exception( "Cannot delete page with slug {$slug}!" );
        }

        $this->save( $slug );
    }

    /**
     * @param $slug
     * @throws PTSH_Exception
     */
    public function delete( $slug ) {
        if ( PTSH_FS::delete_page( $slug ) ) {

            do_action( 'ptsh_page_deleted', $slug );

            PTSH_Utils::send_success([
                'html'      => PTSH_Meta_Box::the_need_save_markup( true ),
                'message'   => 'Deleted success!',
            ]);
        }

        throw new PTSH_Exception( 'Deleted fail!' );
    }

    /**
     *
     */
    public function include_admin_assets() {
        wp_enqueue_script( 'ptsh-admin', PTSH_PLUGIN_DIR_URL . 'js/admin.js', ['jquery'], null, true );
        wp_localize_script( 'ptsh-admin', 'ptshadmin', [
            'nonce' => PTSH_AJAX::get_ajax_nonce(),
        ] );
    }

    public function include_assets() {
        wp_enqueue_script( 'ptsh-main', PTSH_PLUGIN_DIR_URL . 'js/frontend.js', ['jquery'], null, true );
    }

    /**
     * Page_To_Static_HTML constructor.
     */
    private function __construct() {
        $this->define_contants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     *
     */
    private function init_hooks() {
        add_action( 'add_meta_boxes', ['PTSH_Meta_Box', 'create_meta_box'] );
        add_action( 'admin_enqueue_scripts', [$this, 'include_admin_assets'] );
        add_action( 'wp_enqueue_scripts', [$this, 'include_assets'] );

        //AJAX Actions
        add_action( 'wp_ajax_ptsh_save', ['PTSH_AJAX', 'save'] );
        add_action( 'wp_ajax_ptsh_delete', ['PTSH_AJAX', 'delete'] );
        add_action( 'wp_ajax_ptsh_regenerate', ['PTSH_AJAX', 'regenerate'] );

        //Plugins own actions
        add_action( 'ptsh_page_save', ['PTSH_Hooks', 'save_page'] );
        add_action( 'ptsh_page_delete', ['PTSH_Hooks', 'delete_page'] );
        add_action( 'ptsh_page_regenerate', ['PTSH_Hooks', 'save_page'] );

        add_filter( 'ptsh_page_body', ['PTSH_HTML', 'prepare_page'] );
    }

    /**
     * 
     */
    private function define_contants() {
        $constants = [
            'version'           => '0.8',
            'plugin_dir_url'    => plugin_dir_url( PTSH_MAINFILE ),
        ];

        foreach ( $constants as $name => $value ) {
            $contant_name = 'PTSH_' . strtoupper( $name );
            if ( defined( $contant_name ) ) {
                PTSH::send_fail( 'Constant ' . $contant_name . ' already defined!' );
            }

            define( $contant_name, $value );
        }
    }

    /**
     * 
     */
    private function includes() {
        foreach ( glob( PTSH_ABSPATH . 'inc/classes/class-*.php' ) as $file_path ) {
            include_once $file_path;
        }
    }
}

function PTSH() {
    return Page_To_Static_HTML::get_instance();
}