<?php
defined( 'ABSPATH' ) || exit;

class ApplicationMissingException extends Exception {}
class AuthenticationMissingException extends Exception {}
class AuthenticationSaveException extends Exception {}

function bespoke_do_activation_redirect() {
    //error_log('doing activation redirect');
	// Bail if no activation redirect
	if ( ! get_transient( 'bespoke_activation_redirect' ) ) {
        //error_log('no transient :(');
		return;
	}

	// Delete the redirect transient
	delete_transient( 'bespoke_activation_redirect' );

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

	bespoke_redirect( add_query_arg( array( 'page' => 'bespoke-setting-admin' ), admin_url( 'options-general.php' ) ) );
}
