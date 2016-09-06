function prepareImage()
{
    var img = $('img.dragger');

    var width = img.prop("naturalWidth");
    var height = img.prop("naturalHeight"); 

    img.attr('owidth', width);
    img.attr('oheight', height);

    $('div.dragger').each(function(idx){
        var el = $(this);
        el.attr('owidth', parseInt(el.css('width')));
        el.attr('oheight', parseInt(el.css('height')));
        el.attr('oleft', parseInt(el.css('margin-left')));
        el.attr('otop', parseInt(el.css('margin-top')));
    });
}

function rescaleImage(delta) {
    var img = $('img.dragger');

    var original_width = img.attr('owidth');
    var original_height = img.attr('oheight');

    if (!original_width)
    {
        prepareImage();

        original_width = img.attr('owidth');
        original_height = img.attr('oheight');
    }

    var current_width = img.innerWidth();
    var current_height = img.innerHeight();

    var scale = current_width / original_width;

    var cont = $('#viewport');

    var view_width = parseInt(cont.css('width'));
    var view_height = parseInt(cont.css('height'));

    var minScale = Math.min(view_width / original_width, view_height / original_height);

    var newscale = scale + (delta / 10);
    if (newscale < minScale)
        newscale = minScale;

    if (newscale > 1)
        newscale = 1;

    var correctX = Math.max(0, (view_width - original_width*newscale) / 2);
    var correctY = Math.max(0, (view_height - original_height*newscale) / 2);

    img.attr('width', original_width*newscale);
    img.attr('height', original_height*newscale);
    img.css('margin-left', correctX + 'px');
    img.css('margin-top', correctY + 'px');

    $('div.dragger').each(function(idx){
        var el = $(this);
        el.css('margin-left', (el.attr('oleft')*newscale + correctX) + 'px');
        el.css('margin-top', (el.attr('otop')*newscale + correctY) + 'px');
        el.css('width', el.attr('owidth')*newscale + 'px');
        el.css('height', el.attr('oheight')*newscale + 'px');
    });
}

function fitToWindow() {
    var t = $('#g_container');
    var width = t.innerWidth() - (parseInt(t.css('padding-right')) || 0) - (parseInt(t.css('padding-left')) || 0);
    $('#viewport, #viewtable').css('width', Math.ceil(width*0.48));
}

var el_name;

function SubscribeDblClick(selector)
{
    $(selector).dblclick(function() {
        var el = $(this);
        var elName = el.attr('name');

        var items = $('tr[name='+elName+']');

        if (items.length == 0)
            return false;

        if (items.length == 1)
        {
            var id = $(items[0]).attr('id');
            items = $('#' + id + ' a.follow');

            if (items.length == 0) {
                return false;
            }

            var url = $(items[0]).attr('href');
            url += (url.indexOf('?') >= 0 ? '&' : '?') + 'format=raw';
            $.colorbox({
                'href': url,
                'opacity': 0.3,
                'innerWidth' : '1000px',
                'maxHeight' : '98%'
            })
        } else {
            $.colorbox({
                'html': function() {
                    var items = $('tr[name='+elName+'] td[name=c_name]');
                    var name = $(items[0]).text();

                    var html = '<h2><span>' + name + '</span></h2>' + '<table>';

                    var oems = $('tr[name='+elName+'] td[name=c_oem]');
                    var notes = $('tr[name='+elName+'] td[name=c_note]');
                    var urls = $('tr[name='+elName+'] td[name=c_oem] a.follow');

                    var count = oems.length;
                    if (count == 0) {
                        count = notes.length;
                    }

                    for (var idx = 0; idx < count; idx++) {
                        var url = $(urls[idx]).attr('href');
                        url += (url.indexOf('?') >= 0 ? '&' : '?') + 'format=raw';
                        html += '<tr><td><a href="#" onclick="$.colorbox({\'href\': \'' + url +'\',\'opacity\': 0.3, \'innerWidth\' : \'1000px\',\'maxHeight\' : \'98%\'}); return false;">' + $(oems[idx]).text() + '</a></td><td>' + $(notes[idx]).text() + '</td></tr>';
                    }

                    html += '</table>';

                    return html;
                },
                'opacity': 0.3,
                'maxHeight' : '98%'
            })
        }
    })
}

$(document).ready(function($){

    /*
    $('.dragger, #viewport').bind('mousewheel', function(event, delta) {
    rescaleImage(delta);
    return false;
    });
    */

    //$('#viewport').dragscrollable({dragSelector: '.dragger, , #viewport', acceptPropagatedEvent: false});

    $('#viewport div').tooltip({ 
        track: true, 
        delay: 0, 
        showURL: false, 
        fade: 250,
        bodyHandler: function() {
            var name = $(this).attr('name');

            var items = $('tr[name='+name+'] td[name=c_name]');

            if (items.length == 0)
                return false;

            return $(items[0]).text();
        }
    });

    $('tr.g_highlight').click(function() {
        var name = $(this).attr('name');
        $('.g_highlight[name='+name+']').toggleClass('g_highlight_lock');
        $('.g_highlight_over[name='+name+']').toggleClass('g_highlight_lock');
    });

    $('#viewport div').click(function() {
        var name = $(this).attr('name');
        $('.g_highlight[name='+name+']').toggleClass('g_highlight_lock');
        $('.g_highlight_over[name='+name+']').toggleClass('g_highlight_lock');

        var tr = $('tr.g_highlight_lock[name='+name+']');
        if (tr.length == 0)
            return;

        /*var scrolled = false;
        tr.each(function(){
        if (!scrolled)
        $.scrollTo(this);
        //new Fx.Scroll($('#viewtable')).toElement(this);
        scrolled = true;
        });*/
    });

    $('#viewport div, #viewport div img').hover(
        function () {
            hl(this, 'in');
        }, 
        function () {
            hl(this, 'out');
        }
    );

    $(window).bind("resize", function() {
        fitToWindow();
    });

    fitToWindow();

    if ((document.all)?false:true)
        $('#g_container div table').attr('width', '100%');

    $('.guayaquil_zoom').colorbox({
        'href': function () {
            var url = $(this).attr('full');
            return url;
        },
        'photo':true,
        'opacity': 0.3,
        'title' : function () {
            var title = $(this).attr('title');
            return title;
        },
        'maxWidth' : '98%',
        'maxHeight' : '98%',
        'onComplete' : function () {
            var img1 = $('#viewport img.dragger');
            var img2 = $('#cboxLoadedContent img.cboxPhoto');
            var k = img2.innerWidth() / img1.attr('owidth');

            $('#viewport div.dragger').each(function() {
                var el = $(this);
                var blank = $('#viewport div.g_highlight img').attr('src');
                var hl = el.hasClass('g_highlight_lock');
                var nel = '<div class="g_highlight' + (hl ? ' g_highlight_lock' : '') + '" name="' + el.attr('name') + '" style="position: absolute; width: ' + (el.attr('owidth') * k) + 'px; height: ' + (el.attr('oheight') * k) + 'px; margin-top: ' + (el.attr('otop') * k) + 'px; margin-left: ' + (el.attr('oleft') * k) + 'px; overflow: hidden;"><img width="200" height="200" src="' + blank + '"></div>';

                img2.before(nel);
            });

            $('#cboxLoadedContent div').click(function() {
                var el = $(this);
                var name = el.attr('name');
                $('.g_highlight[name='+name+']').toggleClass('g_highlight_lock');
                $('.g_highlight_over[name='+name+']').toggleClass('g_highlight_lock');
            });

            SubscribeDblClick('#cboxLoadedContent div');

            $('#cboxLoadedContent div').tooltip({
                track: true,
                delay: 0,
                showURL: false,
                fade: 250,
                bodyHandler: function() {
                    var name = $(this).attr('name');

                    var items = $('tr[name='+name+'] td[name=c_name]');

                    if (items.length == 0)
                        return false;

                    return $(items[0]).text();
                }
            });
        }
    });

    SubscribeDblClick('#viewport div');


    //дополнительный функции
    $(".unit-resize-image").on("click", function() {
        $(this).toggleClass("resized");
        $("#g_container").toggleClass("resized");
        if ($("#g_container").hasClass("resized")) {
            rescaleImage(2);
        } else {
            rescaleImage(0);    
        }
    })
});
