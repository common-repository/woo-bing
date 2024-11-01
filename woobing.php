<?php
/**
 * Plugin Name: WP Woocommerce to Bing Ads
 * Plugin URI: 
 * Description: Submit your product woocommerce to bing for ads.
 * Author: asaquzzaman
 * Version: 0.1
 * Author URI: http://mishubd.com
 * License: GPL2
 * TextDomain: woobing
 */

/**
 * Copyright (c) 2013 Asaquzzaman Mishu (email: joy.mishu@gmail.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * **********************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


class WP_WooBing {

    /**
     * Initializes the WeDevs_ERP_Accounting() class
     *
     * Checks for an existing WeDevs_ERP_Accounting() instance
     * and if it doesn't find one, creates it.
     */
    public static function getInstance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    function __construct() {
        $this->init();
        $this->includes();
        $this->init_actions();
    }

    function init() {
        $this->define_constants();
        //spl_autoload_register( array( $this, 'autoload' ) );
    }

    function includes() {
        require_once WOOBING_PATH . '/includes/pages.php';
        require_once WOOBING_PATH . '/includes/functions.php';
        require_once WOOBING_PATH . '/includes/urls.php';
        require_once WOOBING_PATH . '/admin/class-woobing-admin-settings.php';
        require_once WOOBING_PATH . '/admin/class-woobing-admin-feed.php';
    }

    function init_actions() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'scripts') );
    }

    /**
     * Load script
     * @return  void
     */
    function scripts() {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'woobing-chosen', plugins_url( '/assets/js/chosen.jquery.min.js', __FILE__ ), array( 'jquery' ), false, true);
        wp_enqueue_script( 'woobing-script', plugins_url( 'assets/js/woobing.js', __FILE__ ), array( 'jquery' ), false, true );
        wp_localize_script( 'woobing-script', 'woobing_var', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'woobing_nonce' ),
            'is_admin' => is_admin() ? 'yes' : 'no',
        ));
        wp_enqueue_style( 'woobing-chosen', plugins_url( '/assets/css/chosen.min.css', __FILE__ ), false, false, 'all' );
        wp_enqueue_style( 'woobing-style', plugins_url( 'assets/css/woobing.css', __FILE__ ) );
        wp_enqueue_style( 'woobing-jquery-ui', plugins_url( '/assets/css/jquery-ui.css', __FILE__ ), false, false, 'all' );
    }

    /**
     * Set woocommerce submenu
     * @return void
     */
    function admin_menu() {
        $woobing = add_submenu_page( 'edit.php?post_type=product', __( 'WooBing', 'woobing' ), __( 'WooBing', 'woobing' ), 'manage_product_terms', woobing_page_slug() , array( $this, 'woobing_output' ) );
       // add_action( 'admin_print_styles-' . $woogool, array( $this, 'scripts' ) );
    }

    function woobing_output() {
        if( ! is_user_logged_in() ) {
            return wp_login_form( array('echo' => false) );
        }

        $current_user_id = get_current_user_id();

        if ( isset( $_GET['product_id'] ) ) {
            update_user_meta( $current_user_id, 'woogbing_product_id', $_GET['product_id'] );
        }

        $query_args = woobing_get_query_args();
        $page       = $query_args['page'];
        $tab        = $query_args['tab'];
        $subtab     = $query_args['sub_tab'];
        
        echo '<div class="woobing wrap" id="woobing">';
            WooBing_Admin_Settings::getInstance()->show_tab_page( $page, $tab, $subtab );
        echo '</div>';    }

    /**
     * Autoload class files on demand
     *
     * @param string $class requested class name
     */
    function autoload( $class ) {
        $name = explode( '_', $class );
        if ( isset( $name[1] ) ) {
            $class_name = strtolower( $name[1] );
            $filename = WOOBING_PATH . '/includes/class/class-' . $class_name . '.php';

            if ( file_exists( $filename ) ) {
                require_once $filename;
            }
        }
    }

    /**
     * Define cpmrp Constants
     *
     * @since 1.1
     * @return type
     */
    public function define_constants() {

        $this->define( 'WOOBING_VERSION', '0.1' );
        $this->define( 'WOOBING_DB_VERSION', '0.1' );
        $this->define( 'WOOBING_PATH', dirname( __FILE__ ) );
        $this->define( 'WOOBING_URL', plugins_url( '', __FILE__ ) );
        $this->define( 'WOOBING_VIEWS', dirname( __FILE__ ) . '/views' );
    }

    /**
     * Define constant if not already set
     *
     * @since 1.1
     *
     * @param  string $name
     * @param  string|bool $value
     * @return type
     */
    public function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Instantiate all the required classes
     *
     * @since 0.1
     */
    function instantiate() {
        do_action( 'woobing_instantiate', $this );
    }
}

/**
 * Returns the main instance.
 *
 * @since  1.1
 * @return WeDevs_CPM
 */
function woobing() {
    return WP_WooBing::getInstance();
}

//cpm instance.
$woobing = woobing();




