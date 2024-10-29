<?php

defined( 'ABSPATH' ) || exit;

function bespoke_setup_admin() {
	$bespoke = bespoke();

	// Skip if already setup
	if ( empty( $bespoke->admin ) ) {

		// Require the admin class
		require_once $bespoke->includes_dir . 'admin/class-bespoke-admin.php';

		// Setup
		$bespoke->admin = class_exists( 'BespokeAdmin' )
			? new BespokeAdmin()
			: new stdClass();
	}

	// Return the admin object
	return $bespoke->admin;

}
