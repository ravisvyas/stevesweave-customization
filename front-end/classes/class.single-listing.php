<?php
/**
 * @class GeoDirSingleListing
 */
if (!class_exists('GeoDirSingleListing', false)) {


    class GeoDirSingleListing {

        /**
         * Initialize required action and filters
         * @return void
         */
        public static function init() {

            //add shortcode for frontend gd_facilitator
            add_shortcode('single_listing_stevesweave', array(__CLASS__, 'add_listing_stevesweave'), 1);

        }

        public static function add_listing_stevesweave() {
            global $gd_post;
            // print_r($gd_post);
            $post_id = $gd_post->ID;
            $thumbnail_url = get_the_post_thumbnail_url($post_id);

            $thumbnail_urls = self::sw_attached_images_urls($post_id);

            // Fetch individual address components
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

            // print_r($thumbnail_urls);
            ob_start();
            ?>
            <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
            <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
            <!-- Banner Upload Section -->
            <!-- <section class="banner-section banner-details-section" style="background-image: url('<?php echo $thumbnail_url; ?>');">
                <div class="main-heading-box">
                    <h1 class="main-banner-heading"><?php echo $gd_post->post_title; ?></h1>
                </div>
            </section> -->

            <section class="banner-section banner-details-section" style="">
                <div class="post-banner-slider">
                    <?php foreach ($thumbnail_urls as $key => $image_url ): ?>
                        <div class="post-banner-image">
                            <img alt="Post Image" class="post-attachment-img" src="<?php echo esc_url( $image_url ); ?>" />
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="main-heading-box">
                    <h1 class="main-banner-heading"><?php echo $gd_post->post_title; ?></h1>
                </div>
            </section>

            <!-- Business Form Section -->
            <section class="business-form-section business-details-section">
                <div class="business-form">
                    <!-- Left Column -->
                    <div class="form-left">
                        <!-- Dropdown Cards -->
                        <div class="dropdown-cards">
                            <div class="dropdown-card">
                                <div class="card-heading-box"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M0.75 19.6875C0.75 20.0356 0.888281 20.3694 1.13442 20.6156C1.38056 20.8617 1.7144 21 2.0625 21H21.9375C22.2856 21 22.6194 20.8617 22.8656 20.6156C23.1117 20.3694 23.25 20.0356 23.25 19.6875V9.75H0.75V19.6875ZM23.25 5.8125C23.25 5.4644 23.1117 5.13056 22.8656 4.88442C22.6194 4.63828 22.2856 4.5 21.9375 4.5H9.97688L7.72687 3H2.0625C1.7144 3 1.38056 3.13828 1.13442 3.38442C0.888281 3.63056 0.75 3.9644 0.75 4.3125V8.25H23.25V5.8125Z" fill="white"/>
                                    </svg> <label class="">Business Category</label>
                                </div>
                                <div class="select-box-outer"><?php echo do_shortcode('[gd_post_meta key="post_category" show="value-strip" no_wrap="1" font_size="0"]'); ?></div>
                            </div>
                            <div class="dropdown-card">
                                <div class="card-heading-box"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M0.75 19.6875C0.75 20.0356 0.888281 20.3694 1.13442 20.6156C1.38056 20.8617 1.7144 21 2.0625 21H21.9375C22.2856 21 22.6194 20.8617 22.8656 20.6156C23.1117 20.3694 23.25 20.0356 23.25 19.6875V9.75H0.75V19.6875ZM23.25 5.8125C23.25 5.4644 23.1117 5.13056 22.8656 4.88442C22.6194 4.63828 22.2856 4.5 21.9375 4.5H9.97688L7.72687 3H2.0625C1.7144 3 1.38056 3.13828 1.13442 3.38442C0.888281 3.63056 0.75 3.9644 0.75 4.3125V8.25H23.25V5.8125Z" fill="white"/>
                                    </svg> <label>Product / Service</label>
                                </div>
                                <div class="select-box-outer"><?php echo self::get_category_names_from_ids( geodir_get_post_meta($post_id,'products_and_services', true), 'product_placecategory' ); ?></div>
                                <!-- <div class="select-box-outer"><?php echo geodir_get_post_meta($post_id,'products_and_services', true); ?></div> -->
                            </div>
                            <div class="dropdown-card">
                                <div class="card-heading-box"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M0.75 19.6875C0.75 20.0356 0.888281 20.3694 1.13442 20.6156C1.38056 20.8617 1.7144 21 2.0625 21H21.9375C22.2856 21 22.6194 20.8617 22.8656 20.6156C23.1117 20.3694 23.25 20.0356 23.25 19.6875V9.75H0.75V19.6875ZM23.25 5.8125C23.25 5.4644 23.1117 5.13056 22.8656 4.88442C22.6194 4.63828 22.2856 4.5 21.9375 4.5H9.97688L7.72687 3H2.0625C1.7144 3 1.38056 3.13828 1.13442 3.38442C0.888281 3.63056 0.75 3.9644 0.75 4.3125V8.25H23.25V5.8125Z" fill="white"/>
                                    </svg> <label>Sustainability Tag</label>
                                </div>
                                <div class="select-box-outer"><?php echo self::get_category_names_from_ids( geodir_get_post_meta($post_id,'sustaini', true), 'stags_placecategory' ); ?></div>
                                <!-- <div class="select-box-outer"><?php echo geodir_get_post_meta($post_id,'sustaini', true); ?></div> -->
                            </div>
                        </div>
                        
                        <div class="main-content-section">
                            <div class="Business-Description-box">
                                <h3 class="second-heading">Business Description*</h3>
                                    <p class="pera-text"><?php echo $gd_post->post_content; ?></p>
                            </div>

                            <div class="Post-content-box">
                                <h3 class="second-heading">Posts</h3>
                                <!-- <div class="text-box">
                                    <div class="heading-btn-box">
                                        <h4 class="third-heading">Join us this Saturday for our Eco-Friendly Workshop</h4>
                                        <a href="#" class="red-more btn">Read More</a>
                                    </div>
                                    <p class="pera-text">Join us this Saturday for our Eco-Friendly Workshop! Learn practical tips for sustainable living, enjoy interactive demonstrations, and connect with like-minded individuals. Don't miss out on this opportunity to make a positive impact on our planet. Reserve your spot today!</p>
                                </div>

                                <div class="text-box">
                                    <div class="heading-btn-box">
                                        <h4 class="third-heading">Introducing our latest innovation</h4>
                                        <a href="#" class="red-more btn">Read More</a>
                                    </div>
                                    <p class="pera-text">Introducing our latest innovation: the Eco-Blend Clothing Line! Crafted from recycled materials and sustainably sourced fabrics, our new collection combines style with sustainability. Embrace fashion with a conscience and shop our Eco-Blend range now!</p>
                                </div>
                                <div class="text-box">
                                    <div class="heading-btn-box">
                                        <h4 class="third-heading">Limited Time Offer: Get 20% off all eco-friendly home products!</h4>
                                        <a href="#" class="red-more btn">Read More</a>
                                    </div>
                                    <p class="pera-text">Limited Time Offer: Get 20% off all eco-friendly home products! From reusable bamboo utensils to energy-efficient appliances, we have everything you need to create a greener home. Shop now and take advantage of this exclusive discount while it lasts!</p>
                                </div> -->

                            </div>

                        </div>
                        
                    </div>

                    <!-- Right Column -->
                    <div class="form-right">
                        <div class="input-group">
                            <label>🌐 Business Link*</label>
                            <span class="right-side-details"><?php echo geodir_get_post_meta($post_id,'website', true); ?></span>
                        </div>
                        <div class="input-group">
                            <label>📍 Location</label>
                            <span class="right-side-details"><?php echo $address; ?></span>
                        </div>
                        <div class="input-group">
                            <label>📧 Business Contact Email</label>
                            <span class="right-side-details"><?php echo geodir_get_post_meta($post_id,'business_contact_', true); ?></span>
                        </div>
                        <div class="input-group">
                            <label>📞  Phone</label>
                            <span class="right-side-details"><?php echo geodir_get_post_meta($post_id,'phone', true); ?></span>
                        </div>

                        <div class="input-group" id="social-media-links">
                            <label>Social Media Links</label>
                            <?php self::populate_social_media_links($gd_post); ?>
                        </div>
                        <div class="input-group">
                            <a href="<?php echo site_url(). '/add-listing/business/?pid='.$post_id; ?>" id="edit-listing-btn" class="save-btn single-btn">Edit This Listing</a>
                        </div>
                        <div class="input-group" id="claim-business-single">
                            <a href="<?= home_url('/claim-listing/'.$post_id.'/') ?>" class="save-btn single-btn" id="claim-listing-btn">Claim This Listing</a>
                        </div>
                    </div>
                </div>
            </section>
            <?php
            return ob_get_clean();
        }

        public static function populate_social_media_links($post) {
            // echo $post->ID;
            $social_media_links = get_post_meta($post->ID, 'social_media_links', true);
            // print_r($social_media_links);
            echo '<div id="social-icons-container" style="display:flex;">';
            if (!empty($social_media_links)) {
                foreach ($social_media_links as $social) {
                    $data = json_decode($social, true);
                    echo '<div id="social-icons-container"><div class="social-icon ' . esc_attr($data['type']) . '" title="' . esc_attr($data['link']) . '">';
                    echo '<i class="fab fa-' . esc_attr($data['type']) . '"></i>';
                    echo '</div></div>';
                }
            }
            echo '</div>';
        }

        public static function sw_attached_images_urls($post_id) {
            global $wpdb;
            // Get all attachments for the post
            $attachments = $wpdb->get_results("select * from {$wpdb->prefix}geodir_attachments where post_id = '$post_id'");
        
            // Initialize an array to store image URLs
            $image_urls = [];
        
            if (!empty($attachments)) {
                foreach ($attachments as $attachment_id => $attachment) {
                    // Get the URL of each attachment
                    $image_urls[] = site_url().'/wp-content/uploads/'.$attachment->file;
                }
            }
        
            return $image_urls;
        }

        public static function get_category_names_from_ids($category_ids, $taxonomy = 'category') {
            // Ensure the input is a string and not empty
            if (empty($category_ids) || !is_string($category_ids)) {
                return '';
            }
        
            // Convert the comma-separated string of IDs into an array
            $category_ids_array = explode(',', $category_ids);
        
            // Fetch the terms for the provided IDs
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'include'  => $category_ids_array,
                'hide_empty' => false, // Include all terms, even if they have no posts
            ));
        
            // If no terms are found, return an empty string
            if (is_wp_error($terms) || empty($terms)) {
                return '';
            }
        
            // Extract term names from the results
            $category_names = wp_list_pluck($terms, 'name');
        
            // Convert the array of names into a comma-separated string
            return implode(', ', $category_names);
        }

    }

    /*
     * Initialize class init method for load its functionality.
     */
    GeoDirSingleListing::init();
}
?>