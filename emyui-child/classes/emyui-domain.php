<?php
/**
 * 24-12-2024
 * 
 * Register Package product type
 **/
add_action( 'init', 'emyui_register_domain_product_type' );
function emyui_register_domain_product_type() {
    class WC_Product_domain extends WC_Product {
        public function __construct( $product ) {
            $this->product_type = 'domain';
            parent::__construct( $product );
        }
    }
}

/**
 * 25-12-2024
 * 
 * Load New Product Type Class
 **/
add_filter( 'woocommerce_product_class', 'emyui_domain_class', 10, 2 ); 
function emyui_domain_class( $classname, $product_type ) {
    if( $product_type == 'domain' ){
        $classname = 'WC_Product_domain';
    }
    return $classname;
}

/**
 * 18-12-2024
 * 
 * Class EMYUI_domain_Product
 * Registers a custom WooCommerce product type: Package
 */
class EMYUI_domain_Product {
    private static $initialized = false;

    /**
     * Initialize hooks and filters.
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }

        add_filter('product_type_selector', array(__CLASS__, 'emyui_add_domain_product_type'));
        add_action('woocommerce_process_product_meta', array(__CLASS__, 'emyui_save_domain_fields'));
        add_filter('woocommerce_product_data_tabs', array(__CLASS__, 'emyui_add_domain_options_tab'));
        add_action('woocommerce_product_data_panels', array(__CLASS__, 'emyui_add_domain_fields'));
        //add_action('admin_init', array(__CLASS__, 'emyui_create_or_update_tld_products'));
        add_shortcode('domain_products', array(__CLASS__,'emyui_display_domain_products'));
        add_shortcode('domain_search', array(__CLASS__,'emyui_display_domain_search'));
        self::$initialized = true;
    }

    /**
     * Add a custom tab for domain product type.
     *
     * @param array $tabs
     * @return array
     */
    public static function emyui_add_domain_options_tab($tabs) {
        unset($tabs['inventory']);
        unset($tabs['shipping']);
        unset($tabs['linked_product']);
        unset($tabs['attribute']);
        $tabs['domain_options'] = [
            'label'    => __('Domain Options', 'emyui'),
            'target'   => 'doamin_options_data',
            'class'    => ['show_if_domain'],
            'priority' => 60,
        ];
        return $tabs;
    }

    /**
     * 18-12-2024
     * Add the domain product type to the dropdown in WooCommerce.
     *
     * @param array $types
     * @return array
     */
    public static function emyui_add_domain_product_type($types) {
        $types['domain'] = __('Domain', 'emyui');
        return $types;
    }

    /**
     * 18-12-2024
     * 
     * Add custom fields for domain product type in the admin.
     */

    public static function emyui_add_domain_fields() {
        wc_enqueue_js( "     
          $(document.body).on('woocommerce-product-type-change',function(event,type){
            if(type=='domain') {
                $('.general_tab').show();
                $('.pricing').show();         
            }
            });");
        global $product_object;
        if( $product_object && 'domain' === $product_object->get_type() ) {
              wc_enqueue_js( "$('.general_tab').show(); $('.pricing').show();");
        }
        echo '<div id="doamin_options_data" class="panel woocommerce_options_panel">';
            echo '<div class="options_group">';
                woocommerce_wp_text_input([
                    'id'          => '_tld_id_protection',
                    'label'       => __('Id Protection', 'emyui'),
                    'description' => __('', 'emyui'),
                    'type'        => 'text',
                    'desc_tip'    => false,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_tld_domain',
                    'label'       => __('Domain', 'emyui'),
                    'description' => __('', 'emyui'),
                    'type'        => 'text',
                    'desc_tip'    => false,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_tld_renew',
                    'label'       => __('Renew Domain', 'emyui'),
                    'description' => __('', 'emyui'),
                    'type'        => 'text',
                    'desc_tip'    => false,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_tld_register',
                    'label'       => __('Register Domain', 'emyui'),
                    'description' => __('', 'emyui'),
                    'type'        => 'text',
                    'desc_tip'    => false,
                ]);
                woocommerce_wp_text_input([
                    'id'          => '_tld_transfer',
                    'label'       => __('Transfer Domain', 'emyui'),
                    'description' => __('', 'emyui'),
                    'type'        => 'text',
                    'desc_tip'    => false,
                ]);
                woocommerce_wp_select(array(
                    'id'          => '_tld_categories',
                    'label'       => __('Domain Category', 'woocommerce'),
                    'options'     => array(
                        ''          => __('Select Domain Category', 'woocommerce'),
                        'deal'      => __('Deal', 'woocommerce'),
                        'country'   => __('Country', 'woocommerce'),
                        'generic'   => __('Generic', 'woocommerce'),
                    ),
                    'description' => __('', 'woocommerce'),
                    'desc_tip'    => false,
                ));
                woocommerce_wp_text_input([
                    'id'          => '_tld_expiry',
                    'label'       => __('Domain Expiry', 'emyui'),
                    'description' => __('', 'emyui'),
                    'type'        => 'text',
                    'desc_tip'    => false,
                ]);
            echo '</div>';
        echo '</div>';
    }

    /**
     * 18-12-2024
     * Save custom fields for domain product type.
     *
     * @param int $post_id
     */
    public static function emyui_save_domain_fields($post_id) {
        $fields = [
            '_tld_id_protection',
            '_tld_domain',
            '_tld_renew',
            '_tld_register',
            '_tld_transfer',
            '_tld_categories',
            '_tld_expiry'
        ];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
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
    public static function get_domain_meta($product_id, $key) {
        return get_post_meta($product_id, $key, true);
    }

    /**
     * 01-29-2025
     * 
     * Create Update Tld data
     **/
    public static function emyui_create_or_update_tld_products() {
        $tlds = self::emyui_tld_callback();
        if (!empty($tlds)) {
            foreach ($tlds as $tld_data) {
                $tld_sku = str_replace('.', '', $tld_data['tld']);
                $existing_product_id = wc_get_product_id_by_sku($tld_sku);
                if ($existing_product_id) {
                    $product = wc_get_product($existing_product_id);
                    if ($product) {
                        $product->set_name($tld_sku);
                        $product->set_description("");
                        $product->set_regular_price($tld_data['register']);
                        $product->save();
                        wp_set_object_terms($existing_product_id, 'domain', 'product_type');
                        self::emyui_update_tld_meta($existing_product_id, $tld_data);
                    }
                }else{
                    $product = new WC_Product_Simple();
                    $product->set_name($tld_sku);
                    $product->set_sku($tld_sku);
                    $product->set_description("");
                    $product->set_regular_price($tld_data['register']);
                    $product_id = $product->save();
                    wp_set_object_terms($product_id, 'domain', 'product_type');
                    self::emyui_update_tld_meta($product_id, $tld_data);
                }
            }
        }
    }

    /**
     * 01-29-2025
     * 
     * Update Tld Meta
     **/
    public static function emyui_update_tld_meta($product_id, $tld_data) {
        update_post_meta($product_id, '_tld_domain', $tld_data['tld']);
        update_post_meta($product_id, '_tld_register', $tld_data['register']);
        update_post_meta($product_id, '_tld_renew', $tld_data['renew']);
        update_post_meta($product_id, '_tld_id_protection', $tld_data['tld_id_protection']);
        update_post_meta($product_id, '_tld_transfer', $tld_data['transfer']);
        update_post_meta($product_id, '_tld_categories', $tld_data['categories']);
        update_post_meta($product_id, '_tld_expiry', $tld_data['expiry']);
    }

    /**
     * 01-29-2025
     * 
     * 
     * TLD (domain)
     **/
    public static function emyui_tld_callback(){
        $tldData = array(
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".ac", "register"=>"74.99", "renew"=>"74.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".academy", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".accountant", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".accountants", "register"=>"129.99", "renew"=>"129.99", "transfer"=>"129.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".actor", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".agency", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".airforce", "register"=>"44.95", "renew"=>"44.95", "transfer"=>"44.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".am", "register"=>"119.99", "renew"=>"119.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".apartments", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".archi", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".army", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".art", "register"=>"34.95", "renew"=>"34.95", "transfer"=>"34.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".asia", "register"=>"19.95", "renew"=>"19.95", "transfer"=>"19.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".associates", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".at", "register"=>"23.99", "renew"=>"23.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".attorney", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".auction", "register"=>"38.95", "renew"=>"38.95", "transfer"=>"38.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".audio", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".band", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".bar", "register"=>"89.99", "renew"=>"89.99", "transfer"=>"89.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".bargains", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".be", "register"=>"20.99", "renew"=>"20.99", "transfer"=>"20.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".berlin", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".best", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".bid", "register"=>"34.95", "renew"=>"34.95", "transfer"=>"34.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".bike", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".bingo", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".bio", "register"=>"89.99", "renew"=>"89.99", "transfer"=>"89.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".biz", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".black", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".blackfriday", "register"=>"249.99", "renew"=>"249.99", "transfer"=>"249.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".blog", "register"=>"34.95", "renew"=>"34.95", "transfer"=>"34.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".blue", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".boutique", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".br.com", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".build", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".builders", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".business", "register"=>"14.99", "renew"=>"14.99", "transfer"=>"14.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".buzz", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".bz", "register"=>"29.95", "renew"=>"29.95", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".ca", "register"=>"16.99", "renew"=>"16.99", "transfer"=>"16.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cab", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cafe", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".camera", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".camp", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".capital", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cards", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".care", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".careers", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cash", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".casino", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".catering", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cc", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".center", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".ceo", "register"=>"119.99", "renew"=>"119.99", "transfer"=>"119.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".chat", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cheap", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".christmas", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".church", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".city", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".claims", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cleaning", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".click", "register"=>"14.99", "renew"=>"14.99", "transfer"=>"14.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".clinic", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".clothing", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cloud", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".club", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cm", "register"=>"139.99", "renew"=>"139.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".cn.com", "register"=>"54.99", "renew"=>"54.99", "transfer"=>"54.99"),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".co", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".co.nz", "register"=>"35.99", "renew"=>"35.99", "transfer"=>"35.99"),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".co.uk", "register"=>"11.99", "renew"=>"11.99", "transfer"=>"11.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".coach", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".codes", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".coffee", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".college", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"deal","tld_id_protection"=>"yes","tld"=>".com", "register"=>"11.99", "renew"=>"19.99", "transfer"=>"11.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".com.co", "register"=>"18.99", "renew"=>"18.99", "transfer"=>"18.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".com.de", "register"=>"14.95", "renew"=>"14.95", "transfer"=>"14.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".com.mx", "register"=>"", "renew"=>"69.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".com.pe", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".com.tw", "register"=>"29.95", "renew"=>"29.95", "transfer"=>"29.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".community", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".company", "register"=>"15.99", "renew"=>"15.99", "transfer"=>"15.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".computer", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".condos", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".construction", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".consulting", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".contractors", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cool", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".coupons", "register"=>"64.99", "renew"=>"64.99", "transfer"=>"64.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".courses", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".credit", "register"=>"129.99", "renew"=>"129.99", "transfer"=>"129.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".creditcard", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cricket", "register"=>"37.99", "renew"=>"37.99", "transfer"=>"37.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cruises", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".cymru", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".dance", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".date", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".dating", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".de", "register"=>"9.99", "renew"=>"9.99", "transfer"=>"9.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".de.com", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".deals", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".degree", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".delivery", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".democrat", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".dental", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".dentist", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".desi", "register"=>"17.99", "renew"=>"17.99", "transfer"=>"17.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".design", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".diamonds", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".diet", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".digital", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".direct", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".directory", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".discount", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".dog", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".domains", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".download", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".earth", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".education", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".email", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".energy", "register"=>"129.99", "renew"=>"129.99", "transfer"=>"129.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".engineer", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".engineering", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".enterprises", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".equipment", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".estate", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".eu", "register"=>"11.99", "renew"=>"11.99", "transfer"=>"11.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".eu.com", "register"=>"27.99", "renew"=>"27.99", "transfer"=>"27.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".eus", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".events", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".exchange", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".expert", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".exposed", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".express", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".fail", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".faith", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".family", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".fans", "register"=>"14.99", "renew"=>"14.99", "transfer"=>"14.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".farm", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".film", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".finance", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".financial", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".fish", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".fitness", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".flights", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".florist", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".flowers", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".fm", "register"=>"149.99", "renew"=>"149.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".football", "register"=>"26.99", "renew"=>"26.99", "transfer"=>"26.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".forsale", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".foundation", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".fund", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".furniture", "register"=>"149.99", "renew"=>"149.99", "transfer"=>"149.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".futbol", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".fyi", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".gal", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".gallery", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".gift", "register"=>"29.95", "renew"=>"29.95", "transfer"=>"29.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".gifts", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".gives", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".glass", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".global", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".gold", "register"=>"129.99", "renew"=>"129.99", "transfer"=>"129.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".golf", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".graphics", "register"=>"27.99", "renew"=>"27.99", "transfer"=>"27.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".gratis", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".green", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".gripe", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".gs", "register"=>"59.99", "renew"=>"59.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".guide", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".guitars", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".guru", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".haus", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".healthcare", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".help", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".hiphop", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".hockey", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".holdings", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".holiday", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".host", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".hosting", "register"=>"499.95", "renew"=>"499.95", "transfer"=>"499.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".house", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".how", "register"=>"29.95", "renew"=>"29.95", "transfer"=>"29.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".idv.tw", "register"=>"27.99", "renew"=>"27.99", "transfer"=>"27.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".immo", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".immobilien", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".in", "register"=>"11.99", "renew"=>"11.99", "transfer"=>"11.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".industries", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".info", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".ink", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".institute", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".insure", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".international", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".investments", "register"=>"149.99", "renew"=>"149.99", "transfer"=>"149.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".io", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".irish", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".it", "register"=>"19.99", "renew"=>"19.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".jewelry", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".juegos", "register"=>"499.95", "renew"=>"499.95", "transfer"=>"499.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".kaufen", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".kim", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".kitchen", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".la", "register"=>"54.95", "renew"=>"54.95", "transfer"=>"54.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".land", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".lawyer", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".lease", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".legal", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".lgbt", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".life", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".lighting", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".limited", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".limo", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".link", "register"=>"13.99", "renew"=>"13.99", "transfer"=>"13.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".live", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".loan", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".loans", "register"=>"139.99", "renew"=>"139.99", "transfer"=>"139.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".lol", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".london", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".love", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".ltd", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".ltda", "register"=>"47.99", "renew"=>"47.99", "transfer"=>"47.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".luxury", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".maison", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".management", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".market", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".marketing", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".mba", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".me", "register"=>"22.99", "renew"=>"22.99", "transfer"=>"22.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".me.uk", "register"=>"10.95", "renew"=>"10.95", "transfer"=>"10.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".media", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".melbourne", "register"=>"59.95", "renew"=>"59.95", "transfer"=>"59.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".memorial", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".men", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".menu", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".mobi", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".moda", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".moe", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".money", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".mortgage", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".movie", "register"=>"349.99", "renew"=>"349.99", "transfer"=>"349.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".ms", "register"=>"59.95", "renew"=>"59.95", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".name", "register"=>"14.95", "renew"=>"14.95", "transfer"=>"14.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".navy", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"deal","tld_id_protection"=>"yes","tld"=>".net", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".net.co", "register"=>"18.99", "renew"=>"18.99", "transfer"=>"18.99"),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".net.nz", "register"=>"39.99", "renew"=>"39.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".network", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".news", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".ninja", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".nl", "register"=>"8.99", "renew"=>"8.99", "transfer"=>"8.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".nom.co", "register"=>"18.99", "renew"=>"18.99", "transfer"=>"18.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".nu", "register"=>"", "renew"=>"27.95", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".one", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".onl", "register"=>"18.99", "renew"=>"18.99", "transfer"=>"18.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".online", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"deal","tld_id_protection"=>"yes","tld"=>".org", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".org.nz", "register"=>"39.99", "renew"=>"39.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".org.tw", "register"=>"29.95", "renew"=>"29.95", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".org.uk", "register"=>"11.99", "renew"=>"11.99", "transfer"=>"11.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".osaka", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".paris", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".partners", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".parts", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".party", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".pe", "register"=>"79.95", "renew"=>"79.95", "transfer"=>"79.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".photo", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".photography", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".photos", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".pics", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".pictures", "register"=>"13.99", "renew"=>"13.99", "transfer"=>"13.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".pink", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".pizza", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".place", "register"=>"23.99", "renew"=>"23.99", "transfer"=>"23.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".plumbing", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".plus", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".poker", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".press", "register"=>"89.99", "renew"=>"89.99", "transfer"=>"89.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".pro", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".productions", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".properties", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".property", "register"=>"199.99", "renew"=>"199.99", "transfer"=>"199.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".pub", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".pw", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".quebec", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".racing", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".recipes", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".red", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".rehab", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".reise", "register"=>"119.99", "renew"=>"119.99", "transfer"=>"119.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".reisen", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".rent", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".rentals", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".repair", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".report", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".republican", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".rest", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".restaurant", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".review", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".reviews", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".rich", "register"=>"2999.99", "renew"=>"2999.99", "transfer"=>"2999.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".rip", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".rocks", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".ru.com", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".run", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".sa.com", "register"=>"279.99", "renew"=>"279.99", "transfer"=>"279.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".sale", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".sarl", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".school", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".schule", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".science", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".scot", "register"=>"49.95", "renew"=>"49.95", "transfer"=>"49.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".se.net", "register"=>"37.99", "renew"=>"37.99", "transfer"=>"37.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".services", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".sh", "register"=>"79.95", "renew"=>"79.95", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".shiksha", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".shoes", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".shop", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".show", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".singles", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".site", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".ski", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".soccer", "register"=>"26.99", "renew"=>"26.99", "transfer"=>"26.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".social", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".software", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".solar", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".solutions", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".soy", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".space", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".srl", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".store", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".studio", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".study", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".style", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".sucks", "register"=>"349.99", "renew"=>"349.99", "transfer"=>"349.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".supplies", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".supply", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".support", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".surgery", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".sydney", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".systems", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".tattoo", "register"=>"49.95", "renew"=>"49.95", "transfer"=>"49.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".tax", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".taxi", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".tc", "register"=>"159.99", "renew"=>"159.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".team", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".tech", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".technology", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".tel", "register"=>"14.95", "renew"=>"14.95", "transfer"=>"14.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".tennis", "register"=>"79.99", "renew"=>"79.99", "transfer"=>"79.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".theater", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".tienda", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".tips", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".tires", "register"=>"129.99", "renew"=>"129.99", "transfer"=>"129.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".today", "register"=>"29.99", "renew"=>"29.99", "transfer"=>"29.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".tools", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".top", "register"=>"9.99", "renew"=>"9.99", "transfer"=>"9.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".tours", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".town", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".toys", "register"=>"74.99", "renew"=>"74.99", "transfer"=>"74.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".trade", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".training", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".tv", "register"=>"44.95", "renew"=>"44.95", "transfer"=>"44.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".tw", "register"=>"29.99", "renew"=>"29.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"country","tld_id_protection"=>"no","tld"=>".uk", "register"=>"11.99", "renew"=>"11.99", "transfer"=>"11.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".uk.com", "register"=>"64.95", "renew"=>"64.95", "transfer"=>"64.95"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".uk.net", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".university", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".uno", "register"=>"27.99", "renew"=>"27.99", "transfer"=>"27.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".us", "register"=>"16.99", "renew"=>"16.99", "transfer"=>"16.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".us.com", "register"=>"24.99", "renew"=>"24.99", "transfer"=>"24.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".vacations", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".vegas", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".ventures", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".vet", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".vg", "register"=>"49.99", "renew"=>"49.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".viajes", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".video", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".villas", "register"=>"59.99", "renew"=>"59.99", "transfer"=>"59.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".vision", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".vote", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".voting", "register"=>"1399.99", "renew"=>"1399.99", "transfer"=>"1399.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".voto", "register"=>"99.99", "renew"=>"99.99", "transfer"=>"99.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".voyage", "register"=>"69.99", "renew"=>"69.99", "transfer"=>"69.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".wales", "register"=>"19.99", "renew"=>"19.99", "transfer"=>"19.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".watch", "register"=>"49.99", "renew"=>"49.99", "transfer"=>"49.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".webcam", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".website", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".wiki", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".win", "register"=>"34.99", "renew"=>"34.99", "transfer"=>"34.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".work", "register"=>"10.99", "renew"=>"10.99", "transfer"=>"10.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".works", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".world", "register"=>"44.99", "renew"=>"44.99", "transfer"=>"44.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".ws", "register"=>"39.99", "renew"=>"39.99", "transfer"=>""),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".wtf", "register"=>"39.99", "renew"=>"39.99", "transfer"=>"39.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".xyz", "register"=>"18.99", "renew"=>"18.99", "transfer"=>"18.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"no","tld"=>".za.com", "register"=>"279.99", "renew"=>"279.99", "transfer"=>"279.99"),
            array("expiry"=>"1 Years","categories"=>"generic","tld_id_protection"=>"yes","tld"=>".zone", "register"=>"42.95", "renew"=>"42.95", "transfer"=>"42.95")
        );
        return $tldData;
    }

    /**
     * 01-30-2025
     * 
     * Domain shortCode
     **/
    /*public static function emyui_display_domain_products($atts) {
        ob_start();
        $atts = shortcode_atts(array(
            'limit' => -1,
        ), $atts);
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => intval($atts['limit']),
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => 'domain',
                ),
            ),
        );

        $categories = [
            'deal'      => __('Free for You','emyui'),
            'country'   => __('Country domains','emyui'),
            'generic'   => __('Generic domains','emyui')
        ];

        $domains = [];
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $product;
                $tld = get_post_meta(get_the_ID(), '_tld_domain', true);
                $category = get_post_meta(get_the_ID(), '_tld_categories', true);
                if (!isset($domains[$category])) {
                    $domains[$category] = [];
                }
                $domains[$category][] = [
                    'tld' => $tld,
                    'product_id' => $product->get_id(),
                    'price'      => $product->get_regular_price()
                ];
            }
            wp_reset_postdata();
        }
        echo '<div id="emyui_domain_select_list" class="domains_list">';
            echo '<div class="emyui_domains_list_wrapper">';
                foreach ($categories as $key => $heading) {
                    if (!empty($domains[$key])) {
                        echo '<div class="emyui_domains_list_wrapper">';
                            echo '<h3>' . esc_html($heading) . '</h3>';
                            echo '<ul class="domain-list">';
                            $count = 0;
                            foreach ($domains[$key] as $domain) {
                                $count++;
                                $extra_class = ($count > 15) ? 'hidden-domain' : '';
                                echo '<li class="text--weight-m ' . esc_attr($extra_class) . '" data-tld="' . esc_attr($domain['tld']) . '" data-product_id="' . esc_attr($domain['product_id']) . '">
                                        <div class="tld-domain-price-wrap">
                                        <span class="tld-domain-span">' . esc_html($domain['tld']) . '</span>' . wc_price($domain['price']) . '
                                     </div>
                                </li>';
                            }
                            echo '</ul>';
                            if($count > 15) {
                                echo '<button class="domain-view-more-btn btn btn-primary">'.__('View More', 'emyui').'</button>';
                            }
                        echo '</div>';
                    }
                }
            echo '</div>';
        echo '</div>';
        return ob_get_clean();
    }*/

    public static function emyui_display_domain_products($atts) {
        ob_start();
        $atts = shortcode_atts(array(
            'limit' => -1,
        ), $atts);
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => intval($atts['limit']),
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => 'domain',
                ),
            ),
        );

        $categories = [
            'deal'      => __('Free for You','emyui'),
            'country'   => __('Country domains','emyui'),
            'generic'   => __('Generic domains','emyui')
        ];

        $domains = [];
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $product;
                $tld = get_post_meta(get_the_ID(), '_tld_domain', true);
                $category = get_post_meta(get_the_ID(), '_tld_categories', true);
                if (!isset($domains[$category])) {
                    $domains[$category] = [];
                }
                $domains[$category][] = [
                    'tld' => $tld,
                    'product_id' => $product->get_id(),
                    'price'      => $product->get_regular_price()
                ];
            }
            wp_reset_postdata();
        }
        ?>
        <div class="content-widget">
            <div class="stats-wrapper mt-0">
                <div class="row">
                    <?php
                    $first = true; 
                    foreach ($categories as $key => $heading) {
                        if (!empty($domains[$key])) {
                            echo '<div class="col-md-12"><h3>' . esc_html($heading) . '</h3></div>';
                            echo '<div class="row row-width">';
                            $count = 0;
                            foreach ($domains[$key] as $domain) {
                                $count++;
                                $extra_class = ($count > 16) ? 'hidden-domain' : '';
                                $active_class = ($first && $count == 1) ? 'emyui-active' : '';
                                ?>
                                <div class="col-md-3 <?php echo esc_attr($extra_class); ?>">
                                    <a href="javascript:void(0);" data-tld="<?php echo esc_attr($domain['tld']); ?>" class="<?php echo esc_attr($active_class); ?> emyui-select-domain" data-product_id="<?php echo esc_attr($domain['product_id']); ?>">
                                        <div class="box-with-shadow numberbox border-opacity">
                                            <h3 class="title"><?php echo esc_html($domain['tld']); ?></h3>
                                            <p class="sub-text mb-0"><?php echo wc_price($domain['price']); ?></p>
                                        </div>
                                    </a>
                                </div>
                                <?php
                            }
                            echo '</div>';
                            if($count > 15) {
                                echo '<button class="domain-view-more-btn btn btn-primary">'.__('View More', 'emyui').'</button>';
                            }
                            $first = false;
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * 02-05-2025
     * 
     * Domain Search ShortCode
     **/
    public static function emyui_display_domain_search(){
         ob_start();
            require_once(EMUI_VIEWS.'/domain.php');
         $output = ob_get_clean();
         return $output; 
    }
}
EMYUI_domain_Product::init();