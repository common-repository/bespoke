<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Bespoke' ) ) :

final class Bespoke {
    public static $name = 'bespoke';

    public static function instance() {

            // Store the instance locally to avoid private static replication
            static $instance = null;

            // Only run these methods if they haven't been ran previously
            if ( null === $instance ) {
                    $instance = new Bespoke;
                    $instance->setup_environment();
                    $instance->includes();
                    $instance->setup_variables();
                    $instance->setup_actions();
            }

            // Always return the instance
            return $instance;
    }

    private function __construct() { /* Do nothing here */ }

    public function setup_environment() {
        $this->file         = __FILE__;
        $this->basename     = apply_filters( 'bespoke_plugin_basename', str_replace( array( 'build/', 'src/' ), '', plugin_basename( $this->file ) ) );
        $this->basepath     = apply_filters( 'bespoke_plugin_basepath', trailingslashit( dirname( $this->basename ) ) );

        $this->plugin_dir   = apply_filters( 'bespoke_plugin_dir_path', plugin_dir_path( $this->file ) );
        $this->plugin_url   = apply_filters( 'bespoke_plugin_dir_url',  plugin_dir_url ( $this->file ) );

        $this->includes_dir = apply_filters( 'bespoke_includes_dir', trailingslashit( $this->plugin_dir . 'includes'  ) );
        $this->includes_url = apply_filters( 'bespoke_includes_url', trailingslashit( $this->plugin_url . 'includes'  ) );
    }

    public function includes() {
        // include $this->plugin_dir . 'config/overrides.php';
        require $this->plugin_dir . 'config/base.php';

        require $this->includes_dir . 'core/class-bespoke-base.php';
        require $this->includes_dir . 'core/abstraction.php';
        require $this->includes_dir . 'core/functions.php';
        require $this->includes_dir . 'core/options.php';
        // NOTE (cjc) moved to actions
        //require $this->includes_dir . 'core/sub-actions.php';
        require $this->includes_dir . 'core/update.php';

        require $this->includes_dir . 'common/shortcodes.php';

        //require $this->includes_dir . 'core/extend.php';
        require $this->includes_dir . 'core/actions.php';
        //require $this->includes_dir . 'core/filters.php';

        if (is_admin()) {
            require $this->includes_dir . 'admin/actions.php';
        }
    }

    public function setup_variables() {
        $this->scopes = array(
            'bespoke',
            // Add more scopes as needed
        );

        $this->ref = 'presscast.io';

        $this->dev_mode = 0;

        if ($this->dev_mode) {
            $this->url_base = 'http://presscast.io';
            $this->api_base = 'http://presscast.io:9000';
        } else {
            $this->url_base = 'https://presscast.io';
            $this->api_base = 'https://presscast.io';
        }

        $this->scope_string = implode('%20', $this->scopes);
        $this->redirect_uri = admin_url() . 'admin-ajax.php?action=add_bespoke_code';

        // Display installation screen
        $this->debug_installation_screen = false;
        $this->options = new BespokeOptions();
    }

    public function setup_actions() {
        // Add actions to plugin activation and deactivation hooks
        add_action( 'activate_'   . $this->basename, 'bespoke_activation'   );
        add_action( 'deactivate_' . $this->basename, 'bespoke_deactivation' );
        add_action('bespoke_activated', array($this, 'activated'));

        if ( $this->is_deactivation( $this->basename ) ) {
          return;
        }

        add_action( 'bespoke_register_shortcodes', array( $this, 'register_shortcodes' ) );
        add_action( 'wp_enqueue_scripts',          array( $this, 'add_scripts' ) );
        add_action( 'wp_footer',                   array( $this, 'add_settings' ) );
        add_filter( 'query_vars',                  array( $this, 'add_query_vars_filter' ) );
    }

    static function format_field_name( $name ) {
        return self::$name . '_' . $name;
    }

    static function settings_config() {
        $arr = array(
            'growth_hack_text'           => array(__('Growth Hack Message'), 'input', 'textarea', BespokeOptions::get_option('growth_hack_text')),
            'growth_hack_enabled'        => array(__('Growth Hack Enabled'), 'input', 'checkbox', BespokeOptions::get_option('growth_hack_enabled')),
            'post_status_column_enabled' => array(__('Post Status Column Enabled'), 'input', 'checkbox', BespokeOptions::get_option('post_status_column_enabled')),
        );

        foreach ( array_keys($arr) as $k ) {
            $arr[self::format_field_name($k)] = $arr[$k];
            unset($arr[$k]);
        }

        return $arr;
    }

    static function on_activation() {
        foreach ( self::settings_config() as $key => $config ) {
            update_option($key, $config[3]);
        }
    }

    function activated() {
        wp_redirect(self::settings_url());
    }

    static function on_deactivation() {

    }

    static function on_uninstall() {

    }

    function is_deactivation( $basename = '' ) {
        global $pagenow;

        $action = false;

        // Bail if not in admin/plugins
        if ( ! ( is_admin() && ( 'plugins.php' === $pagenow ) ) ) {
          return false;
        }

        if ( ! empty( $_REQUEST['action'] ) && ( '-1' !== $_REQUEST['action'] ) ) {
          $action = $_REQUEST['action'];
        } elseif ( ! empty( $_REQUEST['action2'] ) && ( '-1' !== $_REQUEST['action2'] ) ) {
          $action = $_REQUEST['action2'];
        }

        // Bail if not deactivating
        if ( empty( $action ) || ! in_array( $action, array( 'deactivate', 'deactivate-selected' ), true ) ) {
          return false;
        }

        // The plugin(s) being deactivated
        if ( $action === 'deactivate' ) {
          $plugins = isset( $_GET['plugin'] ) ? array( $_GET['plugin'] ) : array();
        } else {
          $plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
        }

        // Set basename if empty
        if ( empty( $basename ) && ! empty( $this->basename ) ) {
          $basename = $this->basename;
        }

        // Bail if no basename
        if ( empty( $basename ) ) {
          return false;
        }

        return in_array( $basename, $plugins, true );
    }

    public function register_shortcodes() {
        $this->shortcodes = new BespokeShortcodes();
    }

    public function url() {
        return rtrim($this->url_base, '/') .'/'. ltrim(implode(func_get_args(), '/'), '/');
    }

    public function api_url() {
        return rtrim($this->api_base, '/') .'/'. ltrim(implode(func_get_args(), '/'), '/');
    }

    public function is_connected() {
        return $this->admin->connected();
    }

    static function settings_url() {
        return admin_url() . 'options-general.php?page=bespoke-setting-admin';
    }

    public function generate_app_name() {
        return str_replace('.', '_', parse_url(get_site_url(), PHP_URL_HOST));
    }

    /**
     * Given a script file, generates url to it.
     */
    public function js_path($js){
        return $this->plugin_url . 'public/js/' . $js;
    }

    public function img_path($img){
        return $this->plugin_url . 'public/img/' . $img;
    }

    public function css_path($css){
        return $this->plugin_url . 'public/css/' . $css;
    }

    public function vendor_path($path){
        return $this->plugin_url . 'public/vendor/' . $path;
    }

    /**
     * Adds on-page feature and style scripts
     */
    function add_scripts() {
        wp_enqueue_script( 'bespoke_js', $this->js_path('bespoke.js'), false );
        wp_enqueue_style( 'bespoke_css', $this->css_path('style.css'), false );
    }

    function add_settings() {
        ?>
            <script type="text/javascript">
            window.bespokeConfig = {
                bannerEnabled: <?= $this->options->growth_hack_enabled(); ?>,
                bannerMessage: "<?= $this->options->growth_hack_text(); ?>",
                bannerLink: "<?= $this->options->growth_hack_link(); ?>"
            };
            </script>
        <?php
    }

    // add the ref variable to wp accessible variable
    // used in ?ref=bespoke for bespoke_more shortcode
    function add_query_vars_filter( $vars ){
        $vars[] = "ref";
        return $vars;
    }

    /**
     * Returns value of bespoke config constant. False if not defined.
     */
    public function config( $var ) {
        $cons = "BESPOKE_".strtoupper($var);
        $val = defined($cons) && constant($cons);
        return $val;
    }
}

function bespoke() {
    return Bespoke::instance();
}

bespoke();

endif;
