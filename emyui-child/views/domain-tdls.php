<?php
$domains = emyui_domain($domain_tlds);
?>
<div class="row domain-box-sale-header aos-init aos-animate emyui-suggest-domain">
    <div class="flex flex--direction-column flex--gap-s">
        <div>
            <strong><?php _e('Important!', 'emyui'); ?></strong>
            <p><?php _e('The domain name you have chosen is not available. Please try a new domain name search in the domain name box above.', 'emyui'); ?></p>
        </div>
        <ul id="domain_suggestions" class="flex flex--direction-column flex--gap-s">
            <?php
                if(is_array($domains) && !empty($domains)): 
                    foreach ($domains as $suffix => $tld): 
                    ?>
                    <li>
                        <label>
                            <input name="domain_suggest_radio" class="domainSelectionRadio radio field field--radio" type="radio" value="<?php echo $domainsearch . $suffix; ?>" data-tdls="<?php echo $suffix; ?>">
                            <?php echo $domainsearch . $suffix; ?> is <span class="cgreen"><?php _e('available', 'emyui'); ?></span>
                        </label>
                    </li>
                <?php 
                endforeach;
            endif;
        ?>
        </ul>
    </div>
</div>