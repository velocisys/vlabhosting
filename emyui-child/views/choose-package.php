<div class="emyui-packages-main">  
    <form method="post" id="emyui-packages" name="emyui_package" action="/package-submit">
        <?php wp_nonce_field( 'emyui_package_submission', 'emyui_package_nonce' ); ?>
        <div class="container">
          <div class="row align-items-center justify-content-center packages-row">
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
                <div class="col-md-6 col-lg-4 col-12 mb-0">
                    <div class="package-card bg-white not-features position-relative <?php echo $featured_class; ?> <?php echo $not_features; ?>">
                        <?php echo $featured_package_text; ?>
                        <?php 
                        if($package_offer_text){
                            ?>
                            <span class="badge position-absolute"><?php echo $package_offer_text; ?></span>
                            <?php
                        }
                        ?>
                        <h3><?php echo $formatted_title; ?></h3>
                        <div class="d-flex gap-2 align-items-center price-group-wrap">
                            <div class="d-flex price-group">
                                <?php echo $product->get_price_html(); ?>
                            </div>
                        </div>
                        <div class="details-text">
                              Limited-time offer. Save big with this amazing discount!
                        </div>
                        <?php 
                            $short_description = apply_filters('woocommerce_short_description', get_the_excerpt());
                            echo $short_description;
                        ?>
                        <button type="submit" class="d-block w-100  btn-action-primary" name="submit_package" value="<?php echo $product_id; ?>"><?php _e('GET STARTED', 'emyui'); ?></button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </form>
</div>