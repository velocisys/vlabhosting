<div class="header-hero-pricing-plans position-relative coodiv-z-index-2">
    <div class="container">
        <form method="post" id="emyui-packages" name="emyui_package">
            <div class="row justify-content-center">
                <?php while ($products->have_posts()) : $products->the_post(); ?>
                    <?php 
                    $product_id         = get_the_ID();
                    $title              = get_the_title($product_id);
                    $title_with_spaces  = str_replace('_', ' ', $title);
                    $formatted_title    = ucwords($title_with_spaces);
                    ?>
                    <?php $price = get_post_meta($product_id, '_price', true); ?>
                    <div class="col-md-12 col-lg-4">
                        <div class="pricing-plans-special border rounded-10 pt-10 pb-8 px-9 white-bg shadow-2 h-100">
                            <div class="pricing-plans-special-header d-flex justify-content-between align-items-center">
                                <h5 class="coodiv-text-6 mb-0"><?php echo $formatted_title; ?></h5>
                            </div>
                            <?php 
                                $short_description = apply_filters('woocommerce_short_description', get_the_excerpt());
                                echo $short_description;
                            ?>
                            <div class="pricing d-flex align-items-center mt-10 mb-9">
                                <span class="coodiv-text-3 font-weight-bold color-blackish-blue mr-4"><?php echo wc_price($price); ?></span>
                                <span class="coodiv-text-12 line-height-20"><?php _e('per month', 'emyui'); ?></span>
                            </div>
                            <button type="button" class="btn btn-outline-dark d-block w-100 coodiv-text-12" name="package" value="<?php echo $product_id; ?>"><?php _e('GET STARTED', 'emyui'); ?></button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </form>
    </div>
</div>