var bxdialogs = new Array();
var links = new Array();
var href = "/bitrix/components/linemedia.auto/tecdoc.auto.select/templates/vin.iblock/ajax_get_info.php?";
var auto_fields = new Array('year', 'month', 'horsepower', 'displacement');

function closeDialogs()
{
    for (i in bxdialogs) {
        bxdialogs[i].Close();
    }
}

function getListBrands(self, dialog)
{
    dialog.Show();
    /*понятия не имею, зачем нужна такая штука, но работает и ладно. смысл прост: если я закрываю диалог выбора модели, то я не хочу закрывать диалог выбора бренда, пусть сам закроется*/
    BX.addCustomEvent(dialog, 'onWindowUnRegister', BX.proxy(closeDialogs, window));
    bxdialogs['brand'] = dialog;
}

function changeBrand(self)
{
    var link = href + 'a=getBrand&MODIFICATIONS_SET='+MODIFICATIONS_SET;
    var dialog = new BX.CDialog({'content_url': link, 'width':'650', 'height':'600', 'min_width':'300', 'min_height':'400', 'resizable':false, 'draggable':true});
    dialog.Show();
    BX.addCustomEvent(dialog, 'onWindowUnRegister', BX.proxy(closeDialogs, window));
    bxdialogs['brand'] = dialog;
}


function getListModels(self, dialog, noset)
{
    dialog.Show();
    bxdialogs['model'] = dialog;
    BX.addCustomEvent(dialog, 'onWindowUnRegister', BX.proxy(closeDialogs, window));
    var id = $(self).attr('rel');
    var title = $(self).html();
    if (!noset) {
        SetBrand(id, title);
    }
}

function changeModel(self)
{
    var BrandID = $('#f_brand_id').val();

    var link = href + 'a=getModel&BrandID=' + BrandID;
    var dialog = new BX.CDialog({'content_url': link, 'width':'650', 'height':'600', 'min_width':'300', 'min_height':'400', 'resizable':false, 'draggable':true});
    bxdialogs['model'] = dialog;
    BX.addCustomEvent(dialog, 'onWindowUnRegister', BX.proxy(closeDialogs, window));
    dialog.Show();

}


function getListModifications(self, dialog, noset)
{
    dialog.Show();
    bxdialogs['modification'] = dialog;
    BX.addCustomEvent(dialog, 'onWindowUnRegister', BX.proxy(closeDialogs, window));
    var id = $(self).attr('rel');
    var title = $(self).html();
    if (!noset) {
        SetModel(id, title);
    }
}

function changeModification(self)
{
    var BrandID = $('#f_brand_id').val();
    var ModelID = $('#f_model_id').val();

    var link = href + 'a=GetModification&BrandID=' + BrandID + '&ModelID=' + ModelID;
    var dialog = new BX.CDialog({'content_url': link, 'width':'650', 'height':'600', 'min_width':'300', 'min_height':'400', 'resizable':false, 'draggable':true});
    bxdialogs['modification'] = dialog;
    BX.addCustomEvent(dialog, 'onWindowUnRegister', BX.proxy(closeDialogs, window));
    dialog.Show();

}




function SetBrand(BrandID, ItemTitle, TriggerEnabled)
{
    TriggerEnabled = (TriggerEnabled == false)?false:true;
    if (BrandID != undefined) {
        if (ItemTitle != undefined) {
            $('#f_brand').val(ItemTitle);
            $('#f_brand_id').val(BrandID);
            $('#td_brand').html('<strong>' + ItemTitle + '</strong> <small>(<a href="javascript: void(0);" onclick="changeBrand(this); return false;">' + langs['change'] + '</a>)</small>');
        }
        $("#tr_brand").show();
        $("#tr_model").hide();
        $("#tr_modification").hide();
        if(TriggerEnabled == true){
            $('#f_brand').trigger('change');
        }
    }
    return true;
}

function SetModel(ModelID, ItemTitle, TriggerEnabled)
{
    var BrandID = $('#f_brand_id').val();
    TriggerEnabled = (TriggerEnabled == false)?false:true;
    if (ModelID != undefined) {
        if (ItemTitle != undefined) {
            $('#f_model').val(ItemTitle);
            $('#f_model_id').val(ModelID);
            $('#td_model').html('<strong>' + ItemTitle + '</strong> <small>(<a href="javascript: void(0);" rel="' + ModelID + '" onclick="changeModel(this); return false;">' + langs['change'] + '</a>)</small>');
        }
        $("#tr_brand").show();
        $("#tr_model").show();
        $("#tr_modification").hide();
        if(TriggerEnabled == true){
            $('#f_model').trigger('change');
        }
    }
    return true;
}

function SetModification(ModificationID, ItemTitle, TriggerEnabled)
{
    var BrandID = $('#f_brand_id').val();
    var ModelID = $('#f_model_id').val();
    TriggerEnabled = (TriggerEnabled == false)?false:true;
    if (ModificationID != undefined && ModelID != undefined && BrandID != undefined) {
        if (ItemTitle != undefined) {
            $('#f_modification').val(ItemTitle);
            $('#f_modification_id').val(ModificationID);
            $('#td_modification').html('<strong>' + ItemTitle + '</strong> <small>(<a class="info" href="javascript: void(0);" rel="' + ModificationID + '" onclick="changeModification(this); return false;">' + langs['change'] + '</a>)</small>');
            closeDialogs();
        }
        $("#tr_brand").show();
        $("#tr_model").show();
        $("#tr_modification").show();
        if(TriggerEnabled == true){
            $('#f_modification').trigger('change');
        }
        GetModificationInfo(BrandID, ModelID, ModificationID);
    }
    return true;
}

function GetModificationInfo(BrandID, ModelID, ModificationID)
{

    $('#lm-auto-vin-field-year, #lm-auto-vin-field-month, #lm-auto-vin-field-horsepower, #lm-auto-vin-field-displacement').attr('disabled', true);

    $.ajax({
        async: false,
        type: 'GET',
        dataType: 'json',
        url: href + 'a=GetModificationInfo&BrandID=' + BrandID + '&ModelID=' + ModelID + '&ModificationID=' + ModificationID,
        complete: function(data) {
            $('#lm-auto-vin-field-year, #lm-auto-vin-field-month, #lm-auto-vin-field-horsepower, #lm-auto-vin-field-displacement').attr('disabled', false);
        },
        success: function(data) {

            if(data){
                $('#lm-auto-vin-field-year').val(data.begin_year);
                $('#lm-auto-vin-field-month').val(data.begin_month);
                $('#lm-auto-vin-field-horsepower').val(data.powerHP);
                $('#lm-auto-vin-field-displacement').val(data.ccm);
            }
            else {
            	if(data == null) {
            		$.each(auto_fields, function (key, value) {
            			$('#lm-auto-vin-field-' + value).val('');
            		});
            	}
            }
        }
    });
}
