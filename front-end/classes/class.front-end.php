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
			?>
				<style>
					.navbar {
						display: flex;
						align-items: center;
						justify-content: space-between;
						background: #ffffff;
						padding: 10px 100px !important;
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
						border: 2px solid #556b2f;
						border-radius: 5px;
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
						<div class="logo">SW</div>
						<div class="search-container">
							<input type="text" placeholder="Sustainable business name or category">
						</div>
						<div class="search-container">
							<input type="text" placeholder="Location">
							<button class="search-btn">🔍</button>
						</div>
						<button class="business-btn">For Business</button>
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
		

		public static function add_custom_footer_html() {
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
					}

					.footer-section ul li {
						margin-bottom: 5px;
					}

					.footer-section ul li a {
						text-decoration: none;
						color: #333;
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
									<li><a href="/faq">Faq</a></li>
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
									<li><a href="#">10 Simple Swaps for a Greener Home: Easy Eco-Friendly Alternatives</a></li>
									<li><a href="#">Local Heroes: Spotlight on Sustainable Businesses Making a Difference</a></li>
									<li><a href="#">The Ultimate Guide to Sustainable Shopping: Tips for Conscious Consumers</a></li>
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