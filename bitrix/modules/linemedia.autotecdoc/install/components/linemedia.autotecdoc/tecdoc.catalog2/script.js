$(function(){
    function addCustomField(){
        $('#addField').closest('div').append('<label><div><?=GetMessage('LM_AUTO_API_CUSTOM_FIELD_CODE')?></div><input type="text" name="custom[code][]" value=""></label>'+
        '<label><div><?=GetMessage('LM_AUTO_API_CUSTOM_FIELD_VALUE')?></div><input type="text" name="custom[value][]" value=""></label>');
    }
});