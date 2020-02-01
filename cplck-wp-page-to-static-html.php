<?php
/*
 * Plugin Name: CPLCK Page To Static HTML
 * Author: Dima Solovey
 * Version: 0.8
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PTSH_Exception
 */
class PTSH_Exception extends Exception {
    public function send_message() {
        PTSH_Utils::send_fail( $this->getMessage() );
    }
}

/**
 * Class Utils
 */
class PTSH_Utils {

    /**
     * @return bool
     */
    public static function is_ajax() {
        return function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' );
    }

    /**
     * @param $message
     * @param int $code
     */
    public static function send_success( $message, $code = 200 ) {
        if ( self::is_ajax() ) {
            wp_send_json_success( $message, $code );
        }
    }

    /**
     * @param $message
     * @param int $code
     */
    public static function send_fail( $message, $code = 400 ) {
        if ( self::is_ajax() ) {
            wp_send_json_error( $message, $code );
        }

        error_log( $message );
    }

    /**
     * @param $slug
     * @return mixed
     */
    public static function prepare_slug( $slug ) {
        $posts = get_posts( [
            'post_type'     => 'any',
            'numberposts'   => 1,
            'post_status'   => 'publish',
            'name'          => $slug,
        ] );

        return empty( $posts ) ? $slug : str_replace( get_option( 'siteurl' ), '', get_the_permalink( $posts[0]->ID ) );
    }
}

/**
 * Class PTSH_AJAX
 */
class PTSH_AJAX {

    /**
     * @var string
     */
    private static $nonce_action = 'ptsh_ajax_nonce';

    /**
     * @throws PTSH_Exception
     */
    public static function save() {
        PTSH_Hooks::save_page( self::check_field() );
    }

    /**
     * @throws PTSH_Exception
     */
    public static function delete() {
        PTSH_Hooks::delete_page( self::check_field() );
    }

    /**
     * @throws PTSH_Exception
     */
    public static function regenerate() {
        PTSH_Hooks::regenerate_page( self::check_field() );
    }

    /**
     * @return false|string
     */
    public static function get_ajax_nonce() {
        return wp_create_nonce( self::$nonce_action );
    }

    /**
     * @return string|void
     */
    private static function check_field() {
        if ( ! empty( $_GET['nonce'] ) && ! empty( $_GET['slug'] ) && wp_verify_nonce( $_GET['nonce'], self::$nonce_action ) ) {
            return filter_input( INPUT_GET, 'slug', FILTER_SANITIZE_SPECIAL_CHARS );
        }

        PTSH_Utils::send_fail( 'Validation failed!' );
    }
}

/**
 * Class PTSH_Metabox
 */
class PTSH_Meta_Box {

    /**
     * @param $post
     * @param $meta
     */
    public static function meta_box_callback( $post, $meta ) {
        echo '<div id="ptsh-controls">' . self::the_meta_box_markup( PTSH_Utils::prepare_slug( $post->post_name ) ) . '</div>';
    }

    /**
     * @param $slug
     */
    public static function the_meta_box_markup( $slug ) {
        ?>

            <div id="ptsh-target-slug" data-slug="<?php echo $slug; ?>">

                <?php if ( PTSH_FS::is_page_saved( $slug ) ) {
                    self::the_already_saved_markup();
                } else {
                    self::the_need_save_markup();
                } ?>

            </div>

        <?php
    }

    /**
     *
     */
    public static function create_meta_box() {
        add_meta_box( 'save-as-html', 'Save as HTML', [__CLASS__, 'meta_box_callback'], ['post', 'page'], 'side', 'high' );
    }

    /**
     * @param bool $return
     * @return void|string
     */
    public static function the_already_saved_markup( $return = false ) {
        if ( $return ) ob_start();
        ?>

        <p class="saved-message" style="padding:.5rem;font-size:1.5rem;text-align:center;">Already saved!</p>
        <p style="text-align:center">
            <button type="button" class="button button-primary ptsh-action ptsh-action--delete">Delete saved</button>
            <button type="button" class="button button-primary ptsh-action ptsh-action--regenerate">Regenerate HTML</button>
        </p>

        <?php
        if ( $return ) return ob_get_clean();
    }

    /**
     * @param bool $return
     * @return void|string
     */
    public static function the_need_save_markup( $return = false ) {
        if ( $return ) ob_start();
        ?>

        <p>
            <button type="button" class="button button-primary ptsh-action ptsh-action--save">Save as HTML</button>
        </p>

        <?php
        if ( $return ) return ob_get_clean();
    }
}

/**
 * Class PTSH_FS
 */
class PTSH_FS {
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
        return self::is_page_saved( $slug ) && unlink( self::get_file_path_by_slug( $slug ) ) && rmdir( self::get_file_path_by_slug( $slug, false ) );
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
}

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
            call_user_func_array([CPLCK_Page_To_Static_HTML::get_instance(), $process_type], [PTSH_Utils::prepare_slug($slug)]);
        } catch ( PTSH_Exception $e ) {
            PTSH_Utils::send_fail( $e->getMessage() );
        }
    }
}

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

        return apply_filters( 'ptsh_page_body', $remote_get['body'] );
    }
}

/**
 * Class CPLCK_Page_To_Static_HTML
 */
final class CPLCK_Page_To_Static_HTML {

    /**
     * @var null
     */
    private static $instance = null;

    /**
     * @return CPLCK_Page_To_Static_HTML|null
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

            PTSH_Utils::send_success([
                'html'      => PTSH_Meta_Box::the_already_saved_markup( true ),
                'message'   => 'Saved success!',
            ]);
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
    public function include_assets() {
        wp_enqueue_script( 'ptsh-admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', ['jquery'], null, true );
        wp_localize_script( 'ptsh-admin', 'ptshadmin', [
            'nonce' => PTSH_AJAX::get_ajax_nonce(),
        ] );
    }

    /**
     * @param $body
     * @return string|string[]|null
     */
    public function body_filter( $body ) {
        return preg_replace( ['/>\s+</', '/(?:[\n|\t]|<!--.*-->)/U', '/ {2,}]/'], ['><','', ' '], $body );
    }

    /**
     * CPLCK_Page_To_Static_HTML constructor.
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     *
     */
    private function init_hooks() {
        add_action( 'add_meta_boxes', ['PTSH_Meta_Box', 'create_meta_box'] );
        add_action( 'admin_enqueue_scripts', [$this, 'include_assets'] );

        //AJAX Actions
        add_action( 'wp_ajax_ptsh_save', ['PTSH_AJAX', 'save'] );
        add_action( 'wp_ajax_ptsh_delete', ['PTSH_AJAX', 'delete'] );
        add_action( 'wp_ajax_ptsh_regenerate', ['PTSH_AJAX', 'regenerate'] );

        //Plugins own actions
        add_action( 'ptsh_page_save', ['PTSH_Hooks', 'save_page'] );
        add_action( 'ptsh_page_delete', ['PTSH_Hooks', 'delete_page'] );
        add_action( 'ptsh_page_regenerate', ['PTSH_Hooks', 'save_page'] );

        add_filter( 'ptsh_page_body', [$this, 'body_filter'] );
    }
}

CPLCK_Page_To_Static_HTML::get_instance();