/**
 * 01-01-2025
 * 
 * Search Domain
 **/
jQuery(document).on('submit', 'form.domain-header-search-form', function(event){
	event.preventDefault();
    var self            = jQuery(this);
	var domain          = jQuery('.inputdomainsearch');
    var domain_tlds     = jQuery('select[name="emyui_domain_tlds"]');
    var domain_selected = jQuery('input[name="emyui_domain"]:checked');
    domain.attr('readonly', 'readonly');
    self.find('.search-btn').attr('disabled', 'disabled');
    self.find('.spinner-border').show();
    self.find('.emyui-suggest-domain').remove();
    self.find('.error').remove();
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
            action:             'emyui_domain_search',
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
                if(response && response.data.domain_tdls){
                    self.find('.search-btn').parent('.single-input').before(response.data.domain_tdls);
                }
                domain.removeAttr('readonly', 'readonly');
                self.find('.search-btn').removeAttr('disabled');
                self.find('.spinner-border').hide();
            }
        },
        error: function(xhr, status, error) {
            console.log(xhr.responseText);
        }
    });
});

/**
 * 01-17-2025
 * 
 * Available domain change TDLS
 **/
jQuery(document).on('click', 'input[name="domain_suggest_radio"]', function(){
    var selectedTLD = jQuery(this).attr('data-tdls');
    if(selectedTLD){
        jQuery("select[name='emyui_domain_tlds']").val("");
        jQuery("select[name='emyui_domain_tlds']").val(selectedTLD);
        jQuery("select[name='emyui_domain_tlds']").niceSelect('update');
    }
});

jQuery(document).ready(function($) {

    /**
     * 01-19-2025
     * 
     * Hosting Deal
     **/
    jQuery('select[name="emyui_hosting_deal"]').on('change', function(){
        var selectedOption  = $(this).find('option:selected');
        var planPrice       = selectedOption.data('plan_price');
        var planOffer       = selectedOption.data('plan-offer');
        var index_id        = selectedOption.data('index_id');
        var package         = jQuery('select[name="emyui_package"]').val();
        if(planPrice){
            jQuery('#selected-plan-price').html(planPrice);
            emyui_set_cookie('plan_price', selectedOption.val(), 1);
        }else{
            jQuery('#selected-plan-price').html('');
            emyui_delete_cookie('plan_price');
        }
        if(planOffer){
            jQuery('#selected-plan-offer').text(planOffer);
            emyui_set_cookie('plan_offer', planOffer, 1);
        }else{
            jQuery('#selected-plan-offer').text('');
            emyui_delete_cookie('plan_offer');
        }
        location.reload();
    });

    /**
     * 01-09-2025
     * 
     * Change packages
     **/
    jQuery('select[name="emyui_package"]').on('change', function(){
        var self    = jQuery(this);
        var package = self.val();
        self.attr('disabled', 'disabled');
        self.parent('.emyui_package_wrap').find('.spinner-border').show();
        jQuery.ajax({
            url: load_emyui.ajax_url,
            type: 'POST',
            data: {
                action:     'emyui_package',
                'package':  package,
            },
            success: function(response) {
                if(response && response.success == true){
                    self.removeAttr('disabled');
                }else{
                    self.removeAttr('disabled');
                }
                location.reload();
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    });

    /**
     * 01-19-2025
     * 
     * Domain radio button
     **/
    jQuery(document).on('change', 'select[name="emyui_data_center_id"]', function(){
        var self = jQuery(this);
        if(self.val() != ''){
            emyui_set_cookie('data_center', self.val(), 1);
        }else{
            emyui_delete_cookie('data_center');
        }
    });

    /**
     * 01-30-2025
     * 
     * Tld box open and close
     **/
    jQuery(document).on('click', '.emyui-domain-tdls .nice-select', function(){
        var domainListWrap = jQuery(this).parents('.domain-header-search-form').find('.emyui-domain-list-wrap');
        if(domainListWrap.length){
            if(domainListWrap.hasClass('hidden')){
             domainListWrap.removeClass('hidden');
            }else{
             domainListWrap.addClass('hidden');
            }
        }
    });

    /**
     * 02-05-2025
     * 
     * Domain tab handle
     **/
    jQuery(document).on('click', '.emyui-domain a', function(e){
        e.preventDefault();
        jQuery('.emyui-domain .toggle-domain-s-t').removeClass('active-link-border').addClass('not-active-link-border');
        jQuery(this).removeClass('not-active-link-border').addClass('active-link-border');
        if($(this).index() === 0) {
            $('.emyui-domain-panel').show();
            $('.emyui-domai-ai-panel').hide();
            $('.emyui-domai-ai-panel').addClass('hidden');
        }else{
            $('.emyui-domain-panel').hide();
            $('.emyui-domai-ai-panel').show();
            $('.emyui-domai-ai-panel').removeClass('hidden');
        }
    });

    /**
     * 02-05-2025
     * 
     * Domain search With AI char length validation
     **/
    $(document).on('input', '#domain-search', function() {
        var maxLength = 250;
        var charCount = $(this).val().length;
        if(charCount > maxLength){
            $(this).val($(this).val().substring(0, maxLength));
            charCount = maxLength;
        }
        $('#char-count').text(charCount);
    });

    $(document).on('submit', 'form[name="emyui-domain-search"]', function(event){
        event.preventDefault();
        var self = jQuery(this);
        self.find('.emyui-error-msg').html('');
        self.find('.spinner-border').show();
        var domain_field = self.find('input[name="domain"]');
        if(domain_field.val() == ''){
            self.find('.emyui-error-msg').html(load_emyui.required);
            self.find('.spinner-border').hide();
            return;
        }
    });

    /**
     * 02-07-2025
     * 
     * Domain view button
     **/
    jQuery('.hidden-domain').hide();
    $(document).on("click", ".domain-view-more-btn", function() {
        $(this).prev(".row").find(".hidden-domain").slideDown();
        $(this).hide();
    });

    /**
     * 02-08-2025
     * 
     * Select domain
     **/
    jQuery(document).on("click", ".emyui-select-domain", function () {
        jQuery(".emyui-select-domain").removeClass("emyui-active");
        jQuery(this).addClass("emyui-active");
        jQuery([document.documentElement, document.body]).animate({
            scrollTop: jQuery('body').offset().top
        }, 500);
        jQuery('input[name="domain"]').first().focus();
    });

});

/**
 * 01-19-2025
 * 
 * Create a cookie
 **/
function emyui_set_cookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

/**
 * 01-19-2025
 * 
 * Get a cookie value
 **/
function emyui_get_cookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return '';
}

/**
 * 01-19-2025
 * 
 * Delete a cookie
 **/
function emyui_delete_cookie(name) {
    document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
}

