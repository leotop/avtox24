$(document).ready(function(){
    /*
    $('.applicability-firm').click(function() {
        var id = $(this).attr('rel');
        $('.applicability-models').hide();
        $('.applicability-modifications').hide();
        $('#applicability-model-' + id).show();
    });
    
    $('.applicability-model').click(function() {
        var id = $(this).attr('rel');
        $('.applicability-modifications').hide();
        $('#applicability-modification-' + id).show();
    });
    */
    $('.lm-auto-vin-extra-header a').click(function() {
        $('#lm-auto-vin-extra-tbody').toggle('slow');
    });
    
    $('.lm-auto-vin-row-add').click(function() {
        var copy_tr = $('#lm-auto-vin-table-request tbody tr:first').clone();
        $(copy_tr).find('td:first').html('<a href="javascript: void(0);" class="lm-auto-vin-row-del"></a>');
        $(copy_tr).find('input').val('');
        $('#lm-auto-vin-table-request tbody tr:last').before(copy_tr);
    });
    
    $('.lm-auto-vin-row-del').live('click', function() {
        $(this).parents('tr').remove();
    });
});