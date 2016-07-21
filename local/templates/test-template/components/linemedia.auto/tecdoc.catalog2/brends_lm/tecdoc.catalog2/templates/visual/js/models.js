
$(document).ready(function() {
  var car_years = new Array();

    /**
     * populate array from existing years of manufacturing and remove duplicates
     *
     */


    $('.tecdoc div.model_card').each( function () {

    	var begin = parseInt($(this).find('div.years span.year_from').attr('data-year'));
    	var end = parseInt($(this).find('div.years span.year_to').attr('data-year'));

    	while (begin <= end) {

    		if (car_years.indexOf(begin) == -1) {
    			car_years.push(begin);
    		}
    		++begin;
    	}
    });

    car_years.sort();


    /**
    *
    * form chain of years
    *
    */


    var modern_appliances = new Array();
    var boundary_year = $.trim($('span.lm-active').text());
    var all_years = 'All years';
    modern_appliances[boundary_year] = 1995;
    var trigger_cookie = true;

    $('.tecdoc div.model_card').show();

    $('.tecdoc div.model_card').each(function(car_years) {

		var current_year_begin = parseInt($(this).find('div.years span.year_from').attr('data-year'));
		var current_year_end = parseInt($(this).find('div.years span.year_to').attr('data-year'));

		if (current_year_end <  modern_appliances[boundary_year]) {
        	$(this).closest('div').hide();
        }

    });



    for (var key in car_years) {

        var val = car_years[key];
        var car_year_button = '<span class="lm-filter-button">' + val + '</span>';
        $('.lm_car_years_filter').append(car_year_button);

        if (trigger_cookie) {
        	$.cookie('active_year', boundary_year);
        	trigger_cookie = false;
        }
    }


    function mode_display() {

    	var current_el = $('.tecdoc div.model_card');
    	while(current_el.length > 0) {
    		if (current_el.css('display') == 'inline-block')
    			return false;
    		current_el = current_el.next();
    	}

    	return true;

    }


    if (mode_display() && $('.tecdoc div').hasClass('model_card') && $.trim($.cookie('active_year')) == $.trim(boundary_year)) {

    	 $('.lm_car_years_filter span:eq(1)').remove();
    	 $('span.show-years').addClass('lm-active');
    	 $('.tecdoc div.model_card').show();
    	 var modif_display = true;
    }




    /**
     *
     *go over years
     */



    $('.lm_car_years_filter span').click(function () {

        $('.lm_car_years_filter span').removeClass('lm-active');
        $(this).addClass('lm-active');

        var active_year;
        $('.tecdoc div.model_card').show();

        if ($.trim($(this).text()) == $.trim(boundary_year)) {

        	active_year = modern_appliances[boundary_year];
        	$.cookie('active_year', boundary_year);

        	$('.tecdoc div.model_card').each(function() {

        		var current_year_begin = parseInt($(this).find('div.years span.year_from').attr('data-year'));
        		var current_year_end = parseInt($(this).find('div.years span.year_to').attr('data-year'));

        		if (current_year_end < active_year) {
                	$(this).closest('div').hide();
                }

            });

        } else {

        	if (!$(this).hasClass('show-years')) {

        		active_year = parseInt($.trim($(this).text()));
            	$.cookie('active_year', active_year);

            	$('.tecdoc div.model_card').each(function() {

            		var current_year_begin = parseInt($(this).find('div.years span.year_from').attr('data-year'));
            		var current_year_end = parseInt($(this).find('div.years span.year_to').attr('data-year'));

            		if (current_year_begin > active_year || current_year_end < active_year) {
                    	$(this).closest('div').hide();
                    }

                });
            }
        	else {
        		$.cookie('active_year', all_years);
        	}
        }
    });

});
