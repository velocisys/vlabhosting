<?php 
foreach ($plan_meta as $key => $plan_value) {
    $plan_name      = isset($plan_value['plan_name']) ? sanitize_text_field($plan_value['plan_name']) : '';
    $plan_price     = isset($plan_value['plan_price']) ? sanitize_text_field($plan_value['plan_price']) : '';
    $plan_offer     = isset($plan_value['plan_offer']) ? sanitize_text_field($plan_value['plan_offer']) : '';
    $plan_default   = isset($plan_value['plan_default']) ? sanitize_text_field($plan_value['plan_default']) : '';
    if($plan_default == 'yes'){
        $plan_default_check = 'checked';
    }else{
        $plan_default_check = '';
    }
    ?>
    <div class="emyui-hosting-plan-main" data-id="<?php echo $key; ?>">
        <p class="form-field _hosting_plan_defaul_<?php echo $key; ?>_field ">
            <label for="_hosting_plan_defaul_<?php echo $key; ?>"><?php _e('Hosting Plan Default', 'emyui'); ?></label>
            <input type="checkbox" name="_hosting_plan[<?php echo $key; ?>][plan_default]" id="_hosting_plan_defaul_<?php echo $key; ?>" value="yes" class="checkbox" <?php echo $plan_default_check; ?>>
            <span class="description"><?php _e('Mark this as the default hosting plan', 'emyui'); ?></span>
        </p>
        <p class="form-field _hosting_plan_name_<?php echo $key; ?>_field">
            <label for="_hosting_plan_name_<?php echo $key; ?>"><?php _e('Hosting Plan Name', 'emyui'); ?></label>
            <input type="text" class="short" name="_hosting_plan[<?php echo $key; ?>][plan_name]" id="_hosting_plan_name_<?php echo $key; ?>" value="<?php echo $plan_name; ?>" placeholder="<?php _e('Enter hosting plan name', 'emyui'); ?>">
            <button type="button" class="remove-hosting-plan"><span class="dashicons dashicons-trash"></span></button>
        </p>
        <p class="form-field _hosting_plan_price_<?php echo $key; ?>_field">
            <label for="_hosting_plan_price_<?php echo $key; ?>"><?php _e('Hosting Plan Price', 'emyui'); ?></label>
            <input type="text" class="short" name="_hosting_plan[<?php echo $key; ?>][plan_price]" id="_hosting_plan_price_<?php echo $key; ?>" value="<?php echo $plan_price; ?>" placeholder="<?php _e('Enter hosting plan price', 'emyui'); ?>">
        </p>
        <p class="form-field _hosting_plan_offer_text_<?php echo $key; ?>_field">
            <label for="_hosting_plan_offer_text_<?php echo $key; ?>"><?php _e('Offer Text', 'emyui'); ?></label>
            <textarea class="short" name="_hosting_plan[<?php echo $key; ?>][plan_offer]" id="_hosting_plan_offer_text_<?php echo $key; ?>" placeholder="<?php _e('Enter offer text', 'emyui'); ?>" rows="2" cols="20"><?php echo $plan_offer; ?></textarea>
        </p>
    </div>
    <?php
}
?>