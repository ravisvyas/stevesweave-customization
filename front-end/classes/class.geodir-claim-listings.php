<?php
if(!defined('ABSPATH')){
    exit;
}
/**
 * CLASS GEODIRClaimListing
*/
if(!class_exists('GEODIRClaimListing', false)){
    class GEODIRClaimListing{
        public function __construct(){
            add_shortcode('stevesweave_claim_listing', [__CLASS__, 'stevesweave_claim_listing_cb']);
            add_action('init', [__CLASS__, 'stevesweave_rewrite_rule']);
            add_filter('query_vars', [__CLASS__, 'stevesweave_add_query_vars']);
            register_deactivation_hook(STEVES_PLUGIN_BASE_FILE, [__CLASS__, 'stevesweave_plugin_deactivate']);
            register_deactivation_hook(STEVES_PLUGIN_BASE_FILE, [__CLASS__, 'stevesweave_plugin_activate']);
            add_action('wp_enqueue_scripts', [__CLASS__, 'stevesweave_enqueue_scripts']);
            //CLAIM REQUEST HANDLER
            add_action('wp_ajax_stevesweave-submit-claim', [__CLASS__, 'stevesweave_submit_claim_cb']);
            add_action('wp_ajax_nopriv_stevesweave-submit-claim', [__CLASS__, 'stevesweave_submit_claim_cb']);
        }
        public static function stevesweave_submit_claim_cb(){
            $return = [];
            $listing_id     =   (isset($_POST['listing_id']))       ?   $_POST['listing_id'] : '';
            $full_name      =   (isset($_POST['full_name']))        ?   sanitize_text_field($_POST['full_name']) : '';
            $email_address  =   (isset($_POST['email_address']))    ?   sanitize_email($_POST['email_address']) : '';
            $phone_number   =   (isset($_POST['phone_number']))     ?   $_POST['phone_number'] : '';
            $security       =   (isset($_POST['security']))         ?   $_POST['security'] : '';
            // Check nonce for security
            if(!is_user_logged_in()){
                $return['status']   = false;
                $return['message']  = 'Please log in to continue.';
            }else if(empty($security) || !wp_verify_nonce($security, 'claim_listing_nonce')){
                $return['status'] = false;
                $return['message'] = 'Nonce verification failed';
            }else if(empty($listing_id)){
                $return['status'] = false;
                $return['message'] = "Oop's something went wrong, please refresh the page try again. Thanks!";
            }else if(!is_email($email_address)){
                $return['status'] = false;
                $return['message'] = 'The email address is invalid.';
            }else if(empty($full_name) || empty($email_address) || empty($phone_number)){
                $return['status'] = false;
                $return['message'] = 'Please fill out all required fields.';
            }else{
                $meta = [
                    'claim_email'           =>  $email_address,
                    'claim_full_name'       =>  $full_name,
                    'claim_phone_number'    =>  $phone_number,
                ];
                $meta_description = serialize($meta);
                $user_id    =   get_current_user_id();
                $author_id  =   get_post_field( 'post_author', $listing_id );
                $post_type  =   (!empty(get_post_type($listing_id))) ? get_post_type($listing_id) : 'gd_place';
                $claim_data = [
                    'post_id'       => $listing_id,
                    'user_id'       => $user_id,
                    'user_number'   => $phone_number,
                    'post_type'     => $post_type,
                    'user_fullname' => $full_name,
                    'author_id'     => $author_id,
                    'meta'          => $meta_description,
                ];
                $claim = GeoDir_Claim_Post::save($claim_data);
                if($claim){
                    update_user_meta($user_id, '_business_claim_id', $claim);
                    $return['status']   = true;
                    $return['message']  = 'Claim request submitted Successfully. Thanks!';
                }else{
                    $return['status'] = false;
                    $return['message'] = "Oop's something went wrong, please refresh the page try again. Thanks!";
                }
            }
            echo json_encode($return);
            exit;
        }
        public static function stevesweave_claim_listing_cb(){
            $listing_id = get_query_var('listing_id', 0);
            ob_start();
            ?>
            <div class="claim-business-form">
                <div class="claim-submission-form-section">
                    <h2>Claiming your Listing</h2>
                    <p>
                        Thank you for taking the initiative to claim your Listing on Steve's Weave! 🌱
                    </p>
                    <p>
                        By submitting your contact details you've started the process to join our community of green businesses. Our team will reach out to you shortly to validate your details. Once approved, you'll gain access to manage your listing and connect with our vibrant green community. We appreciate your participation in our mission to promote sustainability!
                    </p>
                    <div class="claim-loader">
                        <img src="<?= StevesWeaveCustomization::get_plugin_url() ?>front-end/images/loader.gif" class="loader-claim" alt="" height="80" width="80">
                    </div>
                    <form action="#" method="post" id="stevesweave-claim-form">
                        <div class="claim-error claim-msges" style="display:none; color:red;"></div>
                        <div class="form-group">
                            <label for="full_name">Full name <span style="color:red;">*</span> </label>
                            <input type="text" id="full_name" name="full_name">
                        </div>
                        <div class="form-group">
                            <label for="email_address">Email address <span style="color:red;">*</span> </label>
                            <input type="email" id="email_address" name="email_address">
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone number <span style="color:red;">*</span> </label>
                            <input type="tel" id="phone_number" name="phone_number">
                        </div>
                        <div class="form-group">
                            <button type="submit" id="steve-claim-submit-btn">Submit Form</button>
                        </div>
                    </form>
                </div>
                <div class="claim-submission-success-section" style="display:none;">
                    <div class="claim-success">
                        <h2>Request Submitted!</h2>
                        <p>We’ve received your request and appreciate your interest in joining our community of green businesses...</p>
                        <p>Thank you for your patience and for being a part of our mission to promote sustainability!</p>
                        <p>Best regards,</p>
                        <p>The Steve's Weave Team</p>
                    </div>
                </div>
            </div>
            <?php
            $return = ob_get_clean();
            return $return;
        }
        public static function stevesweave_rewrite_rule(){
            add_rewrite_rule('^claim-listing/([0-9]+)/?$', 'index.php?pagename=claim-listing&listing_id=$matches[1]', 'top');
        }
        public static function stevesweave_add_query_vars($vars){
            $vars[] = 'listing_id';
            return $vars;
        }
        // Flush rewrite rules on plugin deactivation
        public static function stevesweave_plugin_deactivate() {
            flush_rewrite_rules();
        }
        // Flush rewrite rules on plugin activation
        public static function stevesweave_plugin_activate(){
            self::stevesweave_rewrite_rule();
            flush_rewrite_rules();
        }
        public static function stevesweave_enqueue_scripts(){
            $version = time();
            wp_enqueue_script(
                'stevesweave-claim-listing',                                
                StevesWeaveCustomization::get_plugin_url() . 'front-end/js/claim-listing.js', 
                ['jquery'],                                     
                $version,                                       
                true                                            
            );
            wp_localize_script(
                'stevesweave-claim-listing', 
                'claimListingData',          
                [
                    'ajax_url'  => admin_url('admin-ajax.php'),         
                    'listingID' => get_query_var('listing_id', 0),     
                    'nonce'     => wp_create_nonce('claim_listing_nonce')
                ]
            );
        }
    }
    $GEODIRClaimListing = new GEODIRClaimListing();
}