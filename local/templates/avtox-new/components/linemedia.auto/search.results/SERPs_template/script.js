/*
 * Добавление в корзину.
 */
function add2cart(hash, url, max_available_quantity)
{
    var qi = $('input[rel="quantity"][data-part-hash="' + hash + '"]');
    var quantity = parseInt(qi.val());
    var quantity = qi.val(), mf = Math.max(1, parseInt(qi.data('step')));

    if (quantity < mf || (quantity % mf > 0)) {
        if (!confirm(langs['LM_AUTO_SEARCH_QUANTITY_SIZE_CONFIRM'] + ' (' + mf + ').')) {
            return false;
        }
    }
    url = url + '&quantity=' + quantity + '&max_available_quantity=' + parseInt(max_available_quantity);

    if(parseInt(qi.data('step')) > 0) {
        url = url + '&step=' + parseInt(qi.data('step'));
    }

    document.location = url;
}


/*
 * Замена раскладки.
 */
function remapping(string)
{
    var rus = 'йцукенгшщзхъфывапролджэячсмитьбюЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ';
    var eng = 'qwertyuiop[]asdfghjkl;\'zxcvbnm,.QWERTYUIOP{}ASDFGHJKL:"ZXCVBNM<>';

    rusLetters = rus.split('');
    engLetters = eng.split('');

    for (var i in rusLetters) {
        string = string.replace(new RegExp(rusLetters[i], 'g'), engLetters[i]);
    }
    return string;
}

$(document).ready(function() {
	
	// показать больше результатов поиска
	$(".show_more_products").click(function(){
		$(this).fadeOut(100);
		$(this).parent().prevUntil("tr.visible_row").slideDown(2000);
	})


    jQuery('.int').on('keyup', function(e) {
        var val = $(this).val();
        $(this).val(val.replace(/\D/g, ''));
    });

    jQuery('.int').on('blur', function() {
        var val = parseInt($(this).val());
        var mf = parseInt($(this).data('step'));
        if(isNaN(mf) || mf <= 0) mf = 1;

        if (isNaN(val) || val <= 0) {
            $(this).val(mf);
        } else {
            var mod = val % mf;
            if(mod != 0) {
                val = parseInt(Math.ceil(val / mf) * mf);
            }
            $(this).val(val);
        }
    });

    jQuery('.maxvalue').on('keyup', function() {

        var max = Math.max(1, parseInt($(this).data('max')));
        var val = parseInt($(this).val());
        if (val > max) {
            $(this).val(max);
        }
    });

    jQuery('.lm-auto-submit').click(function(e) {
        var input = $('#lm-auto-main-search-query-id');
        if (input.data('remapping')) {
            $('#lm-auto-main-search-query-id').val(remapping($('#lm-auto-main-search-query-id').val()));
        }

        jQuery('#lm-auto-main-search-form-id').submit();
    });




    /*
     * При нажатии на кнопку "добавить" в колонке "блокнот" формируется post запрос из параметров
     * данного товара в таблице или если задан part_id, то отправляется только он с дальнейшим
     * поиском по локальной базе
     */

	 /*$(".lm-auto-search-parts").tablesorter({
		 headers: {
		 		3: {sorter: false},
		 		8: {sorter: false},
				9: {sorter: false},
				10: {sorter: false}
			}
	});
*/

});


function AddToNotepad(_this, event) {

    // появление всплывающего сообщения
    var Dialog = new BX.CDialog({
        title: popup_title,
        content: lang_go_to_notepad_body + '<br /> <a target="_blank" href="'+path_notepad+'">'+lang_go_to_notepad+'</a>',

        icon: '',
        resizable: true,
        draggable: true,
        height: '70',
        width: '220',
        buttons: []
    });

    //this.parentWindow.Close();


    var path_to_ajax = '/bitrix/components/linemedia.auto/personal.notepad/ajax.php';
    var tr = $(_this).closest("tr");

    var part_id = parseInt(tr.find(".notepad_part_id").val());
    var api_value = tr.find(".part_api_value").val();
    var title = tr.find(".fn").text();
    var brand_title = tr.find(".brand").text();
    var article = tr.find(".sku").text();
    var quantity = tr.find(".int").val();
    var status = 'add';
    var extra = tr.find("#part_url_extra").val();

    // проверка на существование $part['id'] осуществляется на основе данных о $part['supplier']['PROPS']['api']['VALUE']
    if (!api_value) {
        $.post(path_to_ajax, { notepad: status, part_id: part_id, sessid: sessid, extra: extra})
            .done( function(data,status) {
                if (status == 'success') {
                    Dialog.Show();
                }
            });
    } else {

        $.post(path_to_ajax, { notepad: status, title: title, brand_title: brand_title, article: article, quantity: quantity,
            sessid: sessid, extra:extra})
            .done( function(data,status) {
                if (status == 'success') {
                    Dialog.Show();
                }
            });
    }
    event.preventDefault();
    event.stopPropagation();
}

$(document).ready(function(){
    $('#select-crosses-tecdoc').on('click', function() {
        $(this).next('#table-crosses-tecdoc').toggle();
    });

    $('#select-crosses-lm').on('click', function() {
        $(this).next('#table-crosses-lm').toggle();
    });
    try{
        $('[data-toggle="tooltip"]').tooltip({trigger:'focus'});
    } catch(e) {}
});
