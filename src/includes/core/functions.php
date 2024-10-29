<?php
defined( 'ABSPATH' ) || exit;

function bespoke_redirect( $location = '', $status = 302 ) {

	// Prevent errors from empty $location
	if ( empty( $location ) ) {
		$location = get_site_url();
	}

	// Setup the safe redirect
	wp_safe_redirect( $location, $status );

	// Exit so the redirect takes place immediately
	exit();
}

function partial(/* $func, $args... */) {
    $args = func_get_args();
    $func = array_shift($args);

    return function() use ($func, $args)
    {
        return call_user_func_array($func, array_merge($args, func_get_args()));
    };
}

function merge_paths($path1, $path2){
    $paths = func_get_args();
    $last_key = func_num_args() - 1;
    array_walk($paths, function(&$val, $key) use ($last_key) {
        switch ($key) {
            case 0:
                $val = rtrim($val, '/ ');
                break;
            case $last_key:
                $val = ltrim($val, '/ ');
                break;
            default:
                $val = trim($val, '/ ');
                break;
        }
    });

    $first = array_shift($paths);
    $last = array_pop($paths);
    $paths = array_filter($paths); // clean empty elements to prevent double slashes
    array_unshift($paths, $first);
    $paths[] = $last;
    return implode('/', $paths);
}

function join_paths() {
    $paths = array();

    foreach (func_get_args() as $arg) {
        if ($arg !== '') { $paths[] = $arg; }
    }

    return preg_replace('#/+#','/',join('/', $paths));
}
