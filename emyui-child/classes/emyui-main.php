<?php
class emyui_main{
  private static $_instance = null;
  public static function instance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }


  /**
   * 18-12-2024
   * 
   * Constructor call
   **/
  public function __construct(){
      add_shortcode('wc_login_logout_list', array($this, 'emyui_login_logout_list_shortcode'));
      add_action( 'template_redirect', array($this,'emyui_redirection_template'));
      add_filter('woocommerce_settings_tabs_array', array($this, 'emyui_woocommerce_settings_tab'), 50);
      add_action('woocommerce_settings_vlab-tab', array($this, 'emyui_woocommerce_settings_tab_content'));
      add_action('woocommerce_update_options_vlab-tab', array($this, 'emyui_woocommerce_update_settings'));
  }

   /**
    * 01-12-2025
    * 
    * Check cart contain package.
    */
    public function emyui_check_cart_has_package() {
      if ( !is_admin() ) {
        $cart_items = WC()->cart->get_cart();
        if ( !empty( $cart_items ) ) {
          foreach ( $cart_items as $cart_item_key => $cart_item ) {
            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            if ( $_product->is_type( 'package' ) ) {
              return true;
            }
          }
        }
      }
    }

  /**
   * 01-14-2025
   * 
   * Login/Logout shortcode
   **/
  public function emyui_login_logout_list_shortcode(){
    $output = '';
    if(is_user_logged_in()){
        $current_user   = wp_get_current_user();
        $user_name      = $current_user->display_name;
        $logout_url     = wp_logout_url(home_url());
        $my_account_url = wc_get_page_permalink('myaccount');
        $output = sprintf(
            '<ul class="wc-login-logout-list emyui-login-logout">
                <li class="user-name-menu">
                    <span class="user-name">Hi, <strong>%s</strong></span>
                    <ul class="dropdown-menu">
                        <li><a href="%s">My Account</a></li>
                        <li><a href="%s">Logout</a></li>
                    </ul>
                </li>
            </ul>',
            esc_html($user_name),
            esc_url($my_account_url),
            esc_url($logout_url)
        );
    }else{
      $login_url = wc_get_page_permalink('myaccount');
      $output = sprintf('<ul class="wc-login-logout-list emyui-login-logout">
        <li class="user-name-menu"><a href="%s">Login</a></li>
        </ul>', esc_url($login_url));
    }
    return $output;
  }

  /**
   * 01-17-2025
   * 
   * Template redirect
   **/
  public function emyui_redirection_template(){
    if(is_shop() || is_product_category() || is_product() || is_product_tag()){
      wp_safe_redirect(home_url());
      exit;
    }
    if(WC()->cart->is_empty()){
      setcookie("plan_price", "", time()-(60*60*24*7),"/");
      setcookie("plan_offer", "", time()-(60*60*24*7),"/");
    }
  }

  /**
   * 01-17-2025
   * 
   * Set a WooCommerce transient notice for the frontend.
   * @param string $key Unique key for the transient.
   * @param string $message The message to display in the notice.
   * @param string $type The type of notice (success, error, notice).
   * @param int $duration Expiration time for the transient in seconds.
   */
  public function emyui_set_woocommerce_notice_transient($key, $message, $type = 'notice', $duration = HOUR_IN_SECONDS) {
      $notice_data = [
          'message' => $message,
          'type'    => $type,
      ];
      set_transient($key, $notice_data, $duration);
  }

  /**
   * 01-17-2025
   * 
   * Display WooCommerce transient notice on the frontend.
   * @param string $key Unique key for the transient.
   */
  public function emyui_display_woocommerce_notice_transient($key) {
      $notice = get_transient($key);
      if($notice && is_array($notice)){
          $type = isset($notice['type']) ? $notice['type'] : 'notice';
          $message = isset($notice['message']) ? $notice['message'] : '';
          if($message){
              wc_add_notice($message, $type);
              delete_transient($key);
          }
      }
  }

  /**
   * 10-16-2024
   * 
   * Package in dropdown
   **/
  public  function emyui_get_package_dropdown($product_id, $hosting_plan) {
      $product = wc_get_product($product_id);
      $dropdown_html = '';
      $args = array(
        'post_status'    => 'publish',
        'order'          => 'DESC',
        'orderby'        => 'menu_order',
        'posts_per_page' => -1,
        'meta_query'     => array(
          array(
            'key'     => '_package_hosting_plan',
            'compare' => $hosting_plan
          ),
        )
      );
      if($product && $product->is_type('package')){
        $args['tax_query'][] = array(
          'taxonomy' => 'product_type',
          'field'    => 'slug',
          'terms'    => 'package',
        );
      }
      $query = new WP_Query( $args );
      if ( $query->have_posts() ) {
        $dropdown_html  = '<div class="emyui_package_wrap"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none;"></span><span class="sr-only">Loading...</span>';
        $dropdown_html .= sprintf('<select name="emyui_package" class="emyui_package" id="emyui_package">');
        while ( $query->have_posts() ) {
          $query->the_post();
          $current_product_id = get_the_ID();
          $product = wc_get_product($current_product_id);
          if($product){
            $product_price = $product->get_price();
            $selected = ($current_product_id == $product_id) ? 'selected' : '';
            $producttitle  = str_replace('Select Package', '', preg_replace("/-/", "", trim($product->get_title()))); 
            $dropdown_html .= sprintf('<option value="%d" %s>%s</option>', $current_product_id, $selected, $producttitle);
          }
        }
        wp_reset_postdata();
        $dropdown_html .= '</select>';
        $dropdown_html .= '</div>';
      }
      return $dropdown_html;
    }

    /**
     * 01-20-2025
     * 
     * Create a custom tab
     **/
    public function emyui_woocommerce_settings_tab($settings_tabs) {
        $settings_tabs['vlab-tab'] = __('Vlab Hosting Settings', 'emyui');
        return $settings_tabs;
    }

    /**
     * 01-20-2025
     * 
     * Settings tab content
     **/
    public function emyui_woocommerce_settings_tab_content() {
      $saved_fields = get_option('emyui_data_center', []);
      require_once(EMUI_VIEWS.'/data-center.php');
    }

    /**
     * 01-20-2025
     * 
     * Settings tab update
     **/
    public function emyui_woocommerce_update_settings() {
      if(isset($_POST['emyui_data_center']) && is_array($_POST['emyui_data_center'])){
        $non_empty_fields = array_filter($_POST['emyui_data_center'], function($value){
          return !empty(trim($value));
        });
        if(!empty($non_empty_fields)){
          $sanitized_fields = array_map('sanitize_text_field', $non_empty_fields);
          update_option('emyui_data_center', $sanitized_fields);
        }else{
          delete_option('emyui_data_center');
        }
      }else{
        delete_option('emyui_data_center');
      }
      $options = [
        'whm_user_name' => 'whm_user_name',
        'whm_token' => 'whm_token',
        'whm_server_url' => 'whm_server_url',
        'ip2whois_api_key' => 'ip2whois_api_key',
      ];
      foreach ($options as $option_key => $post_field){
        if(isset($_POST[$post_field]) && !empty($_POST[$post_field])) {
          update_option($option_key, sanitize_text_field($_POST[$post_field]));
        }else{
          delete_option($option_key);
        }
      }
    }

    /**
     * 01-25-2024
     * 
     * 
     * is valid domain name
     **/
    public function emyui_is_valid_domain_name($domain_name){
        if(strpos($domain_name, " ") !== false) {
            return false;
        }
        if(strlen($domain_name) > 253) {
            return false;
        }
        if(preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $domain_name)){
            return false;
        }
        return true;
    }
}
emyui_main::instance();