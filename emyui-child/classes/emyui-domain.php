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

        add_filter('product_type_selector', array(__CLASS__, 'emyui_add_domain_product_type'));
        add_action('woocommerce_process_product_meta', array(__CLASS__, 'emyui_save_domain_fields'));
        add_filter('woocommerce_product_data_tabs', array(__CLASS__, 'emyui_add_domain_options_tab'));
        add_action('woocommerce_product_data_panels', array(__CLASS__, 'emyui_add_domain_fields'));
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
        unset($tabs['attribute']);
        $tabs['package_options'] = [
            'label'    => __('domain Options', 'emyui'),
            'target'   => 'doamin_options_data',
            'class'    => ['show_if_domain'],
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
    public static function emyui_add_domain_product_type($types) {
        $types['domain'] = __('Domain', 'emyui');
        return $types;
    }

    /**
     * 18-12-2024
     * 
     * Add custom fields for Package product type in the admin.
     */

    public static function emyui_add_domain_fields() {
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
            echo '</div>';
        echo '</div>';
    }

    /**
     * 18-12-2024
     * Save custom fields for Package product type.
     *
     * @param int $post_id
     */
    public static function emyui_save_domain_fields($post_id) {
        $fields = [
            '_tld_id_protection',
            '_tld_domain',
            '_tld_renew',
            '_tld_register',
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
}
EMYUI_Package_Product::init();