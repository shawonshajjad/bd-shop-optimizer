jQuery(document).ready(function($) {
    if (typeof sizedProductData !== 'undefined') {
        sizedProductData.forEach(function(p) {
            let $btn = $('a[data-product_id="' + p.id + '"]');
            if($btn.length) {
                $btn.attr('href', p.url).text('Select Size').addClass('select-size-link').removeClass('ajax_add_to_cart add_to_cart_button');
            }
        });
    }
});