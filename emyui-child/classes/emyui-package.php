<?php
/**
 * 18-12-2024
 * 
 * Class EMYUI_Package_Product
 * Registers a custom WooCommerce product type: Package
 */
class EMYUI_Package_Product extends WC_Product {
    private static $initialized = false;

    /**
     * Initialize hooks and filters.
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }

        add_filter('product_type_selector', array(__CLASS__, 'emyui_add_package_product_type'));
        add_action('woocommerce_product_options_general_product_data', array(__CLASS__, 'emyui_add_package_fields'));
        add_action('woocommerce_process_product_meta', array(__CLASS__, 'emyui_save_package_fields'));
        add_action('woocommerce_single_product_summary', array(__CLASS__, 'emyui_display_package_fields'), 20);
        self::$initialized = true;
    }

    /**
     * 18-12-2024
     * 
     * Register the custom product type: Package.
     */
    public function __construct($product = null) {
        parent::__construct($product);
        $this->set_props([
            'type' => 'package',
        ]);
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
        echo '<div class="options_group show_if_package">';
            woocommerce_wp_text_input([
                'id'          => '_package_disk_space',
                'label'       => __('Disk Space (MB)', 'emyui'),
                'description' => __('Enter the disk space included in this package.', 'emyui'),
                'type'        => 'number',
                'desc_tip'    => true,
            ]);
            woocommerce_wp_text_input([
                'id'          => '_package_bandwidth',
                'label'       => __('Bandwidth (MB)', 'emyui'),
                'description' => __('Enter the bandwidth included in this package.', 'emyui'),
                'type'        => 'number',
                'desc_tip'    => true,
            ]);
        echo '</div>';
    }

    /**
     * 18-12-2024
     * Save custom fields for Package product type.
     *
     * @param int $post_id
     */
    public static function emyui_save_package_fields($post_id) {
        $fields = ['_package_disk_space', '_package_bandwidth'];
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
            $disk_space = self::get_package_meta($product->get_id(), '_package_disk_space');
            $bandwidth  = self::get_package_meta($product->get_id(), '_package_bandwidth');
            if ($disk_space || $bandwidth) {
                echo '<div class="woocommerce-package-details">';
                echo '<p><strong>' . __('Disk Space:', 'emyui') . '</strong> ' . esc_html($disk_space) . ' MB</p>';
                echo '<p><strong>' . __('Bandwidth:', 'emyui') . '</strong> ' . esc_html($bandwidth) . ' MB</p>';
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
}

EMYUI_Package_Product::init();
