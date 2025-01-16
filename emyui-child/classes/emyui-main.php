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
}
emyui_main::instance();
