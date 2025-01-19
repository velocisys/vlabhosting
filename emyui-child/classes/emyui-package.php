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
                if($package_id && $domain_selected && $domain_tlds){
                    $emyui_api  = emyui_api::instance();
                    $name       = self::emyui_sanitize_domain_name($domainsearch).$domain_tlds;
                    $data       = $emyui_api->emyui_get_domain_whois_data($name);
                    if(isset($data['error']) && !empty($data['error'])){
                        if(class_exists('WC_Cart') && WC()->cart){
                            if(WC()->cart->get_cart_contents_count() > 0 ){
                                WC()->cart->empty_cart();
                            }
                            WC()->cart->add_to_cart( $package_id, 1, '', '', array(
                                    'domain_name' => $name
                                ) 
                            );
                            $cart_url = wc_get_cart_url();
                            $emyui_main = emyui_main::instance();
                            $emyui_main->emyui_set_woocommerce_notice_transient('woocommerce_notice_cart','Your package has been added.','notice',60);
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
        $error = sprintf('<span class="error">%s</span>',__('Already Purchased, Try another domain name', 'emyui'));
        ob_start();
            require_once(EMUI_VIEWS.'/domain-tdls.php');
        $domain_tlds = ob_get_clean();
        wp_send_json_error(
            array(
                'success'       => false,
                'msg'           => $error,
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
        $text = __( 'Return to package', 'woocommerce' );
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
}
EMYUI_Package_Product::init();
