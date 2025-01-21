<?php
/**
 * @class GeoDirCustomFrontEnd
 */
if (!class_exists('GeoDirCustomFrontEnd', false)) {


    class GeoDirCustomFrontEnd {

        /**
         * Initialize required action and filters
         * @return void
         */
        public static function init() {

            //add shortcode for frontend gd_facilitator
            // add_shortcode('add-listing-stevesweave', array(__CLASS__, 'add_listing_stevesweave'), 1);
            add_action('wp_head', array(__CLASS__, 'add_script_css'), 1);
        }

        /*
         * Function for add custom CSS 
         */

         public static function add_script_css() {
            wp_enqueue_style('gdcus-custom-style', StevesWeaveCustomization::get_plugin_url() . '/front-end/css/style.css', array(), time());
            wp_enqueue_script('gdcus-custom-js', StevesWeaveCustomization::get_plugin_url() . '/front-end/js/main.js', array(), time());
            wp_localize_script('gdcus-custom-js', 'ajax_object', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('file_upload_nonce'),
            ]);
        }

    }

    /*
     * Initialize class init method for load its functionality.
     */
    GeoDirCustomFrontEnd::init();
}
?>