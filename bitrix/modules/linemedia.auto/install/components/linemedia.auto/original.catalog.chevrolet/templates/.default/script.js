$(document).ready(function() {





	if ($(".treeview").length > 0){
		$(".treeview").treeview({
		      animated: "false",
		      collapsed: true,
		      unique: false,
		      persist: "cookie"
		});
	}




	$('input#quick_search').quicksearch('.treeview li, .lm-auto-catalog-original tbody tr, .lm-auto-catalog-original .group_section');


    /*
    * ����� �� ������ �� ��������
    */
    $('input#quick_search_img').quicksearch('.lm-auto-catalog-original.chevrolet.articles tbody tr .img_num', {
        'show': function () {
            var this_parents_tr = $(this).parents('tr');

            this_parents_tr.show();

            /*
            * ������� ����� � ������� ������ ������
            * ���� ,������ 0 - �� ������� ���������
            */
            var current_group = this_parents_tr.attr('class');
            var visible_count = $('tr.' + current_group).filter(function() {
              return $(this).css('display') !== 'block';
            }).length;

            if(visible_count > 0) {
                $('.' + current_group + '_header').show();
            }

        },
        'hide': function () {
            var this_parents_tr = $(this).parents('tr');
            this_parents_tr.hide();

            /*
            * ������� ����� � ������� ������ ������
            * ���� 0 - �� ������ ���������
            */
            var current_group = this_parents_tr.attr('class');
            var visible_count = $('tr.' + current_group).filter(function() {
              return $(this).css('display') !== 'none';
            }).length;

            if(visible_count == 0) {
                $('.' + current_group + '_header').hide();
            }
        }
    });


    /*
    * ������ �� ����� ����
    * ������� ��� ���� ����
    */
    var car_types = new Array();
    var car_years = new Array();

    /**
     *
     * populate array from exiting type of auto
     *
     */

    $('.lm-auto-catalog-original.models .lm-car-type').each(function(){
        var car_type = $.trim($(this).text());
        if (car_types.indexOf(car_type) == -1){
            car_types.push(car_type);
        }
    });

    /**
     * populate array from existing years of manufacturing and remove duplicates
     *
     */

    $('.lm-auto-catalog-original.models .lm-car-years').each(function () {
    	var car_year = $.trim($(this).text());
    	var year_begin_end = car_year.split('-');
    	var begin = parseInt($.trim(year_begin_end[0]));
    	var end = parseInt($.trim(year_begin_end[1]));

    	if (begin && !end && car_years.indexOf(begin) == -1) {
    		car_years.push(begin);
        } else {
    		while (begin <= end) {
    			if (car_years.indexOf(begin) == -1) {
                    car_years.push(begin);
        		}
                begin++;
    		}
    	}
    });

    car_years.sort();

    /**
    *
    * form chain of types and years respectively
    *
    */
    for (var key in car_types) {
        var val = car_types [key];
        var car_type_button = '<span class="lm-filter-button">' + val + '</span>';
        $('.lm_car_type_filter').append(car_type_button);
    }

    for (var key in car_years) {
        var val = car_years [key];
        var car_year_button = '<span class="lm-filter-button">' + val + '</span>';
        $('.lm_car_years_filter').append(car_year_button);
    }


    /**
     *
     * go over each cell of years while being fixed cell of type
     *
     */

    $('.lm_car_years_filter span').click(function () {
        $('.lm_car_years_filter span').removeClass('lm-active');

        $(this).addClass('lm-active');
        var active_year = $.trim($(this).text());
        $('.lm-auto-catalog-original.models tr').show();

        if (!$(this).hasClass('show-years')) {
            $('.lm-auto-catalog-original.models .lm-car-years').each(function() {
                var year = $.trim($(this).text());
                var set_year = year.split('-');
                var start_year = parseInt(set_year[0]);
                var end_year = parseInt(set_year[1]);

                if (active_year && active_year < start_year || end_year && active_year > end_year || !end_year && !start_year ) {
                	$(this).parents('tr').hide();
                }
            });
        }

        var car_lm_type_filter = $('.lm_car_type_filter');
        var active_type =  $.trim(car_lm_type_filter.children('span.lm-filter-button.lm-active').text());

        if (active_type != $.trim(car_lm_type_filter.children('span.show-all').text())) {
        	$('.lm-auto-catalog-original.models .lm-car-type').each(function() {
        		if ($.trim($(this).text()) != active_type) {
        			$(this).parents('tr').hide();
                }
            });
        }
    });


    /**
     * go over each cell of type while being fixed cell of years
     *
     */

    $('.lm_car_type_filter span').click(function() {
        $('.lm_car_type_filter span').removeClass('lm-active');
        $(this).addClass('lm-active');

        var active_type = $.trim($(this).text());

        $('.lm-auto-catalog-original.models tr').show();

        if(!$(this).hasClass('show-all')){
            $('.lm-auto-catalog-original.models .lm-car-type').each(function() {
                var car_type = $.trim($(this).text());

                if(car_type != active_type){
                    $(this).parents('tr').hide();
                }
            });
        }

        var car_lm_year_filter = $('.lm_car_years_filter');
        var active_year =  $.trim(car_lm_year_filter.children('span.lm-filter-button.lm-active').text());

        if (active_year != $.trim(car_lm_year_filter.children('span.show-years').text())) {
        	$('.lm-auto-catalog-original.models .lm-car-years').each(function() {
        		var year = $.trim($(this).text());
                var year_begin_end = year.split('-');
                var start_year = parseInt(year_begin_end[0]);
                var end_year = parseInt(year_begin_end[1]);

                if (active_year && active_year < start_year || end_year && active_year > end_year || !end_year && !start_year ) {
                	$(this).parents('tr').hide();
                }
            });
        }
    });
});




