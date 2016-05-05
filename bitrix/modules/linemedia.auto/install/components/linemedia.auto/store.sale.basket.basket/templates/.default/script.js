$(document).ready(function () {

    $('.cart-item-quantity input[name^="QUANTITY_"]').keyup(function () {

        var current_parent_cell = $(this).parent();
        while (current_parent_cell.attr('class') != 'cart-item-name') {
            current_parent_cell = current_parent_cell.prev();
        }

        var max_available_quantity = parseInt(current_parent_cell.find('div.max_available_quantity').text());

        var val = $(this).val();
        $(this).val(val.replace(/\D/g, ''));
        var val = parseInt($(this).val());

        var mf = parseInt(Math.max(1, $(this).data('step')));

        if (isNaN(val) || val <= 0) {
            $(this).val(mf);
        } else {
            var mod = val % mf;
            if (mod != 0) {
                val = parseInt(Math.ceil(val / mf) * mf);
            }
            $(this).val(val);
        }

        if (val > max_available_quantity) {
            $(this).val(max_available_quantity);
        }
    });

    if (ajaxrecalc == 'Y') {

        // Пересчет количества.
        $('.cart-item-quantity input[name^="QUANTITY_"]').keyup(function () {

            var basket_id = parseInt($(this).attr('name').replace('QUANTITY_', ''));
            var quantity = parseInt($(this).val());

            var mf = parseInt(Math.max(1, $(this).data('step')));

            if (isNaN(quantity) || quantity <= 0) {
                $(this).val(mf);
            } else {
                var mod = quantity % mf;
                if (mod != 0) {
                    quantity = parseInt(Math.ceil(quantity / mf) * mf);
                }
                $(this).val(quantity);
            }


            if ($(this).val().length > 0) {
                // Изменяем только при количестве большем нуля.
                if (quantity > 0) {
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {'BASKET_ID': basket_id, 'QUANTITY': quantity, 'PARAMS': ajaxparams}
                    }).done(function (response, status) {
                        if (status == 'success') {
                            var data = JSON.parse(response);
                            var price = data['TOTAL_PRICE'];
                            if (price.length > 0) {
                                $('.cart-item-price p b').html(price);
                            }
                        }
                    });
                } else {
                    $(this).val(1);
                }
            }
        });
    }

    try{
        $('[data-toggle="tooltip"]').tooltip({trigger:'focus'});
    } catch(e) {}
});
