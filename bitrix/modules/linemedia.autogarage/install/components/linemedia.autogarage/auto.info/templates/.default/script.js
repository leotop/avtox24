var bxdialogs = new Array();
var links = new Array();
var href = "/bitrix/components/linemedia.autogarage/auto.info/ajax_get_info.php?";

function closeDialogs()
{
    for (i in bxdialogs) {
        bxdialogs[i].Close();
    }
}


function getListBrands(self, dialog)
{
    dialog.Show();
    bxdialogs['brand'] = dialog;
}

function changeBrand(self)
{
    bxdialogs['brand'].Show();
}


function getListModels(self, dialog, noset)
{
    dialog.Show();
    bxdialogs['model'] = dialog;
    
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
    
    dialog.Show();
    bxdialogs['model'] = dialog;
}


function getListModifications(self, dialog, noset)
{
    dialog.Show();
    bxdialogs['modification'] = dialog;
    
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
    
    dialog.Show();
    bxdialogs['modification'] = dialog;
}




function SetBrand(BrandID, ItemTitle)
{
    if (BrandID != undefined) {
        if (ItemTitle != undefined) {
            $('#f_brand').val(ItemTitle);
            $('#f_brand_id').val(BrandID);
            $('#td_brand').html('<strong>' + ItemTitle + '</strong> <small>(<a href="javascript: void(0);" onclick="changeBrand(this); return false;">' + langs['change'] + '</a>)</small>');
        }
        $("#tr_brand").show();
        $("#tr_model").hide();
        $("#tr_modification").hide();
    }
    return true;
}

function SetModel(ModelID, ItemTitle)
{
    var BrandID = $('#f_brand_id').val();
    if (ModelID != undefined) {
        if (ItemTitle != undefined) {
            $('#f_model').val(ItemTitle);
            $('#f_model_id').val(ModelID); 
            $('#td_model').html('<strong>' + ItemTitle + '</strong> <small>(<a href="javascript: void(0);" rel="' + ModelID + '" onclick="changeModel(this); return false;">' + langs['change'] + '</a>)</small>');
        }
        $("#tr_brand").show();
        $("#tr_model").show();
        $("#tr_modification").hide();
    }
    return true;
}

function SetModification(ModificationID, ItemTitle, url)
{
    var BrandID = $('#f_brand_id').val();
    var ModelID = $('#f_model_id').val();
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
    }
    return true;
}
