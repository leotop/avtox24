$(document).ready(function() {

    var fields = ['supplier', 'brands', 'delivery'];

    var sortFields = {};

    /*
     * Кнопка сортировки.
     */
    $('#sort').live('click', function(e) {
        $('.catalog-to-result-table tbody tr input:checked').attr('checked', false).trigger('change');
        $('.catalog-to-result-table tbody tr').show();

        sortFields = {};
        for (var i = 0; i < fields.length; ++i) {
            var v = $.trim($('.' + fields[i]).find('select option:selected').val());

            if (v == '') {
                $('.' + fields[i]).find('select option:selected').attr('selected', false);
                $('.' + fields[i]).find('select option:first').attr('selected', 'selected');
                continue;
            }
            if (fields[i] == 'delivery') {
                v = v.replace(/[^0-9]+/g, '');
            }
            sortFields[fields[i]] = v;
        }
        //console.log(sortFields);

        $('.catalog-to-result-table').each(function(){
            var $table = $(this);
            $table.find(' tbody > tr.section-part').each(function () {
                for (var prop in sortFields) {
                    var vl = $.trim($(this).find('.' + prop + '-bl').find('span').attr('data-val'));
                    if (prop == 'delivery') {
                        vl = vl.replace(/[^0-9]+/g, '');
                    }
                    //console.log(vl + '==' + sortFields[prop]);
                    if (vl != sortFields[prop]) {
                        $(this).appendTo($table.find('tbody'));
                        //$(this).hide();
                        break;
                    } else{$(this).prependTo($table.find('tbody'));}
                    //$table.find('tr').hide();
                    //$(this).prependTo($table.find('tbody'));
                    //$(this).show();

                }
            });
        });
        sortTable($('.price select').val(), sortFields);
    });

    /*
    покупка
     */
    $('.buy-a-kit').live('click', function(e){
        e.preventDefault();
        $('.lm_cat_to_form').submit();
    });

    /*
    Изменение количества товаров
     */
    $("input[name^=quantity]").live('change', function() {

        var table_id = $(this).closest(".catalog-to-result-table").attr('id');
        checkPartsQuantity(table_id);
        calculate_elem();
    });

    /*
    аналоги
     */
    /* дубль
    $(".analogs_toggle").live('click', function(e) {

        e.preventDefault();

        var table_id = $(this).attr('data-table');

        if($("#" + table_id).hasClass("row-open")) {
            hideResultRows(table_id);
        } else {
            showResultRows(table_id);
        }
    });
    */

    $(".catalog-to-result-table td.dblckick-sence").live('dblclick', function() {
        var table_id = $(this).closest(".catalog-to-result-table").attr('id');
        if($("#" + table_id).hasClass("row-open")) {
            hideResultRows(table_id);
        } else {
            showResultRows(table_id);
        }
    });
    /*
    чекбокс
     */
    $('input.id_inp').live('change', function() {
        var table_id = $(this).closest(".catalog-to-result-table").attr('id');
        if(!$(this).is(':checked')) {
            if($("#" + table_id).hasClass("row-closed")) {
                hideResultRows(table_id);
            }
        }
        checkPartsQuantity(table_id);
        calculate_elem();
    });
});

function toggle_analogs(table_id) {

    if($("#" + table_id).hasClass("row-open")) {
        hideResultRows(table_id);
    } else {
        showResultRows(table_id);
    }
    return false;
}

/**
 * Поверка на существование в масиве значения
 */
function in_array(value, array)
{
    for (var i = 0; i < array.length; i++) {
        if(array[i] == value) return true;
    }
    return false;
}

function sortTable(by, sortFields)
{
    $('.catalog-to-result-table input.id_inp').prop('checked', false);

    $('.catalog-to-result-table').each(function(){
        var $table = $(this);
        var rows =  $table.find('tr.section-part').get();
        var appropriateRows = new Array();

        for (var key in rows)  {

            var isApprRow = true;
            for (var prop in sortFields) {

                if ($(rows[key]).find('.' + prop + '-bl').find('span').text() != sortFields[prop]) {
                    isApprRow = false;
                    break;
                }
            }

            if (isApprRow) {
                appropriateRows.push(rows[key]);
            }
        }

        appropriateRows.sort(function(a, b) {

            var A = $(a).find('.price-bl').text();
            var B = $(b).find('.price-bl').text();

            A = parseFloat(A.replace(/\s+/g, ''));
            B = parseFloat(B.replace(/\s+/g, ''));

            if (by == 'greater') {
                if (A < B) {
                    return -1;
                }
                if (A > B) {
                    return 1;
                }
            }

            if (by == 'lesser') {
                if (A > B) {
                    return -1;
                }
                if (A < B) {
                    return 1;
                }
            }
            return 0;
        });

        $.each(appropriateRows, function(index, row) {
            $table.children('tbody').prepend(row);
        });


    });

    checkParts();
    calculate_elem();
}

/**
 * Калькуляция выбранных элементов.
 */
function calculate_elem()
{
    var price_val = 0;
    var html = '';
    var date_delivery = [];
    $("tr.section-part").each(function() {

        if($(this).find("input.id_inp:checked").length > 0) {
            var price = parseFloat($(this).find('.price-bl').text().replace(/[^0-9,.]/g, '').replace(',', '.'));
            var quantity = parseInt($(this).find("input[name^=quantity]").val());
            var max_quantity = parseInt($(this).find("input[name=max_quantity]").val());
            if(max_quantity < quantity) {
                $(this).find("input[name^=quantity]").val(max_quantity);
                quantity = max_quantity;
            }
            if(!isNaN(price) && !isNaN(quantity)) {
                price_val += price * quantity;
            }
        }
    });

    if (price_val != 0) {
        $("#price_val").html(price_val.format(0, 3, ' ', ','));
        $("#price-button-block").show();
    } else {
        $("#price-button-block").hide();
    }
}
/**************************** NEW *****************************/

function checkParts() {

    /*
    TODO:
    1. для каждой группы если нет чекнутых элементов - чеким первый.
    2. скрываем все нечекнутые строки
     */
    $('.catalog-to-result-table').each(function() {

        var result_table = $(this);
        /* если не выбран ни один товар - выбираем первый */
        if($(this).find("tr.section-part input.id_inp:checked").length < 1) {
            $(this).find("tr.section-part:first input.id_inp").prop('checked', true);
        }
        hideResultRows($(this).attr('id'));
        checkPartsQuantity($(this).attr('id'));
    });
}

function checkPartsQuantity(table_id) {

    var request_quantity = parseInt($("#" + table_id + '-request-count').val());
    var quantity = 0;
    $("#" + table_id + " tr.section-part").each(function() {
        if($(this).find("input.id_inp:checked").length > 0) {
            quantity += parseInt($(this).find("input.quantity_inp").val());
        }
    });
    $("#" + table_id + '-real-count-val').html(quantity);
    if(quantity != request_quantity) {
        $("#" + table_id + '-real-count').show();
        $("#" + table_id + " tr.section-part").removeClass("status-success");
        $("#" + table_id + " tr.section-part").addClass("status-warning");
    } else {
        $("#" + table_id + '-real-count').hide();
        $("#" + table_id + " tr.section-part").removeClass("status-warning");
        $("#" + table_id + " tr.section-part").addClass("status-success");
    }
}

function hideResultRows(table_id) {

    $("#" + table_id + " tr").each(function() {
        if($(this).hasClass("section-part") && $(this).find("input.id_inp:checked").length < 1) {
            $(this).hide();
        } else if(!$(this).hasClass("section-part")) {
            $(this).hide();
        } else {
            $(this).show();
        }
    });
    $("#" + table_id).removeClass("row-open");
    $("#" + table_id).addClass("row-closed");
    $("#" + table_id + "-analogs").removeClass("row-open");
    $("#" + table_id + "-analogs").addClass("row-closed");
}

function showResultRows(table_id) {

    $("#" + table_id + " tr").show();
    $("#" + table_id).removeClass("row-closed");
    $("#" + table_id).addClass("row-open");
    $("#" + table_id + "-analogs").removeClass("row-closed");
    $("#" + table_id + "-analogs").addClass("row-open");

    var article = $("#" + table_id + '-request-article').val();
    var brand = $("#" + table_id + '-request-brand').val();
    var quantity = parseInt($("#" + table_id + '-request-count').val());
    loadAnalogs(table_id, article, brand, quantity);
}

function buildFilter() {

    var supplier = {};
    var delivery = {};
    var brand = new Array();
    var i = 0;

    $('.catalog-to-result-table tr.section-part').each(function () {

        var supplier_name = $(this).closest('.section-part').find('.supplier-bl').find('span').text().trim();
        var supplier_id = $(this).closest('.section-part').find('.supplier_id_inp').val();
        var brand_d = $(this).closest('.section-part').find('.brands-bl').find('span').text().trim();
        var delivery_text = $(this).closest('.section-part').find('.delivery-bl').find('span').text().trim();
        var delivery_val = $(this).closest('.section-part').find('.delivery_inp').val();

        //price_d = parseFloat($(this).closest('tr').find('.price-bl').text().replace(/\s+/g, ''));

        if (!(supplier_id in supplier)) {
            supplier[supplier_id] = supplier_name;
        }

        if (!(delivery_val in delivery)) {
            delivery[delivery_val] = delivery_text;
        }

        if (brand.indexOf(brand_d) == -1) {
            brand[i] = brand_d;
            i++;
        }
    });

    var supp = br = '<select><option value="">' + 'все' + '</option>';
    var dlivery_opt = '<select><option value="">' + 'все' + '</option>';
    var pr = '<select><option value="">' + 'все' + '</option><option value="lesser">' + 'наименьшая' + '</option><option value="greater">' + 'наибольшая' + '</option></select>';

    for (var key in supplier) {
        supp += '<option value="' + key +'">' + supplier[key] + '</option>';
    }

    for (var key in delivery) {
        dlivery_opt += '<option value="' + key +'">' + delivery[key] + '</option>';
    }

    for (var i = 0; i < brand.length; ++i) {
        br += '<option value="' + brand[i] +'">' + brand[i] + '</option>';
    }

    $('.price').append(pr);
    $('.supplier').append(supp).append('</select>');
    $('.delivery').append(dlivery_opt).append('</select>');
    $('.brands').append(br).append('</select>');
}

/**
 * Number.prototype.format(n, x, s, c)
 *
 * @param integer n: length of decimal
 * @param integer x: length of whole part
 * @param mixed   s: sections delimiter
 * @param mixed   c: decimal delimiter
 */
Number.prototype.format = function(n, x, s, c) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
        num = this.toFixed(Math.max(0, ~~n));

    return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
};