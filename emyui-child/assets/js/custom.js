jQuery(document).on('submit', 'form.domain-header-search-form', function(event){
	event.preventDefault();
    var self            = jQuery(this);
	var domain          = jQuery('.inputdomainsearch');
    var domain_tlds     = jQuery('select[name="emyui_domain_tlds"]');
    var domain_selected = jQuery('input[name="emyui_domain"]:checked');
    domain.attr('readonly', 'readonly');
    self.find('.search-btn').attr('disabled', 'disabled');
    self.find('.spinner-border').show();
	domain.next('span.error').remove();
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
            'domainsearch':     domain.val(),
            'domain_selected':  domain_selected.val(),
            'domain_tlds' :     domain_tlds.val(),
        },
        success: function(response) {
            if(response && response.success == true){
                self.find('.search-btn').removeAttr('disabled');
                domain.removeAttr('readonly', 'readonly');
                self.find('.spinner-border').hide();
                if(response && response.data.cart_url){
                    window.location.href = response.data.cart_url;
                }
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