jQuery(document).ready(function($) {
    var index = 0;
    $('#add-new-hosting-plan').on('click', function(e) {
        e.preventDefault();
        index++;
        if($('.emyui-hosting-plan-main').length > 0){
            index = Math.max(...$('.emyui-hosting-plan-main').map(function() {
                return parseInt($(this).attr('data-id'), 10) + 1;
            }).get());
        }
        var newField = `
            <div class="emyui-hosting-plan-main" data-id="${index}">
                <p class="form-field _hosting_plan_defaul_${index}_field ">
                    <label for="_hosting_plan_defaul_${index}">Hosting Plan Default</label>
                    <input type="checkbox" name="_hosting_plan[${index}][plan_default]" id="_hosting_plan_defaul_${index}" value="yes" class="checkbox">
                    <span class="description">Mark this as the default hosting plan</span>
                </p>
                <p class="form-field _hosting_plan_name_${index}_field">
                    <label for="_hosting_plan_name_${index}">Hosting Plan Name</label>
                    <input type="text" class="short" name="_hosting_plan[${index}][plan_name]" id="_hosting_plan_name_${index}" value="" placeholder="Enter hosting plan name">
                    <button type="button" class="remove-hosting-plan"><span class="dashicons dashicons-trash"></span></button>
                </p>
                <p class="form-field _hosting_plan_price_${index}_field">
                    <label for="_hosting_plan_price_${index}">Hosting Plan Price</label>
                    <input type="text" class="short" name="_hosting_plan[${index}][plan_price]" id="_hosting_plan_price_${index}" value="" placeholder="Enter hosting plan price">
                </p>
                <p class="form-field _hosting_plan_offer_text_${index}_field">
                    <label for="_hosting_plan_offer_text_${index}">Offer Text</label>
                    <textarea class="short" name="_hosting_plan[${index}][plan_offer]" id="_hosting_plan_offer_text_${index}" placeholder="Enter offer text" rows="2" cols="20"></textarea>
                </p>
            </div>`;
        $('#hosting-plan-repeater').before(newField);
    });
    $(document).on('click', '.remove-hosting-plan', function() {
        $(this).closest('.emyui-hosting-plan-main').remove();
        $('.emyui-hosting-plan-main').each(function(idx) {
            $(this).attr('data-id', idx);
            $(this).find('label[for^="_hosting_plan_name"]').attr('for', `_hosting_plan_name_${idx}`);
            $(this).find('input[id^="_hosting_plan_name"]').attr('id', `_hosting_plan_name_${idx}`).attr('name', `_hosting_plan[${idx}][plan_name]`);
            $(this).find('label[for^="_hosting_plan_price"]').attr('for', `_hosting_plan_price_${idx}`);
            $(this).find('input[id^="_hosting_plan_price"]').attr('id', `_hosting_plan_price_${idx}`).attr('name', `_hosting_plan[${idx}][plan_price]`);
            $(this).find('label[for^="_hosting_plan_offer_text"]').attr('for', `_hosting_plan_offer_text_${idx}`);
            $(this).find('textarea[id^="_hosting_plan_offer_text"]').attr('id', `_hosting_plan_offer_text_${idx}`).attr('name', `_hosting_plan[${idx}][plan_offer]`);
            $(this).find('label[for^="_hosting_plan_defaul"]').attr('for', `_hosting_plan_defaul_${idx}`);
            $(this).find('input[id^="_hosting_plan_defaul"]').attr('id', `_hosting_plan_defaul_${idx}`).attr('name', `_hosting_plan[${idx}][plan_default]`);
        });
        index = $('.emyui-hosting-plan-main').length;
    });

    jQuery('.emyui-hosting-plan-main .checkbox').on('change', function() {
        jQuery('.emyui-hosting-plan-main .checkbox').not(this).prop('checked', false);
    });
});
