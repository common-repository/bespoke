<?php
defined( 'ABSPATH' ) or die( 'Unauthorized' );

if ( ! class_exists( 'BespokeBase' ) ) :

class BespokeBase {
    /**
     * Start up
     */
    public function __construct(){
        $this->setup_globals();
        $this->includes();
        $this->setup_variables();
        $this->setup_filters();
        $this->setup_actions();
        $this->init();
    }

    protected function setup_globals()   { }
    protected function includes()        { }
    protected function setup_variables() { }
    protected function setup_filters()   { }
    protected function setup_actions()   { }
    protected function init()            { }
}

endif;
