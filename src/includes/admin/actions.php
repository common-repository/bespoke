<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_head', 'bespoke_admin_head' );
add_action( 'admin_menu', 'bespoke_admin_menu' );
add_action( 'admin_init', 'bespoke_admin_init' );

add_action( 'add_meta_boxes', 'bespoke_add_meta_boxes' );
add_action( 'do_meta_boxes', 'bespoke_do_meta_boxes' );

add_action( 'wp_insert_post', 'bespoke_save_post');

// Hook onto admin_init
// add_action( 'bespoke_activation', 'bespoke_do_activation_redirect', 1 );

// Initialize admin area
add_action( 'bespoke_init', 'bespoke_setup_admin');

add_action( 'post_submitbox_start', 'bespoke_add_submit_button' );
// add_action( 'post_submitbox_misc_actions', 'bespoke_add_submit_status' );
// Url-specific actions
add_action( 'wp_ajax_nopriv_connect_with_bespoke', 'bespoke_ajax_connect' );
add_action( 'wp_ajax_connect_with_bespoke',        'bespoke_ajax_connect' );
//
//add_action( 'wp_ajax_nopriv_add_bespoke_code', 'bespoke_handle_oauth_redirect' );
//add_action( 'wp_ajax_add_bespoke_code',        'bespoke_handle_oauth_redirect' );

// function bespoke_add_submit_status() {
//     do_action('bespoke_add_submit_status');
// }

function bespoke_add_submit_button() {
    do_action('bespoke_add_submit_button');
}

function bespoke_ajax_connect() {
    do_action('bespoke_connect');
}

function bespoke_handle_oauth_redirect() {
    do_action('bespoke_handle_oauth_redirect');
}

function bespoke_admin_head() {
    do_action('bespoke_admin_head');
}

function bespoke_admin_menu() {
    do_action('bespoke_admin_menu');
}

function bespoke_admin_init() {
    do_action('bespoke_admin_init');
}

function bespoke_add_meta_boxes() {
    do_action('bespoke_add_meta_boxes');
}

function bespoke_do_meta_boxes() {
    do_action('bespoke_do_meta_boxes');
}

function bespoke_save_post($postarr, $error = false) {
    do_action('bespoke_save_post', $postarr, $error);
}

//function bespoke_do_activation_redirect() {
//    do_action('bespoke_activation_redirect');
//}
