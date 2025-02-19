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
			add_action('wp_head',  array(__CLASS__, 'add_custom_header_html') );
			add_action('wp_footer', array(__CLASS__, 'add_custom_footer_html'), 999 );

			//add shortcode for headers home page
			add_shortcode('homepage_header',  array(__CLASS__, 'homepage_header_html') );
			add_shortcode('sw_post_termss',  array(__CLASS__, 'sw_display_post_terms_shortcode') );

			//filters
			add_filter( 'geodir_search_output_to_main_select', array( __CLASS__, 'sw_output_main_select' ), 11, 3 );
        }

		public static function sw_output_main_select( $html,$cf, $post_type ) {
			global $wpdb, $aui_bs5;
	
			$cf->input_type = 'SELECT';
	
			$select_fields_result = $wpdb->get_row( $wpdb->prepare( "SELECT option_values  FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s and htmlvar_name=%s  ORDER BY sort_order", array( $post_type, $cf->htmlvar_name ) ) );
			if ( in_array( $cf->input_type, array(
				'CHECK',
				'SELECT',
				'LINK',
				'RADIO'
			) ) ) {
				if ( $cf->htmlvar_name == 'sale_status' && ( $features = geodir_get_classified_statuses( $cf->post_type ) ) ) {
					$options = __( 'Select Status', 'geodirectory' ) . '/';
					foreach ( $features as $feature_value => $feature_label ) {
						$options .= ',' . $feature_label . '/' . $feature_value;
					}
					$select_fields_result->option_values = $options;
				}
				if ( $cf->htmlvar_name == 'products_and_services' ) {
					$options = __( 'Products and Services', 'geodirectory' ) . '/';
					$taxonomy = 'product_placecategory';
					$features = GeoDirectoryTaxonomy::get_dropdown_geodir_categories($taxonomy);
					foreach ( $features as $feature_value => $feature_label ) {
						$options .= ',' . $feature_label . '/' . $feature_value;
					}
					$select_fields_result->option_values = $options;
				}

				if ( $cf->htmlvar_name == 'products_and_services' ) {
					$options = __( 'Sustainability Tag', 'geodirectory' ) . '/';
					$taxonomy = 'stags_placecategory';
					$features = GeoDirectoryTaxonomy::get_dropdown_geodir_categories($taxonomy);
					foreach ( $features as $feature_value => $feature_label ) {
						$options .= ',' . $feature_label . '/' . $feature_value;
					}
					$select_fields_result->option_values = $options;
				}
	
				// optgroup
				$terms = geodir_string_values_to_options( stripslashes_deep( $select_fields_result->option_values ), true );
			} else {
				$terms = explode( ',', $select_fields_result->option_values );
			}
	
			$design_style = geodir_design_style();
	
			$main_class = $design_style && !empty($cf->main_search) ? 'col-auto flex-fill' . ( $aui_bs5 ? ' px-0' : '' ) : '';
			$wrap_attrs = $design_style ? geodir_search_conditional_field_attrs( $cf, '', 'select' ) : '';
	
			$html .= "<div class='gd-search-input-wrapper gd-search-field-cpt gd-search-" . $cf->htmlvar_name . " $main_class'" . $wrap_attrs . ">";
			$output = $design_style ? geodir_advance_search_options_output_aui( $terms, $cf, $post_type, stripslashes( __( $cf->frontend_title, 'geodirectory' ) )) : geodir_advance_search_options_output( $terms, $cf, $post_type, stripslashes( __( $cf->frontend_title, 'geodirectory' ) ));
			$html .= str_replace(array('<li>','</li>'),'',$output);
			$html .= "</div>";
	
			return $html;
		}

		/**
		 * 
		 * fuction for shortcode for post terms
		 */
		public static function sw_display_post_terms_shortcode() {
			global $wpdb, $gd_post;
			print_r($gd_post);
		
			$post_id = '';
		
			if (!$post_id) {
				return ''; // Return nothing if no post ID is found
			}
		
			// Fetch term IDs from the custom table
			$table_name = $wpdb->prefix . 'geodir_gd_place_detail';
			$query = $wpdb->get_row($wpdb->prepare("SELECT post_category, products_and_services, sustaini FROM $table_name WHERE post_id = %d", $post_id), ARRAY_A);
		
			// Initialize an empty array for term names
			$term_names = [];
		
			if ($query) {
				// Mapping fields to taxonomies
				$taxonomy_map = [
					'post_category'        => 'gd_placecategory',
					'products_and_services'=> 'product_placecategory',
					'sustaini'             => 'stags_placecategory',
				];
		
				foreach ($taxonomy_map as $field => $taxonomy) {
					if (!empty($query[$field])) {
						$term_ids = explode(',', $query[$field]); // Convert comma-separated IDs to an array
						foreach ($term_ids as $term_id) {
							$term = get_term((int)$term_id, $taxonomy);
							if ($term && !is_wp_error($term)) {
								$term_names[] = esc_html($term->name);
							}
						}
					}
				}
			}
		
			// Generate the output HTML
			if (!empty($term_names)) {
				$output = '<p class="sw-gd-tags">';
				foreach ($term_names as $term_name) {
					$output .= '<span>' . $term_name . '</span>';
				}
				$output .= '</p>';
				return $output;
			}
		
			return ''; // Return empty if no terms are found
		}

		/**
		 * 
		 * function for the homepage header
		 */
		public static function homepage_header_html() {
			ob_start();
			?>
				<style>
					.navbar {
						display: flex;
						align-items: center;
						justify-content: space-between;
						border-radius: 5px;
					}
					.logo {
						font-size: 14px;
						font-weight: bold;
						background: #e2e2e2;
						color: #000;
						padding: 10px 20px;
						border-radius: 5px;
					}
					.search-container {
						display: flex;
						flex: 1;
						margin: 0 10px;
/* 						border: 2px solid #556b2f;
						border-radius: 5px;*/
						overflow: hidden; 
					}
					.search-container input {
						flex: 1;
						padding: 8px;
						border: none;
						outline: none;
					}
					.search-btn {
						background: #556b2f;
						border: none;
						color: white;
						padding: 8px 12px;
						cursor: pointer;
					}
					.business-btn {
						border: 2px solid #556b2f !important;
						background: #ffffff !important;
						color: #000;
						padding: 8px 12px;
						margin-left: 10px;
						cursor: pointer;
						border-radius: 5px;
					}
					.menu-container {
						position: relative;
						margin-left: 20px;
					}
					.menu-btn {
						background: #556b2f;
						border: none;
						color: white;
						padding: 8px 12px;
						cursor: pointer;
						border-radius: 5px;
					}
					.dropdown {
						display: none;
						position: absolute;
						right: 0;
						top: 100%;
						background: white;
						border-radius: 5px;
						box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
						width: 150px;
						z-index: 9999999;
					}
					.dropdown a {
						display: block;
						padding: 10px;
						text-decoration: none;
						color: black;
					}
					.dropdown a:hover {
						background: #f0f0f0;
					}

				</style>
				<div class="navbar">
					<a href="<?php echo site_url(); ?>"><div class="logo">SW</div></a>
					<div class="search-container">
						<?php echo do_shortcode('[gd_search show="main" customize_filters="default" mb="3"]'); ?>
					</div>
					<a href="<?php echo site_url().'/add-listing'; ?>"><button class="business-btn">For Business</button></a>
					<div class="menu-container">
						<button class="menu-btn" onclick="toggleMenu()">☰</button>
						<div class="dropdown" id="dropdown-menu">
							
							<a href="/account-details/">Account</a>
							<a href="/user-dashboard">Listing Dashboard</a>
							<a href="#">Help</a>
							<a href="/wp-login.php?action=logout">Log out</a>
						</div>
					</div>
				</div>

				<script>
					function toggleMenu() {
						const dropdown = document.getElementById("dropdown-menu");
						dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
					}

					document.addEventListener("click", function(event) {
						const menuContainer = document.querySelector(".menu-container");
						if (!menuContainer.contains(event.target)) {
							document.getElementById("dropdown-menu").style.display = "none";
						}
					});
				</script>
			<?php
			return ob_get_clean();
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
		
		public static function add_custom_header_html() {
			if(!is_front_page()){
			?>
				<style>
					.navbar {
						display: flex;
						align-items: center;
						justify-content: space-between;
						background: #ffffff;
						padding: 10px 10% !important;
						border-radius: 5px;
					}
					.logo {
						font-size: 14px;
						font-weight: bold;
						background: #e2e2e2;
						color: #000;
						padding: 10px 20px;
						border-radius: 5px;
					}
					.search-container {
						display: flex;
						flex: 1;
						margin: 0 10px;
/* 						border: 2px solid #556b2f;
						border-radius: 5px;*/
						overflow: hidden; 
					}
					.search-container input {
						flex: 1;
						padding: 8px;
						border: none;
						outline: none;
					}
					.search-btn {
						background: #556b2f;
						border: none;
						color: white;
						padding: 8px 12px;
						cursor: pointer;
					}
					.business-btn {
						border: 2px solid #556b2f !important;
						background: #ffffff !important;
						color: #000;
						padding: 8px 12px;
						margin-left: 10px;
						cursor: pointer;
						border-radius: 5px;
					}
					.menu-container {
						position: relative;
						margin-left: 20px;
					}
					.menu-btn {
						background: #556b2f;
						border: none;
						color: white;
						padding: 8px 12px;
						cursor: pointer;
						border-radius: 5px;
					}
					.dropdown {
						display: none;
						position: absolute;
						right: 0;
						top: 100%;
						background: white;
						border-radius: 5px;
						box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
						width: 150px;
						z-index: 9999999;
					}
					.dropdown a {
						display: block;
						padding: 10px;
						text-decoration: none;
						color: black;
					}
					.dropdown a:hover {
						background: #f0f0f0;
					}

				</style>
				<div class="navbar">
						<a href="<?php echo site_url(); ?>"><div class="logo">SW</div></a>
						<div class="search-container">
							<?php echo do_shortcode('[gd_search show="main" customize_filters="default" mb="3"]'); ?>
						</div>
						<a href="<?php echo site_url().'/add-listing'; ?>"><button class="business-btn">For Business</button></a>
						<div class="menu-container">
							<button class="menu-btn" onclick="toggleMenu()">☰</button>
							<div class="dropdown" id="dropdown-menu">
								
								<a href="/account-details/">Account</a>
								<a href="/user-dashboard">Listing Dashboard</a>
								<a href="#">Help</a>
								<a href="/wp-login.php?action=logout">Log out</a>
							</div>
						</div>
					</div>

					<script>
						function toggleMenu() {
							const dropdown = document.getElementById("dropdown-menu");
							dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
						}

						document.addEventListener("click", function(event) {
							const menuContainer = document.querySelector(".menu-container");
							if (!menuContainer.contains(event.target)) {
								document.getElementById("dropdown-menu").style.display = "none";
							}
						});
					</script>
			<?php
			}
		}
		

		public static function add_custom_footer_html() {
// 			if(!is_front_page()){
			?>
				<style>
					.footer {
						background-color: #f5f5f5;
						padding: 30px 0;
						font-family: Arial, sans-serif;
					}

					.footer-container {
						display: flex;
						justify-content: space-around;
						align-items: flex-start;
						max-width: 1200px;
						padding: 30px 70px;
						margin: auto;
						/*flex-wrap: wrap;*/
					}

					.footer-section {
						min-width: 200px;
						margin: 10px;
					}

					.footer-section h3 {
						font-size: 18px;
						font-weight: bold;
						margin-bottom: 10px;
					}

					.footer-logo {
						background: #ffffff;
						padding: 33px 10px;
						display: inline-block;
						border-radius: 80px;
						font-weight: bold;
					}

					.footer-section ul {
						list-style: none;
						padding: 0;
					}

					.footer-section-second ul {
						display: grid;
						grid-template-columns: repeat(2, 1fr);
						gap: 10px 40px;
					}

					.footer-section ul li {
						margin-bottom: 5px;
					}

					.footer-section ul li a {
						text-decoration: none;
						color: #527430;
						font-size: 14px;
					}

					.footer-section ul li a:hover {
						text-decoration: underline;
					}

					.footer-bottom {
						background: linear-gradient(to right, #5a7d36, #3e6021);
						text-align: center;
						color: white;
						padding: 20px;
						font-size: 14px;
						border-radius: 0 0 10px 10px;
					}

					</style>
					<footer class="footer">
						<div class="footer-container">
							<div class="footer-section">
								<h3>Steve’s Weave</h3>
								<div class="footer-logo">SW Logo</div>
							</div>
							<div class="footer-section footer-section-second">
								<h3>Info</h3>
								<ul>
									<li><a href="/faq">FAQ</a></li>
									<li><a href="/terms-and-conditions/">Terms of Use</a></li>
									<li><a href="/online-safety/">Online Safety</a></li>
									<li><a href="/contact-us">Contact Us</a></li>
									<li><a href="/about-us">Steve</a></li>
									<li><a href="/donate">Donate</a></li>



								</ul>
							</div>
							<div class="footer-section">
								<h3>Latest Blogs</h3>
								<ul>
									<?php
									$latest_posts = new WP_Query(array(
										'posts_per_page' => 3, // Get 3 latest posts
										'post_status'    => 'publish', // Only show published posts
									));

									if ($latest_posts->have_posts()) :
										while ($latest_posts->have_posts()) : $latest_posts->the_post(); ?>
											<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
										<?php endwhile;
										wp_reset_postdata();
									else :
										echo '<li>No recent blog posts found.</li>';
									endif;
									?>
								</ul>
							</div>
						</div>
						<div class="footer-bottom">
							Copyright © 2024, Steve's Weave. All rights reserved.
						</div>
					</footer>
			<?php
			
		}
		

    }

    /*
     * Initialize class init method for load its functionality.
     */
    GeoDirCustomFrontEnd::init();
}
?>