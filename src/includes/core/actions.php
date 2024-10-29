<?php

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'bespoke_init', 0 );

add_action( 'bespoke_init', 'bespoke_register', 10);

add_action( 'bespoke_register', 'bespoke_register_shortcodes', 10 );

//add_action( 'bespoke_activation', 'bespoke_add_activation_redirect' );

add_action( 'activated_plugin', 'bespoke_activated', 10, 2 );

function bespoke_activation() {
	do_action( 'bespoke_activation' );
}

function bespoke_deactivation() {
	do_action( 'bespoke_deactivation' );
}

function bespoke_init() {
	do_action( 'bespoke_init' );
}

function bespoke_register() {
	do_action( 'bespoke_register' );
}

function bespoke_register_shortcodes() {
	do_action( 'bespoke_register_shortcodes' );
}

//function bespoke_add_activation_redirect() {
//	do_action( 'bespoke_add_activation_redirect' );
//}

function bespoke_activated() {
	do_action( 'bespoke_activated' );
}
