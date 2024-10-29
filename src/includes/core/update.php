<?php

defined( 'ABSPATH' ) || exit;

function bespoke_add_activation_redirect() {
	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

	// Add the transient to redirect
	set_transient( 'bespoke_activation_redirect', true, 30 );
}
