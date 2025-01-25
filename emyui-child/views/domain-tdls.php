<?php
$domains = emyui_domain($domain_tlds);
?>
<div class="availability-message emyui-suggest-domain">
  <div class="emyui-availability-info">
    <div class="emyui-info-wrap">
        <img src="<?php echo EMUI_IMAGES.'/alert-circle.svg'; ?>">
        <strong><?php _e('Important!', 'emyui'); ?></strong> 
    </div>
    <p class="emyui-message-info">
        <?php _e('The domain name you have chosen is not available. Please try a new domain name. Here are some suggestions:', 'emyui'); ?>
    </p>
  </div>
  <ul class="domain-suggestion-list">
        <?php
        if(is_array($domains) && !empty($domains)): 
            foreach ($domains as $suffix => $tld):
                $label_suffix = str_replace('.', '', $suffix); 
                ?>
                <li>
                    <label for="<?php echo $label_suffix; ?>">
                        <input type="radio" name="domain_suggest_radio" class="domainSelectionRadio" id="<?php echo $label_suffix; ?>" value="<?php echo $domainsearch . $suffix; ?>" data-tdls="<?php echo $suffix; ?>">
                        <?php 
                            echo sprintf('<span><strong>%s is</strong> <span class="emyui-available">%s</span></span>',$domainsearch . $suffix,__('available', 'emyui'));
                        ?>
                    </label>
                </li>
                <?php 
            endforeach;
        endif;
        ?>
    </ul>
</div>