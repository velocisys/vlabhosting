<div class="emyui-packages-main">  
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
                <div class="col-lg-4 col-12 pl-lg-15">
                    <div class="ddos-attack-package shadow-2">
                        <span class="ddos-attack-package-head coodiv-text-12">the most The most frequently chosen package</span>
                        <h2 class="coodiv-text-9 mb-0"><?php echo $formatted_title; ?></h2>
                        from
                        <div class="ddos-attack-price d-flex justify-content-between align-items-center mt-7 py-4">
                            <h2 class="coodiv-text-4"><?php echo wc_price($price); ?></h2>offer -30%
                        </div>
                        <?php 
                        $short_description = apply_filters('woocommerce_short_description', get_the_excerpt());
                        echo $short_description;
                        ?>
                        <button type="button" class="btn btn-outline-dark d-block w-100 coodiv-text-12" name="package" value="<?php echo $product_id; ?>"><?php _e('GET STARTED', 'emyui'); ?></button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </form>
</div>