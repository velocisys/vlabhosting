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
        add_action( "woocommerce_package_add_to_cart", array(__CLASS__, 'emyui_package_add_to_cart'));
        add_shortcode( 'package_pricing', array(__CLASS__, 'emyui_package_pricing_shortcode'));
        add_shortcode( 'package_domain', array(__CLASS__, 'emyui_package_submit_shortcode'));
        add_action( 'wp', array(__CLASS__, 'emyui_process_submission' ));
        self::$initialized = true;
    }

    /**
     * 25-12-2024
     * 
     * Add to cart buttton add on packages
     **/
    public function emyui_package_add_to_cart(){
        do_action('woocommerce_simple_add_to_cart');
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
            '_package_hosting_plan'
        ];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
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
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
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
        if(!isset( $_POST['submit_package'] ) || !isset($_POST['emyui_package_nonce'])){
            return;
        }
        if(!wp_verify_nonce( $_POST['emyui_package_nonce'], 'emyui_package_submission' )) {
            wp_die( __( 'Security check failed', 'custom-job' ) );
        }
        $package_id = isset($_POST['package_id']) ? sanitize_text_field($_POST['package_id']) : '';
        if(is_wp_error($package_id)){
            wp_die( __( 'Failed to submit the package', 'emyui' ) );
        }
        wp_redirect(site_url('package-submit'));
        exit;
    }

    /**
     * 01-05-2024
     * 
     * Domain shortcode
     **/
    public static function emyui_package_submit_shortcode() {
        $package_id = isset($_POST['package_id']) ? sanitize_text_field($_POST['package_id']) : '';
        if(!empty($package_id)){
            ob_start();
            require_once(EMUI_VIEWS.'/package-submit.php');
            $output = ob_get_clean();
        }else{
            $output = sprintf('<p>%s</p>', __('Packages not selected.', 'emyui'));
        }
        wp_reset_postdata();
        return $output;
    }
}
EMYUI_Package_Product::init();