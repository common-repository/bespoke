<?php
/*
Plugin Name: bespoke
Plugin URI: https://bespoke.app
Description: bespoke by Presscast
Version: 1.1
Author: Bespoke
Author URI: https://bespoke.app
License: GPLv2
*/

defined( 'ABSPATH' ) or die( 'Unauthorized' );

define( 'BESPOKE_PLUGIN_VERSION', '1.0.0'  );

define( 'BESPOKE_PLUGIN_PATH', plugin_dir_url( __FILE__ ));

register_activation_hook(   __FILE__, array( 'Bespoke', 'on_activation' ) );
register_deactivation_hook( __FILE__, array( 'Bespoke', 'on_deactivation' ) );
register_uninstall_hook(    __FILE__, array( 'Bespoke', 'on_uninstall' ) );

// Assume you want to load from build
$bespoke_loader = __DIR__ . '/build/bespoke.php';

// Load from source if no build exists
if ( ! file_exists( $bespoke_loader ) || defined( 'BESPOKE_LOAD_SOURCE' ) ) {
        $bespoke_loader = __DIR__ . '/src/bespoke.php';
}

// Include bespoke
include $bespoke_loader;

// Unset the loader, since it's loaded in global scope
unset( $bespoke_loader );
