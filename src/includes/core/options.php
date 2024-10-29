<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BespokeOptions' ) ) :

class BespokeOptions extends BespokeBase {
    static $prefix = "bespoke";

    public function setup_actions() {
       add_action( 'bespoke_activation', array($this, 'add_options'));
       add_action( 'bespoke_deactivation', array($this, 'delete_options'));
    }

    function format_field_name( $name ) {
        return self::$prefix . '_' . $name;
    }

    public function growth_hack_text() {
        return self::get_option( "growth_hack_text" );
    }

    public function growth_hack_enabled() {
        $postId = get_the_ID();
        if ( !!$postId ) {
            return get_post_meta( $postId, self::format_field_name( 'teaser_enabled' ), true);
        } else {
            return !!self::get_option( "growth_hack_enabled" );
        }
    }

    public function post_status_column_enabled() {
        return !!self::get_option( "post_status_column_enabled" );
    }

    public function growth_hack_link() {
        $postId = get_the_ID();
        if ( !!$postId ) {
            return self::get_option( "growth_hack_link" )
                . '?ref=wp-plugin&refurl=' . urlencode( get_permalink( $postId ) );
        } else {
            return self::get_option( "growth_hack_link" );
        }
    }

    function get_default_options() {
       return (array) apply_filters( 'bespoke_get_default_options', array(
          'access_token'               => '',
          'api'                        => array(),
          'code'                       => '',
          'refresh_token'              => '',
          'scope'                      => '',
          'token_type'                 => '',

          'growth_hack_link'           => 'https://bespoke.app',
          'growth_hack_text'           => __('Are you an expert on this topic? Get a featured spot here!'),
          'growth_hack_enabled'        => 1,

          'post_status_column_enabled' => 0,
       ));
    }

    function add_options() {
       // Add default options
       foreach ( $this->get_default_options() as $key => $value ) {
          self::add_option( $key, $value );
       }

       // Allow previously activated plugins to append their own options.
       do_action( 'bespoke_add_options' );
    }

    function delete_options() {
       foreach ( array_keys( $this->get_default_options() ) as $key ) {
          self::delete_option( $key );
       }

       do_action( 'bespoke_delete_options' );
    }

    static function get_option( $key ) {
       return get_option( self::format_field_name( $key ) );
    }

    static function add_option( $key, $value ) {
       return add_option( self::format_field_name( $key ), $value );
    }

    static function update_option( $key, $value ) {
       return update_option( self::format_field_name( $key ), $value );
    }

    static function delete_option( $key ) {
       return delete_option( self::format_field_name( $key ) );
    }

}

endif;
