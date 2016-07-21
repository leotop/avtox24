
$(document).ready(function() {

 var years = new Array();
    var current_year = (new Date()).getFullYear();

    $('.tecdoc.modifications tr').each(function () {

    	alert(1);
    	var value = $.trim($(this).children('td:nth-child(4)').text());

    	if (parseInt(value)) {

    		var part_years = value.split('—');
            var begin = parseInt($.trim((part_years[0].split('.'))[1]));
            var tmp_end = $.trim(part_years[1].split('.')[1]);
            var end = parseInt(tmp_end) ? tmp_end : current_year;

            while (begin <= end ) {

            	if (years.indexOf(begin) == -1)
            		years.push(begin);
            	++begin;

            }
    	}

    });

    years.sort();

    var modern_appliances_mod = new Array();
    var boundary_year_mod = $('.lm_type_years_filter span:eq(1)').text();
    modern_appliances_mod[boundary_year_mod] = 1995;


    var pos = 2;

    for (var key in years) {

        var value = years[key];
        var years_button = '<span class="lm-filter-button">' + value + '</span>';
        $('.lm_type_years_filter').append(years_button);
        pos++;

        if (value == $.cookie('active_year')) {

        	$('span.lm-active').removeClass('lm-active');
        	$('.lm_type_years_filter span:nth-child(' + pos + ')').addClass('lm-active');

        	$('.tecdoc.modifications tr').show();

            $('.tecdoc.modifications tr').each(function () {

            	var text = $.trim($(this).children('td:nth-child(4)').text());
            	var current_years = text.split('—');
                var begin = parseInt(current_years[0].split('.')[1]);
                var end = parseInt(current_years[1]) ? current_years[1].split('.')[1] : current_year;

                if (value < begin || value > end)
                	$(this).hide();
            });

        } else {

        	if ($.trim($.cookie('active_year')) == $.trim(boundary_year_mod)) {

        		value =  modern_appliances_mod[boundary_year_mod];

        		$('span.lm-active').removeClass('lm-active');
            	$('.lm_type_years_filter span:eq(1)').addClass('lm-active');

            	$('.tecdoc.modifications tr').show();

                $('.tecdoc.modifications tr').each(function () {

                	var text = $.trim($(this).children('td:nth-child(4)').text());
                	var current_years = text.split('—');
                    var begin = parseInt(current_years[0].split('.')[1]);
                    var end = parseInt(current_years[1]) ? current_years[1].split('.')[1] : current_year;

                    if (value > end)
                    	$(this).hide();
                });
            } else {

            	if ($.cookie('active_year') == 'All years') {

            		$('span.lm-active').removeClass('lm-active');
                	$('span.show-years').addClass('lm-active');
                	$('.tecdoc.modifications tr').show();
            	}

            }
        }
    }


    if ($('.lm_type_years_filter span').hasClass('lm-active') && modif_display) {

		$('.lm_type_years_filter span:eq(1)').remove();
    	$('span.show-years').addClass('lm-active');
    	$('.tecdoc.modifications tr').show();
    	modif_display = false;

    }

   /**
    *
    * problem spot
    *
    */

    $('.lm_type_years_filter span').click(function () {

        $('.lm_type_years_filter span').removeClass('lm-active');
        $(this).addClass('lm-active');
        $('.tecdoc.modifications tr').show();

        var active_year = 0;

        if ($.trim($(this).text()) == $.trim(boundary_year_mod)) {

        	active_year = modern_appliances_mod[boundary_year_mod];

        	 $('.tecdoc.modifications tr').each(function () {

             	var text = $.trim($(this).children('td:nth-child(4)').text());
             	var current_years = text.split('—');
                 var begin = parseInt(current_years[0].split('.')[1]);
                 var end = parseInt(current_years[1]) ? current_years[1].split('.')[1] : current_year;

                 if (active_year > end)
                 	$(this).hide();
             });
        } else {

        	active_year = parseInt($.trim($(this).text()));
            $('.tecdoc.modifications tr').each(function () {

            	var text = $.trim($(this).children('td:nth-child(4)').text());
            	var current_years = text.split('—');
                var begin = parseInt(current_years[0].split('.')[1]);
                var end = parseInt(current_years[1]) ? current_years[1].split('.')[1] : current_year;

                if (active_year < begin || active_year > end)
                	$(this).hide();
            });
        }

    });
});
