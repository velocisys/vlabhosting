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
      add_shortcode('wc_login_logout_list', array($this, 'wc_login_logout_list_shortcode'));
  }

  /**
   * 01-14-2025
   * 
   * Login/Logout shortcode
   **/
  public function wc_login_logout_list_shortcode(){
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
}
emyui_main::instance();