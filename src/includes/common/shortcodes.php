<?php

defined( 'ABSPATH' ) or die( 'Unauthorized' );

if ( ! class_exists( 'BespokeShortcodes' ) ) :

class BespokeShortcodes extends BespokeBase {
    protected function init() {
        add_shortcode( 'bespoke_teaser', array( $this, 'render_teaser' ) );
    }

    // Shortcode bespoke quote placeholder. Both self-closing and enclosing
    // forms are supported.
    // [bespoke_teaser message="Click here for an amazing opportunity"]
    // -- or --
    // [bespoke_teaser message="Totally optional modification"]
    // "Click here"
    // [/bespoke_teaser]
    function render_teaser( $atts, $content = null ) {
        $a = shortcode_atts( array(
            'class'       => '',
            'text'        => __( 'Teaser', 'bespoke' ),
            'message' => get_option('bespoke_growth_hack_text'),
        ), $atts );

        ob_start();
        ?>
        <div id='bespoke-teaser'><div id='bespoke-teaser-text'><?= $a['message'] ?></div><div id='bespoke-teaser-content'><?= $content ?></div></div>
        <?php

        return ob_get_clean();
    }
}
endif;
