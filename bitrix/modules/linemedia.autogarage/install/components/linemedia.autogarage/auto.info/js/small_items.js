var script_url = '/bitrix/components/linemedia/autoportal.auto.info/ajax_get_info_custom.php';function GetBrand(){
    $('#f_brand').val(''); 
    $('#f_model').val('');
    $('#f_modification').val('');
    $('#f_brand_id').val(''); 
    $('#f_model_id').val('');
    $('#f_modification_id').val('');
    $("#td_brand").html('<div class="lm_loading"></div> Ждите, идет загрузка...').load(script_url + '?a=getBrand');
    $("#tr_model").hide();
    $("#tr_modification").hide();
}

function GetModel(BrandID, ItemTitle){
    if(BrandID != undefined){
	$('#f_model').val('');
	$('#f_modification').val('');
	$('#f_model_id').val('');
	$('#f_modification_id').val('');
	if(ItemTitle != undefined){
	    $('#f_brand').val(ItemTitle);
	    $('#f_brand_id').val(BrandID); 
	    $('#td_brand').html('<strong>' + ItemTitle + '</strong> <small>(<a href="javascript: void(0);" onclick="GetBrand(); return false;">изменить</a>)</small>');
	}
	$("#tr_model").css('display', 'table-row');
	$("#td_model").html('<div class="lm_loading"></div> Ждите, идет загрузка...').load(script_url + '?a=getModel&BrandID=' + BrandID);
	$("#tr_modification").hide();
    }
}

function GetModification(ModelID, ItemTitle){
    var BrandID = $('#f_brand_id').val();
    if(ModelID != undefined && BrandID != undefined){
	$('#f_modification').val('');
	$('#f_modification_id').val('');	
	if(ItemTitle != undefined){
	    $('#f_model').val(ItemTitle);
	    $('#f_model_id').val(ModelID);
	    $('#td_model').html('<strong>' + ItemTitle + '</strong> <small>(<a href="javascript: void(0);" onclick="GetModel(\'' + BrandID + '\'); return false;">изменить</a>)</small>');
	}
	$("#tr_modification").css('display', 'table-row');
	$("#td_modification").html('<div class="lm_loading"></div> Ждите, идет загрузка...').load(script_url + '?a=GetModification&BrandID=' + BrandID + '&ModelID=' + ModelID);
    }
}

function SetModification(ModificationID, ItemTitle){
    var ModelID = $('#f_model_id').val();
    if(ModificationID != undefined && ModelID != undefined){
	$('#f_modification').val(ItemTitle);
	$('#f_modification_id').val(ModificationID);
	$('#td_modification').html('<strong>' + ItemTitle + '</strong> <small>(<a href="javascript: void(0);" onclick="GetModification(\'' + ModelID + '\'); return false;">изменить</a>)</small>');
    }
}