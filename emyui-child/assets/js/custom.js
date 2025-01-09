jQuery(document).on('submit', 'form.domain-header-search-form', function(event){
	event.preventDefault();
    var self   = jQuery(this);
	var domain = jQuery('.inputdomainsearch');
    domain.attr('readonly', 'readonly');
    self.find('.search-btn').attr('disabled', 'disabled');
    self.find('.spinner-border').show();
	domain.next('span.error').remove();
    jQuery('input[name="package_id"]').val('');
    jQuery('input[name="package_step"]').val('');
    jQuery('input[name="package_domain"]').val('');
    jQuery('input[name="package_domain_id"]').val('');
	if(domain.val() == ''){
		domain.after('<span class="error">'+load_emyui.required+'</span>');
        domain.removeAttr('readonly', 'readonly');
        self.find('.spinner-border').hide();
        self.find('.search-btn').removeAttr('disabled');
		return;
	}
	jQuery.ajax({
        url: load_emyui.ajax_url,
        type: 'POST',
        data: {
            action: 'emyui_domain_search',
            'domainsearch': domain.val()
        },
        success: function(response) {
            if(response && response.success == true){
                jQuery('input[name="package_id"]').val(response.data.package_id);
                jQuery('input[name="package_step"]').val(response.data.package_step);
                jQuery('input[name="package_domain"]').val(response.data.package_domain);
                jQuery('input[name="package_domain_id"]').val(response.data.package_domain_id);
                self.find('.search-btn').removeAttr('disabled');
                domain.removeAttr('readonly', 'readonly');
                self.find('.spinner-border').hide();
            }else{
                if(response && response.data.msg){
                    domain.after('<span class="error">'+response.data.msg+'</span>');
                    domain.removeAttr('readonly', 'readonly');
                    self.find('.search-btn').removeAttr('disabled');
                    self.find('.spinner-border').hide();
                }
            }
        },
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
        }
    });
});