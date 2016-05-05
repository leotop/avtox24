$(document).ready(function() {
	
	$('.tecdoc-shortcut').click(function(e){
		e.preventDefault();
		
		var ids = $(this).data('group-ids').split(',');
		for (var key in ids) {
		    if (ids[key] == '') {
		        ids.splice(key, 1);
		    }
		}
		
		$('#lm-auto-tecdoc-catalog-groups li').each(function(){
			
			if(ids.length == 0 || ids.indexOf($(this).data('id').toString()) >= 0) {
				$(this).show();
				$(this).parents('li').show();
			} else {
				$(this).hide();
			}
		})
	})

    $('#lm-auto-edit-apply-for-all').click(function(){
        var input = $('#tecdoc-items-edit').find("#lm-td-parent-id");
        var saved_val = input.val();
        input.val(input.val()+':*');
        $.ajax({
            type:"POST",
            url:"/bitrix/components/linemedia.autotecdoc/tecdoc.catalog2/ajax.php?action=save_all",
            data: $('#tecdoc-items-edit').serialize(),
            success:function (html) {
                    alert(html);
            },
            error:function (data) {
                alert("save error");
            }
        });
        input.val(saved_val);
    });
    // Отправка формы с изменениями и изображением.
    $('#tecdoc-item-save').live('click', function() {
        $('#lm-auto-tecdoc-popup-frm').ajaxForm({
            type: 'post',
            success: function(text, status) {
                if (status == 'success') {
                     document.location = document.location;
                } else {
                    alert(text);
                }
            },
            error:function(xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });
        $('#lm-auto-tecdoc-popup-frm').trigger('submit');

        return false;
    });


    $('input#quick_search').quicksearch('.tecdoc ul li, .models .model_card, .tecdoc h2.letter, .tecdoc tbody tr, #lm-auto-tecdoc-catalog-groups li, .tecdoc .part_card');



    if ($("#lm-auto-tecdoc-catalog-groups").length > 0){
        $("#lm-auto-tecdoc-catalog-groups").treeview({
              animated: "false",
              collapsed: true,
              unique: false,
              persist: "cookie"
        });
    }

    // Отправка формы
    $('#tecdoc-items-edit').submit(function() {
        $.ajax({
            type: "POST",
            url: "/bitrix/components/linemedia.autotecdoc/tecdoc.catalog2/ajax.php?action=save_all",
            data: $(this).serialize(),
            success:function (html) {
                if (html == 'OK') {
                    alert('Ok');
                } else {
                    alert(html);
                }
            },
            error:function (data) {
                alert("save error");
            }
        });

        return false;
    });


    // Изменение элемента.
    $('.tecdoc-item-edit').click(function(event) {
        event.preventDefault();

        var type      = $('input[name="type"]').val();
        var source_id = $(this).data('id');
        var parent_id = $('input[name="parent_id"]').val();
        var set_id    = $('input[name="set_id"]').val();
        var mod_id    = $(this).data('mod-id');

        $.ajax({
            type: "POST",
            url: "/bitrix/components/linemedia.autotecdoc/tecdoc.catalog2/ajax.php?action=edit_window&type=" + type,
            data: {'source_id': source_id, 'parent_id': parent_id, 'set_id': set_id, 'mod_id': mod_id},
            success: function (html) {
                var params = {
                    title: langs['LM_AUTO_EDIT_MODE'],
                    content: html,
                    icon: 'head-block',
                    resizable: true,
                    draggable: true,
                    content_url:'/bitrix/components/linemedia.autotecdoc/tecdoc.catalog2/ajax.php?action=save',
                    buttons: [
                        '<input type="button" value="' + langs['LM_AUTO_SAVE'] + '" id="tecdoc-item-save" class="adm-btn-save">',
                        BX.CDialog.btnCancel
                    ]
                };
                (new BX.CDialog(params)).Show();
            },
			complete: function(XMLHttpRequest, textStatus, errorThrown) {
				$('.lm-page-loading').css({'display':'none'})
			},
            error: function (data) {
                alert("window error");
            }
        });
    });

    // Удаление элемента.
    $('.tecdoc-item-delete').click(function(event) {
        event.preventDefault();

        if (!confirm('Delete?')) {
            return;
        }
        var id = $(this).data('id');

        var element = $(this).closest('li,td,div');

        $.ajax({
            type: "POST",
            url: "/bitrix/components/linemedia.autotecdoc/tecdoc.catalog2/ajax.php?action=delete",
            data: {'id': id},
            success: function (html) {
                if (html == 'OK') {
                    element && element.remove();
                } else {
                    alert(html);
                }
            },
            error: function (data) {
                alert("window error");
            }
        });
    });


    // Добавление нового элемента.
    $('#lm-auto-edit-add').click(function(event){
        var type        = $('input[name="type"]').val();
        var parent_id   = $('input[name="parent_id"]').val();
        var set_id      = $('input[name="set_id"]').val();

        $.ajax({
            type: "POST",
            url: "/bitrix/components/linemedia.autotecdoc/tecdoc.catalog2/ajax.php?action=edit_window&type=" + type,
            data: {'parent_id':parent_id, 'set_id':set_id},
            success: function (html) {
                var params = {
                    content : html,
                    icon: 'head-block',
                    resizable: true,
                    draggable: true,
                    content_url: '/bitrix/components/linemedia.autotecdoc/tecdoc.catalog2/ajax.php?action=save',
                    buttons: [
                        '',
                        '<input type="button" value="' + langs['LM_AUTO_SAVE'] + '" id="tecdoc-item-save" class="adm-btn-save">',
                        BX.CDialog.btnCancel
                    ]
                };
                (new BX.CDialog(params)).Show();
            },
            error: function (data) {
                alert("window error");
            }
        });
    });


    // Выделение всех
    $('#lm-auto-select-all').click(function(event) {
        var checked = $(this).attr('checked') == 'checked' ? 'checked' : false;
        $('form#tecdoc-items-edit input[type=checkbox]').attr('checked', checked);
    });


    // Добавление подгруппы
    $('.tecdoc-item-add-child').click(function(event) {
        event.preventDefault();
        var parent_group_id = $(this).data('id');
        var type            = $('input[name="type"]').val();
        var parent_id       = $('input[name="parent_id"]').val();
        var set_id          = $('input[name="set_id"]').val();

        $.ajax({
            type: "POST",
            url: "/bitrix/components/linemedia.autotecdoc/tecdoc.catalog2/ajax.php?action=edit_window&type=" + type,
            data: {'parent_group_id': parent_group_id, 'parent_id':parent_id, 'set_id':set_id},
            success:function (html) {
                var params = {
                    content : html,
                    icon: 'head-block',
                    resizable: true,
                    draggable: true,
                    content_url:'/bitrix/components/linemedia.autotecdoc/tecdoc.catalog2/ajax.php?action=save',
                    buttons: [
                        '',
                        BX.CDialog.btnSave, BX.CDialog.btnCancel
                    ]
                };
                (new BX.CDialog(params)).Show();
                $('input[name = "out[parentNodeId]"]').val(parent_group_id);
            },
            error: function (data) {
                alert("window error");
            }
        });
    });


    var car_years = new Array();

    /**
     * populate array from existing years of manufacturing and remove duplicates
     *
     */
    $('.years').each( function () {

    	var begin = parseInt($(this).find('.year_from').data('year'));
    	var end = parseInt($(this).find('.year_to').data('year'));

    	while (begin <= end) {

    		if (car_years.indexOf(begin) == -1) {
    			car_years.push(begin);
    		}
    		++begin;
    	}
    });

    car_years.sort();

    var modern_appliances = new Array();           //associative array where boundary_year is a string (современные) => 1995
    var boundary_year = $.trim($('span.lm-active').text()); // extract text from button, named 'современные'
    var all_years = 'All years';
    if(typeof contemporaryYear != 'undefined') {
        modern_appliances[boundary_year] = contemporaryYear;
    }
    var trigger_cookie = true;

    /**
    * form chain of years
    */
    $('.tecdoc div.model_card').each(function() {

		var current_year_end = parseInt($(this).find('.year_to').data('year'));
		if (current_year_end <  modern_appliances[boundary_year]) {
        	$(this).hide();
        }

    });


    $.each(car_years, function (index, val) {
        var car_year_button = '<span class="lm-filter-button">' + val + '</span>';
        $('.lm_car_years_filter').append(car_year_button);

        if (trigger_cookie) {                             //by default set cookie as value => boundary_year (value see above)
        	$.cookie('active_year', boundary_year);
        	trigger_cookie = false;
        }
    });


    /**
     * examine whether or not element, having class tecdoc, has an empty cell
     */
    var current_el = $('.tecdoc div.model_card');
    var display_mod = true;

    while (current_el.length > 0) {
    	if (current_el.css('display') == 'inline-block')
    		display_mod = false;
    	current_el = current_el.next();
    }


    if ($.cookie('modif_display') && parseInt($.cookie('contemporary_year')) != 0) {
    	$.cookie('modif_display', false);
    }
    else {
    	$.cookie('modif_display', true);
    }


    /**
     * if @function mode_display returned true and we are looking through models which year of manufacturing ended up before 1995
     * we conceal button 'современные' end switch to button 'all years'
     */

    if ((display_mod && $('.tecdoc div').hasClass('model_card')) || $.cookie('contemporary_year') == 'allyears') {

    	 $('.lm_car_years_filter span:eq(1)').remove();
    	 $('span.show-years').addClass('lm-active');
    	 $('.tecdoc div.model_card').each(function () {
    		 $(this).show();

    	 });
    	 $.cookie('modif_display', true);
    }


    /**
     * go over years and set appropriate cookie we use further by looking through part of compound
     * named modification
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

        		var current_year_begin = parseInt($(this).find('.year_from').data('year'));
        		var current_year_end = parseInt($(this).find('.year_to').data('year'));

        		if (current_year_end < active_year) {
                	$(this).hide();
                }

            });

        } else {

        	if (!$(this).hasClass('show-years')) {

        		active_year = parseInt($.trim($(this).text()));
            	$.cookie('active_year', active_year);

            	$('.tecdoc div.model_card').each(function() {

            		var current_year_begin = parseInt($(this).find('.year_from').data('year'));
            		var current_year_end = parseInt($(this).find('.year_to').data('year'));

            		if (current_year_begin > active_year || current_year_end < active_year) {
                    	$(this).hide();
                    }

                });
            }
        	else {
        		$.cookie('active_year', all_years);
        	}
        }
    });


    /**
     * choose years by looking through parts
     */
    var years = new Array();
    var current_year = (new Date()).getFullYear();

    $('.tecdoc.modifications tr').each(function () {

    	var value = $.trim($(this).children('td:nth-child(5)').text());
    	
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

    var modern_appliances_mod = new Array();                                //settings resemble settings of model above
    var boundary_year_mod = $('.lm_type_years_filter span:eq(1)').text();
    if(typeof contemporaryYear != 'undefined') {
        modern_appliances_mod[boundary_year_mod] = contemporaryYear;
    }

    var pos = 2;  //position of current year, chosen by step above,that should be stored

    /**
     * filling chain of years by keeping year, chosen step above
     */
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

            	var text = $.trim($(this).children('td:nth-child(5)').text());
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

                	var text = $.trim($(this).children('td:nth-child(5)').text());
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


    //whether or not we are wandering inside modification without button 'современные'
    if ($('.lm_type_years_filter span').hasClass('lm-active') && $.cookie('modif_display') && $.cookie('contemporary_year') == 'allyears') {

    	$('.lm_type_years_filter span:eq(1)').remove();
    	if ($.trim($.cookie('active_year')) == $.trim(boundary_year_mod)) {

    		$('span.show-years').addClass('lm-active');
        	$('.tecdoc.modifications tr').show();
        	$.cookie('modif_display', false, { expires: -1 });
    	}
    }

    /**
     * by clicking on button choose year
     */
    $('.lm_type_years_filter span').click(function () {

        $('.lm_type_years_filter span').removeClass('lm-active');
        $(this).addClass('lm-active');
        $('.tecdoc.modifications tr').show();

        var active_year = 0;

        if ($.trim($(this).text()) == $.trim(boundary_year_mod)) {

        	active_year = modern_appliances_mod[boundary_year_mod];

        	 $('.tecdoc.modifications tr').each(function () {

             	var text = $.trim($(this).children('td:nth-child(5)').text());
             	var current_years = text.split('—');
                 var begin = parseInt(current_years[0].split('.')[1]);
                 var end = parseInt(current_years[1]) ? current_years[1].split('.')[1] : current_year;

                 if (active_year > end)
                 	$(this).hide();
             });
        } else {

        	active_year = parseInt($.trim($(this).text()));
            $('.tecdoc.modifications tr').each(function () {

            	var text = $.trim($(this).children('td:nth-child(5)').text());
            	var current_years = text.split('—');
                var begin = parseInt(current_years[0].split('.')[1]);
                var end = parseInt(current_years[1]) ? current_years[1].split('.')[1] : current_year;

                if (active_year < begin || active_year > end)
                	$(this).hide();
            });
        }

    });


    var part_tags = new Array();
    var concealCommodities = $.trim($('.tecdoc_tags_select_hidden').text());

    $('.tecdoc_product_item').each(function () {

    	var part_name = $.trim($(this).data('filter-name'));
        if(part_name != '' && part_tags.indexOf(part_name) == -1)
            	part_tags.push(part_name);
        });


    $.each(part_tags, function (index, val) {
         var part_name_button = '<span class="tecdoc_part_tag">' + val + '</span>';
         $('.tecdoc_tags_select').append(part_name_button);
    });


    $('.tecdoc_tags_select').hide();

    $.each([$('.tecdoc.parts tr'), $('.tecdoc div.part_card')], function () {
    	$(this).show();
    });

    if (concealCommodities == 'Y') {

    	$('.tecdoc_tags_select').show();
        $.each([$('.tecdoc.parts tr'), $('.tecdoc div.part_card')], function() {
        	$(this).hide();
        });
    }
    $('.tecdoc_part_tag').click(function(){

	   	var text = $.trim($(this).text());
        $('.tecdoc.parts tr, .tecdoc div.part_card').each(function () {

            var part_name = $.trim($(this).data('filter-name'));
            if (text == part_name)
                $(this).show();
            else
            	$(this).hide();
        });
	});


});
