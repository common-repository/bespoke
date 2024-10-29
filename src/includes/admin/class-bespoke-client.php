<?php
defined( 'ABSPATH' ) or die( 'Unauthorized' );

if ( ! class_exists( 'BespokeClient' ) ) :

class BespokeClient extends BespokeBase {

    /**
     * Headers for all requests and default, overrideable values
     * @returns array The default options array
     */
    static function default_options() {
        return array(
            'method' => 'GET',
            'timeout' => 10,
            'redirection' => 2,
            'headers' => array(
                "Content-type"  =>  "application/json",
                //"Authorization" =>  "Bearer " . $this->oauth->access_token()
            ),
        );
    }

    /**
     * @param string $path The bespoke url to hit
     * @param array $options The request options with the following keys:
     *   'method': one of ['GET', 'POST', ...]
     *   'query': key-value array
     *   'body': POST body, must be json-encodable
     *   'headers': additional headers, overrides
     */
    protected function request($path, $options, $payload=null) {
        // Build request url with query
        if ( empty($options) ) { $options = array(); }
        $url = bespoke()->api_url($path);
        $qs = !empty($options['query']) ? http_build_query($options['query']) : '';

        $url = !empty($qs) ? $url . '?' . $qs : $url;
        // Merge provided options on top of defaults
        $options = array_merge(self::default_options(), $options);

        // Body can be explicitly set or the payload will be encoded
        $options['body'] = empty($payload) ? $options['body'] : json_encode( $payload );

        // TODO (cjc) Should handle errors here
        return wp_remote_request($url, $options);
    }

}
endif;
