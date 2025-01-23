<h2><?php _e('Vlab Hosting Data Center', 'emyui'); ?></h2>
<table class="form-table">
    <tr valign="top">
        <td class="forminp">
            <div id="emyui-fields-container">
                <label><?php _e('Add Data Center Fields', 'emyui'); ?></label>
                <?php if (!empty($saved_fields)) : ?>
                    <?php foreach ($saved_fields as $index => $value) : ?>
                        <div class="emyui-field">
                            <input type="text" name="emyui_data_center[]" value="<?php echo esc_attr($value); ?>" placeholder="Enter value">
                            <button type="button" class="button remove-emyui-field">
                              <span class="dashicons dashicons-remove"></span>   
                          </button>
                      </div>
                  <?php endforeach; ?>
              <?php endif; ?>
          </div>
          <button type="button" class="button" id="add-emyui-field"><?php _e('Add Field', 'emyui'); ?></button>
      </td>
  </tr>
</table>
<h2><?php _e('Vlab Hosting API Settings', 'emyui'); ?></h2>
<table class="form-table">
    <?php 
    $whm_user_name      = get_option('whm_user_name');
    $whm_token          = get_option('whm_token');
    $whm_server_url     = get_option('whm_server_url');
    $ip2whois_api_key   = get_option('ip2whois_api_key');
    ?>
    <tr valign="top">
        <td class="forminp">
            <div id="emyui-fields-container">
                <label><?php _e('WHM API', 'emyui'); ?></label>
                <div class="form-field">
                    <label><?php _e('User Name', 'emyui'); ?></label>
                    <input type="text" name="whm_user_name" value="<?php echo $whm_user_name; ?>">
                </div>
                <div class="form-field">
                    <label><?php _e('Token', 'emyui'); ?></label>
                    <input type="text" name="whm_token" value="<?php echo $whm_token; ?>">
                </div>
                <div class="form-field">
                    <label><?php _e('Server URL', 'emyui'); ?></label>
                    <input type="text" name="whm_server_url" value="<?php echo $whm_server_url; ?>">
                </div>
            </div>
            <div id="emyui-fields-container">
                <label><?php _e('ip2whois', 'emyui'); ?></label>
                <div class="form-field">
                    <label><?php _e('API Key', 'emyui'); ?></label>
                    <input type="text" name="ip2whois_api_key" value="<?php echo $ip2whois_api_key; ?>">
                </div>
            </div>
        </td>
    </tr>
</table>
