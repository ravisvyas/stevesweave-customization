<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CLASS StevesweaveShortcodes
 */
if (!class_exists('StevesweaveShortcodes', false)) {
    class StevesweaveShortcodes {
        public function __construct() {
            // Hook to enqueue scripts and styles
            add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

            // Register the shortcodes
            add_shortcode('stevesweave_register_form', [__CLASS__, 'render_register_form']);
            add_shortcode('stevesweave_login_form', [__CLASS__, 'render_login_form']);
            add_shortcode('stevesweave_forgot_pass', [__CLASS__, 'render_forgot_pass_form']);
            add_shortcode('stevesweave_account_details', [__CLASS__, 'stevesweave_account_details_cb']);

            // AJAX actions for form submission
            add_action('wp_ajax_stevesweave-register', [__CLASS__, 'handle_ajax_register']);
            add_action('wp_ajax_nopriv_stevesweave-register', [__CLASS__, 'handle_ajax_register']);

            //Ajax Register Verification
            add_action('wp_ajax_stevesweave-register-verification', [__CLASS__, 'stevesweave_register_verification_cb']);
            add_action('wp_ajax_nopriv_stevesweave-register-verification', [__CLASS__, 'stevesweave_register_verification_cb']);

            //Ajax Login Form submission
            add_action('wp_ajax_stevesweave-login', [__CLASS__, 'stevesweave_login_cb']);
            add_action('wp_ajax_nopriv_stevesweave-login', [__CLASS__, 'stevesweave_login_cb']);

            //Ajax Forgot Password Form submission
            add_action('wp_ajax_stevesweave-forgot-password', [__CLASS__, 'stevesweave_forgot_password_cb']);
            add_action('wp_ajax_nopriv_stevesweave-forgot-password', [__CLASS__, 'stevesweave_forgot_password_cb']);
            // Update Account Field
            add_action('wp_ajax_stevesweave_update_account_field', [__CLASS__, 'stevesweave_update_account_field_cb']);
            add_action('wp_ajax_nopriv_stevesweave_update_account_field', [__CLASS__, 'stevesweave_update_account_field_cb']);
            // Delete User Account
            add_action('wp_ajax_stevesweave_delete_user_account', [__CLASS__, 'stevesweave_delete_user_account']);
            //Update Password
            add_action('wp_ajax_stevesweave_update_password', [__CLASS__, 'stevesweave_update_password']);
        }
        /**
         *  UPDATE PASSWORD
         */
        public static function stevesweave_update_password() {
            $return = [];
            if (!is_user_logged_in()) {
                $return['status'] = false;
                $return['message'] = 'You must be logged in.';
            }else{
                $current_password   =   $_POST['current_password'];
                $new_password       =   $_POST['new_password'];
                $user               =   wp_get_current_user();
                if(!wp_check_password($current_password, $user->user_pass, $user->ID)){
                    $return['status'] = false;
                    $return['message'] = 'Your current password is incorrect.';
                }else{
                    wp_set_password($new_password, $user->ID);
                    $return['status'] = true;
                    $return['message'] = 'Password updated successfully.';
                }
            }
            echo json_encode($return);
            exit;
        }

        public static function stevesweave_delete_user_account() {
            $return = [];
            if (!is_user_logged_in()) {
                $return['status'] = false;
                $return['message'] = 'You are not logged in.';
            }
            $user_id = get_current_user_id();
            require_once(ABSPATH . 'wp-admin/includes/user.php');
            wp_delete_user($user_id);
            $return['status'] = true;
            $return['message'] = 'Account deleted successfully.';
            $return['url']  = home_url();
            echo json_encode($return);
            exit;
        }

        public static function stevesweave_update_account_field_cb() {
            if (!is_user_logged_in()) {
                wp_send_json_error(['message' => 'You are not logged in.']);
            }
            // print_r($_POST); exit;  
            $field      =   sanitize_text_field($_POST['field']);
            $value      =   sanitize_text_field($_POST['value']);
            $user_id    =   get_current_user_id();
        
            switch ($field) {
                case 'name':
                    wp_update_user(['ID' => $user_id, 'display_name' => $value]);
                    break;
                case 'email':
                    wp_update_user(['ID' => $user_id, 'user_email' => $value]);
                    break;
                case 'phone':
                    update_user_meta($user_id, 'phone', $value);
                    break;
                case 'location':
                    update_user_meta($user_id, 'default_location', $value);
                    break;
                default:
                    wp_send_json_error(['message' => 'Invalid field.']);
            }
        
            wp_send_json_success(['message' => 'Field updated successfully.']);
            exit;
        }

        /**
         * Account Details
         */
        public static function stevesweave_account_details_cb() {
            if (!is_user_logged_in()) {
                ob_start();
                ?>
                <script type="text/javascript">
                    alert("You are not logged in. Redirecting...");
                    window.location.href = "<?= esc_url(home_url('/login/')); ?>";
                </script>
                <?php
                return ob_get_clean();
            }
        
            $current_user = wp_get_current_user();
            $user_name = esc_html($current_user->display_name);
            $user_email = esc_html($current_user->user_email);
            $user_phone = esc_html(get_user_meta($current_user->ID, 'phone', true)) ?: 'N/A';
            $user_location = esc_html(get_user_meta($current_user->ID, 'default_location', true)) ?: 'N/A';
            ob_start();
            ?>
            <!-- Account Details Section -->
            <div id="stevesweave-account-details">
                <div class="claim-loader" id="loader-section">
                    <img src="<?= StevesWeaveCustomization::get_plugin_url() ?>front-end/images/loader.gif" class="loader-claim" alt="" height="80" width="80">
                </div>
                <h2>Account Details</h2>
                <ul>
                    <li>
                        <div class="account_details_name_tag_container">
                            <strong>Name:</strong>
                        <span id="stevesweave-user-name"><?= $user_name; ?></span>
                        <input type="text" id="stevesweave-input-name" class="stevesweave-input-field" value="<?= $user_name; ?>" style="display: none;">
                        </div>
                        <div class="account_details_edit_button_container">
                            <button class="stevesweave-edit-btn" data-field="name" data-save="false">Edit</button>
                        </div>
                        
                        
                    </li>
                    <li>
                        <div class="account_details_name_tag_container">
                            <strong>Email:</strong>
                        <span id="stevesweave-user-email"><?= $user_email; ?></span>
                        <input type="email" id="stevesweave-input-email" class="stevesweave-input-field" value="<?= $user_email; ?>" style="display: none;">
                        </div>
                        <div class="account_details_edit_button_container"><button class="stevesweave-edit-btn" data-field="email" data-save="false">Edit</button></div>
                    
                        
                    </li>
                    <li>
                        <div class="account_details_name_tag_container">
                            <strong>Phone:</strong>
                        <span id="stevesweave-user-phone"><?= $user_phone; ?></span>
                        <input type="text" id="stevesweave-input-phone" class="stevesweave-input-field" value="<?= $user_phone; ?>" style="display: none;">
                        </div>
                        <div class="account_details_edit_button_container"><button class="stevesweave-edit-btn" data-field="phone" data-save="false">Edit</button></div>
                    
                        
                    </li>
                    <li>
                        <div class="account_details_name_tag_container">
                            <strong>Location:</strong>
                        <span id="stevesweave-user-location"><?= $user_location; ?></span>
                        <input type="text" id="stevesweave-input-location" class="stevesweave-input-field" value="<?= $user_location; ?>" style="display: none;">
                        </div>
                        <div class="account_details_edit_button_container">
                            <button class="stevesweave-edit-btn" data-field="location" data-save="false">Edit</button>
                        </div>
                        
                        
                    </li>
                    <li>
                        <div class="account_details_name_tag_container">
                            <strong>Steve’s Weave Communications</strong>
                            <span id="user-communication">
                            <input type="checkbox" id="communication-preference">
                            Yes, I would like to receive emails from Steve’s Weave
                        </span>
                        </div>
                        <div class="account_details_edit_button_container">
                            <button class="stevesweave-edit-btn" data-field="location" data-save="false">Edit</button>
                        </div>
                        
                        
                        <!-- <button class="stevesweave-edit-btn stevesweave-edit-communication" data-save="false">Edit</button> -->
                    </li>
                </ul>
                <div class="stevesweave-buttons">
                    <button id="stevesweave-update-password" class="btn-blue">Update Password</button>
                    <button id="stevesweave-delete-account" class="btn-red">Delete Account</button>
                </div>
            </div>
            <!-- Update Password Modal -->
            <div id="stevesweave-password-modal" class="stevesweave-modal">
                <div class="stevesweave-modal-content">
                    <span class="stevesweave-close">×</span>
                    <h2>Update Password</h2>
                    <form id="stevesweave-password-form">
                        <label for="current-password">Current Password</label>
                        <input type="password" id="stevesweave-current-password" name="current_password" required="">
                        <label for="new-password">New Password</label>
                        <input type="password" id="stevesweave-new-password" name="new_password" required="">
                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" id="stevesweave-confirm-password" name="confirm_password" required="">
                        <button type="submit" id="update-password-popup-btn">Update Password</button>
                    </form>
                </div>
            </div>
            <p style="display: none;"></p>

            <?php
            return ob_get_clean();
        }
        
        
        /**
         * FORGET PASSWORD AJAX REQUEST HANDLER
         */
        public static function stevesweave_forgot_password_cb() {
            $return         =   [];
            $user_email     =   (isset($_POST['user_email'])) ? sanitize_email($_POST['user_email']) : '';
            // Validate the email field
            if (empty($user_email)) {
                $return['status'] = false;
                $return['message'] = 'Please fill out the required fields!';
            } elseif (!is_email($user_email)) {
                $return['status'] = false;
                $return['message'] = 'Invalid email address!';
            } else {
                // Check if user exists
                $user = get_user_by('email', $user_email);
                if (!$user) {
                    $return['status'] = false;
                    $return['message'] = 'No user found with this email address.';
                } else {
                    // Generate a password reset key
                    $reset_key = get_password_reset_key($user);
        
                    if (is_wp_error($reset_key)) {
                        $return['status'] = false;
                        $return['message'] = 'Could not generate a reset key. Please try again later.';
                    } else {
                        // Generate reset link
                        $reset_link = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login));
        
                        // Prepare the email
                        $email_subject = 'Password Reset Request';
                        $email_body = "
                            <p>Hi,</p>
                            <p>Someone has requested a password reset for your account. If this was you, click the link below to reset your password:</p>
                            <p>
                                <a href='$reset_link' 
                                style='display: inline-block; padding: 10px 20px; background-color: #5B7E3E; color: white; text-decoration: none; border-radius: 5px;'>
                                    Reset Password
                                </a>
                            </p>
                            <p>If you did not request this, you can safely ignore this email.</p>
                ";
                        $email_headers = [
                            'Content-Type: text/html; charset=UTF-8',
                            'From: Steve’s Weave <no-reply@stevesweave.com>',   
                        ];
        
                        $email_sent = wp_mail($user_email, $email_subject, $email_body, $email_headers);
        
                        if ($email_sent) {
                            $return['status'] = true;
                            $return['message'] = 'Password reset link sent to your email address.';
                        } else {
                            $return['status'] = false;
                            $return['message'] = 'Failed to send the reset email. Please try again.';
                        }
                    }
                }
            }
        
            // Return the response
            echo json_encode($return);
            exit;
        }

        /**
         * FORGOT PASSWORD FORM
         */
        public static function render_forgot_pass_form(){
            if(is_user_logged_in()){
                // JavaScript-based redirect
                ob_start();
                ?>
                <script type="text/javascript">
                    // alert("You are already logged in. Redirecting...");
                    window.location.href = "<?= home_url(); ?>"; // Redirect to the homepage or another URL
                </script>
                <?php
                return ob_get_clean();
            }
            ob_start();
            ?>
            <div id="forgot-password-container">
                <div class="claim-loader" id="loader-section">
                    <img src="<?= StevesWeaveCustomization::get_plugin_url() ?>front-end/images/loader.gif" class="loader-claim" alt="" height="80" width="80">
                </div>
                <h2>Forgot Your Password?</h2>
                <form id="forgot-password-form" action="#" method="post">
                    <div class="error-msg error message" style="display:none; color:red;"></div>
                    <div class="success-msg success message" style="display:none; color:green;"></div>
                    <div class="form-group">
                        <label for="user_email">Enter your email address:</label>
                        <input type="email" id="user_email" name="user_email"/>
                    </div>
                    <div class="form-group">
                        <button type="submit">Reset Password</button>
                    </div>
                    <div id="forgot-password-response"></div>
                </form>
            </div>
            <?php
            return ob_get_clean();
        }

        /**
         * Enqueue styles and scripts
         */
        public static function enqueue_assets() {
            $version = time();
            //css
            wp_enqueue_style('stevesweave-shortcodes-css', StevesWeaveCustomization::get_plugin_url().'/front-end/shortcodes/css/styles.css', array(), $version);
            //script
            wp_enqueue_script(
                'stevesweave-shortcodes-scripts',                                
                StevesWeaveCustomization::get_plugin_url() . 'front-end/shortcodes/js/scripts.js', 
                ['jquery'],                                     
                $version,                                       
                true                                            
            );
            wp_localize_script(
                'stevesweave-shortcodes-scripts', 
                'ShortcodesScriptData',         
                [
                    'ajax_url'  => admin_url('admin-ajax.php'),
                    'plugin_url' => StevesWeaveCustomization::get_plugin_url(),
                ]
            );
        }

        /**
         * Render the registration form
         */
        public static function render_register_form() {
            if(is_user_logged_in()){
                // JavaScript-based redirect
                ob_start();
                ?>
                <script type="text/javascript">
                    alert("You are already logged in. Redirecting...");
                    window.location.href = "<?= home_url(); ?>"; // Redirect to the homepage or another URL
                </script>
                <?php
                return ob_get_clean();
            }
            ob_start();
            ?>
            <div class="stevesweave-register-form-section">
                <div class="claim-loader" id="loader-section">
                    <img src="<?= StevesWeaveCustomization::get_plugin_url() ?>front-end/images/loader.gif" class="loader-claim" alt="" height="80" width="80">
                </div>
                <!-- Register Form -->
                <div class="register-section">
                    <h2>Sign Up to Steve’s Weave</h2>
                    <form id="stevesweave-register-form">
                        <div class="error-msg error message" style="display:none; color:red;"></div>
                        <?php wp_nonce_field('stevesweave_register_nonce', 'security_nonce'); ?>
                        <div class="form-group">
                            <label for="full_name">Full Name <span class="required-field">*</span></label>
                            <input type="text" name="full_name" id="full_name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address <span class="required-field">*</span></label>
                            <input type="email" name="email" id="email" />
                        </div>
                        <div class="form-group">
                            <label for="location_preference">Location Preference <span class="required-field">*</span></label>
                            <input type="text" name="location" id="location_preference"/>
                            <span class="location-message">This Location will be used as default for your searches. Don't worry, you can always update it later if needed.</span>
                        </div>
                        <div class="form-group">
                            <label for="password">Password <span class="required-field">*</span></label>
                            <input type="password" name="password" id="password"/>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password <span class="required-field">*</span></label>
                            <input type="password" name="confirm_password" id="confirm_password"/>
                        </div>

                        <div class="form-group password-strength-container">
                            <label for="">Password Strength:</label>
                            <div class="stevesweave-password-strength">
                                <span class="strength-bar"></span>
                                <span class="strength-bar"></span>
                                <span class="strength-bar"></span>
                                <span class="strength-bar"></span>
                            </div>
                            <span class="password-error error" style="display: none; color: red;"></span>
                        </div>


                        <div class="form-group">
                            <label class="custom-checkbox"><input type="checkbox" name="subscribe_newsletter" id="subscribe_newsletter" value="1"/><span>Yes, I would like to receive emails from Steve’s Weave</span></label>
                        </div>
                        <div class="form-group">
                            <label class="custom-checkbox"><input type="checkbox" name="accept_terms" id="accept_terms" value="1"/><span >Accept <a href="#" class="terms_accept_tag">Terms & Conditions</a> of Use</span></label>
                        </div>
                        <div class="form-group">
                            <button type="submit" id="submit-register-form-btn">Create Account</button>
                        </div>
                    </form>
                    <div class="separator">
                        <hr class="line" />
                        <span class="text">or</span>
                        <hr class="line" />
                    </div>
                    <div class="stevesweave-social-buttons">
						
						<?php echo do_shortcode('[nextend_social_login provider="google" redirect="https://stage.stevesweave.net/account-details/"]'); ?>
						<?php echo do_shortcode('[nextend_social_login provider="twitter" redirect="https://stage.stevesweave.net/account-details/"]'); ?>
					</div>

                </div>
                <!-- Verification Form  -->
                <div class="register-verification-section" style="display:none;">
                    <h2>Please Verify</h2>
                    <p>A 6-digit verification code has been sent to your registered email address. Please check your inbox and enter the code below to complete the verification process. Thank you!</p>
                    <form id="verification-form">
                        <div class="error-msg error message" style="display:none; color:red;"></div>
                        <div class="success-msg success message" style="display:none; color:green;"></div>
                        <div class="code-inputs">
                            <input type="text" maxlength="1" class="code-input" name="code1" id="code1"/>
                            <input type="text" maxlength="1" class="code-input" name="code2" id="code2" />
                            <input type="text" maxlength="1" class="code-input" name="code3" id="code3" />
                            <input type="text" maxlength="1" class="code-input" name="code4" id="code4" />
                            <input type="text" maxlength="1" class="code-input" name="code5" id="code5" />
                            <input type="text" maxlength="1" class="code-input" name="code6" id="code6" />
                        </div>
                        <button type="submit" class="verify-btn" id="register-verification-btn">Verify</button>
                    </form>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        /**
         * Render the login form
         */
        public static function render_login_form() {
            if(is_user_logged_in()){
                // JavaScript-based redirect
                ob_start();
                ?>
                <script type="text/javascript">
                    alert("You are already logged in. Redirecting...");
                    window.location.href = "<?= home_url(); ?>"; // Redirect to the homepage or another URL
                </script>
                <?php
                return ob_get_clean();
            }
            ob_start();
            ?>
            <div class="stevesweave-register-form-section">
                <div class="claim-loader" id="loader-section">
                    <img src="<?= StevesWeaveCustomization::get_plugin_url() ?>front-end/images/loader.gif" class="loader-claim" alt="" height="80" width="80">
                </div>
                <!-- Login Form -->
                <div class="login-section">
                    <h2>Log In to Steve’s Weave</h2>
                    <form id="stevesweave-login-form">
                    <div class="error-msg error message" style="display:none; color:red;"></div>
                    <div class="success-msg success message" style="display:none; color:green;"></div>
                        <?php wp_nonce_field('stevesweave_login_nonce', 'security_nonce'); ?>
                        <div class="form-group">
                            <label for="email_address">Email <span class="required-field">*</span></label>
                            <input type="email" name="email_address" id="email_address" />
                        </div>
                        <div class="form-group">
                            <label for="l_password">Password <span class="required-field">*</span></label>
                            <input type="password" name="l_password" id="l_password"/>
                        </div>
                        <div class="form-group">
                            <button type="submit" id="submit-login-form-btn">Log In</button>
                        </div>
                        <div class="form-group text-center">
                            <a href="<?= home_url('/forgot-pass/') ?>">Forgot Password?</a>
                        </div>
                    </form>
                    <div class="separator">
                        <hr class="line" />
                        <span class="text">or</span>
                        <hr class="line" />
                    </div>
                     <div class="stevesweave-social-buttons">
						
						<?php echo do_shortcode('[nextend_social_login provider="google" redirect="https://stage.stevesweave.net/account-details/"]'); ?>
						<?php echo do_shortcode('[nextend_social_login provider="twitter" redirect="https://stage.stevesweave.net/account-details/"]'); ?>
					</div>
                    <p class="signup_link_btn_container">New to Steve’s Weave? <a class="signup_link_btn" href="<?= home_url('sign-up') ?>">Sign Up   </a></p>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        /**
         * Handle AJAX form submission
         */
        public static function handle_ajax_register() {
            $return = [];
            $full_name                  =   (isset($_POST['full_name']))        ?   sanitize_text_field($_POST['full_name']) : '';
            $email                      =   (isset($_POST['email']))            ?   sanitize_email($_POST['email']) : '';
            $location                   =   (isset($_POST['location']))         ?   sanitize_text_field($_POST['location']) : '';
            $password                   =   (isset($_POST['password']))         ?   $_POST['password'] : '';
            $confirm_password           =   (isset($_POST['confirm_password']))     ?   $_POST['confirm_password']      : '';
            $subscribe_newsletter       =   (isset($_POST['subscribe_newsletter'])) ?   $_POST['subscribe_newsletter']  : '';
            $accept_terms               =   (isset($_POST['accept_terms']))         ?   $_POST['accept_terms']          : '';
            $security_nonce             =   (isset($_POST['security_nonce']))         ?   $_POST['security_nonce']          : '';
            $_wp_http_referer           =   (isset($_POST['_wp_http_referer']))         ?   $_POST['_wp_http_referer']          : '';
            if(empty($security_nonce) || !wp_verify_nonce($security_nonce, 'stevesweave_register_nonce')){
                $return['status'] = false;
                $return['message'] = 'Nonce verification failed';
            }else if(!is_email($email)){
                $return['status'] = false;
                $return['message'] = 'The email address is invalid.';
            }else if(empty($full_name) || empty($email) || empty($location) || empty($password) || empty($confirm_password) || empty($accept_terms)){
                $return['status'] = false;
                $return['message'] = 'Please fill out all required fields.';
            }else if ($password !== $confirm_password){
                $return['status'] = false;
                $return['message'] = 'Passwords do not match.';
                echo json_encode($return);
                exit;
            }else{
                // Generate verification code
                $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                // Store temporary data
                update_option('temp_user_' . md5($email), 
                    [
                        'full_name'     => $full_name,
                        'password'      => $password,
                        'location'      => $location,
                        'code'          => $verification_code,
                    ]
                );

                // Send verification email
                $subject = 'Your Verification Code';
                $message = "Hi $full_name,\n\nYour verification code is: $verification_code\n\nThanks,\nSteve's Weave";
                wp_mail($email, $subject, $message);

                $return['status']   =   true;
                $return['message']  =   'Verification code sent to your email.';
            }
            echo json_encode($return);
            exit;
        }
        /**
         * VERIFICATION
         */
        public static function stevesweave_register_verification_cb() {
            $return = [];
            $code1 = isset($_POST['code1']) ? intval($_POST['code1']) : '';
            $code2 = isset($_POST['code2']) ? intval($_POST['code2']) : '';
            $code3 = isset($_POST['code3']) ? intval($_POST['code3']) : '';
            $code4 = isset($_POST['code4']) ? intval($_POST['code4']) : '';
            $code5 = isset($_POST['code5']) ? intval($_POST['code5']) : '';
            $code6 = isset($_POST['code6']) ? intval($_POST['code6']) : '';
            $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
            $verification_code = intval($code1 . $code2 . $code3 . $code4 . $code5 . $code6);
        
            if (empty($email)) {
                $return['status'] = false;
                $return['message'] = 'Oops, something went wrong, please refresh the page and try again. Thanks!';
            } else if (empty($verification_code)) {
                $return['status'] = false;
                $return['message'] = 'Please enter a 6-digit verification code!';
            } else {
                $temp_user = get_option('temp_user_' . md5($email));
                if (!$temp_user || intval($temp_user['code']) !== $verification_code) {
                    $return['status'] = false;
                    $return['message'] = 'Invalid or expired code!';
                } else {
                    $name_parts     =   explode(' ', $temp_user['full_name'], 2);
                    $first_name     =   isset($name_parts[0]) ? $name_parts[0] : '';
                    $last_name      =   isset($name_parts[1]) ? $name_parts[1] : '';
                    // Register the user
                    $user_id = wp_insert_user([
                        'user_login'    =>  $email,
                        'user_email'    =>  $email,
                        'user_pass'     =>  $temp_user['password'],
                        'first_name'    =>  $first_name,
                        'last_name'     =>  $last_name,
                        'display_name'  =>  $temp_user['full_name'],
                    ]);
        
                    if (is_wp_error($user_id)) {
                        $return['status']   = false;
                        $return['message']  = 'An error occurred during registration. Please try again.';
                    } else {
                        update_user_meta($user_id, 'default_location', $temp_user['location']);
                        delete_option('temp_user_' . md5($email));
        
                        // Log in the user
                        wp_set_current_user($user_id);
                        wp_set_auth_cookie($user_id);
        
                        // Send a registration success email
                        $subject = 'Welcome to Steve’s Weave!';
                        $message = "
                            <p>Hi {$temp_user['full_name']},</p>
                            <p>Welcome to <strong>Steve’s Weave</strong>! Your registration was successful. Below are your account details:</p>
                            <p><strong>Email:</strong> {$email}</p>
                            <p><strong>Password:</strong> {$temp_user['password']}</p>
                            <p>You can now log in and start exploring: <a href='" . home_url() . "'>" . home_url() . "</a></p>
                            <p>Thank you for joining us!</p>
                            <p>Best regards,<br>The Steve’s Weave Team</p>
                        ";
                        $headers = [
                            'Content-Type: text/html; charset=UTF-8',
                            'From: Steve’s Weave <no-reply@stevesweave.com>',
                        ];

                        wp_mail($email, $subject, $message, $headers);
        
                        // Return success response
                        $return['status']           =   true;
                        $return['message']          =   'Registration successful! You are now logged in. Redirecting...';
                        $return['redirect_url']     =   home_url('/account-details/');
                    }
                }
            }
        
            echo json_encode($return);
            exit;
        }
        
        /**
         * LOGIN PROCESS HANDLER.
         */
        public static function stevesweave_login_cb() {
            $return = [];
            // Get and sanitize the input values
            $security_nonce = isset($_POST['security_nonce'])   ? sanitize_text_field($_POST['security_nonce']) : '';
            $email_address  = isset($_POST['email_address'])    ? sanitize_email($_POST['email_address']) : '';
            $l_password     = isset($_POST['l_password'])       ? sanitize_text_field($_POST['l_password']) : '';
            // Verify the nonce
            if (empty($security_nonce) || !wp_verify_nonce($security_nonce, 'stevesweave_login_nonce')) {
                $return['status'] = false;
                $return['message'] = 'Nonce verification failed';
            } elseif (empty($email_address) || empty($l_password)) {
                $return['status'] = false;
                $return['message'] = 'Please fill out required fields!';
            } else {
                // Attempt to log the user in
                $credentials = [
                    'user_login'    => $email_address,
                    'user_password' => $l_password,
                    'remember'      => true,
                ];
                
                $user = wp_signon($credentials, is_ssl());
                if (is_wp_error($user)) {
                    // Authentication failed
                    $return['status'] = false;
                    $return['message'] = $user->get_error_message();
                } else {
                    // Authentication successful
                    $return['status'] = true;
                    $return['message'] = 'Login successful! Redirecting...';
                    $return['redirect_url'] = home_url('/account-details/');
                }
            }
            // Return the response as JSON
            echo json_encode($return);
            exit;
        }        
    }

    // Instantiate the class
    $StevesweaveShortcodes = new StevesweaveShortcodes();
}