<?php
defined( 'ABSPATH' ) or die( 'Unauthorized' );

if ( ! class_exists( 'BespokeAdminSettingsPage' ) ) :

/**
 * BespokeAdminSettingsPage is responsible for rendering WordPress submenus
 * and settings.
 */
class BespokeAdminSettingsPage extends BespokeBase {

    protected $page = 'bespoke-setting-admin';

    protected $section = 'plugin_settings';

    protected function setup_globals() {}

    protected function setup_actions() {
        add_action( 'bespoke_admin_menu',  array( $this, 'admin_menus' ) );
        add_action( 'bespoke_admin_init',  array( $this, 'admin_init' ) );
    }

    /**
     * Register and add settings
     */
    public function admin_init(){
        $this->register_settings();
    }

    protected function register_settings() {
        foreach ( array_keys( Bespoke::settings_config() ) as $field ) {
            $optionGroup = $this->page;
            $optionName = $field;
            register_setting( $optionGroup, $optionName );
        }
    }

    public static function get_base_color_hex() {
        return '#4a86e8';
    }

    public static function get_icon_svg( $base64 = true ) {
        $svg = '<svg aria-hidden="true" data-prefix="fas" data-icon="comments" class="svg-inline--fa fa-comments fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M416 192c0-88.4-93.1-160-208-160S0 103.6 0 192c0 34.3 14.1 65.9 38 92-13.4 30.2-35.5 54.2-35.8 54.5-2.2 2.3-2.8 5.7-1.5 8.7S4.8 352 8 352c36.6 0 66.9-12.3 88.7-25 32.2 15.7 70.3 25 111.3 25 114.9 0 208-71.6 208-160zm122 220c23.9-26 38-57.7 38-92 0-66.9-53.5-124.2-129.3-148.1.9 6.6 1.3 13.3 1.3 20.1 0 105.9-107.7 192-240 192-10.8 0-21.3-.8-31.7-1.9C207.8 439.6 281.8 480 368 480c41 0 79.1-9.2 111.3-25 21.8 12.7 52.1 25 88.7 25 3.2 0 6.1-1.9 7.3-4.8 1.3-2.9.7-6.3-1.5-8.7-.3-.3-22.4-24.2-35.8-54.5z"></path></svg>
';

        if ( $base64 ) {
            return 'data:image/svg+xml;base64,' . base64_encode( $svg );
        }

        return $svg;
    }


    /**
     * Add options page
     */
    public function admin_menus(){
        $pageTitle = 'Bespoke';
        $menuTitle = 'Bespoke';
        $capability = 'manage_options';
        $menuSlug = $this->page;
        $renderFunc = array( $this, 'render_admin_page' );
        $position = 99;
        $iconUrl = self::get_icon_svg();

        add_menu_page(
            $pageTitle,
            $menuTitle,
            $capability,
            $menuSlug,
            $renderFunc,
            $iconUrl,
            $position
        );

        $sectionId = 'plugin_settings';
        $sectionTitle = '';
        $renderFunc = array( $this, 'render_setting_section_description' );
        $page = $this->page;

        add_settings_section(
            $sectionId,
            $sectionTitle,
            $renderFunc,
            $page
        );

        foreach ( Bespoke::settings_config() as $fieldId => $config ) {
            $fieldTitle = array_shift($config);
            $renderFunc = partial(array( $this, 'render_field'), $fieldId, ...$config );
            $toPage = $this->page;
            $toSection = $this->section;
            add_settings_field(
                $fieldId,
                $fieldTitle,
                $renderFunc,
                $toPage,
                $toSection
            );
        }
    }

    function render_setting_section_description() {}

    /**
     * Dispatches rendering to type-specific field renderers
     */
    function render_field($name, $type, ...$args) {
        array_unshift( $args, $name );
        call_user_func_array(array($this, 'render_'.$type), $args);
    }

    function render_input($varname, $type='text', $default) {
        if ( $type == 'checkbox' ) {
            $checked = !!get_option($varname, $default);
            echo "<input name=\"$varname\" "
                . "id=\"$varname\" type=\"$type\" "
                . "value=true class=\"code\" "
                . ($checked ? 'checked' : '') . '/>' ;
        } else if ( $type == 'textarea' ) {
            echo "<textarea name=\"$varname\" "
                . "id=\"$varname\" type=\"$type\" "
                . "style=\"resize:both\""
                . '/>' . get_option($varname, $default) . '</textarea>';
        } else {
            echo "<input name=\"$varname\" "
                . "id=\"$varname\" type=\"$type\" "
                . "value=\"". get_option($varname, $default) . "\" class=\"code\" "
                . '/>' ;
        }
    }

    function render_options($varname, $options, $default) {
        $str = '';
        $str .= "<select name=\"$varname\" class=\"code\" id=\"$varname\" value=\"$default\">";
        foreach ($options as $key => $desc) {
            $str .= "<option value=\"$key\" ". selected( get_option($varname), $key, false ). ">$desc</option>";
        }
        $str .= '</select>';
        echo $str;
    }

    /**
     * Options page callback
     */
    public function render_admin_page(){
        $debug = bespoke()->config('debug_installation_screen');
        if ( !bespoke()->admin->connected() && empty($debug) ){
            $this->render_admin_page_not_register();
        } else {
            $this->render_admin_page_already_register();
        }
    }

    public function render_admin_page_already_register(){
        ?>
            <div class="wrap"><h1><?= __('Bespoke Plugin Settings') ?></h1></div>
            <form method="POST" action="options.php">
            <?php settings_fields( $this->page );	//pass slug name of page, also referred
                                                    //to in Settings API as option group name
            do_settings_sections( $this->page ); 	//pass slug name of page
            submit_button();
            ?>
            </form>
        <?php
    }

    public function render_admin_page_not_register(){
        ?>

            <div class="bespoke-settings-admin nowrap pc-center pc-gradient1 pc-H120">
                    <h1 class="pc-txt-white pc-margin0 pc-paddingV50 pc-txt-bigtitle">Great!</h1>
            </div>

            <div class="bespoke-settings-admin nowrap pc-center pc-paddingV50">
                <div class="pc-maxwidth480 pc-margin0 pc-alignleft">
                    <p class="pc-center">You successfully installed the Bespoke plugin.</p>
                    <p class="pc-center"> <strong> One last thing</strong>:  you need to connect to your Bespoke account.</p>
                </div>
            </div>

            <div class="bespoke-settings-admin wrap">

                <form method="post" action="admin-ajax.php?action=connect_with_bespoke">

                <?php
                    // This prints out all hidden setting fields
                    //settings_fields( 'bespoke_option_group' );
                    // do_settings_sections( 'bespoke-setting-admin' );
                    submit_button(__('Connect with Bespoke'), "button-primary draw meet");

                ?>
                </form>
            </div>
        <?php
    }
}

endif;
