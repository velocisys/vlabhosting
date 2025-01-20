<h2><?php _e('Vlab Hosting Settings Section', 'emyui'); ?></h2>
<table class="form-table">
    <tr valign="top">
        <th scope="row" class="titledesc">
            <label><?php _e('Data Center Fields', 'emyui'); ?></label>
        </th>
        <td class="forminp">
            <div id="emyui-fields-container">
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