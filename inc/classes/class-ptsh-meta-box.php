<?php

defined( 'ABSPATH' ) || exit;

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