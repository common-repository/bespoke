<?php
defined( 'ABSPATH' ) or die( 'Unauthorized' );

if ( ! class_exists( 'BespokeAdmin' ) ) :

class BespokeAdmin extends BespokeBase {
    /**
     * Holds the values to be used in the fields callbacks
     */
    protected $options;

    /**
     * Holds metabox class instance
     */
    protected $metaboxes;

    protected function setup_globals() {
        $this->bespoke = bespoke();
        $this->admin_dir = trailingslashit( $this->bespoke->includes_dir . 'admin' );
    }

    protected function includes() {
        // TODO (cjc) Clean up the metaboxes file
        require $this->admin_dir . 'class-bespoke-client.php';
        require $this->admin_dir . 'class-bespoke-admin-settings-page.php';
        require $this->admin_dir . 'class-bespoke-admin-posts.php';
        require $this->admin_dir . 'class-bespoke-meta-boxes.php';
        require $this->admin_dir . 'class-oauth.php';
        require $this->admin_dir . 'common.php';
    }

    protected function setup_variables() {
        // TODO reenable metaboxes when connect is available
        $this->metaboxes = new BespokeMetaBoxes();
        $this->settingsPage = new BespokeAdminSettingsPage();

        if ( $this->bespoke->options->post_status_column_enabled() ) {
            $this->posts = new BespokeAdminPosts();
        }

        $this->client = new BespokeClient();
        $this->oauth = new OAuth(array(
            'url_base'         => bespoke()->url(),
            'api_url_base'     => bespoke()->api_url(),
            'scopes'           => array('bespoke'),
            'redirect_oauth_url' => admin_url('/options-general.php'
                . '?page=bespoke-setting-admin'
                . '&authorized={result}')
        ));
    }

    protected function setup_actions() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

        add_action( 'bespoke_save_post', array( $this, 'save_post'), 15);

        // Auth connection handlers
        add_action('bespoke_connect', array( $this, 'connect'));
    }

    /**
     * Hook triggered upon post save (draft, publish, etc)
     */
    public function save_post( $post_id ) {
        self::update_teaser_settings($post_id);
    }

    /**
     * Derives the nonce id from the action name and returns it
     */
    protected static function get_nonce_id( $action ) {
        return 'bespoke_' . $action . '_nonce';
    }

    /**
     * Returns bool indicating whether or not POST data contained valid nonce for the action
     */
    protected static function valid_action( $action ) {
        $nonceId = self::get_nonce_id( $action );
        return ! ( ! isset( $_POST[$nonceId] ) || ! wp_verify_nonce( $_POST[$nonceId], $action ) );
    }

    /**
     * Updates the teaser settings for a page or post from POST data obtained during save
     */
    protected static function update_teaser_settings( $post_id ) {
        $post_status = get_post_status( $post_id );
        $invalid_status = array( "inherit", "trash" );
        if ( in_array( $post_status, $invalid_status ) ) { return; }

        if ( ! current_user_can( 'edit_post', $post_id ) ) { return; };
        if ( ! self::valid_action('update_teaser_settings') ) { return; };

        $teaserEnabled = isset( $_POST['bespoke_teaser_enabled'] ) ? 1 : 0;
        update_post_meta( $post_id, 'bespoke_teaser_enabled', $teaserEnabled );
    }

    public function enqueue_styles(){
        wp_enqueue_style( 'bespoke_admin_css', bespoke()->css_path('style_admin.css'), false );
        wp_enqueue_script( 'fontawesome-all.min.js', bespoke()->js_path('fontawesome-all.min.js'), false );
        wp_enqueue_script( 'bespoke-admin.js', bespoke()->js_path('bespoke-admin.js'), false );

        wp_enqueue_script( 'bespoke-vars', '/1.0');
    }

    public function connect() {
        $this->oauth->connect();
        exit();
    }

    public function connected() {
        return $this->oauth->connected();
    }
}

endif;
