<?php
defined( 'ABSPATH' ) or die( 'Unauthorized' );

if ( ! class_exists( 'OAuth' ) ) :

/**
 * Negotiations the process of creating a new OAuth application in the
 * associated app and obtaining an access token.
 */
class OAuth {
    const REDIRECT_APP_CREATED = 1;
    const REDIRECT_NONCE_CREATED = 2;
    const REDIRECT_INVALID = 2;

    protected $name;

    protected $scopes;

    protected $url_base;

    protected $api_url_base;

    protected $redirect_oauth_url;

    protected $redirect_action_name = 'add_oauth_code';

    public function __construct($config) {
        $this->setup_variables($config);
        $this->setup_actions();
    }

    /**
     * Initializes the instance variables from a config array.
     *
     * @param array $config
     *     'name': custom application name for oauth
     *     'base_url': the app server url
     *     'api_base_url': the app server api url
     *     'redirect_action_name': (optional) used in construction of auth code redirect
     *     'custom_redirect_callback': (optional) function name or array($class, $func)
     *     'redirect_oauth_url': (optional) url template
     */
    protected function setup_variables($config) {
        if (!empty($config['name']))
            $this->name = $config['name'];

        if (!empty($config['scopes']))
            $this->scopes = $config['scopes'];

        if (!empty($config['url_base']))
            $this->url_base = $config['url_base'];

        if (!empty($config['api_url_base']))
            $this->api_url_base = $config['api_url_base'];

        if (!empty($config['redirect_action_name']))
            $this->redirect_action_name = $config['redirect_action_name'];

        if (!empty($config['redirect_oauth_url']))
            $this->redirect_oauth_url = $config['redirect_oauth_url'];

        $this->redirect_uri = admin_url() . 'admin-ajax.php?action=' . $this->redirect_action_name;

        $this->redirect_callback = !empty($config['custom_redirect_callback'])
            ? $config['custom_redirect_callback']
            : array($this, 'handle_oauth_redirect');
    }

    protected function setup_actions() {
        //add_action( 'wp_ajax_nopriv_oauth_connect', array($this, 'connect'));
        //add_action( 'wp_ajax_oauth_connect',        array($this, 'connect'));

        // Route handler to receive bootstrapping code to obtain first auth token
        add_action( 'wp_ajax_nopriv_add_oauth_code', array($this, 'redirect_callback'));
        add_action( 'wp_ajax_add_oauth_code',        array($this, 'redirect_callback'));
    }

    protected function generate_app_name() {
        return str_replace('.', '_', parse_url(get_site_url(), PHP_URL_HOST));
    }

    /**
     * Headers for all requests and default, overrideable values
     * @returns array The default options array
     */
    protected function default_options() {
        return array(
            'method'      => 'GET',
            'timeout'     => 10,
            'redirection' => 2,
            'headers'     => array(
            ),
        );
    }

    /**
     * @param string $path The service url to hit
     * @param array $options The request options with the following keys:
     *   'method': one of ['GET', 'POST', ...]
     *   'query': key-value array
     *   'body': POST body
     *   'headers': additional headers, overrides
     */
    protected function request($path, $options=null, $payload=null) {
        // Build request url with query
        $url = merge_paths($this->api_url_base, $path);
        $qs = !empty($options['query']) ? http_build_query($options['query']) : '';
        $url = !empty($qs) ? $url . '?' . $qs : $url;

        // Merge provided options on top of defaults
        $req = array_merge($this->default_options(), $options);

        // Body can be explicitly set or the payload will be encoded
        $req['body'] = $payload;

        // TODO (cjc) Should handle errors here
        return wp_remote_request($url, $req);
    }

    public function connected() {
        try {
            $this->authentication();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Oauth negotiation steps
     *   1. Redirect -> Service to create an application
     *   2. Service redirect browser to wp w/ oauth bootstrapping params
     *   3. WP uses redirect params to post to Service and swap bootstrapping
     *      code for first access token
     */

    /**
     * Oauth negotiation Step 1 - Client WP -> Service to create an application
     */
    public function connect() {
        error_log('oauth connecting');
        wp_redirect($this->connect_url());
        exit();
    }

    /**
     * Uses the refresh token to acquire a new access token (if necessary)
     * @param boolean force_refresh If set truthy then a new token will always
     *     be acquired
     * @returns string A valid access token (new or old)
     */
    public function access_token($code=null, $force_refresh=False) {
        // Assumes we've already created an application
        $app = $this->application();

        if (!empty($code)) { // First time through oauth flow
            $params = array(
                'client_id'    => $app['client_id'],
                'redirect_uri' => $this->redirect_uri,
                'code'         => $code,
                'grant_type'   => 'authorization_code',
            );
        } else { // This is a refresh attempt
            $auth = $this->authentication();

            // If no refresh triggers then return early
            if ( !($force_refresh && $auth['expires'] > time() + 10) )
                return $auth['access_token'];

            $params = array(
                'client_id'     => $app['client_id'],
                'client_secret' => $app['client_secret'],
                'refresh_token' => $auth['refresh_token'],
                'grant_type'    => 'refresh_token',
            );
        }

        $response = $this->request('/o/token/', array( 'method' => 'POST' ), $params);

        // TODO (cjc) handle errors

        if ( is_array( $response ) ) {
            $header = $response['headers']; // array of http header lines
            $auth = json_decode( $response['body'], True ); // use the content
        } else {
            // ?
            throw new Exception('request was not sucessful');
        }

        $this->save_authentication($auth);
        return $auth['access_token'];
    }

    function detect_redirect() {
        if (isset($_GET['code'])) {
            return OAuth::REDIRECT_NONCE_CREATED;
        } else if (isset($_GET['client_id'])) {
            return OAuth::REDIRECT_APP_CREATED;
        } else {
            return OAuth::REDIRECT_INVALID;
        }
    }

    /**
     * All redirects come through here
     */
    public function handle_oauth_redirect(){
        // Determine what kind of redirect it is
        switch($this->detect_redirect()) {
            case OAuth::REDIRECT_APP_CREATED:
                // Save app, get nonce
                $app = $this->save_application($_GET);
                $authorize_uri = merge_paths($this->url_base, "/o/authorize/")
                    . "?client_id={$app['client_id']}"
                    . "&client_secret={$app['client_secret']}"
                    . "&scope=". $this->scope_string()
                    . "&response_type=code";

                wp_redirect($authorize_uri);
                break;
            case OAuth::REDIRECT_NONCE_CREATED:
                // Use nonce to get auth token
                try {
                    $this->access_token($_GET['code']);
                    header('Location: '.str_replace('{result}', 'success', $this->redirect_oauth_url));
                } catch (Exception $e) {
                    header('Location: '.str_replace('{result}', 'error', $this->redirect_oauth_url));
                }
                break;
            default:
                header('Location: '.$this->redirect_oauth_fail);
                break;
        }
        exit();
    }

    /**
     * Persists an authentication object as necessary
     * @param array $authentication The authentication object to persist
     *      'access_token': used for authenticated requests
     *      'refresh_token': token to use to request new access tokens
     *      'scope': permissions associated with the current access token
     *      'expires': when the current access token expires
     *      'token_type': 'Bearer'
     */
    protected function save_authentication($authentication) {
        $auth = $authentication;

        if (!isset( $auth['access_token'] ))   { throw new AuthenticationSaveException(); }
        if (!isset( $auth['refresh_token'] ) ) { throw new AuthenticationSaveException(); }
        if (!isset( $auth['token_type'] ) )    { throw new AuthenticationSaveException(); }
        if (!isset( $auth['scope'] ) )         { throw new AuthenticationSaveException(); }

        // DO VALIDATION HERE
        //$auth['expires'] = FORMAT

        update_option( 'oauth_authentication', $auth );
    }

    /**
     * Retrieves the currently associated authentication
     * @raises AuthenticationMissingException Thrown if authentication is missing
     */
    protected function authentication() {
        $auth = get_option( 'oauth_authentication' );

        if (empty($auth)) {
            throw new AuthenticationMissingException();
        }

        return $auth;
    }

    /**
     * Persists the oauth application fields (client_id, client_secret)
     */
    protected function save_application($application) {
        $app = $application;

        $client_id = isset($app['client_id']) ? $app['client_id'] : null;
        $client_secret = isset($app['client_secret']) ? $app['client_secret'] : null;

        // TODO (cjc) validate fields
        $app = array(
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'timestamp'     => (new DateTime('NOW'))->format('c'),
        );

        update_option( 'oauth_application', $app );

        return $app;
    }

    /**
     * Retrieves the currently associated application
     * @raises ApplicationMissingException Thrown if application is missing
     */
    protected function application() {
        $app = get_option( 'oauth_application' );

        if (empty($app)) {
            throw new ApplicationMissingException();
        }

        return $app;
    }

    protected function scope_string() {
        return implode($this->scopes, '%20');
    }

    public function connect_url() {
        return merge_paths($this->url_base, '/o/applications/register/')
          . '?name=' . (empty($this->name) ? $this->generate_app_name() : $this->name)
          . '&redirect_uri=' . urlencode($this->redirect_uri);
    }
}
endif;
