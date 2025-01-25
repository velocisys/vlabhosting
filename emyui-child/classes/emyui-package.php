<?php
/**
 * 24-12-2024
 * 
 * Register Package product type
 **/
add_action( 'init', 'emyui_register_package_product_type' );
function emyui_register_package_product_type() {
    class WC_Product_Package extends WC_Product {
        public function __construct( $product ) {
            $this->product_type = 'package';
            parent::__construct( $product );
        }
    }
}

/**
 * 25-12-2024
 * 
 * Load New Product Type Class
 **/
add_filter( 'woocommerce_product_class', 'emyui_product_class', 10, 2 ); 
function emyui_product_class( $classname, $product_type ) {
    if( $product_type == 'package' ){
        $classname = 'WC_Product_Package';
    }
    return $classname;
}

/**
 * 18-12-2024
 * 
 * Class EMYUI_Package_Product
 * Registers a custom WooCommerce product type: Package
 */
class EMYUI_Package_Product {
    private static $initialized = false;

    /**
     * Initialize hooks and filters.
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }

        add_filter('product_type_selector', array(__CLASS__, 'emyui_add_package_product_type'));
        add_action('woocommerce_process_product_meta', array(__CLASS__, 'emyui_save_package_fields'));
        add_action('woocommerce_single_product_summary', array(__CLASS__, 'emyui_display_package_fields'), 20);
        add_filter('woocommerce_product_data_tabs', array(__CLASS__, 'emyui_add_package_options_tab'));
        add_action('woocommerce_product_data_panels', array(__CLASS__, 'emyui_add_package_fields'));
        add_shortcode( 'package_pricing', array(__CLASS__, 'emyui_package_pricing_shortcode'));
        add_shortcode( 'package_domain', array(__CLASS__, 'emyui_package_submit_shortcode'));
        add_action( 'wp', array(__CLASS__, 'emyui_process_submission' ));
        add_action( 'wp_ajax_emyui_domain_search', array(__CLASS__, 'emyui_domain_search' ));
        add_action( 'wp_ajax_nopriv_emyui_domain_search', array(__CLASS__, 'emyui_domain_search' ));
        add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'emyui_order_line_item' ), 20, 4 );
        add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'emyui_get_item_data' ), 10, 2 );
        add_filter('woocommerce_product_get_name', array( __CLASS__,'emyui_modify_product_titles'), 10, 2);
        add_filter( 'woocommerce_is_sold_individually', array( __CLASS__,'emyui_remove_all_quantity_fields'), 10, 2 );
        add_action( 'woocommerce_product_options_general_product_data', array(__CLASS__,'emyui_add_custom_plans_field'));
        add_filter( 'woocommerce_return_to_shop_text', array(__CLASS__, 'emyui_woocommerce_return_to_shop_text'));
        add_action( 'wp_ajax_emyui_package', array(__CLASS__, 'emyui_package' ));
        add_action( 'wp_ajax_nopriv_emyui_package', array(__CLASS__, 'emyui_package' ));
        add_filter('woocommerce_account_menu_items', array(__CLASS__,'emyui_add_my_account_tab'), 10, 1);
        add_action('init', array(__CLASS__,'emyui_add_my_account_endpoint'));
        add_action('woocommerce_account_vlab_endpoint', array(__CLASS__,'emyui_my_account_tab_content'));
        add_action('admin_init', array(__CLASS__, 'emyui_admin_init'));
        add_action('woocommerce_cart_calculate_fees', array(__CLASS__,'emyui_add_extra_charges'), 10, 1);
        self::$initialized = true;
    }

    /**
     * Add a custom tab for Package product type.
     *
     * @param array $tabs
     * @return array
     */
    public static function emyui_add_package_options_tab($tabs) {
        $tabs['inventory']['class'][]       = 'hide_if_package';
        $tabs['shipping']['class'][]        = 'hide_if_package';
        $tabs['linked_product']['class'][]  = 'hide_if_package';
        $tabs['attribute']['class'][]       = 'hide_if_package';
        $tabs['package_options'] = [
            'label'    => __('Package Options', 'emyui'),
            'target'   => 'package_options_data',
            'class'    => ['show_if_package'],
            'priority' => 50,
        ];
        return $tabs;
    }

    /**
     * 18-12-2024
     * Add the Package product type to the dropdown in WooCommerce.
     *
     * @param array $types
     * @return array
     */
    public static function emyui_add_package_product_type($types) {
        $types['package'] = __('Package', 'emyui');
        return $types;
    }

    /**
     * 18-12-2024
     * 
     * Add custom fields for Package product type in the admin.
     */

    public static function emyui_add_package_fields() {
        wc_enqueue_js( "     
          $(document.body).on('woocommerce-product-type-change',function(event,type){
            if(type=='package') {
                $('.general_tab').show();
                $('.pricing').show();         
            }
            });");
        global $product_object;
        if( $product_object && 'package' === $product_object->get_type() ) {
              wc_enqueue_js( "$('.general_tab').show(); $('.pricing').show();");
        }
        echo '<div id="package_options_data" class="panel woocommerce_options_panel">';
            echo '<div class="options_group">';
                woocommerce_wp_select( array(
                    'id'            => '_package_hosting_plan',
                    'label'         => __( 'Hosting Plan', 'emyui' ),
                    'description'   => __( 'Choose an option from the dropdown.', 'emyui' ),
                    'desc_tip'      => true,
                    'options'       => array(
                        ''               => __( 'Choose once', 'emyui' ),
                        'shared_hosting' => __( 'Shared Hosting', 'emyui' ),
                        'vps'            => __( 'VPS', 'emyui' ),
                        'cloud'          => __( 'Cloud', 'emyui' ),
                        'wp'             => __( 'WordPress', 'emyui' ),
                        'gps'            => __( 'Gps', 'emyui' ),
                    )
                ) );
                woocommerce_wp_text_input([
                    'id'          => '_package_quota',
                    'label'       => __('Package Quota', 'emyui'),
                    'description' => __('Enter the disk space included in this package.', 'emyui'),
                    'type'        => 'text',
                    'desc_tip'    => true,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_package_maxftp',
                    'label'       => __('Max FTP Accounts', 'emyui'),
                    'description' => __('Enter the number of FTP accounts included in this package.', 'emyui'),
                    'type'        => 'text',
                    'desc_tip'    => true,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_package_maxpassengerapps',
                    'label'       => __('Max Passenger Apps', 'emyui'),
                    'description' => __('Enter the maximum number of Passenger apps allowed.', 'emyui'),
                    'type'        => 'number',
                    'desc_tip'    => true,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_package_max_email_acct_quota',
                    'label'       => __('Max Email Account Quota', 'emyui'),
                    'description' => __('Enter the email account quota for this package.', 'emyui'),
                    'type'        => 'text',
                    'desc_tip'    => true,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_package_max_lst',
                    'label'       => __('Max Mailing Lists', 'emyui'),
                    'description' => __('Enter the maximum number of mailing lists allowed.', 'emyui'),
                    'type'        => 'number',
                    'desc_tip'    => true,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_package_bwlimit',
                    'label'       => __('Bandwidth Limit', 'emyui'),
                    'description' => __('Enter the bandwidth limit for this package.', 'emyui'),
                    'type'        => 'text',
                    'desc_tip'    => true,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_package_maxaddon',
                    'label'       => __('Max Addon Domains', 'emyui'),
                    'description' => __('Enter the maximum number of addon domains allowed.', 'emyui'),
                    'type'        => 'number',
                    'desc_tip'    => true,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_package_maxsql',
                    'label'       => __('Max SQL Databases', 'emyui'),
                    'description' => __('Enter the maximum number of SQL databases allowed.', 'emyui'),
                    'type'        => 'number',
                    'desc_tip'    => true,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_package_maxpop',
                    'label'       => __('Max POP Accounts', 'emyui'),
                    'description' => __('Enter the maximum number of POP accounts allowed.', 'emyui'),
                    'type'        => 'number',
                    'desc_tip'    => true,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_package_maxpark',
                    'label'       => __('Max Parked Domains', 'emyui'),
                    'description' => __('Enter the maximum number of parked domains allowed.', 'emyui'),
                    'type'        => 'number',
                    'desc_tip'    => true,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_package_maxsub',
                    'label'       => __('Max Subdomains', 'emyui'),
                    'description' => __('Enter the maximum number of subdomains allowed.', 'emyui'),
                    'type'        => 'number',
                    'desc_tip'    => true,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_package_max_team_users',
                    'label'       => __('Max Team Users', 'emyui'),
                    'description' => __('Enter the maximum number of team users allowed.', 'emyui'),
                    'type'        => 'number',
                    'desc_tip'    => true,
                ]);
            echo '</div>';
        echo '</div>';
    }

    /**
     * 18-12-2024
     * Save custom fields for Package product type.
     *
     * @param int $post_id
     */
    public static function emyui_save_package_fields($post_id) {
        $fields = [
            '_package_quota',
            '_package_maxftp',
            '_package_maxpassengerapps',
            '_package_max_email_acct_quota',
            '_package_max_lst',
            '_package_bwlimit',
            '_package_maxaddon',
            '_package_maxsql',
            '_package_maxpop',
            '_package_maxpark',
            '_package_maxsub',
            '_package_max_team_users',
            '_package_hosting_plan',
            '_featured_package_text',
            '_package_offer_text'
        ];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
        if(isset($_POST['_hosting_plan']) && is_array($_POST['_hosting_plan'])){
            $hosting_plans = array_filter($_POST['_hosting_plan'], function($plan){
                return !empty($plan['plan_name']) || !empty($plan['plan_price']) || !empty($plan['plan_offer']) || !empty($plan['plan_default']);
            });
            if(!empty($hosting_plans)){
                $sanitized_plans = array_map(function($plan) {
                    return [
                        'plan_name'     => sanitize_text_field($plan['plan_name']),
                        'plan_price'    => sanitize_text_field($plan['plan_price']),
                        'plan_offer'    => sanitize_textarea_field($plan['plan_offer']),
                        'plan_default'  => sanitize_textarea_field($plan['plan_default']),
                    ];
                }, $hosting_plans);
                update_post_meta($post_id, '_hosting_plan_meta', json_encode($sanitized_plans));
            }else{
                delete_post_meta($post_id, '_hosting_plan_meta');
            }
        }else{
            delete_post_meta($post_id, '_hosting_plan_meta');
        }
    }

    /**
     * 18-12-2024
     * 
     * Display custom fields on the single product page for Package type.
     */
    public static function emyui_display_package_fields() {
        global $product;
        if ($product->get_type() === 'package') {
            $packageQuota = self::get_package_meta($product->get_id(), '_package_quota');
            $pacakgeFtp  = self::get_package_meta($product->get_id(), '_package_maxftp');
            if ($packageQuota || $pacakgeFtp) {
                echo '<div class="woocommerce-package-details">';
                echo '<p><strong>' . __('Package Qupta:', 'emyui') . '</strong> ' . esc_html($packageQupta) . ' MB</p>';
                echo '<p><strong>' . __('Pacakge Ftp:', 'emyui') . '</strong> ' . esc_html($pacakgeFtp) . ' MB</p>';
                echo '</div>';
            }
        }
    }

    /**
     * 18-12-2024
     * Helper function to get meta value.
     *
     * @param int $product_id
     * @param string $key
     * @return string
     */
    public static function get_package_meta($product_id, $key) {
        return get_post_meta($product_id, $key, true);
    }

    /**
     * 31-12-2024
     * 
     * Create a package shortcode
     **/
    public static function emyui_package_pricing_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'hosting_plan' => '',
            ),
            $atts
        );
        if(empty($atts['hosting_plan'])){
            return sprintf('<p class="emyui-no-package-found">%s</p>', __('No packages found. Please select a hosting plan.', 'emyui'));
        }
        $meta_query = array();
        if(!empty($atts['hosting_plan'])){
            $meta_query[] = array(
                'key'     => '_package_hosting_plan',
                'value'   => sanitize_text_field($atts['hosting_plan']),
                'compare' => '=',
            );
        }
        $args = array(
            'post_type'     => 'product',
            'post_status'   => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'tax_query' => array(
                array(
                    'taxonomy'  => 'product_type',
                    'field'     => 'slug',
                    'terms'     => 'package'
                )
            ),
            'meta_query' => $meta_query,
        );
        $products   = new WP_Query($args);
        $output     = '';
        if($products->have_posts()){
            ob_start();
            require_once(EMUI_VIEWS.'/choose-package.php');
            $output = ob_get_clean();
        }else{
            $output = '<p>No packages found.</p>';
        }
        wp_reset_postdata();
        return $output;
    }

    /**
     * 01-05-2024
     * 
     * Form submission
     **/
    public static function emyui_process_submission(){
        if(isset($_POST['emyui_package_nonce']) && wp_verify_nonce( $_POST['emyui_package_nonce'], 'emyui_package_submission' )) {
            $package_id = isset($_POST['submit_package']) ? sanitize_text_field($_POST['submit_package']) : '';
            if(!empty($package_id)){
                if(WC()->cart->get_cart_contents_count() > 0 ){
                    WC()->cart->empty_cart();
                }
                self::emyui_set_cookie('package_id', $package_id);
                wp_redirect(site_url('package-submit'));
                exit;
            }
        }
    }

    /**
     * 01-05-2024
     * 
     * Domain shortcode
     **/
    public static function emyui_package_submit_shortcode() {
        $package_id = isset($_COOKIE['package_id']) ? sanitize_text_field($_COOKIE['package_id']) : '';
        if(!empty($package_id)){
            ob_start();
            require_once(EMUI_VIEWS.'/package-submit.php');
            $output = ob_get_clean();
        }else{
            $html_message = sprintf(
                __('No package selected. Please <a href="%s">select a package</a> before proceeding.', 'emyui'),
                esc_url(home_url())
            );
            $output = sprintf('<div class="woocommerce-error" role="alert"><p>%s</p></div>', $html_message);
        }
        wp_reset_postdata();
        return $output;
    }

    /**
     * 01-06-2024
     * 
     * Set the cookies values
     **/
    public static function emyui_set_cookie($cookie_name, $cookie_value) {
        $expiration_time = time() + 3600;
        setcookie($cookie_name, $cookie_value, $expiration_time, COOKIEPATH, COOKIE_DOMAIN);
    }

    /**
     * 0106-2024
     * 
     * Domain search
     **/
    public static function emyui_domain_search(){
        $domainsearch = isset($_POST['domainsearch']) ? sanitize_text_field($_POST['domainsearch']) : '';
        $domain_tlds  = isset($_POST['domain_tlds'])?sanitize_text_field($_POST['domain_tlds']):'';
        setcookie("plan_price", "", time()-(60*60*24*7),"/");
        setcookie("plan_offer", "", time()-(60*60*24*7),"/");
        if(isset($_POST['action']) && $_POST['action'] == 'emyui_domain_search'){
            if(empty($domainsearch)){
                wp_send_json_error(
                    array(
                        'success' => false,
                        'msg' => __('This field is reqired.', 'emyui')
                    )
                );
                exit();
            }else{
                $domain_selected    = isset($_POST['domain_selected'])?sanitize_text_field($_POST['domain_selected']):'';
                $package_id         = isset($_COOKIE['package_id'])?sanitize_text_field($_COOKIE['package_id']):'';
                if(!$package_id || !self::emyui_is_valid_package($package_id)){
                    wp_send_json_error(
                        array(
                            'success' => false,
                            'msg' => __('Invalid package selected.', 'emyui')
                        )
                    );
                    exit();
                }
                if($package_id){
                    $emyui_api  = emyui_api::instance();
                    $name       = self::emyui_sanitize_domain_name($domainsearch).$domain_tlds;
                    $data       = $emyui_api->emyui_get_domain_whois_data($name);
                    if(isset($data['error']) && !empty($data['error']) && $domain_selected == 'new' && $domain_tlds){
                        if(class_exists('WC_Cart') && WC()->cart){
                            if(WC()->cart->get_cart_contents_count() > 0 ){
                                WC()->cart->empty_cart();
                            }
                            WC()->cart->add_to_cart( $package_id, 1, '', '', array(
                                    'domain_name' => $name
                                ) 
                            );
                            $emyui_main = emyui_main::instance();
                            $emyui_main->emyui_set_woocommerce_notice_transient('woocommerce_notice_cart','Your package has been added.','notice',60);
                            $cart_url = wc_get_cart_url();
                            wp_send_json_success(
                                array(
                                    'success'  => true,
                                    'cart_url' => $cart_url,
                                )
                            );
                            exit();
                        }
                    }elseif($domain_selected == 'existing' && $domain_tlds){
                        $domain_name = isset($data['domain_name']) ? sanitize_text_field($data['domain_name']) : '';
                        $domain_id   = isset($data['domain_id']) ? sanitize_text_field($data['domain_id']) : '';
                        if(class_exists('WC_Cart') && WC()->cart && $domain_name){
                            if(WC()->cart->get_cart_contents_count() > 0 ){
                                WC()->cart->empty_cart();
                            }
                            WC()->cart->add_to_cart( $package_id, 1, '', '', array(
                                    'domain_name' => $domain_name
                                ) 
                            );
                            $emyui_main = emyui_main::instance();
                            $emyui_main->emyui_set_woocommerce_notice_transient('woocommerce_notice_cart','Your package has been added.','notice',60);
                            $cart_url = wc_get_cart_url();
                            wp_send_json_success(
                                array(
                                    'success'  => true,
                                    'cart_url' => $cart_url,
                                )
                            );
                            exit();
                        }
                    }
                }
            }
        }
        ob_start();
            require_once(EMUI_VIEWS.'/domain-tdls.php');
        $domain_tlds = ob_get_clean();
        wp_send_json_error(
            array(
                'success'       => false,
                'msg'           => false,
                'domain_tdls'   => $domain_tlds
            )
        );
        exit();
    }

    /**
     * 01-12-2025
     * 
     * Remove invalid characters (anything other than a-z, 0-9, and hyphens)
     **/
    public static function emyui_sanitize_domain_name($string) {
        $sanitized = preg_replace('/[^a-zA-Z0-9-]/', '', $string);
        $sanitized = preg_replace('/-+/', '-', $sanitized);
        $sanitized = trim($sanitized, '-');
        return strtolower($sanitized);
    }

    /**
     * 01-12-2025
     * 
     * Function to check if the package is valid.
     **/
    public static function emyui_is_valid_package($package_id) {
        $valid_package = get_post($package_id);
        return ($valid_package && $valid_package->post_status === 'publish');
    }

    /**
     * 01-12-2025
     * 
     * show Domain name in cart
     */
    public static function emyui_get_item_data( $data, $cart_item ) {
        if ( isset( $cart_item['domain_name'] ) ) {
            $data[] = array(
                'name'  => __( 'Domain Name', 'emyui' ),
                'value' => $cart_item['domain_name']
            );
        }
        return $data;
    }

    /**
     * 01-12-2025
     * 
     * order_item_meta function for storing the meta in the order line items
     */
    public static function emyui_order_line_item( $item, $cart_item_key, $values, $order ) {
        if ( isset( $cart_item['domain_name'] ) ) {
            $item->update_meta_data( __( 'Domain Name', 'emyui' ), $cart_item['domain_name'] );
        }
    }

    /**
     * 01-12-2025
     * 
     * Remove underscores and replace them with spaces
     **/
    public static function emyui_modify_product_titles($name, $product) {
        $name = str_replace('_', ' ', $name);
        return ucwords($name);
    }

    /**
     * 01-12-2025
     * 
     * Disable quantity
     **/
    public static function emyui_remove_all_quantity_fields( $return, $product ) {
        return true;
    }

    /**
     * 01-14-2025
     * 
     * Add custom fields to the Advanced tab in WooCommerce settings.
     **/
    public static function emyui_add_custom_plans_field() {
        woocommerce_wp_textarea_input( array(
            'id'            => '_featured_package_text',
            'label'         => __( 'Package Overview', 'woocommerce' ),
            'placeholder'   => __( 'Provide a concise description of the featured package', 'woocommerce' ),
            'desc_tip'      => true,
            'description'   => __( 'Enter a brief and professional description highlighting the key benefits and features of the package.', 'woocommerce' ),
        ));
        woocommerce_wp_text_input( array(
            'id'            => '_package_offer_text',
            'label'         => __( 'Package Offer', 'woocommerce' ),
            'placeholder'   => __( 'Package Offer', 'woocommerce' ),
            'desc_tip'      => true,
            'description'   => __( 'Package offer.', 'woocommerce' ),
        ));
        $package_id     = isset($_GET['post']) ? sanitize_text_field($_GET['post']) : '';
        $package_group  = get_post_meta($package_id, '_hosting_plan_meta', true);
        if(!empty($package_group)){
            $plan_meta = json_decode($package_group, true);
            require_once(EMUI_VIEWS.'/package-custom-general-tab.php');
        }else{
            echo sprintf('<div class="emyui-hosting-plan-main" data-id="0">');
                woocommerce_wp_checkbox(array(
                    'id'            => '_hosting_plan_defaul_0',
                    'name'          => '_hosting_plan[0][plan_default]',
                    'label'         => __('Hosting Plan Default', 'woocommerce'),
                    'desc_tip'      => false,
                    'description'   => __('Mark this as the default hosting plan', 'woocommerce'),
                    'value'         => 'yes',
                    'class'         => 'checkbox'
                ));
                woocommerce_wp_text_input(array(
                    'id'            => '_hosting_plan_name_0',
                    'name'          => '_hosting_plan[0][plan_name]',
                    'label'         => __('Hosting Plan Name', 'woocommerce'),
                    'placeholder'   => 'Enter hosting plan name',
                    'desc_tip'      => false,
                    'description'   => __('Enter the name for the hosting plan.', 'woocommerce'),
                    'type'          => 'text'
                ));
                woocommerce_wp_text_input(array(
                    'id'            => '_hosting_plan_price_0',
                    'name'          => '_hosting_plan[0][plan_price]',
                    'label'         => __('Hosting Plan Price', 'woocommerce'),
                    'placeholder'   => 'Enter hosting plan price',
                    'desc_tip'      => false,
                    'description'   => __('Enter the price for the hosting plan.', 'woocommerce'),
                    'type'          => 'text'
                ));
                woocommerce_wp_textarea_input(array(
                    'id'            => '_hosting_plan_offer_text_0',
                    'name'          => '_hosting_plan[0][plan_offer]',
                    'label'         => __('Offer Text', 'woocommerce'),
                    'placeholder'   => 'Enter offer text',
                    'desc_tip'      => false,
                    'description'   => __('Enter the offer text for this hosting plan.', 'woocommerce'),
                    'type'          => 'textarea'
                ));
            echo sprintf('</div>');
        }
        echo '<div id="hosting-plan-repeater">';
        echo sprintf('<button type="button" id="add-new-hosting-plan" class="button">%s</button>', __('Add New Hosting Plan', 'emyui'));
        echo '</div>';
    }

    /**
     * 01-17-2025
     * 
     * Cart empty button text changed.
     **/
    public static function emyui_woocommerce_return_to_shop_text($text){
        $text = __( 'Back', 'woocommerce' );
        return $text;
    }

    /**
     * 01-19-2025
     * 
     * Changes packages
     **/
    public static function emyui_package(){
        if(isset($_POST['action']) && $_POST['action'] == 'emyui_package'){
            $package_id = isset($_POST['package']) ? sanitize_text_field($_POST['package']) : '';
            if($package_id && !WC()->cart->is_empty()){
                $cart = WC()->cart;
                if($cart){
                    foreach($cart->get_cart() as $cart_item_key => $cart_item ) {
                        $cart->remove_cart_item($cart_item_key);
                        $existing_domain_name = isset($cart_item['domain_name']) ? $cart_item['domain_name'] : '';
                        if($existing_domain_name){
                            $cart->add_to_cart($package_id, 1, '', '', array(
                                'domain_name' => $existing_domain_name
                            ));
                        }else{
                            $cart->add_to_cart($package_id, 1);
                        }
                        WC()->cart->calculate_totals();
                        $emyui_main = emyui_main::instance();
                        $emyui_main->emyui_set_woocommerce_notice_transient('woocommerce_notice_cart','Your package has been updated.','notice',60);
                        wp_send_json_success(
                            array(
                                'success' => true,
                                'msg'     => __('Package successfully update.', 'emyui')
                            )
                        );
                        exit();
                    }
                }
            }
        }
        wp_send_json_error();
        exit();
    }

    /**
     * 01-23-2025
     * 
     * Added custom tab
     **/
    public static function emyui_add_my_account_tab($items) {
        $items = array_slice( $items, 0, 1, true ) 
        + array( 'vlab' => __('Vlab Hosting', 'emyui') )
        + array_slice( $items, 1, NULL, true );
        unset($items['downloads']);
        return $items;
    }

    /**
     * 01-23-2025
     * 
     * Register endpoint
     **/
    public static function emyui_add_my_account_endpoint() {
        add_rewrite_endpoint('vlab', EP_PAGES);
    }

    /**
     * 01-23-2025
     * 
     * Display tab conetent
     **/
    public static function emyui_my_account_tab_content() {
        echo '<h3>' . __('Welcome to Vlab Hosting', 'emyui') . '</h3>';
    }

    /**
     * 01-23-2025
     * 
     * Flush rewrites roles
     **/
    public static function emyui_admin_init() {
        flush_rewrite_rules();
    }

    /**
     * 01-23-2025
     * 
     * calculate extra charges
     **/
    public static function emyui_add_extra_charges($cart) {
        if(is_admin() || !WC()->cart || WC()->cart->is_empty()){
            return;
        }
        $hidden_fee_title   = __('Plan Fees', 'emyui');
        $price              = 0;
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $package_group = get_post_meta($product_id, '_hosting_plan_meta', true);
            if(!empty($package_group)){
                $plan_meta = json_decode($package_group, true);
                $cookie_price = isset($_COOKIE['plan_price']) ? floatval($_COOKIE['plan_price']) : 0;
                if(!empty($cookie_price)){
                    $price = $cookie_price;
                }else{
                    $default_plan = array_filter($plan_meta, function ($plan) {
                        return isset($plan['plan_default']) && $plan['plan_default'] === 'yes';
                    });
                    $default_plan = array_values($default_plan);
                    if(!empty($default_plan)){
                        $price = isset($default_plan[0]['plan_price']) ? floatval($default_plan[0]['plan_price']) : 0;
                    }
                }
            }
            if($price > 0) {
                $cart->add_fee($hidden_fee_title, $price, false);
                break;
            }
        }
    }
}
EMYUI_Package_Product::init();
add_action('init', function(){
    if(isset($_GET['test1'])){
        echo '<pre>';
        $array = array(
            array("tld"=>".ac", "register"=>"74.99", "renew"=>"74.99", "transfer"=>""),
            array("tld"=>".academy", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".accountant", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".accountants", "register"=>"129.99", "renew"=>"129.99", "transfer"=>"129.99"),
            array("tld"=>".actor", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".agency", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".airforce", "register"=>"44.95", "renew"=>"44.95", "transfer"=>"44.95"),
            array("tld"=>".am", "register"=>"119.99", "renew"=>"119.99", "transfer"=>""),
            array("tld"=>".apartments", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".archi", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("tld"=>".army", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".art", "register"=>"34.95", "renew"=>"34.95", "transfer"=>"34.95"),
            array("tld"=>".asia", "register"=>"19.95", "renew"=>"19.95", "transfer"=>"19.95"),
            array("tld"=>".associates", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".at", "register"=>"23.99", "renew"=>"23.99", "transfer"=>""),
            array("tld"=>".attorney", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".auction", "register"=>"38.95", "renew"=>"38.95", "transfer"=>"38.95"),
            array("tld"=>".audio", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("tld"=>".band", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".bar", "register"=>"89.99", "renew"=>"89.99", "transfer"=>"89.99"),
            array("tld"=>".bargains", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".be", "register"=>"20.99", "renew"=>"20.99", "transfer"=>"20.99"),
            array("tld"=>".berlin", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".best", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".bid", "register"=>"34.95", "renew"=>"34.95", "transfer"=>"34.95"),
            array("tld"=>".bike", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".bingo", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".bio", "register"=>"89.99", "renew"=>"89.99", "transfer"=>"89.99"),
            array("tld"=>".biz", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("tld"=>".black", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".blackfriday", "register"=>"249.99", "renew"=>"249.99", "transfer"=>"249.99"),
            array("tld"=>".blog", "register"=>"34.95", "renew"=>"34.95", "transfer"=>"34.95"),
            array("tld"=>".blue", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".boutique", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".br.com", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".build", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".builders", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".business", "register"=>"14.99", "renew"=>"14.99", "transfer"=>"14.99"),
            array("tld"=>".buzz", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".bz", "register"=>"29.95", "renew"=>"29.95", "transfer"=>""),
            array("tld"=>".ca", "register"=>"16.99", "renew"=>"16.99", "transfer"=>"16.99"),
            array("tld"=>".cab", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".cafe", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".camera", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".camp", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".capital", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".cards", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".care", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".careers", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".cash", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".casino", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("tld"=>".catering", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".cc", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("tld"=>".center", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".ceo", "register"=>"119.99", "renew"=>"119.99", "transfer"=>"119.99"),
            array("tld"=>".chat", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".cheap", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".christmas", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".church", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".city", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".claims", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".cleaning", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".click", "register"=>"14.99", "renew"=>"14.99", "transfer"=>"14.99"),
            array("tld"=>".clinic", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".clothing", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".cloud", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".club", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("tld"=>".cm", "register"=>"139.99", "renew"=>"139.99", "transfer"=>""),
            array("tld"=>".cn.com", "register"=>"54.99", "renew"=>"54.99", "transfer"=>"54.99"),
            array("tld"=>".co", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".co.nz", "register"=>"35.99", "renew"=>"35.99", "transfer"=>"35.99"),
            array("tld"=>".co.uk", "register"=>"11.99", "renew"=>"11.99", "transfer"=>"11.99"),
            array("tld"=>".coach", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".codes", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".coffee", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".college", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".com", "register"=>"11.99", "renew"=>"19.99", "transfer"=>"11.99"),
            array("tld"=>".com.co", "register"=>"18.99", "renew"=>"18.99", "transfer"=>"18.99"),
            array("tld"=>".com.de", "register"=>"14.95", "renew"=>"14.95", "transfer"=>"14.95"),
            array("tld"=>".com.mx", "register"=>"", "renew"=>"69.99", "transfer"=>""),
            array("tld"=>".com.pe", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".com.tw", "register"=>"29.95", "renew"=>"29.95", "transfer"=>"29.95"),
            array("tld"=>".community", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".company", "register"=>"15.99", "renew"=>"15.99", "transfer"=>"15.99"),
            array("tld"=>".computer", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".condos", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".construction", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".consulting", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".contractors", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".cool", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".coupons", "register"=>"64.99", "renew"=>"64.99", "transfer"=>"64.99"),
            array("tld"=>".courses", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".credit", "register"=>"129.99", "renew"=>"129.99", "transfer"=>"129.99"),
            array("tld"=>".creditcard", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("tld"=>".cricket", "register"=>"37.99", "renew"=>"37.99", "transfer"=>"37.99"),
            array("tld"=>".cruises", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".cymru", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("tld"=>".dance", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".date", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".dating", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".de", "register"=>"9.99", "renew"=>"9.99", "transfer"=>"9.99"),
            array("tld"=>".de.com", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".deals", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".degree", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".delivery", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".democrat", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".dental", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".dentist", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".desi", "register"=>"17.99", "renew"=>"17.99", "transfer"=>"17.99"),
            array("tld"=>".design", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".diamonds", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".diet", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("tld"=>".digital", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".direct", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".directory", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".discount", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".dog", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".domains", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".download", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".earth", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".education", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".email", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".energy", "register"=>"129.99", "renew"=>"129.99", "transfer"=>"129.99"),
            array("tld"=>".engineer", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".engineering", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".enterprises", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".equipment", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".estate", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".eu", "register"=>"11.99", "renew"=>"11.99", "transfer"=>"11.99"),
            array("tld"=>".eu.com", "register"=>"27.99", "renew"=>"27.99", "transfer"=>"27.99"),
            array("tld"=>".eus", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("tld"=>".events", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".exchange", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".expert", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".exposed", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".express", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".fail", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".faith", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".family", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".fans", "register"=>"14.99", "renew"=>"14.99", "transfer"=>"14.99"),
            array("tld"=>".farm", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".film", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("tld"=>".finance", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".financial", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".fish", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".fitness", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".flights", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".florist", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".flowers", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("tld"=>".fm", "register"=>"149.99", "renew"=>"149.99", "transfer"=>""),
            array("tld"=>".football", "register"=>"26.99", "renew"=>"26.99", "transfer"=>"26.99"),
            array("tld"=>".forsale", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".foundation", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".fund", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".furniture", "register"=>"149.99", "renew"=>"149.99", "transfer"=>"149.99"),
            array("tld"=>".futbol", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("tld"=>".fyi", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".gal", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".gallery", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".gift", "register"=>"29.95", "renew"=>"29.95", "transfer"=>"29.95"),
            array("tld"=>".gifts", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".gives", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".glass", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".global", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("tld"=>".gold", "register"=>"129.99", "renew"=>"129.99", "transfer"=>"129.99"),
            array("tld"=>".golf", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".graphics", "register"=>"27.99", "renew"=>"27.99", "transfer"=>"27.99"),
            array("tld"=>".gratis", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".green", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("tld"=>".gripe", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".gs", "register"=>"59.99", "renew"=>"59.99", "transfer"=>""),
            array("tld"=>".guide", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".guitars", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("tld"=>".guru", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".haus", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".healthcare", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".help", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".hiphop", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".hockey", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".holdings", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".holiday", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".host", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("tld"=>".hosting", "register"=>"499.95", "renew"=>"499.95", "transfer"=>"499.95"),
            array("tld"=>".house", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".how", "register"=>"29.95", "renew"=>"29.95", "transfer"=>"29.95"),
            array("tld"=>".idv.tw", "register"=>"27.99", "renew"=>"27.99", "transfer"=>"27.99"),
            array("tld"=>".immo", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".immobilien", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".in", "register"=>"11.99", "renew"=>"11.99", "transfer"=>"11.99"),
            array("tld"=>".industries", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".info", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".ink", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".institute", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".insure", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".international", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".investments", "register"=>"149.99", "renew"=>"149.99", "transfer"=>"149.99"),
            array("tld"=>".io", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".irish", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".it", "register"=>"19.99", "renew"=>"19.99", "transfer"=>""),
            array("tld"=>".jewelry", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".juegos", "register"=>"499.95", "renew"=>"499.95", "transfer"=>"499.95"),
            array("tld"=>".kaufen", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".kim", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".kitchen", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".la", "register"=>"54.95", "renew"=>"54.95", "transfer"=>"54.95"),
            array("tld"=>".land", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".lawyer", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".lease", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".legal", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".lgbt", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".life", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".lighting", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".limited", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".limo", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".link", "register"=>"13.99", "renew"=>"13.99", "transfer"=>"13.99"),
            array("tld"=>".live", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".loan", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".loans", "register"=>"139.99", "renew"=>"139.99", "transfer"=>"139.99"),
            array("tld"=>".lol", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".london", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".love", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".ltd", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".ltda", "register"=>"47.99", "renew"=>"47.99", "transfer"=>"47.99"),
            array("tld"=>".luxury", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".maison", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".management", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".market", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".marketing", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".mba", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".me", "register"=>"22.99", "renew"=>"22.99", "transfer"=>"22.99"),
            array("tld"=>".me.uk", "register"=>"10.95", "renew"=>"10.95", "transfer"=>"10.95"),
            array("tld"=>".media", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".melbourne", "register"=>"59.95", "renew"=>"59.95", "transfer"=>"59.95"),
            array("tld"=>".memorial", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".men", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".menu", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".mobi", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".moda", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".moe", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("tld"=>".money", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".mortgage", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".movie", "register"=>"349.99", "renew"=>"349.99", "transfer"=>"349.99"),
            array("tld"=>".ms", "register"=>"59.95", "renew"=>"59.95", "transfer"=>""),
            array("tld"=>".name", "register"=>"14.95", "renew"=>"14.95", "transfer"=>"14.95"),
            array("tld"=>".navy", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".net", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("tld"=>".net.co", "register"=>"18.99", "renew"=>"18.99", "transfer"=>"18.99"),
            array("tld"=>".net.nz", "register"=>"39.99", "renew"=>"39.99", "transfer"=>""),
            array("tld"=>".network", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".news", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".ninja", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".nl", "register"=>"8.99", "renew"=>"8.99", "transfer"=>"8.99"),
            array("tld"=>".nom.co", "register"=>"18.99", "renew"=>"18.99", "transfer"=>"18.99"),
            array("tld"=>".nu", "register"=>"", "renew"=>"27.95", "transfer"=>""),
            array("tld"=>".one", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".onl", "register"=>"18.99", "renew"=>"18.99", "transfer"=>"18.99"),
            array("tld"=>".online", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".org", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("tld"=>".org.nz", "register"=>"39.99", "renew"=>"39.99", "transfer"=>""),
            array("tld"=>".org.tw", "register"=>"29.95", "renew"=>"29.95", "transfer"=>""),
            array("tld"=>".org.uk", "register"=>"11.99", "renew"=>"11.99", "transfer"=>"11.99"),
            array("tld"=>".osaka", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".paris", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".partners", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".parts", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".party", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".pe", "register"=>"79.95", "renew"=>"79.95", "transfer"=>"79.95"),
            array("tld"=>".photo", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".photography", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".photos", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".pics", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".pictures", "register"=>"13.99", "renew"=>"13.99", "transfer"=>"13.99"),
            array("tld"=>".pink", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".pizza", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".place", "register"=>"23.99", "renew"=>"23.99", "transfer"=>"23.99"),
            array("tld"=>".plumbing", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".plus", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".poker", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".press", "register"=>"89.99", "renew"=>"89.99", "transfer"=>"89.99"),
            array("tld"=>".pro", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".productions", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".properties", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".property", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("tld"=>".pub", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".pw", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".quebec", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".racing", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".recipes", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".red", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".rehab", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".reise", "register"=>"119.99", "renew"=>"119.99", "transfer"=>"119.99"),
            array("tld"=>".reisen", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".rent", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".rentals", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".repair", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".report", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".republican", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".rest", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".restaurant", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".review", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".reviews", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".rich", "register"=>"2999.99", "renew"=>"2999.99", "transfer"=>"2999.99"),
            array("tld"=>".rip", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".rocks", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("tld"=>".ru.com", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("tld"=>".run", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".sa.com", "register"=>"279.99", "renew"=>"279.99", "transfer"=>"279.99"),
            array("tld"=>".sale", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".sarl", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".school", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".schule", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".science", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".scot", "register"=>"49.95", "renew"=>"49.95", "transfer"=>"49.95"),
            array("tld"=>".se.net", "register"=>"37.99", "renew"=>"37.99", "transfer"=>"37.99"),
            array("tld"=>".services", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".sh", "register"=>"79.95", "renew"=>"79.95", "transfer"=>""),
            array("tld"=>".shiksha", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".shoes", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".shop", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".show", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".singles", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".site", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".ski", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".soccer", "register"=>"26.99", "renew"=>"26.99", "transfer"=>"26.99"),
            array("tld"=>".social", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".software", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".solar", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".solutions", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".soy", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".space", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".srl", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".store", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".studio", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".study", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".style", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".sucks", "register"=>"349.99", "renew"=>"349.99", "transfer"=>"349.99"),
            array("tld"=>".supplies", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".supply", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".support", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".surgery", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".sydney", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".systems", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".tattoo", "register"=>"49.95", "renew"=>"49.95", "transfer"=>"49.95"),
            array("tld"=>".tax", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".taxi", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".tc", "register"=>"159.99", "renew"=>"159.99", "transfer"=>""),
            array("tld"=>".team", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".tech", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".technology", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".tel", "register"=>"14.95", "renew"=>"14.95", "transfer"=>"14.95"),
            array("tld"=>".tennis", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("tld"=>".theater", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".tienda", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".tips", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".tires", "register"=>"129.99", "renew"=>"129.99", "transfer"=>"129.99"),
            array("tld"=>".today", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("tld"=>".tools", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".top", "register"=>"9.99", "renew"=>"9.99", "transfer"=>"9.99"),
            array("tld"=>".tours", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".town", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".toys", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("tld"=>".trade", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".training", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".tv", "register"=>"44.95", "renew"=>"44.95", "transfer"=>"44.95"),
            array("tld"=>".tw", "register"=>"29.99", "renew"=>"29.99", "transfer"=>""),
            array("tld"=>".uk", "register"=>"11.99", "renew"=>"11.99", "transfer"=>"11.99"),
            array("tld"=>".uk.com", "register"=>"64.95", "renew"=>"64.95", "transfer"=>"64.95"),
            array("tld"=>".uk.net", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".university", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".uno", "register"=>"27.99", "renew"=>"27.99", "transfer"=>"27.99"),
            array("tld"=>".us", "register"=>"16.99", "renew"=>"16.99", "transfer"=>"16.99"),
            array("tld"=>".us.com", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("tld"=>".vacations", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".vegas", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".ventures", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".vet", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".vg", "register"=>"49.99", "renew"=>"49.99", "transfer"=>""),
            array("tld"=>".viajes", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".video", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".villas", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("tld"=>".vision", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".vote", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("tld"=>".voting", "register"=>"1399.99", "renew"=>"1399.99", "transfer"=>"1399.99"),
            array("tld"=>".voto", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("tld"=>".voyage", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("tld"=>".wales", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("tld"=>".watch", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("tld"=>".webcam", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".website", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".wiki", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".win", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("tld"=>".work", "register"=>"10.99", "renew"=>"10.99", "transfer"=>"10.99"),
            array("tld"=>".works", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".world", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("tld"=>".ws", "register"=>"39.99", "renew"=>"39.99", "transfer"=>""),
            array("tld"=>".wtf", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("tld"=>".xyz", "register"=>"18.99", "renew"=>"18.99", "transfer"=>"18.99"),
            array("tld"=>".za.com", "register"=>"279.99", "renew"=>"279.99", "transfer"=>"279.99"),
            array("tld"=>".zone", "register"=>"42.95", "renew"=>"42.95", "transfer"=>"42.95")
        );
        print_r($array);
        die;
    }
});