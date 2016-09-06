function hl(el, type){
	var name = $(el).attr('name');

	if (type == 'in')
		$('.g_highlight[name='+name+']').addClass('g_highlight_over').removeClass('g_highlight');
	else
		$('.g_highlight_over[name='+name+']').removeClass('g_highlight_over').addClass('g_highlight');
}

function g_toggle(el, opennedimage, clossedimage){
	var name = $(el).attr('id');

	var e = $('tr#'+name);
	if (e.hasClass('g_collapsed'))
	{
		$('tr.g_replacementRow[name='+name+']').show();
		$(el).attr('src', opennedimage);
		e.removeClass('g_collapsed');
	}
	else
	{
		$('tr.g_replacementRow[name='+name+']').hide();
		$(el).attr('src', clossedimage);
		e.addClass('g_collapsed');
	}
}

function g_toggleAdditional(id, opennedimage, clossedimage){

	var e = $('#' + id + ' .g_additional_toggler');
	if (e.hasClass('g_addcollapsed'))
	{
		$('#' + id + ' tr.g_addgr').removeClass('g_addgr_collapsed');
		$(e).attr('src', opennedimage);
		e.removeClass('g_addcollapsed');
	}
	else
	{
		$('#' + id + ' tr.g_addgr').addClass('g_addgr_collapsed');
		$(e).attr('src', clossedimage);
		e.addClass('g_addcollapsed');
	}
}

function g_getHint() {

	var str='<table border=0>';
	var items = $(this).parent().find('td.g_ttd');

	for (var i = 0; i<items.length-1; i++) {
        var txt = $(items[i]).html();
        if (txt.length <= 0)
            continue;
        
		str = str+'<tr><th align=right>' + $('#'+$(items[i]).attr('name')).text() + ':&nbsp;</th><td>' + txt + '</td></tr>';
	}
    var note_items = $ (items[i]).find ('span.item');
    for (var k = 0; k<note_items.length; k++)
    {
        var txt = $(note_items[k]).find('span.value').text();
        if (txt.length <= 0)
            continue;
        str = str+'<tr><th align=right>' + $(note_items[k]).find('span.name').text() + ':&nbsp;</th><td>' + txt+ '</td></tr>';
    }
	str = str + '</table>';

	return str;
}

$(document).ready(function($){

    $('tr.g_highlight a.follow').colorbox({
        'href': function () {
            var url = ($(this).attr('href')).replace(/[ ]/g, '');
            url += (url.indexOf('?') >= 0 ? '&' : '?') + 'format=raw';
            return url;
        },
        'opacity': 0.3,
        'innerWidth' : '1000px',
        'maxHeight' : '98%'
    })
});