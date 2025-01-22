<?php
class emyui_api{
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
    add_action('admin_init', array($this, 'emyui_create_or_update_package'));
    add_action('init', array($this, 'emyui_get_domain_whois_data1'));
    add_action( 'woocommerce_order_status_completed', array($this, 'emyui_order_status_completed' ));
    //add_action( 'woocommerce_payment_complete', array($this, 'emyui_order_status_completed' ));
  }

  /**
   * 18-12-2024
   * 
   * Fetch packages from WHM API
   * @return array|WP_Error List of packages or error
   **/
  public function emyui_get_whm_packages() {
    $whm_username = 'vlabhost';
    $whm_token    = 'KGSH9ZQK5P1AGLDD7GN3NJTICWMA6OVL';
    $server_url   = 'https://mi3-tr105.supercp.com:2087';
    $endpoint     = 'listpkgs';
    $url  = $server_url . '/json-api/' . $endpoint;
    $args = [
      'headers' => [
        'Authorization' => 'whm ' . $whm_username . ':' . $whm_token,
      ],
      'sslverify' => false,
    ];
    $response = wp_remote_get($url, $args);
    if(is_wp_error($response)){
      return $response;
    }
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if(isset($data['package']) && is_array($data['package'])) {
        return $data['package'];
    }
    return new WP_Error('whm_api_error', 'Failed to retrieve packages', $data);
  }

  /**
   * 21-12-2024
   * 
   * Create WooCommerce products based on the WHM packages
   **/
  public function emyui_create_or_update_package() {
    $packages = $this->emyui_get_whm_packages();
    if(is_wp_error($packages)){
      error_log( 'Error fetching WHM packages: ' . $packages->get_error_message() );
      return;
    }
    if(!empty($packages)){
      foreach( $packages as $package ){
        $existing_product_id = wc_get_product_id_by_sku( $package['name'] );
        if($existing_product_id){
          $product = wc_get_product($existing_product_id);
          if($product){
            $product->set_name( $package['name'] );
            //$product->set_regular_price( $package['price'] ?? '0' );
            $product->set_description( $package['description'] ?? '' );
            $product->save();
            wp_set_object_terms($existing_product_id, 'package', 'product_type');
            $this->emyui_update_package_meta($existing_product_id, $package);
          }
          error_log( "Package product updated: {$package['name']} (ID: $existing_product_id)" );
        }else{
          $product = new WC_Product_Simple();
          $product->set_name( $package['name'] );
          $product->set_sku( $package['name'] );
          //$product->set_regular_price( $package['price'] ?? '0' );
          $product->set_description( $package['description'] ?? '' );
          $product_id = $product->save();
          wp_set_object_terms($product_id, 'package', 'product_type');
          $this->emyui_update_package_meta($product_id, $package);
          error_log( "Package product created: {$package['name']} (ID: $product_id)" );
        }
      }
    }
  }

  /**
   * Update custom meta fields for a package product.
   *
   * @param int   $product_id
   * @param array $package
   **/
    private function emyui_update_package_meta($product_id, $package) {
      $meta_fields = [
        '_package_quota'                => $package['QUOTA'] ?? '',
        '_package_maxpassengerapps'     => $package['MAXPASSENGERAPPS'] ?? '',
        '_package_maxftp'               => $package['MAXFTP'] ?? '',
        '_package_max_email_acct_quota' => $package['MAX_EMAILACCT_QUOTA'] ?? '',
        '_package_max_lst'              => $package['MAXLST'] ?? '',
        '_package_bwlimit'              => $package['BWLIMIT'] ?? '',
        '_package_maxaddon'             => $package['MAXADDON'] ?? '',
        '_package_maxsql'               => $package['MAXSQL'] ?? '',
        '_package_maxpop'               => $package['MAXPOP'] ?? '',
        '_package_maxpark'              => $package['MAXPARK'] ?? '',
        '_package_maxsub'               => $package['MAXSUB'] ?? '',
        '_package_max_team_users'       => $package['MAX_TEAM_USERS'] ?? '',
      ];
      foreach ($meta_fields as $meta_key => $meta_value) {
        update_post_meta($product_id, $meta_key, sanitize_text_field($meta_value));
      }
    }

    /**
     * 01-07-2025
     * 
     * Domain API implement
     **/
      public function emyui_get_domain_whois_data($domain) {
        $domainArr  = [];
        $api_key    = '5D4E1EDD4D6031FC184049BB0A1423A6';
        $api_url    = "https://api.ip2whois.com/v2?key={$api_key}&domain={$domain}";
        $response   = wp_remote_get($api_url);
        if(is_wp_error($response)){
          $domainArr['error'] = $response->get_error_message();
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if(isset($data['error']['error_message'])){
          $domainArr['error'] =  $data['error']['error_message'];
        }else{
          $domainArr['domain_name'] = $data['domain'];
          $domainArr['domain_id']   = $data['domain_id'];
          $domainArr['response']   = $data;
        }
        return $domainArr;
      }

      /**
       * 01-21-2025
       * 
       * Create a account WHM API
       **/
      public function emyui_create_whm_account($username, $domain, $password, $contactemail, $plan = 'default', $featurelist = 'default', $quota = 5000) {
        $whm_server   = "mi3-tr105.supercp.com";
        $api_endpoint = "https://$whm_server:2087/json-api/createacct?api.version=1";
        $auth_token   = "dmxhYmhvc3Q6OClBaWUqN1hXbDd0Qjg=";
        $data = [
          'username'      => $username,
          'domain'        => $domain,
          'password'      => $password,
          'contactemail'  => $contactemail,
          'plan'          => $plan,
          'featurelist'   => $featurelist,
          'quota'         => $quota,
        ];
        $headers = [
          'Authorization' => 'Basic ' . $auth_token,
          'Content-Type'  => 'application/x-www-form-urlencoded',
        ];
        $response = wp_remote_post($api_endpoint, [
          'headers' => $headers,
          'body'    => $data,
          'timeout' => 30,
        ]);
        $output = [
          'success' => false,
          'message' => '',
        ];
        if(is_wp_error($response)) {
          $output['message'] = 'HTTP Error: ' . $response->get_error_message();
          return $output;
        }
        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body, true);
        if(isset($result['metadata']['result']) && $result['metadata']['result'] == 1) {
          $output['success'] = true;
          $output['message'] = 'Account created successfully for username: ' . $username;
        }else{
          $output['message'] = isset($result['metadata']['reason']) ? $result['metadata']['reason'] : 'Unknown error occurred.';
        }
        return $output;
      }

    /**
     * 01-22-2025
     * 
     * Order status completed callback
     **/
    public function emyui_order_status_completed($order_id){
        $order            = wc_get_order( $order_id );
        $customer_id      = $order->get_customer_id();
        $password         = wp_generate_password(12, true);
        $username         = sanitize_user($order->get_billing_first_name() . $order->get_billing_last_name());
        $contactemail     = sanitize_email($order->get_billing_email());
        $plan             = 'default'; 
        $featurelist      = 'default'; 
        $quota            = 5000;
        $domain = '';
        foreach ($order->get_items() as $item) {
            $line_item_domain = $item->get_meta('domain_name');
            if(!empty($line_item_domain)) {
                $domain = sanitize_text_field($line_item_domain);
                break;
            }
        }
        $customer_account = $this->emyui_create_whm_account($username, $domain, $password, $contactemail, $plan, $featurelist, $quota);
    }

    public function emyui_get_domain_whois_data1(){
      if(isset($_GET['test']) && $_GET['test'] == 1){
          $data = $this->emyui_get_domain_whois_data('vlabhosting.com');
          echo '<pre>';
          print_r($data);
          die();
      }
    }
}
emyui_api::instance();