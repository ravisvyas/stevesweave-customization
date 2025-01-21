<?php
/**
 * @class GeoDirUserDashboard
 */
if (!class_exists('GeoDirUserDashboard', false)) {


    class GeoDirUserDashboard {

        /**
         * Initialize required action and filters
         * @return void
         */
        public static function init() {

            //add shortcode for frontend gd_facilitator
            add_shortcode('user_dashboard_stevesweave', array(__CLASS__, 'user_dashboard_stevesweave'), 1);

        }

        public static function user_dashboard_stevesweave() {
            if ( ! is_user_logged_in() ) {
                $referer = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
                $login_url = um_get_core_page( 'login' );
                $login_url = add_query_arg( 'redirect_to', urldecode_deep( $referer ), $login_url );
                wp_safe_redirect( $login_url );
                exit;
            }
        
            // Get the current user
            $current_user = wp_get_current_user();
            echo $user_id = $current_user->ID;
        
            // Query to get the user's listings (gd_place post type)
            $args = array(
                'post_type' => 'gd_place',
                'author' => $user_id,
                'posts_per_page' => -1, // Get all listings
                'post_status' => array('pending','publish'),
            );
            $listings = new WP_Query($args);
        
            // Initialize statistics
            $total_listings = $listings->found_posts;
            $total_views = 0;
            $listing_data = '';
        
            // Loop through the listings and calculate views
            if ($listings->have_posts()) {
                while ($listings->have_posts()) {
                    $listings->the_post();
                    $views = get_post_meta(get_the_ID(), 'views', true); // Assuming the view count is stored in 'views' meta
                    $total_views += intval($views);

                    // Construct the listing rows with "View" and "Edit" actions
                    $listing_data .= '<tr>';
                    $listing_data .= '<td class="listing-item-first">' . get_the_title() . '</td>';
                    $listing_data .= '<td>' . get_the_author_meta('display_name', get_post_field('post_author', get_the_ID())) . '</td>';
                    $listing_data .= '<td>' . geodir_get_post_meta(get_the_ID(), 'website', true) . '</td>'; // Assuming 'site_address' is a custom field
                    $listing_data .= '<td>' . get_the_modified_date('d/m/Y H:i') . '</td>';
                    $listing_data .= '<td>' . $views . '</td>';
                    $listing_data .= '<td class="action-column">';
                    // Add "View" link and "Edit" link (Edit goes to a custom URL)
                    $listing_data .= '<a href="' . get_permalink() . '" target="_blank">View</a> | ';
                    $listing_data .= '<a href="' . site_url('/add-listing/business/?pid=' . get_the_ID()) . '" target="_blank">Edit</a>';
                    $listing_data .= '</td>';
                    $listing_data .= '</tr>';
                }
            }
        
            // Reset post data
            wp_reset_postdata();
        
            ob_start();
            ?>
            <div class="business-section-container">
                <div class="header-box-green">
                    <h2>Steve's Weave For Businesses</h2>
                    <p class="pera-text">Welcome to the Business Portal at Steve's Weave — your gateway to sustainable success! Here, you have the opportunity to showcase your eco-friendly initiatives, connect with environmentally-conscious consumers &amp; contribute to a greener future.</p>
                    <p class="pera-text">Whether you’re promoting sustainable products, hosting eco-friendly events, or sharing educational content, our platform empowers you to amplify your impact and reach a wider audience. Join us in building a vibrant green community and together, let’s pave the way towards a more sustainable tomorrow.</p>
                    <button class="faq-button">Learn More - FAQ</button>
                </div>
                <div class="stats">
                    <div class="stat-box">
                        <p class="pera-text">Total Listings</p>
                        <h3 class="numb-heading"><?php echo $total_listings; ?></h3>
                    </div>
                    <div class="stat-box">
                        <p class="pera-text">Total Listing Views</p>
                        <h3 class="numb-heading"><?php echo $total_views; ?></h3>
                    </div>
                    <div class="stat-box">
                        <p class="pera-text">Listing Click Rate</p>
                        <?php 
                        // Assuming total views divided by total listings gives a rough "click rate"
                        $click_rate = $total_listings ? round(($total_views / $total_listings) * 100, 2) : 0;
                        ?>
                        <h3 class="numb-heading"><?php echo $click_rate . '%'; ?></h3>
                    </div>
                    <div class="stat-box create-button">
                        <a href="/add-listing/business">
                            <h3 class="numb-heading">+</h3>
                            <p class="pera-text">Creat Listing</p>
                        </a>
                    </div>
                </div>
                <table class="listing-table">
                    <thead>
                        <tr>
                            <th>
                                Listing <span class="arrow">▲▼</span>
                            </th>
                            <th>
                                Owner <span class="arrow">▲▼</span>
                            </th>
                            <th>
                                Site <span class="arrow">▲▼</span>
                            </th>
                            <th>
                                Date / time modified <span class="arrow">▲▼</span>
                            </th>
                            <th>
                                Views <span class="arrow">▲▼</span>
                            </th>
                            <th>
                                Actions <span class="arrow">▲▼</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $listing_data; ?>
                    </tbody>
                </table>
            </div>
            <?php
            return ob_get_clean();
        }

    }

    /*
     * Initialize class init method for load its functionality.
     */
    GeoDirUserDashboard::init();
}
?>