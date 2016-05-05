$(document).ready(function() {
	
	
	
	
	$('#lm-auto-vin-frm').submit(function(){
		var act = $(this).attr('action');
		document.location = act + $('#lm-auto-vin-inp').val() + '/';
		return false;
	})
	
	
	
	
	
	if ($(".treeview").length > 0){
		$(".treeview").treeview({
		      animated: "false",
		      collapsed: true,
		      unique: false,
		      persist: "cookie"
		});
	}
	
	
	
	
	$('input#quick_search').quicksearch('.treeview li, #lm-auto-catalog-original tbody tr');
	
	$('input#quick_search_img').quicksearch('.lm-auto-catalog-original.kia.articles tbody tr .img_num', {
        'show': function () {
            $(this).parents('tr').show();
            
            /*
            * Сколько строк в текущей группе видимы
            * Если ,больше 0 - то покажем заголовок
            */
            var current_group = $(this).parents('tr').attr('class');
            var visible_count = $('tr.' + current_group).filter(function() {
              return $(this).css('display') !== 'block';
            }).length;
            
            if(visible_count > 0) {
                $('.' + current_group + '_header').show();
            }           
            
        },
        'hide': function () {
            $(this).parents('tr').hide();
            
            /*
            * Сколько строк в текущей группе видимы
            * Если 0 - то прячем заголовок
            */
            var current_group = $(this).parents('tr').attr('class');
            var visible_count = $('tr.' + current_group).filter(function() {
              return $(this).css('display') !== 'none';
            }).length;
            
            if(visible_count == 0) {
                $('.' + current_group + '_header').hide();
            }           
        }
    });

});