<?php
/**
 * @class GeoDirAddListing
 */
if (!class_exists('GeoDirAddListing', false)) {


    class GeoDirAddListing {

        /**
         * Initialize required action and filters
         * @return void
         */
        public static function init() {

            //add shortcode for frontend gd_facilitator
            add_shortcode('add_listing_stevesweave', array(__CLASS__, 'add_listing_stevesweave'), 1);

            //add ajax action for file upload
            add_action('wp_ajax_sw_file_upload', array(__CLASS__, 'handle_sw_file_upload') );
            add_action('wp_ajax_nopriv_sw_file_upload', array(__CLASS__, 'handle_sw_file_upload') );

            add_action( 'save_post', array(__CLASS__, 'save_social_media_links' ) );

            add_filter('geodir_ajax_save_post_message', array(__CLASS__, 'custom_save_post_message_handler' ), 10, 2);
            add_action('wp_enqueue_scripts', array(__CLASS__,  'replace_geodir_add_listing_script' ), 99);
            // add_filter( 'geodir_post_output_user_notes', array(__CLASS__, 'modify_geodir_post_output_user_notes'),1, 99 );
        }

        public static function modify_geodir_post_output_user_notes( $user_notes ) {
            // print_r($user_notes);
            return $user_notes['gd-info'].'&'; // Return null instead of the original value
        }
        

        public static function replace_geodir_add_listing_script() {
            // Check if the 'geodir-add-listing' script is registered before deregistering
            if (wp_script_is('geodir-add-listing', 'registered')) {
                // Deregister the original script
                wp_deregister_script('geodir-add-listing');
                $plugin_url = StevesWeaveCustomization::get_plugin_url();
                // Enqueue your custom script
                wp_enqueue_script(
                    'geodir-add-listing', // Same handle as the original
                    $plugin_url . 'front-end/js/add-listing.js', // Path to your custom script
                    array('jquery'), // Dependencies
                    time(), // Version
                    true // Load in the footer
                );
            }
        }
        


        public static function custom_save_post_message_handler($message, $post_data) {
            // $message = "Your post has been saved successfully!";
            // print_r($post_data);
            // Check if 'id' exists in $post_data to avoid undefined index notices
            if ( isset( $post_data['ID'] ) ) {
                // Append the post ID to the message
                $message = intval( $post_data['ID'] );
            }

            return $message;
        }

        public static function save_social_media_links($post_id) {
            // print_r($_POST);
            if (isset($_POST['social_media'])) {
                $social_media_links = array_map('sanitize_text_field', $_POST['social_media']);
                update_post_meta($post_id, 'social_media_links', $social_media_links);
            }
        }
        
        public static function handle_sw_file_upload() {
            // Check the nonce for security
            check_ajax_referer('file_upload_nonce', 'security');
        
            // Check if a file is uploaded
            if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
                $file = $_FILES['file'];
        
                // Handle the upload
                $uploaded = wp_handle_upload($file, ['test_form' => false]);
        
                if (!isset($uploaded['error']) && isset($uploaded['url'])) {
                    // Insert the uploaded file into the media library
                    $attachment_id = wp_insert_attachment([
                        'guid'           => $uploaded['url'],
                        'post_mime_type' => $uploaded['type'],
                        'post_title'     => sanitize_file_name($file['name']),
                        'post_content'   => '',
                        'post_status'    => 'inherit',
                    ], $uploaded['file']);
        
                    // Generate attachment metadata and update the database record
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata($attachment_id, $uploaded['file']);
                    wp_update_attachment_metadata($attachment_id, $attach_data);
        
                    // Return the file URL and attachment ID
                    wp_send_json_success([
                        'url'          => $uploaded['url'],
                        'attachment_id' => $attachment_id,
                    ]);
                } else {
                    wp_send_json_error(['message' => $uploaded['error']]);
                }
            }
        
            wp_send_json_error(['message' => 'No file uploaded.']);
        }

        public static function add_listing_stevesweave() {
            global $aui_bs5, $cat_display, $post_cat, $current_user, $gd_post,$geodir_label_type, $wp;

            if ( ! is_user_logged_in() ) {
                $referer = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
                $login_url = um_get_core_page( 'login' );
                $login_url = add_query_arg( 'redirect_to', urldecode_deep( $referer ), $login_url );
                wp_safe_redirect( $login_url );
                exit;
            }

            $category_field = geodir_get_field_infoby( 'htmlvar_name', 'post_category', 'gd_place' );
            $category_field['name'] = 'post_category';
            
            $products_and_services_field = geodir_get_field_infoby( 'htmlvar_name', 'products_and_services', 'gd_place' );
            $products_and_services_field['name'] = 'products_and_services';

            $sustaini_field = geodir_get_field_infoby( 'htmlvar_name', 'sustaini', 'gd_place' );
            $sustaini_field['name'] = 'sustaini';
            
            $post_images = geodir_get_field_infoby( 'htmlvar_name', 'post_images', 'gd_place' );
            $post_images['name'] = 'post_images';

            $post_website = geodir_get_field_infoby( 'htmlvar_name', 'website', 'gd_place' );
            $post_website['name'] = 'website';

            $post_business_contact_ = geodir_get_field_infoby( 'htmlvar_name', 'business_contact_', 'gd_place' );
            $post_business_contact_['name'] = 'business_contact_';

            $post_phone = geodir_get_field_infoby( 'htmlvar_name', 'phone', 'gd_place' );
            $post_phone['name'] = 'phone';

            $post_website = geodir_get_field_infoby( 'htmlvar_name', 'website', 'gd_place' );
            $post_website['name'] = 'website';
            // echo '<pre>';
            // print_r($old_field);
            // echo '</pre>';
            $params        = array();
            $page_id       = get_the_ID();
            $post          = '';
            $submit_button = '';
            $post_id       = '';
            $post_parent   = '';
            $user_notes    = array();

            $user_id = get_current_user_id();

            $business_contact_ = '';
            $website = '';
            $phone = '';
            $address = '';
            $social_link = '';
            $post_title = '';
            $post_content = '';
            $business_name = 'Business Name';
            

            // if we have the post id.
            if ( $user_id && isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
                global $post;

                $post_id        = absint( $_REQUEST['pid'] );

                $business_contact_ = geodir_get_post_meta($post_id,'business_contact_', true);
                $website = geodir_get_post_meta($post_id,'website', true);
                $phone = geodir_get_post_meta($post_id,'phone', true);
                $post_title = geodir_get_post_meta($post_id,'post_title', true);
                
                $business_name = geodir_get_post_meta($post_id,'post_title', true);

                $street   = geodir_get_post_meta($post_id, 'street', true);
                $street2  = geodir_get_post_meta($post_id, 'street2', true);
                $city     = geodir_get_post_meta($post_id, 'city', true);
                $region   = geodir_get_post_meta($post_id, 'region', true);
                $country  = geodir_get_post_meta($post_id, 'country', true);
                $zip      = geodir_get_post_meta($post_id, 'zip', true);

                // Combine all fields, removing any empty ones
                $address = implode(', ', array_filter([$street, $street2, $city, $region, $country, $zip]));

                if(geodir_get_post_meta($post_id, 'sw_address', true)){
                    $address = geodir_get_post_meta($post_id,'sw_address', true);
                }

                $social_link = self::edit_social_media_links($post_id);

                // check if user has privileges to edit the post
                $maybe_parent = wp_get_post_parent_id( $post_id  );
                $parent_id = $maybe_parent ? absint( $maybe_parent ) : '';
                if ( ! GeoDir_Post_Data::can_edit( $post_id, get_current_user_id(), $parent_id ) ) {
                    echo GeoDir_Post_Data::output_user_notes( array( 'gd-error' => __( 'You do not have permission to edit this post.', 'geodirectory' ) ) );
                    return;
                }

                $post           = $gd_post = geodir_get_post_info( $post_id );
                $listing_type   = $post->post_type;
                $post_revisions = wp_get_post_revisions( $post_id, array(
                    'check_enabled' => false,
                    'author'        => $user_id
                ) );

                $post_content = get_post_field( 'post_content', $gd_post->ID );

                // if we have a post revision
                if ( ! empty( $post_revisions ) ) {
                    $revision    = reset( $post_revisions );
                    $post_parent = $post_id;
                    $post_id     = absint( $revision->ID );
                    $post        = $gd_post = geodir_get_post_info( $post_id );

                    $user_notes['has-revision'] = sprintf( __( 'Hey, we found some unsaved changes from earlier and are showing them below. If you would prefer to start again then please %sclick here%s to remove this revision.', 'geodirectory' ), "<a href='javascript:void(0)' onclick='geodir_delete_revision();'>", "</a>" );

                } // create a post revision
                else {
                    $revision_id = _wp_put_post_revision( $post );
                    $post_parent = $post_id;
                    $post_id     = absint( $revision_id );
                    $post        = $gd_post = geodir_get_post_info( $post_id );
                }

            } // New post
            elseif ( isset( $_REQUEST['listing_type'] ) && $_REQUEST['listing_type'] != '' ) {

                $listing_type = sanitize_text_field( $_REQUEST['listing_type'] );
                $auto_drafts  = GeoDir_Post_Data::get_user_auto_drafts( $user_id, $listing_type );

                // if we have a user auto-draft then populate it
                if ( ! empty( $auto_drafts ) && isset( $auto_drafts[0] ) ) {
                    $post        = $auto_drafts[0];
                    $post_parent = $post_id;
                    $post_id     = absint( $post->ID );
                    $post        = $gd_post = geodir_get_post_info( $post_id );

                    if ( $post->post_modified_gmt != '0000-00-00 00:00:00' ) {
                        $user_notes['has-auto-draft'] = sprintf( __( 'Hey, we found a post you started earlier and are showing it below. If you would prefer to start again then please %sclick here%s to remove this revision.', 'geodirectory' ), "<a href='javascript:void(0)' onclick='geodir_delete_revision();'>", "</a>" );
                    }
                } else {
                    // Create the auto draft
                    $post    = $gd_post = GeoDir_Post_Data::create_auto_draft( $listing_type );
                    $post_id = absint( $post->ID );
                    $post    = $gd_post = geodir_get_post_info( $post_id );
                }

            } else {
                // echo '### a post type could not be determined.';

                // return;
            }


            $post_type_info = geodir_get_posttype_info( $listing_type );

            $cpt_singular_name = ( isset( $post_type_info['labels']['singular_name'] ) && $post_type_info['labels']['singular_name'] ) ? __( $post_type_info['labels']['singular_name'], 'geodirectory' ) : __( 'Listing', 'geodirectory' );

            $package = geodir_get_post_package( $post, $listing_type );

            // user notes
            if ( ! empty( $user_notes ) ) {
                echo GeoDir_Post_Data::output_user_notes( $user_notes );
            }

            /*
            * Create the security nonce, we also use this for logged out user preview.
            */
            $security_nonce = wp_create_nonce( "geodir-save-post" );

            $design_style =  geodir_design_style();
            $horizontal = false;
            if($design_style){
                $horizontal = $geodir_label_type == 'horizontal' ? true : false;
            }

            // wrap class
            $wrap_class = geodir_build_aui_class($params);
            ob_start();
        ?>
        <form name="geodirectory-add-post" id="geodirectory-add-post" class="<?php echo $wrap_class;?>"
                action="<?php echo get_page_link( $post->ID ); ?>" method="post"
                enctype="multipart/form-data">
            <input type="hidden" name="action" value="geodir_save_post"/>
            <input type="hidden" name="preview" value="<?php echo esc_attr( $listing_type ); ?>"/>
            <input type="hidden" name="post_type" value="<?php echo esc_attr( $listing_type ); ?>"/>
            <input type="hidden" name="post_parent" value="<?php echo esc_attr( $post_parent ); ?>"/>
            <input type="hidden" name="ID" value="<?php echo esc_attr( $post_id ); ?>"/>
            <input type="hidden" name="security"
                    value="<?php echo esc_attr( $security_nonce ); ?>"/>


            <?php if ( $page_id ) { ?>
                <input type="hidden" name="add_listing_page_id" value="<?php echo $page_id; ?>"/>
            <?php }
            if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) { ?>
            <?php }

            if ( ! empty( $params['container'] ) ) {
                ?>
                <input type="hidden" id="gd-add-listing-replace-container"
                        value="<?php echo esc_attr( $params['container'] ); ?>"/>
            <?php }
            /*
			 * Add the register fields if no user_id
			 */
			if ( ! $user_id ) {

				if($design_style ){

					echo '<fieldset class="' . ( $aui_bs5 ? 'mb-3' : 'form-group' ) . '" id="geodir_fieldset_your_details">';
					echo '<h3 class="h3">'.__( "Your Details", "geodirectory" ).'</h3>';
					echo '</fieldset>';

					echo aui()->input(
						array(
							'id'                => "user_login",
							'name'              => "user_login",
							'required'          => true,
							'label'              => __("Name", 'geodirectory').' <span class="text-danger">*</span>',
							'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
							'type'              => 'text',
							'class'             => '',
							'help_text'         => __("Enter your name.", 'geodirectory'),
						)
					);

					echo aui()->input(
						array(
							'id'                => "user_email",
							'name'              => "user_email",
							'required'          => true,
							'label'              => __("Email", 'geodirectory').' <span class="text-danger">*</span>',
							'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
							'type'              => 'email',
							'class'             => '',
							'help_text'         => __("Enter your email address.", 'geodirectory'),
						)
					);
				}else{

				?>
				<h5 id="geodir_fieldset_details" class="geodir-fieldset-row" gd-fieldset="user_details"><?php _e( "Your Details", "geodirectory" ); ?></h5>

				<div id="user_login_row" class="required_field geodir_form_row clearfix gd-fieldset-details">
					<label><?php _e( "Name", "geodirectory" ); ?> <span>*</span></label>
					<input field_type="text" name="user_login" id="user_login" value="" type="text"
					       class="geodir_textfield">
					<span class="geodir_message_note"><?php _e( "Enter your name.", "geodirectory" ); ?></span>
					<span class="geodir_message_error"></span>
				</div>
				<div id="user_email_row" class="required_field geodir_form_row clearfix gd-fieldset-details">
					<label><?php _e( "Email", "geodirectory" ); ?> <span>*</span></label>
					<input field_type="text" name="user_email" id="user_email" value="" type="text"
					       class="geodir_textfield">
					<span class="geodir_message_note"><?php _e( "Enter your email address.", "geodirectory" ); ?></span>
					<span class="geodir_message_error"></span>
				</div>
				<?php

				}
			}else{ ?>
            <!-- End of User Details -->
                <!-- Banner Upload Section -->
                <section class="banner-section add-listing-banner-section" id="banner-upload-section">
                    <div class="upload-main-banner-inner">
                        <div class="banner-upload">
                            <!-- <input type="hidden" name="post_images" id="post_images" value="https://green-anteater-835969.hostingersite.com/wp-content/uploads/2024/11/banner-1.jpg|452||" class="">
                            <input type="hidden" name="post_imagesimage_limit" id="post_imagesimage_limit" value="0">
                            <input type="hidden" name="post_imagestotImg" id="post_imagestotImg" value="1">
                            <input type="hidden" name="post_images_allowed_types" id="post_images_allowed_types" value="jpg,jpe,jpeg,gif,png,bmp,ico,webp,avif" data-exts=".jpg, .jpe, .jpeg, .gif, .png, .bmp, .ico, .webp, .avif">
                            <input type="file" name="file-upload" id="file-upload" accept="image/*" style="display: none;" />
                            <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 72 72" fill="none">
                                <path d="M42.75 31.5C44.085 31.5 45.3901 31.1041 46.5001 30.3624C47.6101 29.6207 48.4753 28.5665 48.9862 27.3331C49.4971 26.0997 49.6308 24.7425 49.3703 23.4331C49.1099 22.1238 48.467 20.921 47.523 19.977C46.579 19.033 45.3762 18.3902 44.0669 18.1297C42.7575 17.8693 41.4003 18.0029 40.1669 18.5138C38.9335 19.0247 37.8793 19.8899 37.1376 20.9999C36.3959 22.1099 36 23.415 36 24.75C36 26.5402 36.7112 28.2571 37.977 29.523C39.2429 30.7888 40.9598 31.5 42.75 31.5ZM42.75 22.5C43.195 22.5 43.63 22.632 44 22.8792C44.37 23.1264 44.6584 23.4778 44.8287 23.889C44.999 24.3001 45.0436 24.7525 44.9568 25.189C44.87 25.6254 44.6557 26.0263 44.341 26.341C44.0263 26.6557 43.6254 26.87 43.189 26.9568C42.7525 27.0436 42.3001 26.999 41.889 26.8287C41.4778 26.6584 41.1264 26.37 40.8792 26C40.632 25.63 40.5 25.195 40.5 24.75C40.5 24.1533 40.7371 23.581 41.159 23.159C41.581 22.7371 42.1533 22.5 42.75 22.5Z" fill="#252525"/>
                                <path d="M58.5 9H13.5C12.3065 9 11.1619 9.47411 10.318 10.318C9.47411 11.1619 9 12.3065 9 13.5V58.5C9 59.6935 9.47411 60.8381 10.318 61.682C11.1619 62.5259 12.3065 63 13.5 63H58.5C59.6935 63 60.8381 62.5259 61.682 61.682C62.5259 60.8381 63 59.6935 63 58.5V13.5C63 12.3065 62.5259 11.1619 61.682 10.318C60.8381 9.47411 59.6935 9 58.5 9ZM58.5 58.5H13.5V45L24.75 33.75L37.3275 46.3275C38.1706 47.1656 39.3112 47.6361 40.5 47.6361C41.6888 47.6361 42.8294 47.1656 43.6725 46.3275L47.25 42.75L58.5 54V58.5ZM58.5 47.6325L50.4225 39.555C49.5794 38.7169 48.4388 38.2464 47.25 38.2464C46.0612 38.2464 44.9206 38.7169 44.0775 39.555L40.5 43.1325L27.9225 30.555C27.0794 29.7169 25.9388 29.2464 24.75 29.2464C23.5612 29.2464 22.4206 29.7169 21.5775 30.555L13.5 38.6325V13.5H58.5V47.6325Z" fill="#252525"/>
                            </svg> -->
                            <?php echo geodir_cfi_files('',$post_images); ?>
                            <!-- <div class="upload-icon-row">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M6.75 2.2V3.3C6.75 3.41046 6.66046 3.5 6.55 3.5H5C4.20116 3.5 3.54817 4.12446 3.50255 4.91186L3.5 5V19C3.5 19.7988 4.12446 20.4518 4.91186 20.4975L5 20.5H19C19.7988 20.5 20.4518 19.8755 20.4975 19.0881L20.5 19V5C20.5 4.20116 19.8755 3.54817 19.0881 3.50255L19 3.5H17.95C17.8395 3.5 17.75 3.41046 17.75 3.3V2.2C17.75 2.08954 17.8395 2 17.95 2H19C20.6569 2 22 3.34315 22 5V19C22 20.6569 20.6569 22 19 22H5C3.34315 22 2 20.6569 2 19V5C2 3.34315 3.34315 2 5 2H6.55C6.66046 2 6.75 2.08954 6.75 2.2ZM12.2429 2.00039C12.4155 1.99507 12.5899 2.04648 12.7356 2.15527L12.7963 2.20541L16.9349 5.98753C16.9764 6.02542 17 6.079 17 6.13516V8.03734C17 8.04418 16.9974 8.05076 16.9926 8.05571C16.9886 8.06 16.9818 8.06023 16.9774 8.05624L12.9998 4.42211L13 15.2421C13 15.3526 12.9105 15.4421 12.8 15.4421C12.8 15.4421 12.8 15.4421 12.8 15.4421H11.7C11.5895 15.4421 11.5 15.3526 11.5 15.2421L11.4998 4.40811L7.8349 7.75652C7.75335 7.83103 7.62685 7.82532 7.55235 7.74377C7.51867 7.70691 7.5 7.65879 7.5 7.60887V6.12117C7.5 6.065 7.52362 6.01142 7.56509 5.97352L11.689 2.20541C11.8457 2.0622 12.0454 1.9942 12.2429 2.00039Z" fill="#252525"/>
                                </svg> <p>Upload Business Listing Page Banner</p>
                            </div> -->
                        </div>
                    </div>
                    <div class="main-heading-box">
                        <h1 class="main-banner-heading"><?php echo $business_name; ?></h1>
                    </div>
                </section>

                <!-- Business Form Section -->
                <section class="business-form-section add-listing-form-section">
                    <div class="business-form">
                        <!-- Left Column -->
                        <div class="form-left add-listing-form-left">
                            <input type="text" name="post_title" id="post_title" class="input-field" value="<?php echo $post_title; ?>" placeholder="Business name*" >

                            <!-- Dropdown Cards -->
                            <div class="dropdown-cards">
                                <div class="dropdown-card">
                                    <div class="card-heading-box"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M0.75 19.6875C0.75 20.0356 0.888281 20.3694 1.13442 20.6156C1.38056 20.8617 1.7144 21 2.0625 21H21.9375C22.2856 21 22.6194 20.8617 22.8656 20.6156C23.1117 20.3694 23.25 20.0356 23.25 19.6875V9.75H0.75V19.6875ZM23.25 5.8125C23.25 5.4644 23.1117 5.13056 22.8656 4.88442C22.6194 4.63828 22.2856 4.5 21.9375 4.5H9.97688L7.72687 3H2.0625C1.7144 3 1.38056 3.13828 1.13442 3.38442C0.888281 3.63056 0.75 3.9644 0.75 4.3125V8.25H23.25V5.8125Z" fill="white"/>
                                        </svg> <label class="">Business Category</label>
                                    
                                    </div>
                                    <div class="select-box-outer" >
                                        <?php echo geodir_cfi_categories('',$category_field); ?>
                                    </div>
                                    <!-- <div class="select-box-outer" >
                                        <div class="custom-selectbox">
                                            <input type="hidden" name="tax_input[gd_placecategory][]" id="gd_placecategory" placeholder="Search Business Category">
                                            <input type="hidden" name="default_category" id="default_category" placeholder="Search Business Category">
                                            <input type="text" name="default_categorys" id="business-category" placeholder="Search Business Category">
                                            <ul class="select-options">
                                            <?php //self::generate_gd_placecategory_select_box(); ?>
                                            </ul>
                                        </div>
                                    </div> -->
                                </div>
                                <div class="dropdown-card">
                                    <div class="card-heading-box"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M0.75 19.6875C0.75 20.0356 0.888281 20.3694 1.13442 20.6156C1.38056 20.8617 1.7144 21 2.0625 21H21.9375C22.2856 21 22.6194 20.8617 22.8656 20.6156C23.1117 20.3694 23.25 20.0356 23.25 19.6875V9.75H0.75V19.6875ZM23.25 5.8125C23.25 5.4644 23.1117 5.13056 22.8656 4.88442C22.6194 4.63828 22.2856 4.5 21.9375 4.5H9.97688L7.72687 3H2.0625C1.7144 3 1.38056 3.13828 1.13442 3.38442C0.888281 3.63056 0.75 3.9644 0.75 4.3125V8.25H23.25V5.8125Z" fill="white"/>
                                        </svg> <label>Product / Service</label>
                                    </div>
                                    <div class="select-box-outer" >
                                        <?php echo geodir_cfi_multiselect('',$products_and_services_field); ?>
                                    </div>
                                    <!-- <div class="select-box-outer">
                                        <select name="products_and_services">
                                        <?php //self::render_geodirectory_custom_select_field('products_and_services'); ?>
                                        </select>
                                    </div> -->
                                </div>
                                <div class="dropdown-card">
                                    <div class="card-heading-box"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M0.75 19.6875C0.75 20.0356 0.888281 20.3694 1.13442 20.6156C1.38056 20.8617 1.7144 21 2.0625 21H21.9375C22.2856 21 22.6194 20.8617 22.8656 20.6156C23.1117 20.3694 23.25 20.0356 23.25 19.6875V9.75H0.75V19.6875ZM23.25 5.8125C23.25 5.4644 23.1117 5.13056 22.8656 4.88442C22.6194 4.63828 22.2856 4.5 21.9375 4.5H9.97688L7.72687 3H2.0625C1.7144 3 1.38056 3.13828 1.13442 3.38442C0.888281 3.63056 0.75 3.9644 0.75 4.3125V8.25H23.25V5.8125Z" fill="white"/>
                                        </svg> <label>Sustainability Tag</label>
                                    </div>
                                    <div class="select-box-outer" >
                                        <?php echo geodir_cfi_multiselect('',$sustaini_field); ?>
                                    </div>
                                    <!-- <div class="select-box-outer">
                                        <select name="sustaini">
                                            <?php //self::render_geodirectory_custom_select_field('sustaini'); ?>
                                        </select>
                                    </div> -->
                                </div>
                            </div>
                            
                            <label class="label second-heading">Business Description*</label>
                            <textarea name="post_content" id="post_content" placeholder="Please describe your business" class="textarea-field" ><?php echo $post_content; ?></textarea>
                            
                            <div class="post-section">
                                <h3 class="second-heading">Posts</h3>
                                <button type="button" class="add-post-btn">Add a Post</button>
                                <p>Posts are where you can share exciting updates about your business, such as upcoming events, new product launches, special promotions, and more.</p>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="form-right add-listing-form-right">
                            <div class="input-group">
                                <label>🌐 Business Link*</label>
                                <input type="text" name="website" value="<?php echo $website; ?>" class="input-field" placeholder="business1234.business.com" >
                            </div>
                            <div class="input-group">
                                <label>📍 Location</label>
                                <input type="text" name="sw_address" class="input-field" 
                                    value="<?php echo isset($address) ? $address : ''; ?>" 
                                    placeholder="1234 Boston St, Boston, MA">

                                <input type="hidden" name="street" 
                                    value="<?php echo isset($street) ? $street : '1234'; ?>">

                                <input type="hidden" name="street2" 
                                    value="<?php echo isset($street2) ? $street2 : 'Boston MA'; ?>">

                                <input type="hidden" name="city" 
                                    value="<?php echo isset($city) ? $city : 'Boston'; ?>">

                                <input type="hidden" name="region" 
                                    value="<?php echo isset($region) ? $region : 'MA'; ?>">

                                <input type="hidden" name="country" 
                                    value="<?php echo isset($country) ? $country : 'USA'; ?>">

                                <input type="hidden" name="zip" 
                                    value="<?php echo isset($zip) ? $zip : ''; ?>">
                            </div>
                            <div class="input-group">
                                <label>📧 Business Contact Email</label>
                                <input type="email"  name="business_contact_" required value="<?php echo $business_contact_; ?>" class="input-field" placeholder="contact@goodfilling.com" >
                            </div>
                            <div class="input-group">
                                <label>📞  Phone</label>
                                <input type="tel"  name="phone" value="<?php echo $phone; ?>" class="input-field" placeholder="223 336 9823" >
                                <?php echo geodir_cfi_phone('',$post_phone); ?>
                            </div>

                            <div class="input-group" id="social-media-links">
                                <label>Social Media Links</label>
                                <div id="social-icons-container"><?php echo $social_link; ?></div>
                                <button type="button" class="icon-plus" id="add-social-media">+</button>
                            </div>

                            <!-- Popup Modal -->
                            <div id="social-media-modal" style="display: none;">
                                <div class="modal-overlay" id="modal-overlay"></div>
                                <div class="modal-box">
                                    <div class="modal-header">
                                        <h2>Add Social Media Link</h2>
                                        <button type="button" id="close-modal" class="modal-close">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <label for="social-media-type" style="display: block; font-size: 14px; margin-bottom: 5px;">Select Social Media</label>
                                        <select id="social-media-type" style="width: 100%; padding: 10px; margin-bottom: 15px; font-size: 14px;">
                                            <option value="facebook" data-icon="fa-facebook">Facebook</option>
                                            <option value="x-twitter" data-icon="fa-x-twitter">X</option>
                                            <option value="instagram" data-icon="fa-instagram">Instagram</option>
                                            <option value="linkedin" data-icon="fa-linkedin">LinkedIn</option>
                                            <option value="youtube" data-icon="fa-youtube">YouTube</option>
                                            <option value="pinterest" data-icon="fa-pinterest">Pinterest</option>
                                            <option value="whatsapp" data-icon="fa-whatsapp">WhatsApp</option>
                                            <option value="tiktok" data-icon="fa-tiktok">TikTok</option>
                                        </select>

                                        <label for="social-media-link" style="display: block; font-size: 14px; margin-bottom: 5px;">Enter Social Media Link</label>
                                        <input type="text" id="social-media-link" placeholder="https://example.com" style="width: 100%; padding: 10px; margin-bottom: 20px; font-size: 14px;">

                                        <button type="button" id="save-social-media" style="background: #28a745; color: #fff; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; font-size: 14px;">Add Link</button>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="geodir_button save-btn">Save Listing Changes</button>
                            <span id="gd-notification-sw"></span>
                            <button type="button" class="remove-btn">Request Listing Removal</button>
                        </div>
                    </div>
                </section>
                <span class="geodir_message_note" style="padding-left:0px;"> <?php //_e( 'Note: You will be able to see a preview in the next page', 'geodirectory' ); ?></span>
            </form>
            <?php
            }
            return ob_get_clean();
        }

        public static function generate_gd_placecategory_select_box() {
            // Get all terms from the 'gd_placecategory' taxonomy
            $terms = get_terms([
                'taxonomy' => 'gd_placecategory',
                'hide_empty' => false, // Show all categories, even if they have no posts
            ]);
        
            // Check if terms are available
            if (!empty($terms) && !is_wp_error($terms)) {
                // echo '<option value="">Select a Category</option>'; // Default placeholder option
                
                // Loop through terms and add them as options
                foreach ($terms as $term) {
                    // echo '<option value="' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</option>';
                    echo '<li data-cid="' . esc_attr($term->term_id) . '"><span class="icon"></span><span>' . esc_html($term->name) . '</span></li>';
                }

            } else {
                echo '<p>No categories found.</p>'; // Message if no terms exist
            }
        }
        
        public static function render_geodirectory_custom_select_field($field_key) {
            global $wpdb;
        
            // Fetch the field's `option_values` from the database
            $table_name = $wpdb->prefix . 'geodir_custom_fields';
            $query = $wpdb->prepare("SELECT option_values FROM $table_name WHERE htmlvar_name = %s", $field_key);
            $option_values = $wpdb->get_var($query);
        
            // Check if option_values exist and are not empty
            if (!empty($option_values)) {
                // Convert comma-separated values into an array
                // $options = explode(',', $option_values);
                $options = array_filter(array_map('trim', explode("\n", $option_values)));
        
                // Generate the select box
                // echo '<select id="custom_select_field">';
                foreach ($options as $option) {
                    $option = trim($option); // Remove extra spaces
                    echo '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                }
                // echo '</select>';
            } else {
                echo '<p>No options available for this field.</p>';
            }
        }

        public static function edit_social_media_links($post_id) {
            // echo $post->ID;
            $social_media_links = get_post_meta($post_id, 'social_media_links', true);
            $result = '';
            // print_r($social_media_links);
            // echo '<div id="social-icons-container" style="display:flex;">';
            if (!empty($social_media_links)) {
                foreach ($social_media_links as $social) {
                    $data = json_decode($social, true);
                    $result .= '<div id="social-icon-container"><div data-info="{&quot;type&quot;:&quot;' . esc_attr($data['type']) . '&quot;,&quot;link&quot;:&quot;' . esc_attr($data['link']) . '&quot;}" class="social-icon ' . esc_attr($data['type']) . '" title="' . esc_attr($data['link']) . '">';
                    $result .= '<i class="fab fa-' . esc_attr($data['type']) . '"></i>';
                    $result .= '</div><div class="delete-icon">×</div></div>';
                    $result .= '<input type="hidden" name="social_media[]" value="{&quot;type&quot;:&quot;' . esc_attr($data['type']) . '&quot;,&quot;link&quot;:&quot;' . esc_attr($data['link']) . '&quot;}">';
                }
            }
            return $result;
            // echo '</div>';
        }

    }

    /*
     * Initialize class init method for load its functionality.
     */
    GeoDirAddListing::init();
}
?>