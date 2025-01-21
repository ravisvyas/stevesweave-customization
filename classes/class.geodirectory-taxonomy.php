<?php
/**
 * @class GeoDirectoryTaxonomy
 */
if (!class_exists('GeoDirectoryTaxonomy', false)) {


    class GeoDirectoryTaxonomy {

        /**
         * Initialize required action and filters
         * @return void
         */
        public static function init() {

            add_action( 'init', array(__CLASS__,'create_subjects_hierarchical_taxonomy'));

            // Register advertising settings.
            add_filter( 'geodir_custom_field_input_multiselect_products_and_services', array( __CLASS__, 'sw_geodir_custom_field_input_multiselect_search_post_id'), 1, 2);
            add_filter( 'geodir_custom_field_input_multiselect_sustaini', array( __CLASS__, 'sw_geodir_custom_field_input_multiselect_search_post_id'), 1, 2);
            add_filter( 'geodir_custom_field_input_taxonomy_post_category', array( __CLASS__, 'sw_geodir_custom_field_input_taxonomy_post_category'), 1, 2);

        }

        /**
         * Get the html input for the custom field: taxonomy
         *
         * @param string $html The html to be filtered.
         * @param array $cf The custom field array details.
         * @since 1.6.6
         *
         * @return string The html to output for the custom field.
         */
        public static function sw_geodir_custom_field_input_taxonomy_post_category($html,$cf){
            global $aui_bs5;

            $html_var = $cf['htmlvar_name'];

            // If no html then we run the standard output.
            if(empty($html)) {
                global $geodir_label_type;
                ob_start(); // Start  buffering;
                $value = geodir_get_cf_value($cf);

                if(is_admin() && $cf['name']=='post_tags'){return;}

                $horizontal = empty( $geodir_label_type ) || $geodir_label_type == 'horizontal' ? true : false;
                //print_r($cf);echo '###';
                $name = $cf['name'];
                $frontend_title = $cf['frontend_title'];
                $frontend_desc = $cf['desc'];
                $is_required = $cf['is_required'];
                $is_admin = $cf['for_admin_use'];
                $required_msg = $cf['required_msg'];
                $taxonomy = $cf['post_type']."category";
                $placeholder = ! empty( $cf['placeholder_value'] ) ? __( $cf['placeholder_value'], 'geodirectory' ) : __( 'Select Category', 'geodirectory' );

                // admin only
                $admin_only = geodir_cfi_admin_only($cf);
                $conditional_attrs = geodir_conditional_field_attrs( $cf );

                if ($value == $cf['default']) {
                    $value = '';
                } ?>

                <div id="<?php echo $taxonomy;?>_row"
                    class="<?php echo esc_attr( $cf['css_class'] ); ?> <?php if ($is_required) echo 'required_field';?> <?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> <?php echo $horizontal ? ' row' : '';?>"  data-argument="<?php echo esc_attr($taxonomy);?>"<?php echo $conditional_attrs; ?>>
                    <label for="cat_limit" class=" <?php echo $horizontal ? ' col-sm-2 col-form-label' : ''; echo $geodir_label_type == 'hidden' || $geodir_label_type=='floating' ? ' sr-only visually-hidden ' : '';?>">
                        <?php $frontend_title = __($frontend_title, 'geodirectory');
                        echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; echo $admin_only;?>
                        <?php if ($is_required) echo '<span class="text-danger">*</span>';?>
                    </label>

                    <div id="<?php echo $taxonomy;?>_wrap" class="geodir_taxonomy_field <?php echo $horizontal ? ' col-sm-10' : '';?>" >
                        <?php

                        global $wpdb, $gd_post, $cat_display, $post_cat, $package_id, $exclude_cats;

                        $exclude_cats = array();

                        $package = geodir_get_post_package( $gd_post, $cf['post_type'] );
                        //if ($is_admin == '1') {
                        if ( ! empty( $package ) && isset( $package->exclude_category ) ) {
                            if ( is_array( $package->exclude_category ) ) {
                                $exclude_cats = $package->exclude_category;
                            } else {
                                $exclude_cats = $package->exclude_category != '' ? explode( ',', $package->exclude_category ) : array();
                            }
                        }
                        //}

                        $extra_fields = maybe_unserialize( $cf['extra_fields'] );
                        if ( is_array( $extra_fields ) && ! empty( $extra_fields['cat_display_type'] ) ) {
                            $cat_display = $extra_fields['cat_display_type'];
                        } else {
                            $cat_display = 'select';
                        }

                        $post_cat = geodir_get_cf_value($cf);

                        $category_limit = ! empty( $package ) && isset( $package->category_limit ) ? absint( $package->category_limit ) : 0;
                        $category_limit = (int) apply_filters( 'geodir_cfi_post_categories_limit', $category_limit, $gd_post, $package );

                        if ($cat_display != '') {
                            $required_limit_msg = '';
                            if ($category_limit > 0 && $cat_display != 'select' && $cat_display != 'radio') {
                                $required_limit_msg = wp_sprintf( __('Only select %d categories for this package.', 'geodirectory'), $category_limit );
                            } else {
                                $required_limit_msg = $required_msg;
                            }

                            if ($cat_display == 'select' || $cat_display == 'multiselect') {
                                $data_attrs = '';
                                if ( $category_limit == 1 ) {
                                    $cat_display = 'select'; // Force single select.
                                } elseif ( $category_limit > 0 ) {
                                    $data_attrs .= ' data-maximum-selection-length="' . $category_limit . '"';
                                }
                                $multiple = '';
                                $default_field = '';
                                if ($cat_display == 'multiselect') {
                                    $multiple = 'multiple="multiple"';
                                    $default_field = 'data-aui-cmultiselect="default_category"';
                                } else {
                                    $default_field = 'data-cselect="default_category"';
                                }

                                // Required category message.
                                if ( ! empty( $required_msg ) ) {
                                    $required_msg = __( $required_msg, 'geodirectory' );
                                } else {
                                    $required_msg = __( 'Select at least one category from the list!', 'geodirectory' );
                                }
                                $data_attrs .= ' required oninvalid="setCustomValidity(\'' . esc_attr( $required_msg ) . '\')" onchange="try{setCustomValidity(\'\')}catch(e){}"';

                                echo '<select  id="' .$taxonomy . '" ' . $multiple . ' type="' . $taxonomy . '" name="tax_input['.$taxonomy.'][]" alt="' . $taxonomy . '" field_type="' . $cat_display . '" class="geodir-category-select ' . ( $aui_bs5 ? 'form-select' : 'geodir-select' ) . ' aui-select2" data-placeholder="' . esc_attr( $placeholder ) . '" ' . $default_field . ' aria-label="' . esc_attr( $placeholder ) . '" style="width:100%"' . $data_attrs . '>';

                                if ($cat_display == 'select')
                                    echo '<option value="">' . __('Select Category', 'geodirectory') . '</option>';
                            }

                            // echo GeoDir_Admin_Taxonomies::taxonomy_walker($taxonomy);
                            echo self::sw_taxonomy_walker($taxonomy);

                            if ($cat_display == 'select' || $cat_display == 'multiselect')
                                echo '</select>';

                        }

                        echo class_exists("AUI_Component_Helper") ? AUI_Component_Helper::help_text(__($frontend_desc, 'geodirectory')) : '';
                        ?>
                    </div>
                </div>
                <?php
                // cat limit
                echo '<input type="hidden" cat_limit="' . $category_limit . '" id="cat_limit" value="' . esc_attr($required_limit_msg) . '" name="cat_limit[' . $taxonomy . ']"  />';

                $html = ob_get_clean();

                // Default category select
                if ( $cat_display == 'multiselect' || $cat_display == 'checkbox' ) {
                    // required
                    $required = ! empty( $cf['is_required'] ) ? ' <span class="text-danger">*</span>' : '';

                    $default_category = (int) geodir_get_cf_default_category_value();

                    $html .= aui()->select( array(
                        'id'              => "default_category",
                        'name'            => "default_category",
                        'placeholder'     => esc_attr__( "Select Default Category", 'geodirectory' ),
                        'value'           => $default_category,
                        'required'        => false,
                        'label_type'      => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
                        'label'           => __( "Default Category", 'geodirectory' ) . $required,
                        'help_text'       => esc_attr__( "The default category can affect the listing URL and map marker.", 'geodirectory' ),
                        'multiple'        => false,
                        'options'         => $default_category ? array( $default_category => '' ) : array(),
                        'element_require' => '[%' . $taxonomy . '%]!=null',
                        'wrap_attributes' => geodir_conditional_field_attrs( $cf, 'default_category', 'select' )
                    ) );
                } else {
                    // leaving this out should set the default as the main cat anyway
                    $html .= '<input type="hidden" id="default_category" name="default_category" value="' . esc_attr( geodir_get_cf_default_category_value() ) . '">';
                }
            }

            return $html;
        }

        public static function sw_taxonomy_walker( $cat_taxonomy, $cat_parent = 0, $hide_empty = false, $padding = 1, $parent_name = '' ) {
            global $aui_bs5, $cat_display, $post_cat, $exclude_cats;
        
            $search_terms = trim( $post_cat, "," );
            $search_terms = explode( ",", $search_terms );
        
            $cat_terms = get_terms( array(
                'taxonomy'   => $cat_taxonomy,
                'parent'     => $cat_parent,
                'hide_empty' => $hide_empty,
                'exclude'    => $exclude_cats,
            ));
        
            $display = '';
            $onchange = '';
            $term_check = '';
            $main_list_class = '';
            $out = '';
        
            // If there are terms, start displaying
            if ( count( $cat_terms ) > 0 ) {
                $p = $padding * 20; // Padding for indentation
                $padding++;
        
                if ( ( ! geodir_is_page( 'listing' ) ) || ( is_search() && $_REQUEST['search_taxonomy'] == '' ) ) {
                    if ( $cat_parent == 0 ) {
                        $list_class = 'main_list gd-parent-cats-list gd-cats-display-' . $cat_display;
                        $main_list_class = 'main_list_selecter';
                    } else {
                        $list_class = 'sub_list gd-sub-cats-list';
                        if ( geodir_design_style() ) {
                            $list_class .= ' pl-3 ps-3'; // Left padding for sub-categories.
                        }
                    }
                }
        
                if ( $cat_display == 'checkbox' || $cat_display == 'radio' ) {
                    $p = 0;
                    $out = '<div class="' . $list_class . ' gd-cat-row-' . $cat_parent . '" style="margin-left:' . $p . 'px;' . $display . ';">';
                    if ( geodir_design_style() ) {
                        $main_list_class .= ( $aui_bs5 ? ' me-1' : ' mr-1' );
                    }
                }
        
                if ( $main_list_class ) {
                    $main_list_class = 'class="' . $main_list_class . '"';
                }
        
                foreach ( $cat_terms as $cat_term ) {
                    $checked = '';
                    $sub_out = '';
                    $no_child = false;
        
                    if ( absint( $cat_parent ) == 0 && count( $cat_terms ) == 1 ) {
                        // Call recursion to print sub cats
                        $sub_out = self::sw_taxonomy_walker( $cat_taxonomy, $cat_term->term_id, $hide_empty, $padding, $cat_term->name );
        
                        if ( trim( $sub_out ) == '' ) {
                            $no_child = true; // Set category selected when only one category.
                        }
                    }
        
                    if ( in_array( $cat_term->term_id, $search_terms ) || $no_child ) {
                        if ( $cat_display == 'select' || $cat_display == 'multiselect' ) {
                            $checked = 'selected="selected"';
                        } else {
                            $checked = 'checked="checked"';
                        }
                    }
        
                    // Build the child name format: " - parent category name : subcategory name"
                    $child_dash = $parent_name ? "  - " : '';
                    // $child_dash = $parent_name ? " - $parent_name : " : '';
                    $child_name = $child_dash . geodir_utf8_ucfirst( $cat_term->name );
        
                    if ( $cat_display == 'radio' ) {
                        $out .= '<span style="display:block"><input type="radio" field_type="radio" name="tax_input[' . $cat_term->taxonomy .'][]" ' . $main_list_class . ' alt="' . $cat_term->taxonomy . '" title="' . $child_name . '" value="' . $cat_term->term_id . '" ' . $checked . $onchange . ' id="gd-cat-' . $cat_term->term_id . '" data-cradio="default_category">' . $term_check . $child_name . '</span>';
                    } elseif ( $cat_display == 'select' || $cat_display == 'multiselect' ) {
                        $out .= '<option ' . $main_list_class . ' style="margin-left:' . $p . 'px;" alt="' . $cat_term->taxonomy . '" title="' . $child_name . '" value="' . $cat_term->term_id . '" ' . $checked . $onchange . '>' . $term_check . $child_name . '</option>';
                    } else {
                        $class = $checked ? 'class="gd-term-checked"' : '';
                        $out .= '<span style="display:block" ' . $class . '><input style="display:inline-block" type="checkbox" field_type="checkbox" name="tax_input[' . $cat_term->taxonomy .'][]" ' . $main_list_class . ' alt="' . $cat_term->taxonomy . '" title="' . $child_name . '" value="' . $cat_term->term_id . '" ' . $checked . $onchange . ' id="gd-cat-' . $cat_term->term_id . '" data-ccheckbox="default_category">' . $term_check . $child_name . '<span class="gd-make-default-term" style="display:none" title="' . esc_attr( wp_sprintf( __( 'Make %s default category', 'geodirectory' ), $child_name ) ) . '">' . __( 'Make default', 'geodirectory' ) . '</span><span class="gd-is-default-term" style="display:none">' . __( 'Default', 'geodirectory' ) . '</span></span>';
                    }
        
                    if ( ! ( absint( $cat_parent ) == 0 && count( $cat_terms ) == 1 ) ) {
                        // Call recursion to print sub cats
                        $sub_out = self::sw_taxonomy_walker( $cat_taxonomy, $cat_term->term_id, $hide_empty, $padding, $cat_term->name );
                    }
        
                    $out .= $sub_out;
                }
        
                if ( $cat_display == 'checkbox' || $cat_display == 'radio' ) {
                    $out .= '</div>';
                }
        
                return $out;
            }
            return '';
        }

        public static function sw_geodir_custom_field_input_multiselect_search_post_id($html, $cf){
            // If no html then we run the standard output.
            // echo 'hello';
            if ( empty( $html ) ) {
                global $geodir_label_type;
                $extra_attributes = array();
                $_value = geodir_get_cf_value($cf);

                $extra_fields = !empty($cf['extra_fields']) ? maybe_unserialize($cf['extra_fields']) : NULL;
                $multi_display = !empty($extra_fields['multi_display_type']) ? $extra_fields['multi_display_type'] : 'select';
                $validation_text = '';
                $id = $cf['htmlvar_name'];

                // required
                $required = ! empty( $cf['is_required'] ) ? ' <span class="text-danger">*</span>' : '';

                // Required message
                if ( $required && ! empty( $cf['required_msg'] ) ) {
                    $validation_text = __( $cf['required_msg'], 'geodirectory' );
                }

                // Validation message
                if ( ! empty( $cf['validation_msg'] ) ) {
                    $validation_text = __( $cf['validation_msg'], 'geodirectory' );
                }

                        // Validation
                if ( ! empty( $cf['validation_pattern'] ) ) {
                    $extra_attributes['pattern'] = $cf['validation_pattern'];
                }

                $title = $validation_text;

                // help text
                $help_text = __( $cf['desc'], 'geodirectory' );

                // placeholder
                $placeholder = esc_attr__( $cf['placeholder_value'], 'geodirectory' );
                if ( empty( $placeholder ) ) {
                    $placeholder = wp_sprintf( __( 'Select %s&hellip;', 'geodirectory' ), __($cf['frontend_title'], 'geodirectory'));
                }

                $value = ( ! is_array( $_value ) && $_value !== '' ) ? trim( $_value ) : $_value;
                if ( ! is_array( $value ) ) {
                    $value = explode( ',', $value );
                }

                if ( ! empty( $value ) ) {
                    $value = stripslashes_deep( $value );
                    $value = array_map( 'trim', $value );
                }
                $value = array_filter( $value );

                //extra
                $extra_attributes['data-placeholder'] = esc_attr( $placeholder );
                $extra_attributes['option-ajaxchosen'] = 'false';

                // admin only
                $admin_only = geodir_cfi_admin_only( $cf );

                // if ( $multi_display == 'select' ) 
                {
                    // Set validation message
                    if ( ! empty( $validation_text ) ) {
                        $extra_attributes['oninvalid'] = 'try{this.setCustomValidity(\'' . esc_attr( addslashes( $validation_text ) ) . '\')}catch(e){}';
                        $extra_attributes['onchange'] = 'try{this.setCustomValidity(\'\')}catch(e){}';
                    }

                    $conditional_attrs = geodir_conditional_field_attrs( $cf );
                    
                    if($id == 'products_and_services')  {
                        $taxonomy = 'product_placecategory';
                    }else{
                        $taxonomy = 'stags_placecategory';
                    }
                    $options = self::get_dropdown_geodir_categories($taxonomy);
                    $o = 0;
                    $option_data = array();
                    foreach ($options as $key => $option) {
                        # code...
                        $option_data[$o]['label'] = $option;
                        $option_data[$o]['value'] = $key;
                        $option_data[$o]['optgroup'] = '';
                        $o++;
                    }

                    // echo '<pre>'; print_r($option_data); echo '</pre>';

                    $html .= aui()->select( array(
                        'id'                 => $cf['name'],
                        'name'               => $cf['name'],
                        'title'              => $title,
                        'placeholder'        => $placeholder,
                        'value'              => $value,
                        'required'           => ! empty( $cf['is_required'] ) ? true : false,
                        'label_show'         => true,
                        'label_type'         => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
                        'label'              => __( $cf['frontend_title'], 'geodirectory' ) . $admin_only . $required,
                        'validation_text'    => $validation_text,
                        'validation_pattern' => ! empty( $cf['validation_pattern'] ) ? $cf['validation_pattern'] : '',
                        'help_text'          => $help_text,
                        'extra_attributes'   => $extra_attributes,
                        'options'            => $option_data,
                        'select2'            => true,
                        'multiple'           => true,
                        'data-allow-clear'   => false,
                        'wrap_attributes'    => $conditional_attrs
                    ) );
                }
            }
            return $html;
        }

        public static function create_subjects_hierarchical_taxonomy() {
            $labels = array(
                'name' => _x( 'Product & Services', 'taxonomy general name' ),
                'singular_name' => _x( 'Product & Services', 'taxonomy singular name' ),
                'search_items' =>  __( 'Search Product & Services' ),
                'all_items' => __( 'All Product & Services' ),
                'parent_item' => __( 'Parent Product & Services' ),
                'parent_item_colon' => __( 'Parent Product & Services:' ),
                'edit_item' => __( 'Edit Product & Services' ),
                'update_item' => __( 'Update Product & Services' ),
                'add_new_item' => __( 'Add New Product & Services' ),
                'new_item_name' => __( 'New Product & Services Name' ),
                'menu_name' => __( 'Product & Services' ),
            );


            register_taxonomy('product_placecategory','gd_place', array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_in_rest' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array( 'slug' => 'product_placecategory' ),
            ));


            //for region type
            $region = array(
                'name' => _x( 'Sustainability Tags', 'taxonomy general name' ),
                'singular_name' => _x( 'Sustainability Tags', 'taxonomy singular name' ),
                'search_items' =>  __( 'Search Sustainability Tags' ),
                'all_items' => __( 'All Sustainability Tags' ),
                'parent_item' => __( 'Parent Sustainability Tags' ),
                'parent_item_colon' => __( 'Parent Sustainability Tags:' ),
                'edit_item' => __( 'Edit Sustainability Tags' ),
                'update_item' => __( 'Update Sustainability Tags' ),
                'add_new_item' => __( 'Add New Sustainability Tags' ),
                'new_item_name' => __( 'New Sustainability Tags Name' ),
                'menu_name' => __( 'Sustainability Tags' ),
            );


            register_taxonomy('stags_placecategory','gd_place', array(
                'hierarchical' => true,
                'labels' => $region,
                'show_ui' => true,
                'show_in_rest' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array( 'slug' => 'stags_placecategory' ),
            ));

                 
        }

        public static function get_dropdown_geodir_products_and_services() {
            $all_categories = get_terms(array(
                'taxonomy'   => 'gd_type_placecategory',
                'hide_empty' => false,
            ));

            $categories = array();

            foreach ($all_categories as $category) {
                $categories[$category->term_id] = $category->name;
            }

            return $categories;
        }

        public static function get_dropdown_geodir_categories($taxonomy) {
            // Get all categories
            $all_categories = get_terms(array(
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ));
        
            // Initialize an empty array to hold the hierarchical structure
            $categories = array();
        
            // Build the hierarchy
            $categories = self::build_category_hierarchy_with_parent($all_categories);
        
            return $categories;
        }
        
        // Helper function to build hierarchy
        private static function build_category_hierarchy_with_parent($categories, $parent_id = 0, $level = 0, $parent_name = '') {
            $hierarchy = array();
        
            foreach ($categories as $category) {
                if ($category->parent == $parent_id) {
                    // Prefix for child categories
                    $prefix = str_repeat(' - ', $level);
        
                    // Determine the display name
                    $display_name = $prefix . $category->name;
                    // $display_name = $prefix . ($parent_name ? "$parent_name : " : '') . $category->name;
        
                    // Add the category to the hierarchy
                    $hierarchy[$category->term_id] = $display_name;
        
                    // Recursively add child categories
                    $hierarchy += self::build_category_hierarchy_with_parent($categories, $category->term_id, $level + 1, $category->name);
                }
            }
        
            return $hierarchy;
        }

    }
    /*
     * Initialize class init method for load its functionality.
     */
    GeoDirectoryTaxonomy::init();
}