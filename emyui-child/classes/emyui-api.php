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
            $product->set_regular_price( $package['price'] ?? '0' );
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
          $product->set_regular_price( $package['price'] ?? '0' );
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
}
emyui_api::instance();