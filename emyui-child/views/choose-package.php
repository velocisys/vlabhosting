<div class="emyui-packages-main">  
    <form method="post" id="emyui-packages" name="emyui_package" action="/package-submit">
        <?php wp_nonce_field( 'emyui_package_submission', 'emyui_package_nonce' ); ?>
        <div class="container">
            <div class="row align-items-center justify-content-center">   
                <?php while ($products->have_posts()) : $products->the_post(); ?>
                    <?php
                    $featured_class         = '';
                    $not_features           = '';
                    $featured_package_text  = '';
                    $package_offer_text     = ''; 
                    $product_id             = get_the_ID();
                    $product                = wc_get_product( $product_id );
                    $title                  = get_the_title($product_id);
                    $title_with_spaces      = str_replace('_', ' ', $title);
                    $formatted_title        = ucwords($title_with_spaces);
                    $package_offer_text     = get_post_meta($product_id, '_package_offer_text', true);
                    $featured_package_text  = get_post_meta($product_id, '_featured_package_text', true);
                    if($featured_package_text){
                        $featured_package_text  = sprintf('<span class="ddos-attack-package-head coodiv-text-12">%s</span>', $featured_package_text);
                        $featured_class  = 'emyui-featured';
                    }else{
                        $not_features    = 'not-features';
                    }
                    ?>
                    <div class="col-lg-4 col-12 mb-lg-0 mb-15">
                       <div class="ddos-attack-package vpn-version shadow-2 <?php echo $featured_class; ?> <?php echo $not_features; ?>">
                            <?php echo $featured_package_text; ?>
                            <h3 class="coodiv-text-9 mb-0"><?php echo $formatted_title; ?></h3>
                            <div class="ddos-attack-price d-flex justify-content-between align-items-center mt-7 py-4">
                                <?php echo $product->get_price_html(); ?>
                                 <?php 
                                 if($package_offer_text){
                                    ?>
                                        <span><?php echo $package_offer_text; ?></span>
                                    <?php
                                 }
                                 ?>
                            </div>
                            <?php 
                                $short_description = apply_filters('woocommerce_short_description', get_the_excerpt());
                                echo $short_description;
                            ?>
                            <button type="submit" class="btn btn-outline-dark d-block w-100 coodiv-text-12" name="submit_package" value="<?php echo $product_id; ?>"><?php _e('GET STARTED', 'emyui'); ?></button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </form>
</div>