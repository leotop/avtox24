$('#br_table a.info').live('mousedown',function(){
	var self = $(this);
	self.fancybox({type:'ajax',autoDimensions : true});

	setTimeout(function(){
			var brand = self.attr("rel");
			var title = self.html();
			GetModel(brand, title);
	},500)

 });

$('table.model_select a.info').live('mousedown',function(){
	var self = $(this);
	self.fancybox({type:'ajax',autoDimensions : true});

	setTimeout(function(){
			var brand = self.attr("rel");
			var title = self.html();
			GetModification(brand, title);
	},500)

});

$('#td_model a.info').live('mousedown',function(){
	var self = $(this);
	self.fancybox({type:'ajax',autoDimensions : true});
});

$('#td_modification a.info').live('mousedown',function(){
	var self = $(this);
    self.fancybox({type:'ajax',autoDimensions : true});
});

$('#getBrands').live('click', function() {
    $.fancybox.open($('#br_table'));
});



function GetBrand() {
    $('#f_brand').val(''); 
    $('#f_model').val('');
    $('#f_modification').val('');
    $('#f_brand_id').val(''); 
    $('#f_model_id').val('');
    $('#f_modification_id').val('');
    $.fancybox.open($('#br_table'));
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
		$("#tr_modification").hide();
    }
	return true;
}

function GetModification(ModelID, ItemTitle){
    var BrandID = $('#f_brand_id').val();
    if(ModelID != undefined && BrandID != undefined){
	$('#f_modification').val('');
	$('#f_modification_id').val('');	
	if(ItemTitle != undefined){
	    $('#f_model').val(ItemTitle);
	    $('#f_model_id').val(ModelID);
	    $('#td_model').html('<strong>' + ItemTitle + '</strong> <small>(<a class="info" href="/bitrix/components/linemedia.auto/auto.info/ajax_get_info.php?a=getModel&BrandID='+ BrandID +'" onclick="GetModel(\'' + BrandID + '\'); return false;">изменить</a>)</small>');
	}
	$("#tr_model").css('display', 'table-row');
    }
}

function SetModification(ModificationID, ItemTitle){
	var BrandID = $('#f_brand_id').val();
    var ModelID = $('#f_model_id').val();
    if(ModificationID != undefined && ModelID != undefined){
	$('#f_modification').val(ItemTitle);
	$('#f_modification_id').val(ModificationID);
	$('#td_modification').html('<strong>' + ItemTitle + '</strong> <small>(<a class="info" href="/bitrix/components/linemedia.auto/auto.info/ajax_get_info.php?a=GetModification&BrandID='+ BrandID + '&ModelID=' + ModelID + '" onclick="GetModification(\'' + ModelID + '\'); return false;">изменить</a>)</small>');
	$("#tr_modification").css('display', 'table-row');
    }
}
