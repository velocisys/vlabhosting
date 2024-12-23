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
    //add_action('admin_init', array($this, 'emyui_create_or_update_woocommerce_package_products'));
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
    if(!isset($data['error_data']['whm_api_error']['package']) || $data['error_data']['whm_api_error']['package'] !== 1){
      return new WP_Error('whm_api_error', 'Failed to retrieve packages', $data);
    }
    return $data['data'];
  }

/**
 * 18-12-2024
 * 
 * Display WHM packages in a WordPress admin page
 **/
  function display_whm_packages_admin_page() {
      $packages = $this->emyui_get_whm_packages();
      if(is_wp_error($packages)){
          echo '<div class="notice notice-error"><p>' . esc_html($packages->get_error_message()) . '</p></div>';
          return;
      }
      echo '<h1>WHM Packages</h1>';
      echo '<table class="widefat striped">';
      echo '<thead><tr><th>Package Name</th><th>Disk Space</th><th>Bandwidth</th></tr></thead>';
      echo '<tbody>';
      foreach ($packages as $package) {
          echo '<tr>';
          echo '<td>' . esc_html($package['name']) . '</td>';
          echo '<td>' . esc_html($package['diskquota']) . '</td>';
          echo '<td>' . esc_html($package['bandwidth']) . '</td>';
          echo '</tr>';
      }
      echo '</tbody>';
      echo '</table>';
  }

  /**
   * 21-12-2024
   * 
   * Create WooCommerce products based on the WHM packages
   **/
  public function emyui_create_or_update_woocommerce_package_products() {
    $packages = $this->emyui_get_whm_packages();
    echo '<pre>';
    print_r( $packages );
    die;
    if(is_wp_error($packages)){
      error_log( 'Error fetching WHM packages: ' . $packages->get_error_message() );
      return;
    }
    if(!empty($packages)){
      foreach( $packages as $package ){
        $existing_product_id = wc_get_product_id_by_sku( $package['name'] );
        if($existing_product_id){
          $product = wc_get_product( $existing_product_id );
          if( $product->get_type() !== 'package' ){
            $product->set_type( 'package' );
          }
          $product->set_name( $package['name'] );
          $product->set_regular_price( $package['price'] ?? '0' );
          $product->set_description( $package['description'] ?? 'No description available' );
          $product->save();
          update_post_meta( $existing_product_id, '_package_disk_space', $package['diskquota'] );
          update_post_meta( $existing_product_id, '_package_bandwidth', $package['bandwidth'] );
          error_log( "Package product updated: {$package['name']} (ID: $existing_product_id)" );
        }else{
          $product = new EMYUI_Package_Product();
          $product->set_name( $package['name'] );
          $product->set_sku( $package['name'] );
          $product->set_regular_price( $package['price'] ?? '0' );
          $product->set_description( $package['description'] ?? 'No description available' );
          $product_id = $product->save();
          update_post_meta( $product_id, '_package_disk_space', $package['diskquota'] );
          update_post_meta( $product_id, '_package_bandwidth', $package['bandwidth'] );
          error_log( "Package product created: {$package['name']} (ID: $product_id)" );
        }
      }
    }
  }
}
emyui_api::instance();