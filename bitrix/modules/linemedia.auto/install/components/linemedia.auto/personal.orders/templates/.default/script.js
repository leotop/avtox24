$(document).ready(function() {
    $('.lm-auto-order-toggle').click(function() {
        var order = $(this).attr('rel');
        
        if ($(this).hasClass('lm-auto-order-toggle-expand')) {
            $('#lm-auto-orders-table-id tr[rel="order-' + order + '"]').hide('fast');
            $(this).removeClass('lm-auto-order-toggle-expand');
            $(this).addClass('lm-auto-order-toggle-turn');
        } else {
            $('#lm-auto-orders-table-id tr[rel="order-' + order + '"]').show('fast');
            $(this).removeClass('lm-auto-order-toggle-turn');
            $(this).addClass('lm-auto-order-toggle-expand');
        }
    });

    // show if hash specified
    var hash = location.hash.replace('#', '');

    if($('tr[rel="order-' + hash + '"]').length) {
        $('#lm-auto-orders-table-id tr[rel="order-' + hash + '"]').show('fast');
        $(this).removeClass('lm-auto-order-toggle-turn');
        $(this).addClass('lm-auto-order-toggle-expand');
    }

    $('.return_goods').click(function() {

        var args = $(this).attr('rel').split('-');
        var basketId = args[1];
        var title = $(this).attr('title');
        var sessid = $("#sessid").val();

        if(confirm(title + ' ?') && parseInt(basketId) > 0) {
            $.ajax({
                type:"POST",
                url:"/bitrix/components/linemedia.auto/personal.orders/ajax.php?action=return",
                data: {'basket':basketId, 'sessid':sessid},
                success:function (html) {
                    if(html == 'OK') {
                        var url = location.href;
                        if(url.indexOf('#') != -1) {
                            url = url.substr(0, url.indexOf('#'));
                        }
                        url += '#' + args[0];
                        location.href = url;
                        location.reload();
                    } else {
                        alert(html);
                    }
                },
                error:function (data) {
                    alert("return error");
                }
            });
        }
    });

    $('.cancel_basket').click(function() {

        var args = $(this).attr('rel').split('-');
        var basketId = args[1];
        var title = $(this).attr('title');
        var sessid = $("#sessid").val();

        if(confirm(title + ' ?') && parseInt(basketId) > 0) {
            $.ajax({
                type:"POST",
                url:"/bitrix/components/linemedia.auto/personal.orders/ajax.php?action=cancel",
                data: {'basket':basketId, 'sessid':sessid},
                success:function (html) {
                    if(html == 'OK') {
                        var url = location.href;
                        if(url.indexOf('#') != -1) {
                            url = url.substr(0, url.indexOf('#'));
                        }
                        url += '#' + args[0];
                        location.href = url;
                        location.reload();
                    } else {
                        alert(html);
                    }
                },
                error:function (data) {
                    alert("cancel error");
                }
            });
        }
    });

    $('.remove_cancel_basket').click(function() {

        var args = $(this).attr('rel').split('-');
        var basketId = args[1];
        var title = $(this).attr('title');
        var sessid = $("#sessid").val();

        if(confirm(title + ' ?') && parseInt(basketId) > 0) {
            $.ajax({
                type:"POST",
                url:"/bitrix/components/linemedia.auto/personal.orders/ajax.php?action=remove_cancel",
                data: {'basket':basketId, 'sessid':sessid},
                success:function (html) {
                    if(html == 'OK') {
                        var url = location.href;
                        if(url.indexOf('#') != -1) {
                            url = url.substr(0, url.indexOf('#'));
                        }
                        url += '#' + args[0];
                        location.href = url;
                        location.reload();
                    } else {
                        alert(html);
                    }
                },
                error:function (data) {
                    alert("cancel error");
                }
            });
        }
    });
});
