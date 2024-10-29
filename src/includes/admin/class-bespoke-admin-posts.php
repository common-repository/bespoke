<?php
defined( 'ABSPATH' ) or die( 'Unauthorized' );

if ( ! class_exists( 'BespokeAdminPosts' ) ) :

/**
 * BespokeAdminPosts is responsible for rendering Bespoke status on Posts
 * screen.
 */
class BespokeAdminPosts extends BespokeBase {

    protected function setup_filters() {
        add_filter( 'manage_posts_columns', array( $this, 'add_status_column' ) );
    }

    protected function setup_actions() {
        add_action( 'manage_posts_custom_column' , array( $this, 'status_column_details' ) );
    }

    function add_status_column($columns) {
        $columns['bespoke_status'] = __( 'Bespoke', 'your_text_domain' );
        return $columns;
    }

    function status_column_details($column) {
        global $post;
        if ( $column == 'bespoke_status' ) {
            $status = get_post_meta( $post->ID, 'bespoke_status', true );
            if ( empty( $status ) ) {
                echo 'Not available';
                return;
            } else {
                echo $status;
                return;
            }
        }
    }
}

endif;
