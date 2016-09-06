var glow_name = '';

function glow(name){
	glow_name = name.toUpperCase();

	$('.guayaquil_floatunitlist_box').removeClass('g_highlight_glow');

	$('div[name="' + glow_name + '"]').parent().addClass('g_highlight_glow');
	
	window.location = '#_' + name;
}

function hl(el, type){
	var name = el.name;
	if (name == null)
		name = el.getProperty('name');

	if (glow_name == name)
	{
		if (type == 'in')
			$('.g_highlight_glow[name='+name+']').attr('class','g_highlight_over');
		else
			$('.g_highlight_over[name='+name+']').attr('class','g_highlight_glow');
	} 
	else
	{
		if (type == 'in')
			$('.g_highlight[name='+name+']').attr('class','g_highlight_over');
		else
			$('.g_highlight_over[name='+name+']').attr('class','g_highlight');
	}
}

$(document).ready(function($){
	$('.guayaquil_floatunitlist_box div').hover(
		function () {
			$('div[name="' + $(this).attr('name') + '"]').parent().addClass('guayaquil_floatunitlist_box_hover');
		},
		function () {
			$('div[name="' + $(this).attr('name') + '"]').parent().removeClass('guayaquil_floatunitlist_box_hover');
		}
	);

    $(document).ready(function(){
        $('.guayaquil_zoom').colorbox({
                href: function () {
                    var url = $(this).attr('full');
                    return url;
                },
                photo:true,
                rel: "img_group",
                opacity: 0.3,
                title : function () {
                    var title = $(this).attr('title');
                    var url = $(this).attr('link');
                    return '<a href="' + url + '">' + title + '</a>';
                },
                current: 'Рис. {current} из {total}',
                maxWidth : '98%',
                maxHeight : '98%'
            }
        )
    });

	$('.guayaquil_floatunitlist_box').tooltip({
	    track: true,
	    delay: 0,
	    showURL: false,
	    fade: 250,
	    bodyHandler: function() {
			var id = $(this).attr('note');

            var items = $('#unm'+id);
            var tooltip = $(items[0]).text();

			items = $('#utt'+id);
			if (items.length > 0)
				tooltip = tooltip + '<br>' + $(items[0]).text();

			return tooltip;
		}
	});

});