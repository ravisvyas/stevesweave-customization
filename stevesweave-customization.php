<?php
/*
 * Plugin Name: StevesWeave customizations
 * Description: This will include customization related to GeoDirectory and Wordpress features for StevesWeave web app.
 * Author: Fusion Web Experts
 * Version: 1.0.0
 * Author URI: https://fusionwebexperts.tech
 * License: GPL2
 * Text Domain: https://fusionwebexperts.tech
 */
defined( 'ABSPATH' ) || exit;
/**
 * @class StevesWeaveCustomization
 */
if (!class_exists('StevesWeaveCustomization', false)) {

    class StevesWeaveCustomization {

        /**
         * Initialize required action and filters
         * @return void accommodation_supplement
         */
        public function __construct() {
            //Define..
            if(!defined('STEVES_PLUGIN_BASE_FILE')){
                define('STEVES_PLUGIN_BASE_FILE', __FILE__);
            }

            //include inner functionality 
            // include_once self::get_plugin_dir_path() . '/classes/class.geodirectory-filters.php';
            include_once self::get_plugin_dir_path() . '/classes/class.geodirectory-taxonomy.php';
            // include_once self::get_plugin_dir_path() . '/classes/class.geodirectory-location.php';

            //include frontend all 
            include_once self::get_plugin_dir_path() . '/front-end/classes/class.front-end.php';
            include_once self::get_plugin_dir_path() . '/front-end/classes/class.add-listing.php';
            include_once self::get_plugin_dir_path() . '/front-end/classes/class.single-listing.php';
            include_once self::get_plugin_dir_path() . '/front-end/classes/class.user-dashboard.php';
            include_once self::get_plugin_dir_path() . '/front-end/classes/class.geodir-claim-listings.php';

            //Include Shortcode Class
            include_once self::get_plugin_dir_path() . '/front-end/shortcodes/class.stevesweave-shortcodes.php';

            //save title for event
            //add_filter( 'wp_insert_post_data' ,  array(__CLASS__, 'set_post_title_gd_event' ) , 10 , 3 );
            add_filter('template_include', array( __CLASS__, 'vip_template_include' ), 100 );
        }

        // single and archive files
        public static function vip_template_include($template) {
            // Determine the type of current theme
            // echo $template;

            return $template; // Default to the original template
        }

        /**
         * Get plugin file path
         * @return system
         */
        public static function get_plugin_file_path() {
            return __FILE__;
        }

        /**
         * Get plugin dir path.
         * @return type
         */
        public static function get_plugin_dir_path() {
            return dirname(__FILE__);
        }

        /**
         * Get plugin url.
         * @return type
         */
        public static function get_plugin_url() {
            return plugin_dir_url(__FILE__);
        }

    }
    
}

/*
 * Initialize class init method for load its functionality.
 */
function tvc_load_plugin()
{
    // Initialize dependency injection.
    $GLOBALS['gdcus'] = new StevesWeaveCustomization();
}
add_action('plugins_loaded', 'tvc_load_plugin');
?>