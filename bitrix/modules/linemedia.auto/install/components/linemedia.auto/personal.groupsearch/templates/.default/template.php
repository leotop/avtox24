<?php 
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


global $APPLICATION;
$APPLICATION->AddHeadScript($templateFolder . DIRECTORY_SEPARATOR . 'js/vendor/jquery.ui.widget.js');
$APPLICATION->AddHeadScript($templateFolder . DIRECTORY_SEPARATOR . 'js/jquery.iframe-transport.js');
$APPLICATION->AddHeadScript($templateFolder . DIRECTORY_SEPARATOR . 'js/jquery.fileupload.js');
$APPLICATION->AddHeadScript($templateFolder . DIRECTORY_SEPARATOR . 'js/jquery.fileupload-process.js');
$APPLICATION->AddHeadScript($templateFolder . DIRECTORY_SEPARATOR . 'js/jquery.fileupload-validate.js');
$APPLICATION->SetAdditionalCSS($templateFolder . DIRECTORY_SEPARATOR . 'css/style.css');
$APPLICATION->SetAdditionalCSS($templateFolder . DIRECTORY_SEPARATOR . 'css/jquery.fileupload.css');
$APPLICATION->SetAdditionalCSS($templateFolder . DIRECTORY_SEPARATOR . 'css/jquery.fileupload-ui.css');
?>

<div style="margin-bottom: 50px">
    <div>
    <table>
     <?php foreach ($arResult['director'] as $massage => $value) {
     	        if ($value == null) {
                    continue;
                }?>
    
           <tr>
              <td>
                 <?php echo $massage . ' ' . $value; ?>
              </td>
           </tr>
    
    <?php }?>
    
    </table>
    </div>
    <div>
        <div style="margin-bottom: 20px">
          <table class="lm-suppliers-table">
          	<tr>
             <?php 
			 $counter = 0;
			 $count = count($arResult['suppliers']);
			 foreach ($arResult['suppliers'] as $title => $supplier) {

				$counter++;
				?>
                    <td class="suppliersID">
                    	<table>
                        	<tr>
 
                            	<td><input type="checkbox" id="<?= $supplier['id'] ?>"></td>
                                <td><?= $supplier['title'] ?></td>
                                <td>~ <?php if ($supplier['delivery'] >= 24) {
                                	              $days = round($supplier['delivery']/24);
                                	              $supplier['delivery'] = $days . ' ' . GetMessage('LM_AUTO_GROUP_SEARCH_DELIVERY_DAYS');
                                            } else {
                                 	              $supplier['delivery'] .= ' ' . GetMessage('LM_AUTO_GROUP_SEARCH_DELIVERY_HOURS');
                                            }
                                
                                            echo $supplier['delivery'];
                                       ?></td>
                                <td><? $GLOBALS['APPLICATION']->IncludeComponent('linemedia.auto:supplier.reliability.statistic', '.default',
                                    array(
                                            'SUPPLIER_ID' => $supplier['id'],
                                            'WIDTH'=>'400px',
                                            'HEIGHT'=>'200px'
                                        ),
                                    $component);
                                ?></td>
                         	
							
                            </tr>
                        </table>
                    </td>
                    
                    <? if($counter % 2 == 0 && $counter < 10) { ?>  </tr><tr> <? } ?>
                    <? if($counter % 2 == 0 && $counter >= 10) { ?>  </tr><tr class="row-hidden"> <? } ?>
                    <? if($counter == $count && $counter >= 10) { ?>  <tr>
                    	<td colspan="2" align='center'><a href="#" class="show-row-hidden"><?=GetMessage('LM_SHOW_ROW_BTN_TEXT')?></a></td>
                    </tr> <? } ?>
             <?php }?>
          </table>
        </div>
        <div style="width: 300px" class="pull-left">
            <textarea id="article-for-search" style="width: 300px; height: 103px; resize: none; margin-bottom: 17px;" placeholder="<?php echo GetMessage('LM_AUTO_GROUP_SEARCH_TEXT_AREA') ?>"></textarea>
            <div>
                <input type="button" onClick="goSearch('article');" value="<?php echo GetMessage('LM_AUTO_GROUP_SEARCH_DOWNLOAD_SPARES_FROM_INPUT_FIELD') ?>" class="load-items btn" id="article" style="width:170px; height:30px"/>
            </div>
        </div>
    </div>

    <div class="pull-left" style="padding-left: 50px; padding-top: 75px"><span><?php echo GetMessage('LM_AUTO_GROUP_SEARCH_OR') ?></span></div>

    <div style="width: 300px" class="pull-right">
        <div class="border-white-box" style="background-color: white; padding: 10px 8px 11px 8px">
            <p><?php echo GetMessage('LM_AUTO_GROUP_SEARCH_TYPE_FILE_MESSAGE') ?></p>
        </div>
        <br />
        <span class="btn btn-success fileinput-button">
            <i class="glyphicon glyphicon-plus"></i>
            <span><?php echo GetMessage('LM_AUTO_GROUP_SEARCH_UPLOAD_FILE') ?></span>
            <!-- The file input field used as target for the file upload widget -->
            <input id="fileupload" type="file" name="files" multiple>
        </span>
        <br /><br />
        <!-- The global progress bar -->
        <div id="progress" class="progress">
            <div class="progress-bar progress-bar-success"></div>
        </div>
        <!-- The container for the uploaded files -->
        <div id="files" class="files"></div>
    </div>
</div>
<div class="clear-fix"></div>

<div id="loadItems"></div>


<script>

$(document).ready(function () {

    /*$('.load-items').click(function () {
    	
    	var id = $.trim($(this).attr('id'));
    	var articles = 0;

        if (id == 'article') {
            articles = $.trim($('#article-for-search').val());
        }
     	    	
    	$.ajax({
    		type: 'POST',
    		data: {'load' : id, 'data' : articles},
    		url: '<?= $templateFolder . DIRECTORY_SEPARATOR . '../../ajax.php' ?>'
    	}).done(function(html) {

        	if ($('#loadItems').html()) {
        		$('#loadItems').html('');
        	}
        	
    		$('#loadItems').append(html);
    	});
    	
    });*/

    var url = '<?= $templateFolder . DIRECTORY_SEPARATOR . 'server/php/index.php' ?>';

    $('#fileupload').fileupload({
        url: url,
        dataType: 'json',
       // autoUpload: false,
        acceptFileTypes: /(\.|\/)(csv|xlsx|xls)$/i,
        maxFileSize: 5000000, // 5 MB
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: /Android(?!.*Chrome)|Opera/
            .test(window.navigator.userAgent),
        previewMaxWidth: 100,
        previewMaxHeight: 100,
        previewCrop: true
    }).on('click', function() {
        $('#files').html('');
    }).on('fileuploadadd', function (e, data) {

        data.context = $('<div/>').appendTo('#files');
        $.each(data.files, function (index, file) {
            var node = $('<p/>')
                    .append($('<span/>').text(file.name));
            node.appendTo(data.context);
        });
    }).on('fileuploadprocessalways', function (e, data) {
    	
        var index = data.index,
            file = data.files[index],
            node = $(data.context.children()[index]);       
       
        if (file.error) {
            node
                .append('<br>')
                .append($('<span class="text-danger"/>').text(file.error));
        }
    }).on('fileuploadprogressall', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#progress .progress-bar').css(
        		{'width' : progress + '%',  'height' : '2px', 'background-color' : 'green', 'margin' : '5px 0 0'}
        );
    }).on('fileuploaddone', function (e, data) {
        $.each(data.result.files, function (index, file) {
            if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
            }
        });
        goSearch('price');
    }).on('fileuploadfail', function (e, data) {   	
        $.each(data.files, function (index, file) {
            var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index])
                .append('<br>')
                .append(error);
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

    $(".show-row-hidden").on('click', function(e) {
        e.preventDefault();
        $(".row-hidden").toggle();
    });
});

function goSearch(type) {

    var articles = 0;

    if (type == 'article') {
        articles = $.trim($('#article-for-search').val());
    }

    var suppliers = new Array();

    $('.suppliersID input[type=checkbox]').each(function () {
         if ($(this).attr('checked')) {
             suppliers.push($(this).attr('id'));
         }
    });
    
    $.ajax({
        type: 'POST',
        data: {'load' : type, 'data' : articles, 'suppliers' : suppliers},
        url: '<?= $templateFolder . DIRECTORY_SEPARATOR . '../../ajax.php' ?>'
    }).done(function(html) {

        if ($('#loadItems').html()) {
            $('#loadItems').html('');
        }

        //console.log(html);
        
        $('#loadItems').append(html);

        checkParts();
        //hide_identical_positions();
        calculate_elem();

        buildFilter();
    });
}

function loadAnalogs(table_id, article, brand, quantity) {

    var suppliers = new Array();

    $('.suppliersID input[type=checkbox]').each(function () {
        if ($(this).attr('checked')) {
            suppliers.push($(this).attr('id'));
        }
    });

    /*
     загрузим аналоги если ранее не загружались
     */
    if(!$("#" + table_id).hasClass('ajax_loaded')) {

        $("#" + table_id + '-loader').show();

        $.ajax({
            type: 'GET',
            data: {'article' : article, 'brand' : brand, 'quantity' : quantity, 'table_id' : table_id, 'suppliers' : suppliers},
            url: '<?=$templateFolder?>/analogs.php'

        }).done(function(html) {

            // добавим только уникальные строки
            $(html).find('tr').each(function() {
                if($(this).hasClass("section-part")) {
                    if(!$("#" + $(this).attr('id')).length) {
                        if($('#' + table_id + ' .section-part').length) {
                            $(this).insertAfter('#' + table_id + ' .section-part:last');
                        } else {
                            $('#' + table_id).append($(this));
                        }
                        //$('#' + table_id + ' .section-part:last').append($(this));
                    }
                } else { // catalogs links
                    $('#' + table_id).append($(this));
                }

            });

            $("#" + table_id).addClass('ajax_loaded');
            $("#" + table_id + '-loader').hide();

            checkPartsQuantity(table_id);
        });
    }
}

function loadBrand(table_id, article, brand, quantity) {

    $("#" + table_id).removeClass('ajax_loaded');

    loadAnalogs(table_id, article, brand, quantity);
}

</script>

